<?php


namespace App\Models;

use Psr\Container\ContainerInterface;

abstract class ABaseModel
{
    
    protected static $container;
    protected static $db;
    
    /*public function __construct(ContainerInterface $container)
    {
        self::$container = $container;
        self::$db = $container->get('db');
    }*/
    
    abstract public static function init(ContainerInterface $container);
    
    protected static function getObjectFromDb($query, $params = [])
    {
        $stmt = self::$db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    protected static function getObjectsFromDb($query, $params = [])
    {
        $stmt = self::$db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_CLASS);
    }
    
    protected static function getAllFromDb($query, $params = [])
    {
        $stmt = self::$db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    protected static function getOneFromDb($query, $params = [])
    {
        $stmt = self::$db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch();
    }
    
    protected static function clearNullEmptyValue($cont)
    {
        if (is_array($cont)) {
            foreach ($cont as $key => $item) {
                $cont[$key] = self::clearNullEmptyValue($item);
            }
        } else {
            foreach ($cont as $key => $item) {
                if (is_null($item) || strlen($item) == 0) {
                    unset($cont->$key);
                }
            }
        }
        
        return $cont;
    }
    
    protected static function getHid($hcode)
    {
        $hid = self::getObjectFromDb("SELECT Hospital_ID FROM Hospitals WHERE Hospital_Code = :hcode;",
            ['hcode' => $hcode]);
        
        return $hid->Hospital_ID;
    }

}