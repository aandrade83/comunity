

<?php 

require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");

// var $_company comes from VARS.

echo "<pre>";


echo "<BR>--------------------------------------------<BR>";
echo " ----------------sportS----------------------------<BR>";
echo "--------------------------------------------<BR><BR>";


$bdsports = get_all_sports($_company);

$autoMover = new AutoMover();
$resultsports = $autoMover->sports();


if(!empty($resultsports)){

	foreach ($resultsports['sport'] as $sport) {
		
		

       if(!isset($bdsports[$sport['@attributes']['id']])){
		$sports = new _Sports;
		$sports->vars['id'] = $sport['@attributes']['id']."_".$_company;
		$sports->vars['id_sport'] = $sport['@attributes']['id'];
		$sports->vars['name'] = $sport['name'];
		$sports->vars['short'] = $sport['abbreviation'][0];
		$sports->vars['available'] = 1;
		$sports->vars['id_company'] = $_company ;
		$sports->insert();
		echo "sport ID: ".$sport['@attributes']['id']." -- ".$sport['name']." --> NEW sport ADDED<BR>";
       }
         else{
         	echo "sport ID: ".$sport['@attributes']['id']." -- ".$sport['name']." --> IS ALREADY ADDED<BR>";
         }
        
	}
}

/*



// Example usage:
$token = "e_6V!q!-_9_fmy_x";
$autoMover = new AutoMover($token);


/*
// Example usage for sports method
$resultsports = $autoMover->sports();
var_dump($resultsports);

// Example usage for scores method with sport parameter
$resultScores = $autoMover->scores("your_sport_name");
var_dump($resultScores);

// Example usage for sports method
$resultsports = $autoMover->sports();
var_dump($resultsports);

// Example usage for schedule method
$resultSchedule = $autoMover->schedule();
var_dump($resultSchedule);

// Example usage for sports method
$resultSports = $autoMover->sports();
var_dump($resultSports);

*/

?>