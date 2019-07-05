<?php

namespace App\Models;


use App\Classes\Logg;
use Psr\Container\ContainerInterface;

class MTbrelation extends ABaseModel implements IModel
{
    private const TABLE = 'Targets_Biomaterials';
    
    public static function init(ContainerInterface $container)
    {
        self::$container = $container;
        self::$db = $container->get('db');
    }
    
    public static function findAll()
    {
        try {
            $items = self::getObjectsFromDb(
                "SELECT bmCode AS biomaterialCode, bmName AS biomaterialName, targetCode, targetName FROM " .
                self::TABLE .
                " WHERE forHuman = 1 AND RIGHT(targetCode, 1) NOT IN ('к', 'я') ORDER BY bmCode, targetCode"
            );
        } catch (\PDOException $e) {
            Logg::error($e->getMessage(), __METHOD__);
            $items = false;
        }
        
        return $items;
    }
    
}