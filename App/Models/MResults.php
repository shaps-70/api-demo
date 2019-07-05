<?php

namespace App\Models;

use App\Classes\Logg;
use Psr\Container\ContainerInterface;
use stdClass;

class MResults extends ABaseModel implements IModel
{
    
    public static function init(ContainerInterface $container)
    {
        self::$container = $container;
        self::$db = $container->get('db');
    }
    
    public static function findTargetsByBarcode($bcode)
    {
        $order = self::getObjectFromDb("CALL API_OrderByBarcode(:bcode);", [':bcode' => $bcode]);
        if (!$order) {
            return false;
        }
        
        $order = self::getObjectFromDb("CALL API_OrderById(:oid);", [':oid' => $order->orderId]);
        $order = self::clearNullEmptyValue($order);
        
        $sample = self::getObjectFromDb("CALL API_SampleByBarcode(:bcode);", [':bcode' => $bcode]);
        
        $targets = self::getObjectsFromDb("CALL API_TargetsByBarcode(:bcode);", [':bcode' => $bcode]);
        $targets = self::clearNullEmptyValue($targets);
        
        $result = new stdClass();
        $result->Order = $order;
        $result->Order->Sample = $sample;
        $result->Order->Sample->Targets = $targets;
        
        foreach ($result->Order->Sample->Targets as $keyTar => $target) {
            $tests = self::getObjectsFromDb("CALL API_TestsByTargetId(:tid);", [':tid' => $target->id]);
            $tests = self::clearNullEmptyValue($tests);
    
            $result->Order->Sample->Targets[$keyTar]->Tests = $tests;
            
            foreach ($result->Order->Sample->Targets[$keyTar]->Tests as $keyTst => $test) {
                $antibiotics = self::getObjectsFromDb("CALL GetAntibioticsByTID_API(:testid);",
                    [':testid' => $test->id]);
                if ($antibiotics) {
                    $result->Order->Sample->Targets[$keyTar]->Tests[$keyTst]->Antibiotics = $antibiotics;
                }
            }
        }
        
        return $result;
    }
    
    public static function findTargetsByAmbulcardDate($acard, $dreg)
    {
        $orders = self::getObjectsFromDb("CALL API_OrdersByAmbulCard(:acard, :dreg);",
            [':acard' => $acard, ':dreg' => $dreg]);
        if (!$orders) {
            return false;
        }
        $orders = self::clearNullEmptyValue($orders);
        
        $result = new stdClass();
        $result->Orders = $orders;
        
        foreach ($orders as $keyOrd => $order) {
            $samples = self::getObjectsFromDb("CALL API_SamplesByOrderId(:oid);", [':oid' => $order->id]);
            $samples = self::clearNullEmptyValue($samples);
            
            $result->Orders[$keyOrd]->Samples = $samples;
            foreach ($samples as $keySample => $sample) {
                $targets = self::getObjectsFromDb("CALL API_TargetsByBarcode(:bcode);", [':bcode' => $sample->barcode]);
                $targets = self::clearNullEmptyValue($targets);
                $result->Orders[$keyOrd]->Samples[$keySample]->Targets = $targets;
                
                foreach ($targets as $keyTar => $target) {
                    $tests = self::getObjectsFromDb("CALL API_TestsByTargetId(:tid);", [':tid' => $target->id]);
                    $tests = self::clearNullEmptyValue($tests);
                    $result->Orders[$keyOrd]->Samples[$keySample]->Targets[$keyTar]->Tests = $tests;
                    
                    foreach ($tests as $keyTest => $test) {
                        $antibiotics = self::getObjectsFromDb("CALL GetAntibioticsByTID_API(:testid);",
                            [':testid' => $test->id]);
                        $antibiotics = self::clearNullEmptyValue($antibiotics);
                        if ($antibiotics) {
                            $result->Orders[$keyOrd]->Samples[$keySample]->Targets[$keyTar]->Tests[$keyTest]->Antibiotics = $antibiotics;
                        }
                    }
                }
            }
        }
        
        return $result;
    }
    
    public static function findTargetsComplete($hcode)
    {
        
        $hid = self::getHid($hcode);
        
        $lastTargetValidate = self::getObjectFromDb("SELECT CAST(lastDtValidate AS CHAR) AS lastDtValidate FROM LastGetTargetsByClients WHERE hospitalCode = :hcode",
            [':hcode' => $hcode]);
        
        if ($lastTargetValidate) {
            $dtvalid = $lastTargetValidate->lastDtValidate;
        } else {
            $dtTmp = date('Y-m-d 00:00:00.000');
            try {
                $item = ['hospitalCode' => $hcode, 'lastDtValidate' => $dtTmp];
                self::$db->prepare("INSERT INTO LastGetTargetsByClients (" . (implode(",",
                        array_keys($item))) . ") VALUES ('" . (implode("','", $item)) . "');")->execute();
            } catch (\PDOException $e) {
                Logg::error('(GetTargetsByOrderLastChange INSERT): ' . $e->getMessage());
            }
            $dtvalid = $dtTmp;
        }
        
        $orders = self::getObjectsFromDb("CALL API_OrdersByTargetsDtReady(:hid, :dtvalid);",
            [':hid' => $hid, ':dtvalid' => $dtvalid]);
        
        if (!$orders) {
            return false;
        }
        
        $lastTargetValidate = end($orders);
        
        try {
            $params = [
                'lv' => $lastTargetValidate->lastDtValidate,
                'hc' => $hcode
            ];
            
            self::$db->prepare("UPDATE LastGetTargetsByClients SET lastDtValidate = :lv WHERE hospitalCode = :hc")->execute($params);
            
        } catch (\PDOException $e) {
            Logg::error('(GetTargetsByOrderLastChange UPDATE LastValue): ' . $e->getMessage());
        }
        
        $orderTmp = array();
        foreach ($orders as $order) {
            $orderTmp[] = $order->id;
        }
        $orders = array_unique($orderTmp);
        unset($orderTmp);
        
        foreach ($orders as $keyOrdi => $item) {
            $orderItems[] = self::getObjectFromDb("CALL API_OrderById(:oid);", [':oid' => $item]);
        }
        
        $result = new stdClass();
        $result->Orders = self::clearNullEmptyValue($orderItems);
        
        foreach ($result->Orders as $keyOrd => $order) {
            $samples = self::getObjectsFromDb("CALL API_SamplesByOrderId(:oid);", [':oid' => $order->id]);
            $samples = self::clearNullEmptyValue($samples);
            
            $result->Orders[$keyOrd]->Samples = $samples;
            
            // TODO action if $sample->barcode is null
            
            foreach ($samples as $keySample => $sample) {
                $targets = self::getObjectsFromDb("CALL API_TargetsValidByBarcodeByDtVaildate(:bcode, :dtvalid);",
                    [':bcode' => $sample->barcode, ':dtvalid' => $dtvalid]);
                $targets = self::clearNullEmptyValue($targets);
                
                if (!$targets) {
                    unset($result->Orders[$keyOrd]->Samples[$keySample]);
                    continue;
                } else {
                    $result->Orders[$keyOrd]->Samples[$keySample]->Targets = $targets;
                }
                
                foreach ($targets as $keyTar => $target) {
                    $tests = self::getObjectsFromDb("CALL API_TestsByTargetId(:tid);", [':tid' => $target->id]);
                    $tests = self::clearNullEmptyValue($tests);
                    $result->Orders[$keyOrd]->Samples[$keySample]->Targets[$keyTar]->Tests = $tests;
                    
                    foreach ($tests as $keyTest => $test) {
                        $antibiotics = self::getObjectsFromDb("CALL GetAntibioticsByTID_API(:testid);",
                            [':testid' => $test->id]);
                        $antibiotics = self::clearNullEmptyValue($antibiotics);
                        if ($antibiotics) {
                            $result->Orders[$keyOrd]->Samples[$keySample]->Targets[$keyTar]->Tests[$keyTest]->Antibiotics = $antibiotics;
                        }
                    }
                }
            }
        }
        
        return $result;
    }
    
    public static function getAttachFile($params)
    {
        
        $hid = self::getHid($params['hcode']);
        
        $res = self::getObjectFromDb("SELECT Targets.Target_Attachmetn_FilesID FROM Targets
                                    INNER JOIN Orders ON Targets.Order_ID = Orders.Order_ID
                                    WHERE Targets.Target_Attachmetn_FilesID = :fid AND Orders.Hospital_ID = :hid",
            [':fid' => $params['fid'], ':hid' => $hid]);
        
        if ($res) {
            return self::$container['settings']['appConfig']['AttachFilesPath'] .
                $res->Target_Attachmetn_FilesID .
                self::$container['settings']['appConfig']['AttachFileExt'];
        } else {
            return false;
        }
    }
}