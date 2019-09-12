# Hackernews api 

This api client is build using php

## Pre-requisites
- Php Web Server
- curl

## Dependencies
- [Guzzle Http](https://github.com/guzzle/guzzle)

## Installation
- Clone this repo into the web root of your web server
- `git clone https://github.com/MamlukiSn/hackernews.git`
- `cd hackernews`
- Run `composer install`
- Ensure that the uploads directory is writable by the web.

## Endpoints
Top 10 most occurring words in the titles of the last 25 stories http://localhost/hackernews/latest25.php

Top 10 most occurring words in the titles of the post of exactly the last week http://localhost/hackernews/lastweek.php

Top 10 most occurring words in titles of the last 600 stories of users with at
least 10.000 karma http://localhost/hackernews/topusers.php





