<?php

namespace App\Classes;

class SQLscripts {

	public static function getReferenceScript(string $table) {

		$scripts = [
			'Biomaterials'			=> "SELECT Id, Code, Name FROM Biomaterials",
			'Target_Groups'			=> "SELECT codeid AS ID,nameid AS Name FROM sp_gruppa WHERE codeid > 1",
			'Target_SubGroups'		=> "SELECT codeid AS ID,nameid AS Name,fid_research_group AS TargetID FROM sp_gruppa_result WHERE codeid > 1",
			'Managers'				=> "SELECT Id,SName,Name,PName,Email,Phone,IdSpRegion FROM Managers WHERE IsActive = 1",
			'Hospitals'				=> "SELECT
										  Id as Hospital_ID, Number as Hospital_Code, ltrim(rtrim(Name)) as Hospital_Name,
										  IdPartnerType as Hospital_Type, email as Hospital_Email, IdManagers as Hospital_Manager
										FROM partners
										WHERE IsActive = 1 AND len(Number) > 4 ORDER BY Number",
			'Target_CodeName'		=> "SELECT codeid AS Target_ID,number AS Target_Code,nameid AS Target_Name,Comment AS Target_Comment, ForHuman AS forHuman, typeid FROM sp_analiz WHERE codeid >= 0",
			'Test_CodeName'			=> "SELECT codeid AS Test_ID, codeid_analiz AS Target_ID, number AS Test_Code, nameid AS Test_Name FROM sp_podanaliz",
			'Test_Groups'			=> "SELECT Id AS ID, Name AS Name FROM TestGroups",
			'Container_CodeName'	=> "SELECT id AS ID, name AS Name FROM sp_container",
			'Targets_Biomaterials'	=> "select DISTINCT
											sa.number as targetCode,
											sa.nameid as targetName,
											sa.ForHuman as forHuman,
											b.Code as bmCode,
											b.Name as bmName
										from sp_analiz as sa
											inner join sp_container_analisys as sac on sa.codeid = sac.id_sp_analisys
											left outer join Biomaterials as b on b.Id= sac.IdBiomaterial
										where sa.typeid=0 and sa.codeid >= 0 and b.Code is not null
										order by sa.number",
            'Departments'           => "SELECT Id, Name FROM Departaments",
//			'' => "",
		];

		if(array_key_exists($table, $scripts)) {
			return $scripts[$table];
		}
		else {
			return null;
		}

	}

	public static function getLisScript($script, $params = []) {
	    
	    //stub
	    
		$scripts = [];

		if(array_key_exists($script, $scripts)) {
			$sql = $scripts[$script];
			if(count($params) > 0) {
				$i = 1;
				foreach ($params as $param) {
					$sql = str_replace("[" . $i++ . "]", $param, $sql);
				}
				return $sql;
			}
			else {
				return $sql;
			}
		}
		else {
			return null;
		}
	}

	public static function getLocalScript($script, $params = []) {
		$scripts = [];
        
        if(array_key_exists($script, $scripts)) {
            $sql = $scripts[$script];
            if(count($params) > 0) {
                $i = 1;
                foreach ($params as $param) {
                    $sql = str_replace("[" . $i++ . "]", $param, $sql);
                }
                return $sql;
            }
            else {
                return $sql;
            }
        }
        else {
            return null;
        }
	}
}