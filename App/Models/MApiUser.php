<?php

namespace App\Models;

use App\Classes\Logg;
use Psr\Container\ContainerInterface;

class MApiUser extends ABaseModel implements IModel
{
    public $hospitalCode;
    public $login;
    public $passwordHash;
    public $admin;
    
    private const TABLE = 'ApiUsers';
    
    public function __construct(ContainerInterface $container)
    {
        self::$container = $container;
        self::$db = $container->get('db');
    }
    
    public static function init(ContainerInterface $container)
    {
        // abstract stub
        return false;
    }
    
    public function save()
    {
        return $this->upsertUser();
    }
    
    private function upsertUser()
    {
        try {
            $checkUser = self::getObjectFromDb("SELECT * FROM " . self::TABLE . " WHERE login = :login",
                [':login' => $this->login]);
            
            if ($checkUser) {
                $arrayUser =  (array)$this;
                unset($arrayUser['id'], $arrayUser['hospitalCode']);
    
                $query = "UPDATE " . self::TABLE . " SET";
                foreach ($arrayUser as $name => $value) {
                    $query .= ' ' . $name . ' = :' . $name . ',';
                    $values[':' . $name] = $value;
                }
                $query = substr($query, 0, -1) . " WHERE id = " . $checkUser->id;
                
                $stmt = self::$db->prepare($query);
                $stmt->execute($arrayUser);
            } else {
                $arrayUser =  (array)$this;
                
                self::$db->prepare("INSERT INTO " . self::TABLE . " (" . (implode(",",
                        array_keys($arrayUser))) . ") VALUES ('" . (implode("','", $arrayUser)) . "');")
                    ->execute();
            }
        } catch (\PDOException $e) {
            Logg::error($e->getMessage(), __METHOD__);
            
            return false;
        }
        
        return true;
    }
    
}