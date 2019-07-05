<?php

namespace App\Controllers;

use App\Classes\Array2XML;
use App\Classes\Logg;
use App\Classes\SFTP;
use Psr\Container\ContainerInterface;

class CAppRequest extends ABaseController
{
    protected $container;
    protected $appConfig;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->appConfig = $container['settings']['appConfig'];
    }
    
    public function addNewAppRequest($request, $response)
    {
        $input = $request->getParsedBody();
    
        $hcode = $request->getAttribute('hcode');
        $xmlArray = $input['Request'];
    
        $pathXml = $this->appConfig['XmlTmpPath'] . $hcode;
        $xmlFileName = 'Request-' . date('ymdHi-') . $xmlArray['AmbulantCard'] . '.xml';
    
        try {
            $xml = Array2XML::createXML('Request', $xmlArray);
        } catch (\Exception $e) {
            Logg::error('(addNewAppRequest, createXML): ' . $e->getMessage());
            return false;
        }
        $strXml = $xml->saveXML();
        
        /*var_export($strXml);
        echo PHP_EOL . PHP_EOL;
        var_export($pathXml . "/" . $xmlFileName);
        die;*/
    
        if ($strXml) {
            if (!is_writable($pathXml)) {
                mkdir($pathXml, 0777, true);
            }
            file_put_contents($pathXml . "/" . $xmlFileName, $strXml);
        }
    
        $status = $this->SendRequestToLis($hcode, $pathXml, $xmlFileName);
        
        if ($status) {
            $status = 201;
            $resp = [
                'message' => 'Request completed successfully.'
            ];
        } else {
            $status = 400;
            $resp = [
                'message' => 'Incorrect syntax. Presumably JSON formatting.'
            ];
        }
        
        return $this->BuildResponse($status, $resp, $request, $response);
    }
    
    private function SendRequestToLis($hcode, $filePath, $fileName)
    {
        if (!$hcode) {
            return false;
        }
        
        $status = true;
        
        $ftp = new SFTP(
            $this->appConfig['LisFtp']['url'],
            $this->appConfig['LisFtp']['login'],
            $this->appConfig['LisFtp']['password']
        );
        
        $ftp->passive = true;
        
        if ($ftp->connect()) {
            $ftp->cd($hcode);
            if ($ftp->put($filePath . '/' . $fileName, $fileName)) {
                unlink($filePath . '/' . $fileName);
            } else {
                Logg::info('Send request to LIS: ' . $ftp->error);
                $status = false;
            }
        } else {
            Logg::info('FTP LIS connection error: ' . $ftp->error);
            $status = false;
        }
        
        return $status;
    }
}