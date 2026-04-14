

<?php 

require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");

// var $_company comes from VARS.

echo "<pre>";


echo "<BR>--------------------------------------------<BR>";
echo " ----------------scheduleS----------------------------<BR>";
echo "--------------------------------------------<BR><BR>";


$bdschedules = get_active_schedule();
$autoMover = new AutoMover();
$resultschedules = $autoMover->schedule();

if(!empty($resultschedules)){


	foreach ($resultschedules['schedule']['sport'] as $schedule) {

	 //if($schedule['@attributes']['id'] == 7){	
		
			if(isset(($schedule['league']['group'][0]))){
            foreach ($schedule['league']['group'] as $group) {



           	 if(isset(($group['event'][0]))){

				foreach ($group['event'] as $event) {

				
				$insert = 0;
				if(isset($bdschedules[$event['@attributes']['id']]->vars['id'])){
					if(($bdschedules[$event['@attributes']['id']]->vars['event_state'] != $event['event_state']) && ($bdschedules[$event['@attributes']['id']]->vars['event_state'] != 'FINAL')){
						$bdschedules[$event['@attributes']['id']]->vars['event_state'] = $event['event_state'];
						$bdschedules[$event['@attributes']['id']]->vars['updated_date'] = date("Y-m-d H:i:s");
						$bdschedules[$event['@attributes']['id']]->update(array('event_state','updated_date'));
						echo "EVENT ".$bdschedules[$event['@attributes']['id']]->vars['event_name']." UPDATED<BR>";
					}
					$insert = 0;
                   
				} else{ $insert = 1; }	

				if($insert){

				if(isset($event['participant'][0]['team']['@attributes']['name']) && trim($event['participant'][0]['team']['@attributes']['name']) != "" && $event['event_type'] == 'team_event'){
					
				$schedules = new _Schedule;
				$schedules->vars['id_sport'] = $schedule['@attributes']['id'];
				$schedules->vars['id_league'] = $schedule['league']['@attributes']['id'];
				$schedules->vars['id_league_sub'] = $group['@attributes']['league_sub'];
				$schedules->vars['id_event'] =  $event['@attributes']['id'];
				$schedules->vars['event_date'] = date_convert($event['@attributes']['date']);
				$schedules->vars['event_name'] = $event['@attributes']['name'];
				$schedules->vars['event_state'] = $event['event_state'];
				$schedules->vars['time_changed'] = $event['time_changed'];
				$schedules->vars['game_number'] = $event['game_number'];
				$schedules->vars['event_location'] = $event['location']['@attributes']['name'] ;
				$schedules->vars['rotation_away'] = $event['participant'][0]['@attributes']['rot'];
				$schedules->vars['rotation_home'] = $event['participant'][1]['@attributes']['rot'];
				$schedules->vars['name_away'] = $event['participant'][0]['team']['@attributes']['name'];
				$schedules->vars['name_home'] = $event['participant'][1]['team']['@attributes']['name'];
				$schedules->vars['updated_feed'] = date_convert($resultschedules['updated']);
				$schedules->vars['updated_date'] = date("Y-m-d H:i:s");
				$schedules->vars['added_date'] = date("Y-m-d");
				$schedules->insert();


				echo "schedule ".$schedules->vars['name_away']." VS  ".$schedules->vars['name_home']." --> NEW schedule ADDED 1 <BR>"; 
			   } else { echo $schedule['@attributes']['name']." EVENT ID ".$event['@attributes']['id']. " TYPE ".$event['event_type']. "  Does not have particpants<BR>"; }

			  }
				 

				}           
                //print_r($group);

           	 
           	 } else {



				$insert = 0;
				if(isset($bdschedules[$group['event']['@attributes']['id']]->vars['id'])){
					if(($bdschedules[$group['event']['@attributes']['id']]->vars['event_state'] != $group['event']['event_state']) && ($bdschedules[$group['event']['@attributes']['id']]->vars['event_state'] != 'FINAL')) {
						$bdschedules[$group['event']['@attributes']['id']]->vars['event_state'] = $group['event']['event_state'];
						$bdschedules[$group['event']['@attributes']['id']]->vars['updated_date'] = date("Y-m-d H:i:s");
						$bdschedules[$group['event']['@attributes']['id']]->update(array('event_state','updated_date'));
						echo "EVENT ".$bdschedules[$event['@attributes']['id']]->vars['event_name']." UPDATED<BR>";
					}
					$insert = 0;
                   
				} else{ $insert = 1; }	
				if($insert){ 	

                if(isset($group['event']['participant'][0]['team']['@attributes']['name'])  && trim($group['event']['participant'][0]['team']['@attributes']['name']) != "" && $group['event']['event_type'] == 'team_event'){

	   			$schedules = new _Schedule;
				$schedules->vars['id_sport'] = $schedule['@attributes']['id'];
				$schedules->vars['id_league'] = $schedule['league']['@attributes']['id'];
				$schedules->vars['id_league_sub'] = $group['@attributes']['league_sub'];
				$schedules->vars['id_event'] =  $group['event']['@attributes']['id'];
				$schedules->vars['event_date'] = date_convert($group['event']['@attributes']['date']);
				$schedules->vars['event_name'] = $group['event']['@attributes']['name'];
				$schedules->vars['event_state'] = $group['event']['event_state'];
				$schedules->vars['time_changed'] = $group['event']['time_changed'];
				$schedules->vars['game_number'] = $group['event']['game_number'];
				$schedules->vars['event_location'] = $group['event']['location']['@attributes']['name'] ;
				$schedules->vars['rotation_away'] = $group['event']['participant'][0]['@attributes']['rot'];
				$schedules->vars['rotation_home'] = $group['event']['participant'][1]['@attributes']['rot'];
				$schedules->vars['name_away'] = $group['event']['participant'][0]['team']['@attributes']['name'];
				$schedules->vars['name_home'] = $group['event']['participant'][1]['team']['@attributes']['name'];
				$schedules->vars['updated_feed'] = date_convert($resultschedules['updated']);
				$schedules->vars['updated_date'] = date("Y-m-d H:i:s");
				$schedules->vars['added_date'] = date("Y-m-d");
				$schedules->insert();

				 echo "schedule event:" .$group['event']['@attributes']['id']." ".$schedules->vars['name_away']." VS  ".$schedules->vars['name_home']." --> NEW schedule ADDED 2<BR>"; 
          		} else { echo $schedule['@attributes']['name']." EVENT ID ".$group['event']['@attributes']['id']." Type ". $group['event']['event_type']. " Does not have particpants<BR>"; }

 		        }



           	 }	


           }

        
		} else {


			//echo "NO HAY VARIOS GROUPS 2";
           
          if(isset(($schedule['league']['group']['event'][0]))){
				

				foreach ($schedule['league']['group']['event'] as $event) {

			    $insert = 0;
				if(isset($bdschedules[$event['@attributes']['id']]->vars['id'])){
				if($bdschedules[$event['@attributes']['id']]->vars['event_state'] != $event['event_state']){
						$bdschedules[$event['@attributes']['id']]->vars['event_state'] = $event['event_state'];
						$bdschedules[$event['@attributes']['id']]->vars['updated_date'] = date("Y-m-d H:i:s");
						$bdschedules[$event['@attributes']['id']]->update(array('event_state','updated_date'));
						echo "EVENT ".$bdschedules[$event['@attributes']['id']]->vars['event_name']." UPDATED<BR>";
					}
					$insert = 0;
                   
				} else{ $insert = 1; }	
				if($insert){ 	
		
				if(isset($event['participant'][0]['team']['@attributes']['name'])  && trim($event['participant'][0]['team']['@attributes']['name']) != "" && $event['event_type'] == 'team_event'){
					
				$schedules = new _Schedule;
				$schedules->vars['id_sport'] = $schedule['@attributes']['id'];
				$schedules->vars['id_league'] = $schedule['league']['@attributes']['id'];
				$schedules->vars['id_league_sub'] = $schedule['league']['group']['@attributes']['league_sub'];
				$schedules->vars['id_event'] =  $event['@attributes']['id'];
				$schedules->vars['event_date'] = date_convert($event['@attributes']['date']);
				$schedules->vars['event_name'] = $event['@attributes']['name'];
				$schedules->vars['event_state'] = $event['event_state'];
				$schedules->vars['time_changed'] = $event['time_changed'];
				$schedules->vars['game_number'] = $event['game_number'];
				$schedules->vars['event_location'] = $event['location']['@attributes']['name'] ;
				$schedules->vars['rotation_away'] = $event['participant'][0]['@attributes']['rot'];
				$schedules->vars['rotation_home'] = $event['participant'][1]['@attributes']['rot'];
				$schedules->vars['name_away'] = $event['participant'][0]['team']['@attributes']['name'];
				$schedules->vars['name_home'] = $event['participant'][1]['team']['@attributes']['name'];
				$schedules->vars['updated_feed'] = date_convert($resultschedules['updated']);
				$schedules->vars['updated_date'] = date("Y-m-d H:i:s");
				$schedules->vars['added_date'] = date("Y-m-d");
				$schedules->insert();

				echo "schedule ".$schedules->vars['name_away']." VS  ".$schedules->vars['name_home']." --> NEW schedule ADDED 3 <BR>"; 
                } else { echo $schedule['@attributes']['name']." EVENT ID ". $event['@attributes']['id']." Type = ". $event['event_type']." Does not have particpants<BR>"; }

			    }
				 

				}           
          //          print_r($group);
      
          } else {

           $insert = 0;
				if(isset($bdschedules[$schedule['league']['group']['event']['@attributes']['id']]->vars['id'])){
				if($bdschedules[$schedule['league']['group']['event']['@attributes']['id']]->vars['event_state'] != $schedule['league']['group']['event']['event_state']){
						$bdschedules[$schedule['league']['group']['event']['@attributes']['id']]->vars['event_state'] = $event['event_state'];
						$bdschedules[$schedule['league']['group']['event']['@attributes']['id']]->vars['updated_date'] = date("Y-m-d H:i:s");
						$bdschedules[$schedule['league']['group']['event']['@attributes']['id']]->update(array('event_state','updated_date'));
						echo "EVENT ".$bdschedules[$schedule['league']['group']['event']['@attributes']['id']]->vars['event_name']." UPDATED<BR>";
					}
					$insert = 0;
                   
				} else{ $insert = 1; }	
		  
		  if($insert){ 	
		
           if(isset($schedule['league']['group']['event']['participant'][0]['team']['@attributes']['name'])  && trim($schedule['league']['group']['event']['participant'][0]['team']['@attributes']['name']) != "" && $schedule['league']['group']['event']['event_type'] == 'team_event'){
					
            $schedules = new _Schedule;
			$schedules->vars['id_sport'] = $schedule['@attributes']['id'];
			$schedules->vars['id_league'] = $schedule['league']['@attributes']['id'];
			$schedules->vars['id_league_sub'] = $schedule['league']['group']['@attributes']['league_sub'];
			$schedules->vars['id_event'] =  $schedule['league']['group']['event']['@attributes']['id'];
			$schedules->vars['event_date'] = date_convert($schedule['league']['group']['event']['@attributes']['date']);
			$schedules->vars['event_name'] = $schedule['league']['group']['event']['@attributes']['name'];
			$schedules->vars['event_state'] = $schedule['league']['group']['event']['event_state'];
			$schedules->vars['time_changed'] = $schedule['league']['group']['event']['time_changed'];
			$schedules->vars['game_number'] = $schedule['league']['group']['event']['game_number'];
			$schedules->vars['event_location'] = $schedule['league']['group']['event']['location']['@attributes']['name'] ;
			$schedules->vars['rotation_away'] = $schedule['league']['group']['event']['participant'][0]['@attributes']['rot'];
			$schedules->vars['rotation_home'] = $schedule['league']['group']['event']['participant'][1]['@attributes']['rot'];
			$schedules->vars['name_away'] = $schedule['league']['group']['event']['participant'][0]['team']['@attributes']['name'];
			$schedules->vars['name_home'] = $schedule['league']['group']['event']['participant'][1]['team']['@attributes']['name'];
			$schedules->vars['updated_feed'] = date_convert($resultschedules['updated']);
			$schedules->vars['updated_date'] = date("Y-m-d H:i:s");
			$schedules->vars['added_date'] = date("Y-m-d");
			$schedules->insert();
 

			echo "schedule ".$schedules->vars['name_away']." VS  ".$schedules->vars['name_home']." --> NEW schedule ADDED 4 <BR>"; 
	     	} else { echo $schedule['@attributes']['name']." EVENT ID ".$schedule['league']['group']['event']['@attributes']['id']." Type: ".$schedule['league']['group']['event']['event_type']." Does not have particpants<BR>"; }
	     }

          }	




		}

		
		//print_r($schedule['league']['group']);
		echo "<BR><BR><BR>";
   		

  // }  //if sport 
 }
}


?>