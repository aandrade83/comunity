

<?php 

require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");

// var $_company comes from VARS.

echo "<pre>";


echo "<BR>--------------------------------------------<BR>";
echo " ----------------LEAGUES----------------------------<BR>";
echo "--------------------------------------------<BR><BR>";


$bdleagues = get_all_leagues($_company);

$autoMover = new AutoMover();
$resultleagues = $autoMover->leagues();
echo "<pre>";
//print_r($resultleagues); exit;


if(!empty($resultleagues)){

	foreach ($resultleagues['league'] as $league) {
		
		

       if(!isset($bdleagues[$league['@attributes']['id']])){
		$leagues = new _leagues;
		$leagues->vars['id'] = $league['@attributes']['id']."_".$_company;
		$leagues->vars['id_league'] = $league['@attributes']['id'];
		$leagues->vars['name'] = $league['name'];
		$leagues->vars['abbreviation'] = $league['abbreviation'];
		$leagues->vars['id_sport'] = $league['sport']['@attributes']['id'];
		$leagues->vars['available'] = 1;
		$leagues->vars['id_company'] = $_company ;
		$leagues->insert();
		
		echo "league ID: ".$league['@attributes']['id']." -- ".$league['name']." --> NEW league ADDED<BR>";
       }
         else{
         	echo "league ID: ".$league['@attributes']['id']." -- ".$league['name']." --> IS ALREADY ADDED<BR>";
         }


        
	}
}



?>