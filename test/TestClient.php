<?php

use Bnb\Linkr\Client as LinkrClient;

require_once '../vendor/autoload.php';

$client = new LinkrClient('https://s.bnb.re', 'TestApiKey');

try {
    $link = $client->shorten('https://bnb.re/with-a-very-long-url-name');

    var_dump($link);
} catch (\Bnb\Linkr\ShortUrlException $e) {
    echo $e->getMessage();

    exit(99);
}