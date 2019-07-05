<?php

namespace App\Controllers;

use App\Models\MApiUser;
use Exception;
use Psr\Container\ContainerInterface;

class CAdmin extends ABaseController
{
    protected $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function addNewUser($request, $response)
    {
        if(!$request->isPost()) {
            return $this->BuildResponse(405, ['Method Not Allowed'], $request, $response);
        }
        
        $adminAccess = $request->getAttribute('adminaccess');
        
        if(!$adminAccess) {
            return $this->BuildResponse(403, ['Access denied'], $request, $response);
        }

        $params = $request->getQueryParams();
        
        $user = new MApiUser($this->container);
        
        $user->hospitalCode = $params['hcode'];
        $user->login = $params['login'];
        $user->passwordHash = password_hash($params['password'], PASSWORD_DEFAULT);;
        $user->admin = $params['admin'];
        
        $status = $user->save();
    
        return $this->BuildResponse(
            $status ? 201 : 400,
            $status ? ['ok'] : ['Bad Request'],
            $request, $response);
        
    }
}