<?php
require __DIR__ .'/vendor/autoload.php';
use  Kenneth\HackerNews\Repository\HackerNewsRepository;
use  Kenneth\HackerNews\Controllers\Controller;


$stories = new Controller(new HackerNewsRepository());
echo json_encode($stories->latest25(), JSON_PRETTY_PRINT);
exit;