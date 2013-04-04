<?php
namespace Gracenote\WebAPI;

// You will need a Gracenote Client ID to use this. Visit https://developer.gracenote.com/ for info.

// Defaults
if (!defined("GN_DEBUG")) { define("GN_DEBUG", false); }

// Dependencies
include(dirname( __FILE__ )."/GracenoteError.class.php");
include(dirname( __FILE__ )."/HTTP.class.php");
include(dirname( __FILE__ )."/Search.class.php");

class GracenoteWebAPI
{
    // Constants
    const BEST_MATCH_ONLY = 0; // Will put API into "SINGLE_BEST" mode.
    const ALL_RESULTS     = 1;

    // Members
    private $_clientID  = null;
    private $_clientTag = null;
    private $_userID    = null;
    public  $_lastResponse = null;
    private $_apiURL    = "https://[[CLID]].web.cddbp.net/webapi/xml/1.0/";

    // Constructor
    public function __construct($clientID, $clientTag, $userID = null)
    {
        // Sanity checks
        if ($clientID === null || $clientID == "")   { throw new GNException(GNError::INVALID_INPUT_SPECIFIED, "clientID"); }
        if ($clientTag === null || $clientTag == "") { throw new GNException(GNError::INVALID_INPUT_SPECIFIED, "clientTag"); }

        $this->_clientID  = $clientID;
        $this->_clientTag = $clientTag;
        $this->_userID    = $userID;
        $this->_apiURL    = str_replace("[[CLID]]", $this->_clientID, $this->_apiURL);
    }

    // Will register your clientID and Tag in order to get a userID. The userID should be stored
    // in a persistent form (filesystem, db, etc) otherwise you will hit your user limit.
    public function register($clientID = null)
    {
        // Use members from constructor if no input is specified.
        if ($clientID === null) { $clientID = $this->_clientID."-".$this->_clientTag; }

        // Make sure user doesn't try to register again if they already have a userID in the ctor.
        if ($this->_userID !== null)
        {
            echo "Warning: You already have a userID, no need to register another. Using current ID.\n";
            return $this->_userID;
        }

        // Do the register request
        $request = "<QUERIES>
                       <QUERY CMD=\"REGISTER\">
                          <CLIENT>".$clientID."</CLIENT>
                       </QUERY>
                    </QUERIES>";
        $http = new HTTP($this->_apiURL);
        $response = $http->post($request);
        
        $response = $this->_checkResponse($response);
        
        
        // Cache it locally then return to user.
        $this->_userID = (string)$response->RESPONSE->USER;
        return $this->_userID;
    }

    // This retrieves ONLY the OET data from a fetch, and nothing else. Will return an array of that data.
    public function fetchOETData($gn_id)
    {
        // Sanity checks
        if ($this->_userID === null) { $this->register(); }

        $body = "<GN_ID>".$gn_id."</GN_ID>
                 <OPTION>
                     <PARAMETER>SELECT_EXTENDED</PARAMETER>
                     <VALUE>ARTIST_OET</VALUE>
                 </OPTION>
                 <OPTION>
                     <PARAMETER>SELECT_DETAIL</PARAMETER>
                     <VALUE>ARTIST_ORIGIN:4LEVEL,ARTIST_ERA:2LEVEL,ARTIST_TYPE:2LEVEL</VALUE>
                 </OPTION>";

        $data = $this->_constructQueryRequest($body, "ALBUM_FETCH");
        $request = new HTTP($this->_apiURL);
        $response = $request->post($data);
        $xml = $this->_checkResponse($response);

        $output = array();
        $output["artist_origin"] = ($xml->RESPONSE->ALBUM->ARTIST_ORIGIN) ? $this->_getOETElem($xml->RESPONSE->ALBUM->ARTIST_ORIGIN) : "";
        $output["artist_era"]    = ($xml->RESPONSE->ALBUM->ARTIST_ERA)    ? $this->_getOETElem($xml->RESPONSE->ALBUM->ARTIST_ERA)    : "";
        $output["artist_type"]   = ($xml->RESPONSE->ALBUM->ARTIST_TYPE)    ? $this->_getOETElem($xml->RESPONSE->ALBUM->ARTIST_TYPE)  : "";
        return $output;
    }

    // Fetches album metadata based on a table of contents.
    public function albumToc($toc)
    {
        // Sanity checks
        if ($this->_userID === null) { $this->register(); }

        $body = "<TOC><OFFSETS>".$toc."</OFFSETS></TOC>";

        $data = $this->_constructQueryRequest($body, "ALBUM_TOC");
        return $this->_execute($data);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////

    // Simply executes the query to Gracenote WebAPI
    public function _execute($data)
    {
        $request = new HTTP($this->_apiURL);
        $response = $request->post($data);
        $this->_lastResponse = $response;        
        return $this->_parseResponse($response);
    }

    // This will construct the gracenote query, adding in the authentication header, etc.
    public function _constructQueryRequest($body, $command = "ALBUM_SEARCH")
    {
        return
            "<QUERIES>
                <AUTH>
                    <CLIENT>".$this->_clientID."-".$this->_clientTag."</CLIENT>
                    <USER>".$this->_userID."</USER>
                </AUTH>
                <QUERY CMD=\"".$command."\">
                    ".$body."
                </QUERY>
            </QUERIES>";
    }

    // Constructs the main request body, including some default options for metadata, etc.
    public function _constructQueryBody($options)
    {
        if ($this->_userID === null) { $this->register(); }
        $body = "";

        // If a fetch scenario, user the Gracenote ID.
        if ($options['command'] == "ALBUM_FETCH")
        {
            $body .= "<GN_ID>".$options['gn_id']."</GN_ID>";
        }
        // Otherwise, just do a search.
        else
        {
            // Only get the single best match if that's what the user wants.
            if ($options['matchmode'] == self::BEST_MATCH_ONLY) { $body .= "<MODE>SINGLE_BEST</MODE>"; }

            // If a search scenario, then need the text input
            if ($options['artist_name'] != "") { $body .= "<TEXT TYPE=\"ARTIST\">".$options['artist_name']."</TEXT>"; }
            if ($options['album_title'] != "")  { $body .= "<TEXT TYPE=\"ALBUM_TITLE\">".$options['album_title']."</TEXT>"; }
            if ($options['track_title'] != "")  { $body .= "<TEXT TYPE=\"TRACK_TITLE\">".$options['track_title']."</TEXT>"; }
            // add paging
            $body .= '<RANGE>
                        <START>'.$options['paging_start'].'</START>
                        <END>'.$options['paging_end'].'</END>
                      </RANGE>';
    
        }
        
        // Include extended data.
        $body .= "<OPTION>
                      <PARAMETER>SELECT_EXTENDED</PARAMETER>
                      <VALUE>COVER,REVIEW,ARTIST_BIOGRAPHY,ARTIST_IMAGE,ARTIST_OET,MOOD,TEMPO</VALUE>
                  </OPTION>";

        // Include more detailed responses.
        $body .= "<OPTION>
                      <PARAMETER>SELECT_DETAIL</PARAMETER>
                      <VALUE>GENRE:3LEVEL,MOOD:2LEVEL,TEMPO:3LEVEL,ARTIST_ORIGIN:4LEVEL,ARTIST_ERA:2LEVEL,ARTIST_TYPE:2LEVEL</VALUE>
                  </OPTION>";

        // Return all sizes, parser will handle it 
        $body .= "<OPTION>
                      <PARAMETER>COVER_SIZE</PARAMETER>
                      <VALUE>LARGE,XLARGE,SMALL,MEDIUM,THUMBNAIL</VALUE>
                  </OPTION>";

        return $body;
    }

    // Check the response for any Gracenote API errors.
    protected function _checkResponse($response = null)
    {
        // Response is in XML, so attempt to load into a SimpleXMLElement.
        $xml = null;
        try
        {
            $xml = new \SimpleXMLElement($response);
        }
        catch (Exception $e)
        {
            throw new GNException(GNError::UNABLE_TO_PARSE_RESPONSE);
        }

        // Get response status code.
        $status = (string) $xml->RESPONSE->attributes()->STATUS;

        // Check for any error codes and handle accordingly.
        switch ($status)
        {
            case "ERROR":    throw new GNException(GNError::API_RESPONSE_ERROR, (string) $xml->MESSAGE); break;
            case "NO_MATCH": throw new GNException(GNError::API_NO_MATCH); break;
            default:
                if ($status != "OK") { throw new GNException(GNError::API_NON_OK_RESPONSE, $status); }
        }

        return $xml;
    }

    // This parses the API response into a PHP Array object.
    protected function _parseResponse($response)
    {
        // Parse the response from Gracenote, check for errors, etc.
        try
        {
            $xml = $this->_checkResponse($response);
        }
        catch (SAPIException $e)
        {
            // If it was a no match, just give empty array back
            if ($e->getCode() == SAPIError::GRACENOTE_NO_MATCH)
            {
                return array();
            }

            // Otherwise, re-throw the exception
            throw $e;
        }

        // If we get to here, there were no errors, so continue to parse the response.
        $output = array();
        foreach ($xml->RESPONSE->ALBUM as $a)
        {
            $obj = array();

            // Album metadata
            $obj["album_gnid"]        = (string)($a->GN_ID);
            $obj["album_artist_name"] = (string)($a->ARTIST);
            $obj["album_title"]       = (string)($a->TITLE);
            $obj["album_year"]        = (string)($a->DATE);
            $obj["genre"]             = $this->_getOETElem($a->GENRE);
            $obj["album_art_url"]     = (array)($this->_getAttribElem($a->URL, "TYPE", "COVERART",true));

            // Artist metadata
            $obj["artist_image_url"]  = (string)($this->_getAttribElem($a->URL, "TYPE", "ARTIST_IMAGE"));
            $obj["artist_bio_url"]    = (string)($this->_getAttribElem($a->URL, "TYPE", "ARTIST_BIOGRAPHY"));
            $obj["review_url"]        = (string)($this->_getAttribElem($a->URL, "TYPE", "REVIEW"));

            // If we have artist OET info, use it.
            if ($a->ARTIST_ORIGIN)
            {
                $obj["artist_era"]    = $this->_getOETElem($a->ARTIST_ERA);
                $obj["artist_type"]   = $this->_getOETElem($a->ARTIST_TYPE);
                $obj["artist_origin"] = $this->_getOETElem($a->ARTIST_ORIGIN);
            }
            // If not available, do a fetch to try and get it instead.
            else
            {
                $obj = array_merge($obj, $this->fetchOETData((string)($a->GN_ID)));
            }

            // Parse track metadata if there is any.
            foreach($a->TRACK as $t)
            {
                $track = array();

                $track["track_number"]      = (int)($t->TRACK_NUM);
                $track["track_gnid"]        = (string)($t->GN_ID);
                $track["track_title"]       = (string)($t->TITLE);
                $track["track_artist_name"] = (string)($t->ARTIST);

                // If no specific track artist, use the album one.
                if (!$t->ARTIST) { $track["track_artist_name"] = $obj["album_artist_name"]; }

                $track["mood"]              = $this->_getOETElem($t->MOOD);
                $track["tempo"]             = $this->_getOETElem($t->TEMPO);

                // If track level GOET data exists, overwrite metadata from album.
                if (isset($t->GENRE))         { $obj["genre"]         = $this->_getOETElem($t->GENRE); }
                if (isset($t->ARTIST_ERA))    { $obj["artist_era"]    = $this->_getOETElem($t->ARTIST_ERA); }
                if (isset($t->ARTIST_TYPE))   { $obj["artist_type"]   = $this->_getOETElem($t->ARTIST_TYPE); }
                if (isset($t->ARTIST_ORIGIN)) { $obj["artist_origin"] = $this->_getOETElem($t->ARTIST_ORIGIN); }

                $obj["tracks"][] = $track;
            }

            $output[] = $obj;
        }
        return $output;
    }

    // A helper function to return the child node which has a certain attribute value.
    private function _getAttribElem($root, $attribute, $value, $array = false)
    {
        $finds = array();
        
        foreach ($root as $r)
        {
            $attrib = $r->attributes();
            if ($attrib[$attribute] == $value) { $finds[] = (string)$r; }
        }
        
        if ($array == false) { return $finds[0];}
        return $finds;
    }

    // A helper function to parse OET data into an array
    private function _getOETElem($root)
    {
        $array = array();
        foreach($root as $data)
        {
             $array[] = array("id"   => (int)($data["ID"]),
                              "text" => (string)($data));
        }
        return $array;
    }
}
