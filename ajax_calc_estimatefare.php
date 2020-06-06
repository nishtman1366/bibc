<?
include_once("common.php");
include_once('generalFunctions.php');

$from_lat = isset($_REQUEST['from_lat'])?$_REQUEST['from_lat']:'';
$from_long = isset($_REQUEST['from_long'])?$_REQUEST['from_long']:'';
$to_lat = isset($_REQUEST['to_lat'])?$_REQUEST['to_lat']:'';
$to_long = isset($_REQUEST['to_long'])?$_REQUEST['to_long']:'';
$vehicle_type_id = isset($_REQUEST['vehicle_type_id'])?$_REQUEST['vehicle_type_id']:'';


if($from_lat != '' && $to_lat != "" && $vehicle_type_id != "")
{
	$vCurrencyPassenger= $_SESSION['sess_currency'];
    if($vCurrencyPassenger == '')
        $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault','Yes','','true');

    /*$GOOGLE_API_KEY="AIzaSyCGTySdOtlLxfQqmqqX1cG8bdW8MTHIloA"; //$generalobj->getConfigurations("configurations","GOOGLE_SEVER_API_KEY_WEB");
		$url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$from_lat.",".$from_long."&destination=".$to_lat.",".$to_long."&sensor=false&key=".$GOOGLE_API_KEY;

		try {
			$jsonfile = file_get_contents($url);
		} catch (ErrorException $ex) {
			exit;
		}

		$jsondata = json_decode($jsonfile);
		$distance_google_directions=($jsondata->routes[0]->legs[0]->distance->value)/1000;
		$duration_google_directions=($jsondata->routes[0]->legs[0]->duration->value)/60;

		$tripDistance = round($distance_google_directions,2);
		$tripDuration = round($duration_google_directions,2);*/
		try {
			$origin = $from_long.",".$from_lat;
			$destination = $to_long.",".$to_lat;
			$authToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjkxNDY2NTQ5ZTI1ZGYxNzlhMGM1YjUwZGUxMzM0ODQxNGVhNDBkNjI4YTdhNDE4Mzg3NDIxNzNiYjRhYzg2NjlhNzg3YzQ3MmFmMjRmMjgwIn0.eyJhdWQiOiJteWF3ZXNvbWVhcHAiLCJqdGkiOiI5MTQ2NjU0OWUyNWRmMTc5YTBjNWI1MGRlMTMzNDg0MTRlYTQwZDYyOGE3YTQxODM4NzQyMTczYmI0YWM4NjY5YTc4N2M0NzJhZjI0ZjI4MCIsImlhdCI6MTU0NDM2MTUzMywibmJmIjoxNTQ0MzYxNTMzLCJleHAiOjE1NDQzNjUxMzMsInN1YiI6IiIsInNjb3BlcyI6WyJiYXNpYyIsImVtYWlsIl19.Wct5e7Ph3TXCZcyDXzBjN0UEFxNcmzO0BTOTq7CZu_8QBTdB3ysqm0meXHavWT8OhZ3449wb-oIJDDgrtizt88vtZjuQwRM66COqIJ7SMI15eFRopuoNd8Vgzcri4CMb6sSc399ckCPKYplOg-iV-qaCXwdWroRGN_-bJH2c8jkgBD35jZQgJgglX0qMHUQAWLIHxdCX4cZLBBB3bmJ713J6S9FDnl-ozuqPDYw2mSdGzf7xgrwQWJ3j78HiMrKymMwiJPAn__axVBUfm2sR7UQFqpVBdwLcmI4PRAM-fGbRnNlrJOW3dqfZ81Svgl21_jMKWaYTaHAlw79TN0OMJw';
			$url = 'https://map.ir/routes/route/v1/driving/'.$origin.";".$destination."?alternatives=false&steps=false";
			$context = stream_context_create(array(
					'http' => array(
					'method' => 'GET',
					'header' => "x-api-key: {$authToken}\r\n"
			)));

			$response = file_get_contents($url, FALSE, $context);
			$responseData = json_decode($response, TRUE);

			if ($response == null || $responseData == null
								|| $responseData['code'] == null
								|| $responseData['code'] != "Ok")  {
									$returnArr['Action'] = "0";
									echo json_encode($returnArr);
									exit;
			}
			$tripDistance = round($responseData['routes'][0]['distance']/1000,2);
			$tripDuration = round($responseData['routes'][0]['duration']/60,2);
	    $priceRatio=get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger,'','true');

	    $Fare_data=calculateFareEstimate('false','false',0,$tripDuration,$tripDistance,$vehicle_type_id,1,1);

	    $Fare_data[0]['tripDistance'] = $tripDistance;
	    $Fare_data[0]['tripDuration'] = $tripDuration;

	    $Fare_data[0]['total_fare'] = intval($Fare_data[0]['total_fare']);
		} catch (\Exception $e) {
			echo json_encode($e->getMessage());
		}


    echo json_encode($Fare_data[0]);
    die();
}
?>




view-source:https://taxialo.com/app/ajax_calc_estimatefare.php?from_lat=34.791386496397884&from_long=48.51236925955345&to_lat=34.81844539474842&to_long=48.5162316405349&vehicle_type_id=1
