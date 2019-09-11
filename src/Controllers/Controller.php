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
        echo json_encode($latest);
        exit;
    }
}