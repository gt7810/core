<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include "../../functions.php" ;
include "../../config.php" ;

//New PDO DB connection
try {
  	$connection2=new PDO("mysql:host=$databaseServer;dbname=$databaseName;charset=utf8", $databaseUsername, $databasePassword);
	$connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

@session_start() ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$gibbonSchoolYearID=$_GET["gibbonSchoolYearID"] ;
$gibbonFinanceFeeID=$_POST["gibbonFinanceFeeID"] ;
$search=$_GET["search"] ;

if ($gibbonFinanceFeeID=="" OR $gibbonSchoolYearID=="") {
	print "Fatal error loading this page!" ;
}
else {
	$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/fees_manage_edit.php&gibbonFinanceFeeID=$gibbonFinanceFeeID&gibbonSchoolYearID=$gibbonSchoolYearID&search=$search" ;
	
	if (isActionAccessible($guid, $connection2, "/modules/Finance/fees_manage_edit.php")==FALSE) {
		//Fail 0
		$URL.="&updateReturn=fail0" ;
		header("Location: {$URL}");
	}
	else {
		//Proceed!
		//Check if person specified
		if ($gibbonFinanceFeeID=="") {
			//Fail1
			$URL.="&updateReturn=fail1" ;
			header("Location: {$URL}");
		}
		else {
			try {
				$data=array("gibbonSchoolYearID"=>$gibbonSchoolYearID, "gibbonFinanceFeeID"=>$gibbonFinanceFeeID); 
				$sql="SELECT * FROM gibbonFinanceFee WHERE gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonFinanceFeeID=:gibbonFinanceFeeID" ; 
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { 
				//Fail2
				$URL.="&deleteReturn=fail2" ;
				header("Location: {$URL}");
				break ;
			}
			
			if ($result->rowCount()!=1) {
				//Fail 2
				$URL.="&updateReturn=fail2" ;
				header("Location: {$URL}");
			}
			else {
				$name=$_POST["name"] ;
				$nameShort=$_POST["nameShort"] ;
				$active=$_POST["active"] ;
				$description=$_POST["description"] ;
				$gibbonFinanceFeeCategoryID=$_POST["gibbonFinanceFeeCategoryID"] ;
				$fee=$_POST["fee"] ;
			
				if ($name=="" OR $nameShort=="" OR $active=="" OR $gibbonFinanceFeeCategoryID=="" OR $fee=="") {
					//Fail 3
					$URL.="&addReturn=fail3" ;
					header("Location: {$URL}");
				}
				else {
					//Write to database
					try {
						$data=array("gibbonSchoolYearID"=>$gibbonSchoolYearID, "name"=>$name, "nameShort"=>$nameShort, "active"=>$active, "description"=>$description, "gibbonFinanceFeeCategoryID"=>$gibbonFinanceFeeCategoryID, "fee"=>$fee, "gibbonPersonIDUpdate"=>$_SESSION[$guid]["gibbonPersonID"], "gibbonFinanceFeeID"=>$gibbonFinanceFeeID); 
						$sql="UPDATE gibbonFinanceFee SET gibbonSchoolYearID=:gibbonSchoolYearID, name=:name, nameShort=:nameShort, active=:active, description=:description, gibbonFinanceFeeCategoryID=:gibbonFinanceFeeCategoryID, fee=:fee, gibbonPersonIDUpdate=:gibbonPersonIDUpdate, timestampUpdate='" . date("Y-m-d H:i:s") . "' WHERE gibbonFinanceFeeID=:gibbonFinanceFeeID" ;
						$result=$connection2->prepare($sql);
						$result->execute($data);
					}
					catch(PDOException $e) { 
						//Fail 2
						$URL.="&updateReturn=fail2" ;
						header("Location: {$URL}");
						break ;
					}

					//Success 0
					$URL.="&updateReturn=success0" ;
					header("Location: {$URL}");
				}
			}
		}
	}
}
?>