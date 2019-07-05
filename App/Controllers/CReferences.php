<?php

namespace App\Controllers;

use App\Classes\Logg;
use App\Models\MBiomaterial;
use App\Models\MTarget;
use App\Models\MTbrelation;

class CReferences extends ABaseController
{
    
    public function getBiomaterials($request, $response)
    {
        // $items = $this->getAllItems(new MBiomaterial($this->container));
        MBiomaterial::init($this->container);
        $items = MBiomaterial::findAll();
        
        $status = $items ? 200 : 204;
        
        return $this->BuildResponse($status, $items, $request, $response);
    }
    
    public function getTargets($request, $response)
    {
        //$items = $this->getAllItems(new MTarget($this->container));
        MTarget::init($this->container);
        $items = MTarget::findAll();
        
        $status = $items ? 200 : 204;
        
        return $this->BuildResponse($status, $items, $request, $response);
    }
    
    public function getTbrelations($request, $response)
    {
        //$items = $this->getAllItems(new MTbrelation($this->container));
        MTbrelation::init($this->container);
        $items = MTbrelation::findAll();
        
        $status = $items ? 200 : 204;
        
        return $this->BuildResponse($status, $items, $request, $response);
    }
    
    private function getAllItems($refClass)
    {
    
        try {
            $items = $refClass::findAll();
        } catch (\Exception $e) {
            $items = null;
            Logg::error(__METHOD__ . ' - ' . $e->getMessage());
        }
        
        return $items;
        
    }
    
    
}