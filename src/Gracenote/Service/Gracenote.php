<?php
/**
 * Gracenote PHP Library
 *
 * @version 1.0.0
 */

namespace Gracenote\Service;

use Zend\Http\Client;
use Zend\Http\Headers;
use Zend\Json\Json;
use Zend\I18n\Validator\Alnum as Alnum;

final class Gracenote {

    static private $clientId;
    static private $userId;
    static private $language = 'eng';
    static private $country = 'usa';

    static public function getClientId()
    {
        return self::$clientId;
    }

    static public function setClientId($value)
    {
        self::$clientId = $value;
    }

    static public function getUserId()
    {
        return self::$userId;
    }

    static public function setUserId($value)
    {
        self::$userId = $value;
    }

    static public function getLanguage()
    {
        return self::$language;
    }

    static public function setLanguage($value)
    {
        self::$language = $value;
    }

    static public function getCountry()
    {
        return self::$country;
    }

    static public function setCountry($value)
    {
        self::$country = $value;
    }

    static public function configure($clientId, $userId = '')
    {
        self::setClientId($clientId);
        self::setUserID($userId);
    }

    static public function getApiUrl()
    {
        return 'https://c' . strtok(self::getClientId(), '-') . '.web.cddbp.net/webapi/xml/1.0/';
    }

    static public function register()
    {
        // Validate configuration
        if (!self::getClientId())
            throw new \Exception('Gracenote has not been configured');

        $http = new Client();

        $xml = new \SimpleXmlElement('<QUERIES></QUERIES>');
        $query = $xml->addChild('QUERY');
        $query->addAttribute('CMD', 'REGISTER');
        $query->addChild('CLIENT', self::getClientId());

        $http->setOptions(array('sslverifypeer' => false));
        $http->setUri(self::getApiUrl());
        $http->setMethod('POST');
        $http->getRequest()->setContent($xml->asXML());

        $response = $http->send();
        $responseXml = simplexml_load_string($response->getBody());

        if ((string)$responseXml->RESPONSE['STATUS'] == 'OK') {
            die('Your user id is ' . (string)$responseXml->RESPONSE->USER);
        } else {
            echo "There was an error creating your userid";
            print_r($responseXml);
        }
    }

    static public function query($command, $options)
    {
        // Validate configuration
        if (!self::getClientId() or !self::getUserId())
            throw new \Exception('Gracenote has not been configured');

        $http = new Client();

        $xml = new \SimpleXmlElement('<QUERIES></QUERIES>');
        $xml->addChild('LANG', self::getLanguage());
        $xml->addChild('COUNTRY', self::getCountry());

        // Add auth
        $auth = $xml->addChild('AUTH');
        $auth->addChild('CLIENT', self::getClientId());
        $auth->addChild('USER', self::getUserId());

        $query = $xml->addChild('QUERY');
        $query->addAttribute('CMD', $command);

        foreach($options as $key => $val) {
            switch ($key) {
                case 'parameters':
                    foreach ($val as $parameter => $value) {
                        $param = $query->addChild('TEXT', $value);
                        $param->addAttribute('TYPE', $parameter);
                    }
                    break;

                case 'options':
                    foreach ($val as $parameter => $value) {
                        $option = $query->addChild('OPTION');
                        $option->addChild('PARAMETER', $parameter);
                        $option->addChild('VALUE', $value);
                    }
                    break;

                default:
                    $query->addAttribute($key, $val);
                    break;
            }
        }

        $http->setOptions(array('sslverifypeer' => false));
        $http->setUri(self::getApiUrl());
        $http->setMethod('POST');
        $http->getRequest()->setContent($xml->asXML());

        $response = $http->send();
        $responseXml = simplexml_load_string($response->getBody());

        return $responseXml;
    }
}
