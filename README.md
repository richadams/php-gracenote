#php-gracenote

A simple PHP client for the <a href="http://www.gracenote.com">Gracenote</a> Music API, which can retrieve Artist, Album and Track metadata with the most common options.

This is basically the PHP equivilent to the <a href="https://github.com/cweichen/pygn">pygn project</a>.

php-gracenote allows you to look up artists, albums, and tracks in the Gracenote database, and returns a number of metadata fields, including:

* Basic metadata like Artist Name, Album Title, Track Title.
* Descriptors like Genre, Origin, Mood, Tempo.
* Related content like Album Art, Artist Image, Biographies.

##Installation

Just copy the `php-gracenote` directory into your project, then include the `Gracenote.class.php` file.

    <?php
    include("./php-gracenote/Gracenote.class.php");

##Getting Started

You will need a Gracenote Client ID to use this module. Please visit https://developer.gracenote.com to get yours.

Each installed application also needs to have a User ID, which may be obtained by registering your Client ID with the Gracenote API. To do this, do:

    $api = new Gracenote\WebAPI\GracenoteWebAPI($clientID, $clientTag); // If you already have a userID, you can specify as third parameter to constructor and skip this step.
    $userID = $api->register();

This registration should be done only once per application to avoid hitting your API quota (i.e. definitely do NOT do this before every query). The userID can be stored in persistent storage (e.g. on the filesystem) and used for all subsequent pygn function calls.

Once you have your Client ID and User ID, you can start making queries.

To search for the Moby track "Porcelin" from his album "Play",

    $results = $api->searchTrack("Moby", "Play", "Porcelin");

The results are a PHP array containing the metadata information,

    array(1) {
      [0]=>
      array(13) {
        ["album_gnid"]=>
        string(40) "5026977-5C6DC28B1E1ADB1D028FF248DDFAEB55"
        ["album_artist_name"]=>
        string(4) "Moby"
        ["album_title"]=>
        string(4) "Play"
        ["album_year"]=>
        string(4) "1999"
        ["genre"]=>
        array(3) {
          [0]=>
          array(2) {
            ["id"]=>
            int(35470)
            ["text"]=>
            string(11) "Electronica"
          }
          [1]=>
          array(2) {
            ["id"]=>
            int(25364)
            ["text"]=>
            string(22) "Electronica Mainstream"
          }
          [2]=>
          array(2) {
            ["id"]=>
            int(25665)
            ["text"]=>
            string(15) "Pop Electronica"
          }
        }
        ["album_art_url"]=>
        string(199) "https://web.content.cddbp.net/cgi-bin/content-thin?id=09A52178DC85EC3C&client=8311808&class=cover&origin=front&size=medium&type=image/jpeg&tag=02ZN.Tw8wfNAUu6it6mLiLDSViRnlvaEFZWbjCuOwENtGktsnA1oauYQ"
        ["artist_image_url"]=>
        string(186) "https://web.content.cddbp.net/cgi-bin/content-thin?id=C018FAD072939E99&client=8311808&class=image&size=medium&type=image/jpeg&tag=02Zid-KwnjTQF5IcTTtEz0ffcaiw59OA7fr-HEt4u3gyKwVx7s5HvPBg"
        ["artist_bio_url"]=>
        string(178) "https://web.content.cddbp.net/cgi-bin/content-thin?id=1274BA55D9C33B8E&client=8311808&class=biography&type=text/plain&tag=02S7b-pi.H9yei-m1W6UQAoL--ObBoOOB-nNa4Pa6MpXLniCk9JnB18A"
        ["review_url"]=>
        string(175) "https://web.content.cddbp.net/cgi-bin/content-thin?id=2BE8903E6FDA54AF&client=8311808&class=review&type=text/plain&tag=02FHvc-3RfDd7rxAHCydaUeuXTdOi64CvYbET4RChNV4XMFLWfLt4t0A"
        ["artist_era"]=>
        array(1) {
          [0]=>
          array(2) {
            ["id"]=>
            int(29484)
            ["text"]=>
            string(6) "1990's"
          }
        }
        ["artist_type"]=>
        array(2) {
          [0]=>
          array(2) {
            ["id"]=>
            int(29422)
            ["text"]=>
            string(4) "Male"
          }
          [1]=>
          array(2) {
            ["id"]=>
            int(29426)
            ["text"]=>
            string(4) "Male"
          }
        }
        ["artist_origin"]=>
        array(4) {
          [0]=>
          array(2) {
            ["id"]=>
            int(29889)
            ["text"]=>
            string(13) "North America"
          }
          [1]=>
          array(2) {
            ["id"]=>
            int(29908)
            ["text"]=>
            string(13) "United States"
          }
          [2]=>
          array(2) {
            ["id"]=>
            int(30199)
            ["text"]=>
            string(8) "New York"
          }
          [3]=>
          array(2) {
            ["id"]=>
            int(30634)
            ["text"]=>
            string(13) "New York City"
          }
        }
        ["tracks"]=>
        array(1) {
          [0]=>
          array(6) {
            ["track_number"]=>
            int(1)
            ["track_gnid"]=>
            string(40) "5026978-59020A61693879EB2A8BBD376B836091"
            ["track_title"]=>
            string(5) "Honey"
            ["track_artist_name"]=>
            string(0) ""
            ["mood"]=>
            array(2) {
              [0]=>
              array(2) {
                ["id"]=>
                int(42961)
                ["text"]=>
                string(10) "Energizing"
              }
              [1]=>
              array(2) {
                ["id"]=>
                int(43050)
                ["text"]=>
                string(15) "Arousing Groove"
              }
            }
            ["tempo"]=>
            array(3) {
              [0]=>
              array(2) {
                ["id"]=>
                int(34283)
                ["text"]=>
                string(12) "Medium Tempo"
              }
              [1]=>
              array(2) {
                ["id"]=>
                int(34291)
                ["text"]=>
                string(11) "Medium Fast"
              }
              [2]=>
              array(2) {
                ["id"]=>
                int(34320)
                ["text"]=>
                string(4) "100s"
              }
            }
          }
        }
      }
    }


Note that URLs to related content (e.g. Album Art, Artist Image, etc) are not valid forever, so your application should download the content you want relatively soon after the lookup and cache it locally.

If you don't know which album a track is on (or don't care which album version you get), you can simply leave that parameter blank:

	$results = $api->searchTrack("Moby", "", "Porcelin");

There are also convenience functions to look up just an Artist or just an Album.

	$results = $api->searchArtist("CSS");

Will return the same result array with metadata for the top album by CSS (which happens to be "Cansei De Ser Sexy" at time of writing), and the track info for each album.

	$results = $api->searchAlbum("Jaga Jazzist", "What We Must");

Will return a array with metadata for Jaga Jazzist's "What We Must" album, and metadata for each track on the album.
