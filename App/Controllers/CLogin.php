<?php

namespace App\Controllers;


use App\Classes\Funcs;
use App\Classes\Logg;
use Psr\Container\ContainerInterface;

class CLogin extends ABaseController
{
    protected $container;
    
    public function __invoke($request, $response)
    {
        if(!$request->hasHeader('Authorization')) {
            return $this->BuildResponse(400, [], $request, $response);
        }
        
        $header = $request->getHeaderLine('Authorization');
        $header = str_replace("Basic ", "", $header);
        $header = base64_decode($header);
        $header = explode(":", $header);
    
        $token = false;
        $db = $this->container->get('db');
        $stmt = $db->prepare("SELECT * FROM ApiUsers WHERE login = :login;");
        $stmt->execute(['login' => $header[0]]);
        $result = $stmt->fetch();
    
        if ($stmt->rowCount() && password_verify($header[1], $result['passwordHash'])) {
            try {
                $token = md5(uniqid(rand(), true));
                $tokenCreated = date("Y-m-d H:i:s");
            
                $params = ['token' => $token, 'tokenCreated' => $tokenCreated, 'id' => $result['id']];
            
                $stmt = $db->prepare("UPDATE ApiUsers SET token = :token, tokenCreated = :tokenCreated WHERE id = :id");
                $stmt->execute($params);
                if (!$stmt->rowCount()) {
                    $token = false;
                }
            } catch (\PDOException $e) {
                Logg::error($e->getMessage(), 'Get token');
                $token = false;
            }
        }

        if ($token) {
            $response->getBody()->write($token);
            return $response->withStatus(200)->withHeader('Content-Type', 'text/plain');
        } else {
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json')->withJson([
                'message' => 'Wrong username or password',
            ]);
        }
    }
}