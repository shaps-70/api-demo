<?php

namespace App\Models;

use App\Classes\Logg;
use Psr\Container\ContainerInterface;

class MTarget extends ABaseModel implements IModel
{
    private const TABLE = 'Target_CodeName';
    
    public static function init(ContainerInterface $container)
    {
        self::$container = $container;
        self::$db = $container->get('db');
    }
    
    public static function findAll()
    {
        try {
            $items = self::getObjectsFromDb(
                "SELECT Target_Code AS code, Target_Name as name FROM " .
                self::TABLE .
                " WHERE typeid = 0 AND forHuman = 1 AND RIGHT(Target_Code, 1) NOT IN ('к', 'я') ORDER BY Target_Code;"
            );
        } catch (\PDOException $e) {
            Logg::error($e->getMessage(), __METHOD__);
            $items = false;
        }
        
        return $items;
    }
    
}