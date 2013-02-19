Gracenote API PHP Library
=====================
This is a library to abstract the Gracenote API 

Installation
------------
  1. edit `composer.json` file with following contents:

     ```json
     "require": {
        "gracenote/gracenote-tha": "dev-master"
     }
     ```
  2. install composer via `curl -s http://getcomposer.org/installer | php` (on windows, download
     http://getcomposer.org/installer and execute it with PHP)
  3. run `php composer.phar install`

Use
---
Configure the service
```php
use Gracenote\Service\Gracenote;

Gracenote::configure($clientId, $userId);
```

If you do not have a userId you may fetch one by setting your clientId and running
```php
Gracenote::register();
```

Run a query
```php
$simpleXmlResult = Gracenote::query('ALBUM_SEARCH', array(
    'mode' => 'SINGLE_BEST_COVER',
    'parameters' => array(
        'ARTIST' => 'the bengals',
        'ALBUM_TITLE' => 'different light',
        'TRACK_TITLE' => 'walk like an egyptian'
    ),
    'options' => array(
        'SELECT_EXTENDED' => 'COVER,REVIEW,ARTIST_BIOGRAPHY,ARTIST_IMAGE,ARTIST_OET,MOOD,TEMPO',
        'SELECT_DETAIL' => 'GENRE:3LEVEL,MOOD:2LEVEL,TEMPO:3LEVEL,ARTIST_ORIGIN:4LEVEL,ARTIST_ERA:2LEVEL,ARTIST_TYPE:2LEVEL',
    ),
));
```

The Gracenote API will return a SimpleXmlElement of the results
