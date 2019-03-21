<?php

use Bnb\Linkr\Client as LinkrClient;

require_once '../vendor/autoload.php';

$credentials = json_decode(file_get_contents(__DIR__ . '/credentials.json'), true);

$client = new LinkrClient($credentials['url'], $credentials['key']);

try {
    $link = $client->shorten('https://bnb.re/with-a-very-long-url-name');
    $info = $client->info($link->alias);
    $client->delete($link->alias);
} catch (\Bnb\Linkr\ShortUrlException $e) {
    echo $e->getMessage();

    exit(99);
}