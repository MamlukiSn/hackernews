<?php
/**
 * Created by PhpStorm.
 * User: Kenneth Kariuki
 * Date: 11/09/2019
 * Time: 14:15
 */

namespace Kenneth\HackerNews\Repository;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Promise;

/**
 * Class HackerNewsRepository
 *
 * @package \Kenneth\HackerNews
 */

class HackerNewsRepository
{

    public function sendRequest($url){
        $client  = new Client(['verify' => false]);
        // $response = $client->request('GET', 'https://hacker-news.firebaseio.com/v0/' . $url . '.json',['verify' => false]);
        // if ($response->getStatusCode() !== 200){
        //    throw new \Exception('Error fetching from API.');
        // }
        // return json_decode($response->getBody()->getContents());

        $request = new Request('GET', 'https://hacker-news.firebaseio.com/v0/' . $url . '.json');
        $promise = $client->sendAsync($request)->then(function ($res) {
            $response = json_decode($res->getBody()->getContents());

            // print_r($response);
            // die;

            return $response;
        });
        $response = $promise->wait();
        return $response;
    }

    public function sendParallelRequest($urls, $location){
        $client  = new Client(['verify' => false]);
        $promises = [];
        foreach ($urls as $url){
            $promises[$url] = $client->getAsync('https://hacker-news.firebaseio.com/v0/' .$location. $url . '.json');
        }

        $results = Promise\settle($promises)->wait();
        $output = [];
        foreach ($results as $key => $value){
            if ($value['state'] === 'fulfilled' ){
                $response = $value['value'];
                if ($response->getStatusCode() == 200){
                    $output[$key] = json_decode($response->getBody()->getContents());
                }
            }



        }

        return $output;
    }

    /**
     * @return mixed
     * @throws \Exception
     */

    public function getNewStories(){
        return $this->sendRequest('newstories');
    }

    /**
     * @return mixed
     * @throws \Exception
     */

    public function getTopStories(){
        return $this->sendRequest('topstories');
    }

    /**
     * @return mixed
     * @throws \Exception
     */

    public function getBestStories(){
        return $this->sendRequest('beststories');
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getSingleItem($id){
        return $this->sendRequest('item/'. $id);
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function getUser($name){
        return $this->sendRequest('user/'. $name);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getMaxitem(){
        return $this->sendRequest('maxitem');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getLatestStoriesByCount($count){
        return array_slice($this->getNewStories(), 0, $count, true);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getTitlesFromStories($count){

        $storyIds = $this->getLatestStoriesByCount($count);
        $oldFile = 'uploads/latest25.json';
        if (file_exists($oldFile)){
            $oldStories = file_get_contents($oldFile);
            $oldTitles = json_decode($oldStories, true);

            $removedStories = array_diff(array_keys($oldTitles), $storyIds);

            $addedStories = array_diff($storyIds, array_keys($oldTitles));
            if ($removedStories){
                foreach ($removedStories as $story){
                    unset($oldTitles[$story]);
                }
            }

            if ($addedStories){
                $newStories = $this->sendParallelRequest($addedStories, 'item/');
                foreach ($newStories as $story){
                    $oldTitles[$story->id] = $story->title;
                }

            }
            file_put_contents($oldFile, json_encode($oldTitles, JSON_PRETTY_PRINT));
            return $oldTitles;

        }else{

            $stories = $this->sendParallelRequest($storyIds, 'item/');
            $titles = [];
            foreach ($stories as $story) {

                $titles[$story] = $story->title;
            }
            file_put_contents($oldFile, json_encode($titles, JSON_PRETTY_PRINT));
            return $titles;
        }

    }

    /**
     * @param $titles
     * @return array
     * @throws \Exception
     */
    public function getMostPopularWordsFromTitle(array $titles){
        //todo replace special characters

        if (!empty($titles)){
            $words = explode(' ', strtolower(str_replace(array(':', '\\', '-', '*'), '', join($titles))));
            $popularWords = array_count_values($words);
            arsort($popularWords);
            return array_slice($popularWords, 0, 10, true);
        }
        return [];


    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getTopWordsFromLast25($count){
        $titles = $this->getTitlesFromStories($count);
        return $this->getMostPopularWordsFromTitle($titles);

    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getAllStories(){

        $allStories = array_unique(array_merge($this->getNewStories(), $this->getBestStories(), $this->getBestStories()),SORT_REGULAR);
        sort($allStories);
        return $allStories;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return array
     * @throws \Exception
     */

    public function getBetweenDate($startDate, $endDate){


        $allStories = $this->getAllStories();

        $oldFile = 'uploads/lastweek.json';
        if (file_exists($oldFile)){
            $oldStories = file_get_contents($oldFile);
            $oldTitles = json_decode($oldStories, true);

            $removedStories = array_diff(array_keys($oldTitles), $allStories);

            $addedStories = array_diff($allStories, array_keys($oldTitles));
            if ($removedStories){
                foreach ($removedStories as $story){
                    unset($oldTitles[$story]);
                }
            }

            if ($addedStories){

                foreach ($addedStories as $story){
                    $singleStory = $this->getSingleItem($story);
                    if (($singleStory->time >= $startDate) && ($singleStory->time <= $endDate)){
                        // $titles[$story] = $singleStory->title;
                        $oldTitles[$story] = $singleStory->title;
                    }
                    if ($singleStory->time > $endDate){
                        break;
                    }
                }

            }
            file_put_contents($oldFile, json_encode($oldTitles, JSON_PRETTY_PRINT));
            return $oldTitles;

        }else{
            $titles = [];
            foreach ($allStories as $story){
                $singleStory = $this->getSingleItem($story);
                if ($singleStory->time >= $startDate && $singleStory->time <= $endDate){
                    $titles[$story] = $singleStory->title;
                }
                if ($singleStory->time > $endDate){
                    break;
                }
            }
            file_put_contents($oldFile, json_encode($titles, JSON_PRETTY_PRINT));

            return $titles;
        }

    }

    /**
     * @param $startDate
     * @param $endDate
     * @return array
     * @throws \Exception
     */
    public function getTopWordsFromLastWeek($startDate, $endDate){
        $titles = $this->getBetweenDate($startDate, $endDate);
        return $this->getMostPopularWordsFromTitle($titles);

    }

    /**
     * @param $karma
     * @param $count
     * @return array
     * @throws \Exception
     */
    
    public function getTopWordsFromUserStories($karma, $count){
        $titles = $this->getStoriesWithUsers($karma, $count);
        if (count($titles > $count)) {
            $titles = array_slice($titles, 0, $count, true);
        }

        return $this->getMostPopularWordsFromTitle($titles);
    }


    public function getStoriesWithUsers($karma,$count){
        ini_set('max_execution_time', '30000');

        $stories = $this->getAllStories();
        $allStories = array_slice($stories, 0, $count, true);

        $titles = [];

        $oldFile = 'uploads/topusers.json';
        if (file_exists($oldFile)){
            $oldStories = file_get_contents($oldFile);
            $oldTitles = json_decode($oldStories, true);

            $removedStories = array_diff(array_keys($oldTitles), $allStories);

            $addedStories = array_diff($allStories, array_keys($oldTitles));
            if ($removedStories){
                foreach ($removedStories as $story){
                    unset($oldTitles[$story]);
                }
            }

            if ($addedStories){

                foreach ($addedStories as $story){
                    $singleStory = $this->getSingleItem($story);
                    if ($singleStory && $singleStory->by) {
                        $user = $this->getUser($singleStory->by);
                        if ($user && ($user->karma >= $karma)) {
                             $oldTitles[$story] = $singleStory->title;
                        }
                    }
                    
                }

            }
            file_put_contents($oldFile, json_encode($oldTitles, JSON_PRETTY_PRINT));
            return $oldTitles;

        }else{
            
            foreach ($allStories as $story){
                $singleStory = $this->getSingleItem($story);
                if ($singleStory && $singleStory->by) {
                    $user = $this->getUser($singleStory->by);
                    if ($user && ($user->karma >= $karma)) {
                         $titles[$story] = $singleStory->title;
                    }
                }
                
            }
            file_put_contents($oldFile, json_encode($titles, JSON_PRETTY_PRINT));

            return $titles;
        }
    }

}