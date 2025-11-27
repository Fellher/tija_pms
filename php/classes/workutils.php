<?php 
/**
 * Work class
 * */

class Workutils { 

	public static function client_project_array ($clientsArr, $DBConn) {
		if ($clientsArr) {
			$projectArray = array();
			foreach ($clientsArr as $c => $clientData) {
				$clientProjects= Projects::projects_mini (array('clientID'=> $clientData->clientID), false, $DBConn);
				if ($clientProjects) {
					foreach ($clientProjects as $key => $value) {					
						if ($value->clientID == $clientData->clientID ) {
							$projectArray[$clientData->clientName][] = $value;
						}
					}				
				}			
			}

			return count($projectArray) >0 ? $projectArray : false;
	
			// code...
		}
	}

	public static function phase_task_array ($phasesArr, $DBConn) {
		if ($phasesArr) {
			$taskPhaseArr = array();
			foreach ($phasesArr as $key => $phase) {	
				$phaseTasks=Projects::project_tasks(array('projectID'=> $phase->projectID, 'Suspended'=> 'N'), false, $DBConn);
				if ($phaseTasks) {
					foreach ($phaseTasks as $key => $phaseTask) {
						if ($phaseTask->projectPhaseID == $phase->projectPhaseID) {
							$taskPhaseArr[$phase->projectPhaseName][]=$phaseTask;
						}
					}
				}		
			}
			return count($taskPhaseArr) > 0 ? $taskPhaseArr : false;
		}
		return false;
	}

	public static function client_projects_array ($projects, $DBConn) {
		if ($projects) {
			$clientProjectArr = array();
			foreach ($projects as $key => $project) {	
				$clientData= Client::clients(array('clientID'=> $project->clientID), true, $DBConn);
				$projectData = Projects::projects_mini(array('projectID'=> $project->projectID), true, $DBConn);
				if ($clientData) {
					$clientProjectArr[$clientData->clientName][]=$projectData;
				}			
			}
			return count($clientProjectArr) > 0 ? $clientProjectArr : false;
		}
		return false;
	}
	public static function get_total_hours_in_month($month, $year, $DBConn) {
		$totalHours = 0;
		$currentMonth = $month ? $month : date('m');
		$currentYear = $year ? $year : date('Y');
	
		$totalDaysInMonth = date('t', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
		$totalWeekdaysInMonth = 0;
		for ($i = 1; $i <= $totalDaysInMonth; $i++) {
			$dayOfWeek = date('w', mktime(0, 0, 0, $currentMonth, $i, $currentYear));
			if ($dayOfWeek != 0 && $dayOfWeek != 6) { // Skip Sundays and Saturdays
				$totalWeekdaysInMonth++;
			}
		}
		$totalHours = $totalWeekdaysInMonth * 8; // Assuming 8 hours a day
		return $totalHours ? $totalHours : 0;
	}
}
		

