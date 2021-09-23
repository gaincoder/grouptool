<?php

namespace AppBundle\Services;

class HttpApi
{

    private $apiUrl;
    private $apiKey;

    public function __construct($apiUrl,$apiKey){
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    public function post($path,$json){
        $url = $this->apiUrl.$path;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','X-TOKEN: '.$this->apiKey));
        curl_exec($ch);
        curl_close($ch);
    }
}