<?php

namespace Gracenote\WebAPI;

class Search
{

    const BEST_MATCH_ONLY = 0; // Will put API into "SINGLE_BEST" mode.
    const ALL_RESULTS     = 1;
    
    private $api;

    private $artistName = null;
    private $albumTitle = null;
    private $trackTitle = null;
    private $gn_id = null;
    
    private $matchmode = self::ALL_RESULTS;
    private $command = 'ALBUM_SEARCH';

    private $paging_pageSize = 10;
    private $paging_count = 0;
    
    private $paging_start = 1;
    private $paging_end = 1;
    
    
    public function __construct($api){
    
        $this->api = $api;
        // set up correct page-end
        $this->paging_end = $this->paging_start + $this->paging_pageSize;
    }
    
    public function search($artistName, $albumTitle = null, $trackTitle = null){
        
        $this->artistName = $artistName;
        $this->albumTitle = $albumTitle;
        $this->trackTitle = $trackTitle;
        
        return $this->exec();
    }
    
    public function getAlbum($gn_id){
        $this->gn_id = $gn_id;
        $this->command = 'ALBUM_FETCH';
        return $this->exec();
    }
    
    // show only the best result. Provide `false` if you want to see all
    public function showBest($bool = true){
        $matchmode = is_bool($bool) ? $bool : false;
    }
    
    private function exec(){
        $body = $this->api->_constructQueryBody($this->getOptions());
        $data = $this->api->_constructQueryRequest($body, $this->command);
        return $this->api->_execute($data);
    }
    
    private function getOptions(){
        return array(
            'artist_name'   => $this->artistName,
            'album_title'   => $this->albumTitle,
            'track_title'   => $this->trackTitle,
            'gn_id'         => $this->gn_id,
            'command'       => $this->command,
            'matchmode'     => $this->matchmode,
            'paging_start'  => $this->paging_start,
            'paging_end'    => $this->paging_end
        );
    }
        

}