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
    static private $clientTag;

    static public function getClientId()
    {
        return self::$clientId;
    }

    static public function setClientId($value)
    {
        self::$clientId = $value;
    }

    static public function getClientTag()
    {
        return self::$clientTag;
    }

    static public function setClientTag($value)
    {
        self::$clientTag = $value;
    }

    static public function configure($clientId, $clientTag)
    {
        self::setClientId($clientId);
        self::setClientTag($clientTag);
    }

    static public function getApiUrl()
    {
        return 'https://c' . strtok(self::getClientId(), '-') . '.web.cddbp.net/webapi/xml/1.0/';
    }

    static public function register()
    {
        $http = new Client();

        $http->setOptions(array('sslverifypeer' => false));
#        $headers = new Headers();
#        $headers->addHeaderLine('Authorization', self::getApiKey());
#        $headers->addHeaderLine('Content-Type', 'application/json');
#        $http->setHeaders($headers);

        $http->setUri('https://api.keen.io/3.0/projects/' . self::getProjectId() . '/events/' . $collectionName);
        $http->setMethod('POST');
        $http->getRequest()->setContent(Json::encode($parameters));

        die (strtok(self::getClientId(), '-'));
    }

    static public function addEvent($collectionName, $parameters)
    {
        // Validate configuration
        if (!self::getClientId() or !self::getClientTag())
            throw new \Exception('Keen IO has not been configured');

        // Validate collection name
        $validator = new Alnum();
        if (!$validator->isValid($collectionName))
            throw new \Exception("Collection name '$collectionName' contains invalid characters or spaces.");

        $http = new Client();

        $http->setOptions(array('sslverifypeer' => false));
        $headers = new Headers();
        $headers->addHeaderLine('Authorization', self::getApiKey());
        $headers->addHeaderLine('Content-Type', 'application/json');
        $http->setHeaders($headers);

        $http->setUri('https://api.keen.io/3.0/projects/' . self::getProjectId() . '/events/' . $collectionName);
        $http->setMethod('POST');
        $http->getRequest()->setContent(Json::encode($parameters));

        $response = $http->send();
        $json = Json::decode($response->getBody());

        return $json->created;
    }
}
