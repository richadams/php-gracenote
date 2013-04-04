<?php

include("./php-gracenote/Gracenote.class.php");

// You will need a Gracenote Client ID to use this. Visit https://developer.gracenote.com/
// for more information.

$clientID  = ""; // Put your Client ID here.
$clientTag = ""; // Put your Client Tag here.

/* You first need to register your client information in order to get a userID.
Best practice is for an application to call this only once, and then cache the userID in
persistent storage, then only use the userID for subsequent API calls. The class will cache
it for just this session on your behalf, but you should store it yourself. */
$api = new Gracenote\WebAPI\GracenoteWebAPI($clientID, $clientTag); // If you have a userID, you can specify as third parameter to constructor.
$userID = $api->register();

echo "\n\nSearch Best Track:\n";

// doing a new search, create a new search object and feed it the API object
$search = new Gracenote\WebAPI\Search($api);
$search->showBest();
$response = $search->search('Moby','Play', 'Porcelin');
var_dump($response);

echo "\n\nSearch Artist:\n";
$search = new Gracenote\WebAPI\Search($api);
$response = $search->search('Moby');
var_dump($response);

echo "\n\nSearch Album:\n";
$search = new Gracenote\WebAPI\Search($api);
$response = $search->search('Moby', 'Play');
var_dump($response);

echo "\n\nFetch Album:\n";
$search = new Gracenote\WebAPI\Search($api);
$response = $search->getAlbum('5026977-5C6DC28B1E1ADB1D028FF248DDFAEB55');
var_dump($response);

echo "\n\nAlbum Toc:\n";
$results = $api->albumToc("150 20512 30837 50912 64107 78357 90537 110742 126817 144657 153490 160700 175270 186830 201800 218010 237282 244062 262600 272929");
var_dump($results);

