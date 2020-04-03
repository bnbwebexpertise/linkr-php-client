# Linkr PHP Client

For https://github.com/LINKIWI/linkr

## Usage

```php

$client = new \Bnb\Linkr\Client('https://linkr.foo.bar', 'MyApiKey');

// Create a link - Returns a Bnb\Linkr\ShortUrl
$link = $client->shorten('https://foo.bar/with/a/very/long/url');

// Get link info - Returns a Bnb\Linkr\ShortUrl
$link = $client->info('myAlias');

// Delete a link by alias
$client->delete($link->alias);

```

If behind a proxy, configure once at runtim :

```
// Anonymous
Linkr::setHttpProxy('http://10.10.10.10');

// Authenticated
Linkr::setHttpProxy('http://10.10.10.10', 'user', 'password');
```
