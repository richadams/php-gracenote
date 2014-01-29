A simple PHP client for the [Gracenote Web API](https://developer.gracenote.com/web-api), which allows you to look up artists, albums, and tracks in the Gracenote database, and returns a number of metadata fields, including:

* Basic metadata; e.g. Artist Name, Album Title, Track Title.
* Descriptors; e.g. Genre, Origin, Mood, Tempo.
* Related content; e.g. Album Art, Artist Image, Biographies.

:exclamation: **_This is just example code to get you started on your own projects using Gracenote's API, and is not meant as an exhaustive wrapper of the full API._**

### Installation

Just copy the `php-gracenote` directory into your project, then include the `Gracenote.class.php` file.

    <?php
    include("./php-gracenote/Gracenote.class.php");

### Prerequisites

You will need a Gracenote Client ID from the [Gracenote Developer Portal](https://developer.gracenote.com/) to use the API.

Each installed application also needs to have a User ID, which may be obtained by registering your Client ID with the Gracenote API. To do this, do:

    $api = new Gracenote\WebAPI\GracenoteWebAPI($clientID, $clientTag);
    $userID = $api->register();

**This registration should be done only once per application to avoid hitting your API quota** (i.e. definitely do NOT do this before every query). The userID can be stored in persistent storage (e.g. on the filesystem) and used for all subsequent pygn function calls.

Once you have your Client ID and User ID, you can start making queries.

### Usage

First, initialize the object using your credentials and UserID.

    $api = new Gracenote\WebAPI\GracenoteWebAPI($clientID, $clientTag, $userID);

Then, to search for the Kings of Convenience track "Homesick" from the album "Riot On An Empty Street",

    $results = $api->searchTrack("Kings Of Convenience", "Riot On An Empty Street", "Homesick", Gracenote\WebAPI\GracenoteWebAPI::BEST_MATCH_ONLY);

The results are a PHP array containing the metadata information,

    array(1) {
      [0]=>
      array(13) {
        ["album_gnid"]=>
        string(41) "59247312-2ED193587EF0504C7A0C416ED66DA962"
        ["album_artist_name"]=>
        string(20) "Kings Of Convenience"
        ["album_title"]=>
        string(23) "Riot On An Empty Street"
        ["album_year"]=>
        string(4) "2004"
        ["genre"]=>
        array(3) {
          [0]=>
          array(2) {
            ["id"]=>
            int(25312)
            ["text"]=>
            string(18) "Alternative & Punk"
          }
          [1]=>
          array(2) {
            ["id"]=>
            int(35477)
            ["text"]=>
            string(10) "Indie Rock"
          }
          [2]=>
          array(2) {
            ["id"]=>
            int(25460)
            ["text"]=>
            string(9) "Indie Pop"
          }
        }
        ["album_art_url"]=>
        string(199) "https://web.content.cddbp.net/cgi-bin/content-thin?id=8D43DA988315CC43&client=2918400&class=cover&origin=front&size=medium&type=image/jpeg&tag=02JTBEWOCB2v-BpuCUwFcZ1gUckuGcwyqVqWFL2rLZjA.5FpKTJGAF5Q"
        ["artist_image_url"]=>
        string(186) "https://web.content.cddbp.net/cgi-bin/content-thin?id=797304D567E8970F&client=2918400&class=image&size=medium&type=image/jpeg&tag=02LSJZOY.aEoqkYZ4BK4Y0XdvB85jGGk1FbUGf2QY4BfDk7HgLFd.a0w"
        ["artist_bio_url"]=>
        string(178) "https://web.content.cddbp.net/cgi-bin/content-thin?id=22DA84B96832BF4F&client=2918400&class=biography&type=text/plain&tag=02WlrWiKA7BKEwFcnYa8O.qAy2LEwC8TTGfF2lKUFQuPxF0Zw7hTUO1Q"
        ["review_url"]=>
        string(175) "https://web.content.cddbp.net/cgi-bin/content-thin?id=4045DA976DB1DEFA&client=2918400&class=review&type=text/plain&tag=02ReD1hup0RUvCA7zQ1pfZebg17zj5X9fJQYgp71ntyQTR5IKfOCh81A"
        ["artist_era"]=>
        array(1) {
          [0]=>
          array(2) {
            ["id"]=>
            int(29483)
            ["text"]=>
            string(6) "2000's"
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
            int(29432)
            ["text"]=>
            string(8) "Male Duo"
          }
        }
        ["artist_origin"]=>
        array(2) {
          [0]=>
          array(2) {
            ["id"]=>
            int(29896)
            ["text"]=>
            string(11) "Scandinavia"
          }
          [1]=>
          array(2) {
            ["id"]=>
            int(29990)
            ["text"]=>
            string(6) "Norway"
          }
        }
        ["tracks"]=>
        array(1) {
          [0]=>
          array(6) {
            ["track_number"]=>
            int(1)
            ["track_gnid"]=>
            string(41) "59247313-E198021B46C38679362C35619E93396B"
            ["track_title"]=>
            string(8) "Homesick"
            ["track_artist_name"]=>
            string(20) "Kings Of Convenience"
            ["mood"]=>
            array(2) {
              [0]=>
              array(2) {
                ["id"]=>
                int(42949)
                ["text"]=>
                string(10) "Melancholy"
              }
              [1]=>
              array(2) {
                ["id"]=>
                int(65343)
                ["text"]=>
                string(16) "Light Melancholy"
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
                int(34289)
                ["text"]=>
                string(11) "Medium Slow"
              }
              [2]=>
              array(2) {
                ["id"]=>
                int(34311)
                ["text"]=>
                string(3) "60s"
              }
            }
          }
        }
      }
    }

_Note that URLs to related content (e.g. Album Art, Artist Image, etc) are not valid forever, so your application should download the content you want relatively soon after the lookup and cache it locally._ Additionally, if you don't do a BEST_MATCH_ONLY search, then the results may not contain the full collection of metadata (specifically it appears as though mood and tempo are missing).

If you don't know which album a track is on (or don't care which album version you get), you can simply leave that parameter blank,

    $results = $api->searchTrack("Moby", "", "Porcelin");

There are also convenience functions to look up just an Artist...,

    $results = $api->searchArtist("CSS");

(_This will return the same result array with metadata for the top album by CSS (which happens to be "Cansei De Ser Sexy" at time of writing), and the track info for each album._)

...or to look up just an Album,

    $results = $api->searchAlbum("Jaga Jazzist", "What We Must");

(_This will return an array with metadata for Jaga Jazzist's "What We Must" album, and metadata for each track on the album._)

You can also lookup an album based upon the TOC data,

    $results = $api->albumToc("150 20512 30837 50912 64107 78357 90537 110742 126817 144657 153490 160700 175270 186830 201800 218010 237282 244062 262600 272929");
