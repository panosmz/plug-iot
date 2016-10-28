<?php
	session_start();

	error_reporting(-1);
	$root = realpath($_SERVER["DOCUMENT_ROOT"]);

	require $root.'/api/plug_api_functions.php';

	$returnMessage = array('success'=>'ERROR', 'message'=>'An error occured.');
	

	if(isset($_POST['action']) && !empty($_POST['action'])) {

		$action = $_POST['action'];

		if($action == 'login') {
			if($loginToken = users_login($_POST['username'], $_POST['password'])) {
				$returnMessage['token'] = $loginToken;
				$returnMessage['success'] = 'SUCCESS';
				$returnMessage['message'] = 'Login Successfull';
				if($_POST['client'] == 'web') {
					$_SESSION['token'] = $loginToken;
					$_SESSION['username'] = $_POST['username'];
					$userDetails = json_decode(user_edit_get_details($loginToken), true);
					$_SESSION['email'] = $userDetails['email'];
				}		
			} else {
				$returnMessage['message'] = 'Could not login';
			}
		} elseif ($action == 'register') {
			if($registerMessage = users_register($_POST['username'], $_POST['password'], $_POST['email'])) {
				$returnMessage['registerMessage'] = $registerMessage;
				$returnMessage['message'] = 'Register Error';
				if($registerMessage == 'Success') {
					$returnMessage['success'] = 'SUCCESS';
					$returnMessage['message'] = 'Register Successfull';
				}
			} else {
				$returnMessage['message'] = 'Could not register user';
			}
		} else {
			if(isset($_POST['token']) && !empty($_POST['token'])) {
				if(token_is_active($_POST['token'])) {
					if($action == 'getDeviceList') {
						//get full device list
						if($deviceList = user_get_device_list($_POST['token'])) {
							$returnMessage['success'] = 'SUCCESS';
							$returnMessage['message'] = 'Got device list successfully';
							$returnMessage['deviceList'] = json_decode($deviceList);
						} else {
							$returnMessage['message'] = 'Could not get device list';
						}
					} elseif ($action == 'getDeviceUpdates') {
						//get only device updates
						if($deviceUpdates = user_get_devices_status($_POST['token'])) {
							$returnMessage['success'] = 'SUCCESS';
							$returnMessage['message'] = 'Got device updates successfully';
							$returnMessage['deviceList'] = json_decode($deviceUpdates);
						} else {
							$returnMessage['message'] = 'Could not get device updates';
						}
					} elseif ($action == 'removeDevice' && isset($_POST['serial'])) {
						//remove device
						if(device_unlink($_POST['serial'], $_POST['token'])) {
							$returnMessage['success'] = 'SUCCESS';
							$returnMessage['message'] = 'Device removed successfully';
						}
					}elseif ($action == 'setSDevice' && isset($_POST['serial']) && isset($_POST['status']) &&
						$_POST['status'] == 'on' || $_POST['status'] == 'off') {
						//set state of s device
						if(device_set_status($_POST['serial'], $_POST['token'], $_POST['status'])) {
							$returnMessage['success'] = 'SUCCESS';
							$returnMessage['message'] = 'Device status updated';
						} else {
							$returnMessage['message'] = 'Device status did not update';
						}
					} elseif ($action == 'editDevice' && isset($_POST['serial'])) {
						//edit device details
						$details['nickname'] = $_POST['nickname'];
						$details['icon'] = $_POST['icon'];
						if(device_edit($_POST['serial'], $details, $_POST['token'])) {
							$returnMessage['success'] = 'SUCCESS';
							$returnMessage['message'] = 'Device details updated';
						} else {
							$returnMessage['message'] = 'Device details did not update';
						}
					} elseif ($action == 'addDevice' && isset($_POST['serial'])) {
						//add new device
						if(device_user_add($_POST['serial'], $_POST['token'])) {
							$deviceId = device_get_by_serial($_POST['serial']);
							$userId = user_get_by_token($_POST['token']);
							if($deviceKey = device_key_generate($deviceId, $userId)) {
								$returnMessage['success'] = 'SUCCESS';
								$returnMessage['message'] = 'Device added successfully';
								$returnMessage['deviceKey'] = $deviceKey;
							} else {
								$returnMessage['message'] = 'Could not generate device key';
							}
						} else {
							$returnMessage['message'] = 'Could not add device';
						}
					} elseif ($action == 'createDevice') {
						//create new device
					} elseif ($action == 'getAccountDetails') {
						//get full account details
						if($details = user_edit_get_details($_POST['token'])) {
							$returnMessage['success'] = 'SUCCESS';
							$returnMessage['message'] = 'Got user details successfully';
							$returnMessage['userDetails'] = json_decode($details);
						} else {
							$returnMessage['message'] = 'Could not get user details';
						}
					} elseif ($action == 'editAccountDetails') {
						//edit account details
						$details['name'] = $_POST['name'];
						$details['surname'] = $_POST['surname'];
						$details['email'] = $_POST['email'];
						if(users_edit_user($details, $_POST['token'])) {
							$returnMessage['success'] = 'SUCCESS';
							$returnMessage['message'] = 'User details updated';
						} else {
							$registerMessage['message'] = 'Could not update user details';
						}
					} else {
						//error
						$returnMessage['message'] = 'Action not defined';
					}
				}
			}
		}
	}
	echo json_encode($returnMessage);

?>