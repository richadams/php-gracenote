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
echo "UserID = ".$userID."\n";

// Once you have the userID, you can search for tracks, artists or albums easily.
echo "\n\nSearch Tracks:\n";
$results = $api->searchTrack("Kings Of Convenience", "Riot On An Empty Street", "Homesick");
var_dump($results);

echo "\n\nSearch Best Track:\n";
$results = $api->searchTrack("Kings Of Convenience", "Riot On An Empty Street", "Homesick", Gracenote\WebAPI\GracenoteWebAPI::BEST_MATCH_ONLY);
var_dump($results);

echo "\n\nSearch Artist:\n";
$results = $api->searchArtist("Kings Of Convenience");
var_dump($results);

echo "\n\nSearch Album:\n";
$results = $api->searchAlbum("Kings Of Convenience", "Riot On An Empty Street");
var_dump($results);

echo "\n\nFetch Album:\n";
$results = $api->fetchAlbum("5026977-5C6DC28B1E1ADB1D028FF248DDFAEB55");
var_dump($results);

echo "\n\nAlbum Toc:\n";
$results = $api->albumToc("150 20512 30837 50912 64107 78357 90537 110742 126817 144657 153490 160700 175270 186830 201800 218010 237282 244062 262600 272929");
var_dump($results);
