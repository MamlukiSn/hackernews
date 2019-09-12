<?php
/**
 * Created by PhpStorm.
 * User: 001921
 * Date: 11/09/2019
 * Time: 16:36
 */

namespace Kenneth\HackerNews\Controllers;

use Kenneth\Hackernews\Repository\HackerNewsRepository;


class Controller
{
    protected  $repository;
    public function __construct(HackerNewsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function latest25(){
        $latest = $this->repository->getTopWordsFromLast25(25);
        return $latest;
    }

    public function latestWeek(){
        $weekStart =  strtotime('-1 day',strtotime('monday last week'));
        $weekEnd = strtotime('+2 day',strtotime('sunday last week'));
        $latest = $this->repository->getTopWordsFromLastWeek($weekStart, $weekEnd);
        return $latest;
    }

    public function topUsers(){
        $latest = $this->repository->getTopWordsFromUserStories(1000, 600);
        return $latest;
    }
}