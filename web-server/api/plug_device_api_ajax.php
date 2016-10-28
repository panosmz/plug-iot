<?php
ini_set('error_reporting', E_ALL);
	
	session_start();
	$root = realpath($_SERVER["DOCUMENT_ROOT"]);

	require $root.'/api/plug_api_functions.php';

	$returnMessage = array('success'=>'ERROR');

	$data = json_decode(file_get_contents('php://input'), true);
	

	if(isset($data['action']) && !empty($data['action'])) {
		$action = $data['action'];

		if($action == 'login' && isset($data['serial']) &&  isset($data['key'])) {
			if($token = device_login($data['serial'], $data['key'])) {
				$returnMessage['success'] = 'OK';
				$returnMessage['token'] = $token;
				device_report_on($token);
				
			}
		} else {
			if(isset($data['token']) && isset($data['serial'])) {
				if($action == 'deviceGetStatus') {
					if($status = device_get_status($data['serial'], $data['token'])) {
						if($status == 'on') {
							$returnMessage['status'] = 1;
						} else {
							$returnMessage['status'] = 0;
						}
						device_report_on($data['token']);
						$returnMessage['success'] = 'OK';
					}
				} elseif ($action == 'logTemphum' && isset($data['temp']) && isset($data['hum'])) {
					if(sensor_temphum_log($data['serial'], $data['token'], $data['temp'], $data['hum'])) {
						device_report_on($data['token']);
						$returnMessage['success'] = 'OK';
					}

				} elseif ($action == 'logMotion' && isset($data['motion'])) {
					if(sensor_motion_log($data['serial'], $data['token'], $data['motion'])) { 
						device_report_on($data['token']);
						$returnMessage['success'] = 'OK';
					}
				}
			}
		}
	}
	echo json_encode($returnMessage);
?>