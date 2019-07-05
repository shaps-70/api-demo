<?php

namespace App\Models;


use App\Classes\Logg;
use Psr\Container\ContainerInterface;

class MBiomaterial extends ABaseModel implements IModel
{
    private const TABLE = 'Biomaterials';
    
    public static function init(ContainerInterface $container)
    {
        self::$container = $container;
        self::$db = $container->get('db');
    }
    
    public static function findAll()
    {
        try {
            $items = self::getObjectsFromDb(
                "SELECT Code AS code, `Name` as name FROM " . self::TABLE . " ORDER BY `Name`"
            );
        } catch (\PDOException $e) {
            Logg::error($e->getMessage(), __METHOD__);
            $items = false;
        }
        
        return $items;
    }
    
}