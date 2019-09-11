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

/**
 * Class HackerNewsRepository
 *
 * @package \Kenneth\HackerNews
 */

class HackerNewsRepository
{

    public function sendRequest($url){
        $client  = new Client();
        $response = $client->request('GET', 'https://hacker-news.firebaseio.com/v0/' . $url . '.json',['verify' => false]);
        if ($response->getStatusCode() !== 200){
           throw new \Exception('Error fetching from API.');
        }
        return json_decode($response->getBody()->getContents());
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

        $stories = $this->getLatestStoriesByCount($count);
        $oldFile = 'uploads/latest25.json';
        if (file_exists($oldFile)){
            $oldStories = file_get_contents($oldFile);
            $oldTitles = json_decode($oldStories, true);

            $removedStories = array_diff(array_keys($oldTitles), $stories);

            $addedStories = array_diff($stories, array_keys($oldTitles));
            if ($removedStories){
                foreach ($removedStories as $story){
                    unset($oldTitles[$story]);
                }
            }

            if ($addedStories){
                foreach ($addedStories as $story){
                    $singleStory = $this->getSingleItem($story);
                    $oldTitles[$story] = $singleStory->title;
                }

            }
            file_put_contents($oldFile, json_encode($oldTitles, JSON_PRETTY_PRINT));
            return $oldTitles;

        }else{
            $titles = [];
            foreach ($stories as $story) {

                $singleStory = $this->getSingleItem($story);

                $titles[$story] = $singleStory->title;
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
     * @param $startDate
     * @param $endDate
     * @return array
     * @throws \Exception
     */

    public function getBetweenDate($startDate, $endDate){

        $newStories = $this->getNewStories();
        $bestStories = $this->getBestStories();
        $topStories = $this->getTopStories();

        $allStories = array_unique(array_merge($newStories, $bestStories, $topStories),SORT_REGULAR);
        sort($allStories);
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
            sort($allStories);
            if ($addedStories){

                foreach ($addedStories as $story){
                    $singleStory = $this->getSingleItem($story);
                    if ($singleStory->time >= $startDate && $singleStory->time <= $endDate){
                        $titles[$story] = $singleStory->title;
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

}