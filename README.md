# wiki-api-client
Simple client for get data from mediawiki API

## Installation

Run composer 
```bash
composer install
```

Make config
```bash
cp Config.tpl.php Config.php
```

Fill params
```php
const API_ENDPOINT = 'https://www.mediawiki.org/w/api.php';
const API_LOGIN = '';
const API_PASSWORD = '';
const API_COOKIE_FILE = __DIR__ . '/cookie.jar';
```

## Usage
Init client
```php
$client = new \ixapek\WikiApiClient\Client(
    \ixapek\WikiApiClient\Config::API_ENDPOINT, 
    \ixapek\WikiApiClient\Config::API_LOGIN, 
    \ixapek\WikiApiClient\Config::API_PASSWORD, 
    \ixapek\WikiApiClient\Config::API_COOKIE_FILE
);
```

Enjoy
```php

// https://www.mediawiki.org/w/api.php?action=query&meta=tokens&type=login&format=json

$client->request([
    'action' => 'query',
    'meta'   => 'tokens',
    'type'   => 'login',
]);
```