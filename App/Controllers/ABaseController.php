<?php

namespace App\Controllers;

use App\Classes\Array2XML;
use App\Classes\Logg;
use Psr\Container\ContainerInterface;

abstract class ABaseController
{
    protected $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /*protected function init(ContainerInterface $container) {
        $this->container = $container;
    }*/
    
    /**
     * @param $status
     * @param $items
     * @param $req
     * @param $resp
     * @param string $rootNode    for XML response
     *
     * @return mixed
     */
    protected function BuildResponse($status, $items, $req, $resp, $rootNode = 'Request')
    {
        $mType = 'application/json';
        if ($req->hasHeader('Accept')) {
            $mType = $req->getHeaderLine('Accept');
            if (stripos($mType, 'application/xml')) {
                $mType = 'application/xml';
            }
        }
    
        // TODO XML Response needed?
        
        switch ($mType) {
            case 'application/xml':
                /*$xml = Array2XML::createXML($rootNode, $items);
                $resultItems = $xml->saveXML();
                $resp->getBody()->write($resultItems);
                break;*/
            case 'application/json':
            default:
                $mType = 'application/json';
                $resp = $resp->withJson($items);
        }
        
        return $resp->withStatus($status)->withHeader('Content-Type', $mType);
    }
}