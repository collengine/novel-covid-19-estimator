<?php

	header("Content-Type:application/json");
	$postData = file_get_contents('php://input');
	$data=json_decode($postData,true);


	$response = covid19ImpactEstimator($data);
	echo json_encode($response, JSON_PRETTY_PRINT);




//Functions
////////////////////////////////////////////////////////////////////////////////////////////////////
	function covid19ImpactEstimator($data){
				// Region Data
		$name= $data['data']['region']['name'];
		$avgAge= $data['data']['region']['avgAge'];
		$avgDailyIncomeInUSD= $data['data']['region']['avgDailyIncomeInUSD'];
		$avgDailyIncomePopulation= $data['data']['region']['avgDailyIncomePopulation'];

		// Region Data
		$periodType= $data['data']['periodType'];
		$timeToElapse= $data['data']['timeToElapse'];
		$reportedCases= $data['data']['reportedCases'];
		$population= $data['data']['population'];
		$totalHospitalBeds= $data['data']['totalHospitalBeds'];


	//Impact Data
	////////////////////////////////////////////////////////////////////////////////////////////////////
		$currentlyInfected = getCurrentlyInfected($reportedCases);
		$days = (int)getDays($periodType, $timeToElapse);
		$factor = (int)($days/3);


		$infectionsByRequestedTime = getInfectionsByRequestedTime($factor, $currentlyInfected);
		$severeCasesByRequestedTime = getSevereCasesByRequestedTime($infectionsByRequestedTime);
		$hospitalBedsByRequestedTime = getHospitalBedsByRequestedTime($severeCasesByRequestedTime, $totalHospitalBeds);
		$casesForICUByRequestedTime = getCasesForICUByRequestedTime($infectionsByRequestedTime);
		$casesForVentilatorsByRequestedTime = getCasesForVentilatorsByRequestedTime($infectionsByRequestedTime);
		$dollarsInFlight = getDollarsInFlight($infectionsByRequestedTime, $days, $avgDailyIncomeInUSD, $avgDailyIncomePopulation );


		//Severe Impact Data
		////////////////////////////////////////////////////////////////////////////////////////////////////
		$currentlyInfectedSevere = getCurrentlyInfectedSevere($reportedCases);
		$infectionsByRequestedTimeSevere = getInfectionsByRequestedTime($factor, $currentlyInfectedSevere);
		$severeCasesByRequestedTimeSevere = getSevereCasesByRequestedTime($infectionsByRequestedTimeSevere);
		$hospitalBedsByRequestedTimeSevere = getHospitalBedsByRequestedTime($severeCasesByRequestedTimeSevere, $totalHospitalBeds);
		$casesForICUByRequestedTimeSevere = getCasesForICUByRequestedTime($infectionsByRequestedTimeSevere);
		$casesForVentilatorsByRequestedTimeSevere = getCasesForVentilatorsByRequestedTime($infectionsByRequestedTimeSevere);
		$dollarsInFlightSevere = getDollarsInFlight($infectionsByRequestedTimeSevere, $days, $avgDailyIncomeInUSD, $avgDailyIncomePopulation );

	//Process
	////////////////////////////////////////////////////////////////////////////////////////////////////

		$impact =  array(
			"currentlyInfected"=>$currentlyInfected,
			"infectionsByRequestedTime"=>$infectionsByRequestedTime, 
			"severeCasesByRequestedTime"=>$severeCasesByRequestedTime,
			"hospitalBedsByRequestedTime"=>$hospitalBedsByRequestedTime, 
			"casesForICUByRequestedTime"=>$casesForICUByRequestedTime, 
			"casesForVentilatorsByRequestedTime"=>$casesForVentilatorsByRequestedTime,
			"dollarsInFlight"=>$dollarsInFlight, 
		);
		$severeImpact =  array(
			"currentlyInfected"=>$currentlyInfectedSevere,
			"infectionsByRequestedTime"=>$infectionsByRequestedTimeSevere, 
			"severeCasesByRequestedTime"=>$severeCasesByRequestedTimeSevere,
			"hospitalBedsByRequestedTime"=>$hospitalBedsByRequestedTimeSevere, 
			"casesForICUByRequestedTime"=>$casesForICUByRequestedTimeSevere, 
			"casesForVentilatorsByRequestedTime"=>$casesForVentilatorsByRequestedTimeSevere,
			"dollarsInFlight"=>$dollarsInFlightSevere, 
		);
		$estimate = array("impact"=>$impact,"severeImpact"=>$severeImpact );
		$data = array("estimate"=>$estimate);

		
		return $data;
	}









	function getCurrentlyInfected($reportedCs){
		return ($reportedCs * 10);
	}
	function getCurrentlyInfectedSevere($reportedCs){
		return ($reportedCs * 50);
	}
	function getInfectionsByRequestedTime($factor, $crtlyInfected){
		return $crtlyInfected * (pow(2, $factor));
	}
	function getSevereCasesByRequestedTime($infectionsBRT){
		return (int)($infectionsBRT * 0.15);
	}
	function getHospitalBedsByRequestedTime($severeCasesBRT, $totalHospitalBeds){
		$availableBeds = (int)(0.35 * $totalHospitalBeds);
		return $availableBeds - $severeCasesBRT ;
	}
	function getCasesForICUByRequestedTime($infectionsBRT){
		return (int)($infectionsBRT * 0.05);
	}
	function getCasesForVentilatorsByRequestedTime($infectionsBRT){
		return (int)($infectionsBRT * 0.02);
	}
	function getDollarsInFlight($infectionsBRT, $days, $avgDailyIncomeInUSD, $avgDailyIncomePopulation ){
		return (int)(($infectionsBRT * $avgDailyIncomeInUSD * $avgDailyIncomePopulation * $days) );
	}
	function getDays($type, $value){
		switch ($type) {
			case "days":
				return $value;
				break;
			case "weeks":
				return ($value*7);
				break;
			case "months":
				return ($value*30);
				break;
		}
	}













	class User {
	
		private $name;
		private $age;
	
		function __construct( $name, $age ) {
			$this->name = $name;
			$this->age = $age;
		}
	
		function getName() {
			return $this->name;
		}
	
		function isAdult() {
			return $this->age >= 18?"an Adult":"Not an Adult";
		}
	
	}

?>