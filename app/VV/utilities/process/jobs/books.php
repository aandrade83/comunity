

<?php 

require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");

// var $_company comes from VARS.

echo "<pre>";


echo "<BR>--------------------------------------------<BR>";
echo " ----------------BOOKS----------------------------<BR>";
echo "--------------------------------------------<BR><BR>";


$bdBooks = get_all_books($_company);

$autoMover = new AutoMover();
$resultBooks = $autoMover->books();


if(!empty($resultBooks)){

	foreach ($resultBooks['sportsBook'] as $book) {
		
		

       if(!isset($bdBooks[$book['@attributes']['id']])){
		$books = new _Books;
		$books->vars['id'] = $book['@attributes']['id']."_".$_company;
		$books->vars['id_book'] = $book['@attributes']['id'];
		$books->vars['name'] = $book['name'];
		$books->vars['short'] = $book['abbreviation'][0];
		$books->vars['available'] = 1;
		$books->vars['id_company'] = $_company ;
		$books->insert();
		echo "Book ID: ".$book['@attributes']['id']." -- ".$book['name']." --> NEW BOOK ADDED<BR>";
       }
         else{
         	echo "Book ID: ".$book['@attributes']['id']." -- ".$book['name']." --> IS ALREADY ADDED<BR>";
         }
        
	}
}

/*



// Example usage:
$token = "e_6V!q!-_9_fmy_x";
$autoMover = new AutoMover($token);


/*
// Example usage for books method
$resultBooks = $autoMover->books();
var_dump($resultBooks);

// Example usage for scores method with league parameter
$resultScores = $autoMover->scores("your_league_name");
var_dump($resultScores);

// Example usage for leagues method
$resultLeagues = $autoMover->leagues();
var_dump($resultLeagues);

// Example usage for schedule method
$resultSchedule = $autoMover->schedule();
var_dump($resultSchedule);

// Example usage for sports method
$resultSports = $autoMover->sports();
var_dump($resultSports);

*/

?>