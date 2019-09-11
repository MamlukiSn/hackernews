<?php
require __DIR__ .'/vendor/autoload.php';
use  Kenneth\HackerNews\Repository\HackerNewsRepository;
use  Kenneth\HackerNews\Controllers\Controller;

//$router = new AltoRouter();
//$router->setBasePath('');
//$router->map( 'GET', '/latest25/', 'Controller#latest25' );
?>

<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>
<div class="container">
    <?php
    $stories = new Controller(new HackerNewsRepository());
    print_r($stories->latest25());
    die;
    print_r($stories->getMostOcuuringLatest(25));


    ?>
</div>
</body>
</html>
