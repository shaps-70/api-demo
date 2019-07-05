<?php

namespace App\Controllers;


use App\Classes\Logg;
use App\Models\MResults;
use Slim\Http\Stream;

class CResults extends ABaseController
{
    
    public function getResultsByBarcode($request, $response)
    {
        try {
            //$res = new MResults($this->container);
            MResults::init($this->container);
            $items = MResults::findTargetsByBarcode($request->getAttribute('bcode'));
        } catch (\Exception $e) {
            $items = false;
            Logg::error(__METHOD__ . ' - ' . $e->getMessage());
        }
        
        $status = $items ? 200 : 204;
        
        return $this->BuildResponse($status, $items, $request, $response);
    }
    
    public function getResultsByAmbulcardDate($request, $response)
    {
        try {
            //$res = new MResults($this->container);
            MResults::init($this->container);
            $items = MResults::findTargetsByAmbulcardDate($request->getAttribute('acard'), $request->getAttribute('dreg'));
        } catch (\Exception $e) {
            $items = null;
            Logg::error(__METHOD__ . ' - ' . $e->getMessage());
        }
        
        $status = $items ? 200 : 204;
        
        return $this->BuildResponse($status, $items, $request, $response);
    }
    
    public function getResultsByTargetsComplete($request, $response)
    {
        try {
            //$res = new MResults($this->container);
            MResults::init($this->container);
            $items = MResults::findTargetsComplete($request->getAttribute('hcode'));
        } catch (\Exception $e) {
            $items = null;
            Logg::error(__METHOD__ . ' - ' . $e->getMessage());
        }
        
        $status = $items ? 200 : 204;
        
        return $this->BuildResponse($status, $items, $request, $response);
    }
    
    public function getAttachFile($request, $response)
    {
        try {
            //$res = new MResults($this->container);
            MResults::init($this->container);
            
            $params = [
                'hcode' => $request->getAttribute('hcode'),
                'fid' => $request->getAttribute('id')
            ];
    
            $attachFile = MResults::getAttachFile($params);
    
            if ($attachFile) {
                $fh = fopen($attachFile, 'rb');
                $stream = new Stream($fh);
                
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/octet-stream')
                    ->withHeader('Content-Type', 'application/pdf')
                    ->withHeader('Content-Description', 'File Transfer')
                    ->withHeader('Content-Transfer-Encoding', 'binary')
                    ->withHeader('Content-Disposition', 'attachment; filename="cld-attach-' .
                        basename($attachFile) . '"')
                    ->withHeader('Expires', '0')
                    ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                    ->withHeader('Pragma', 'public')
                    ->withBody($stream);
            } else {
                return $response->withStatus(204);
            }
            
        } catch (\Exception $e) {
            Logg::error(__METHOD__ . ' - ' . $e->getMessage());
            return $response->withStatus(500);
        }
    }
    
}