<?php

function users_login($username, $password) {

	if(users_username_exists($username)) {

		require 'base.php';

		$password = hash('sha256', $password);

		if($stmt = mysqli_prepare($con,
			"SELECT isActive, id
			FROM users
			WHERE username = ? AND password = ?")) {

			mysqli_stmt_bind_param($stmt, 'ss', $username, $password);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_store_result($stmt);

			if(mysqli_stmt_num_rows($stmt)==1) {
				mysqli_stmt_bind_result($stmt, $isActive, $userId);
				mysqli_stmt_fetch($stmt);

				if($isActive) {
					$token = token_generate($userId, 'user');
					log_login('user', $userId, 'API Login');
					return $token;
				}
			}
		}
	}
	return false;
}

function users_register($username, $password, $email) {
	if(!empty($username) && !empty($password) && !empty($email)) {
	if(users_email_exists($email)) {
		return 'Emails already exists';
	} elseif (users_username_exists($username)) {
		return 'Username already exists';
	} else {
		require 'base.php';

		$password = hash('sha256', $password);

		if($stmt = mysqli_prepare($con,
			"INSERT INTO users (username, password, email, isActive)
			VALUES (?, ?, ?, true)")) {

			mysqli_stmt_bind_param($stmt, 'sss', $username, $password, $email);
			mysqli_stmt_execute($stmt);

			if($stmt->affected_rows > 0) {
				return 'Success';
			}
		} else {
			return 'An error occured, please try again';
		}
	}
}
	return false;
}

function users_username_exists($username) {
	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"SELECT id
		FROM users
		WHERE username = ? AND isActive = true")) {

		mysqli_stmt_bind_param($stmt, 's', $username);
		mysqli_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt)==1) {
			return true;
		}
	}
	return false;
}

function users_email_exists($email) {
	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"SELECT id
		FROM users
		WHERE email = ?")) {

		mysqli_stmt_bind_param($stmt, 's', $email);
		mysqli_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt)==1) {
			return true;
		}
	}
	return false;
}

function user_edit_get_details($token) {
	if($userId = user_get_by_token($token)) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
			"SELECT username, email, name, surname
			FROM users
			WHERE id = ?")) {

			mysqli_stmt_bind_param($stmt, 'i', $userId);
			mysqli_execute($stmt);
			mysqli_stmt_store_result($stmt);

			if(mysqli_stmt_num_rows($stmt) > 0) {
				mysqli_stmt_bind_result($stmt, $username, $email, $name, $surname);
				mysqli_stmt_fetch($stmt);

				$returnArray = array();

				$returnArray['username'] = $username;
				$returnArray['email'] = $email;
				$returnArray['name'] = $name;
				$returnArray['surname'] = $surname;

				return json_encode($returnArray);
			}
		}
	}
	return false;
}

function users_edit_user($details, $token) {

	if((isset($details['name']) || isset($details['surname']) || isset($details['email']) &&
		token_is_active($token))) {
		require 'base.php';

		$userId = user_get_by_token($token);

		if(!is_null($details['name'])) {
			if($stmt = mysqli_prepare($con,
				"UPDATE users
				SET name = ?
				WHERE id = ?")) {

				mysqli_stmt_bind_param($stmt, 'si', $details['name'], $userId);
				mysqli_stmt_execute($stmt);


			}
		}

		if(!is_null($details['surname'])) {
			if($stmt = mysqli_prepare($con,
				"UPDATE users
				SET surname = ?
				WHERE id = ?")) {

				mysqli_stmt_bind_param($stmt, 'si', $details['surname'], $userId);
				mysqli_stmt_execute($stmt);

			}
		}

		if(!is_null($details['email'])) {
			if($stmt = mysqli_prepare($con,
				"UPDATE users
				SET email = ?
				WHERE id = ?")) {

				mysqli_stmt_bind_param($stmt, 'si', $details['email'], $userId);
				mysqli_stmt_execute($stmt);

			}
		}

		return true;
	}
	return false;
}

function users_get_id($username) {
	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"SELECT id
		FROM users
		WHERE username = ? AND isActive = true")) {

		mysqli_stmt_bind_param($stmt, 's', $username);
		mysqli_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt)==1) {
			mysqli_stmt_bind_result($stmt, $id);
			mysqli_stmt_fetch($stmt);

			return $id;
		}
	}
	return false;
}

function token_generate ($id, $type) {

	require 'base.php';

	$retryCount = 0; 
	$success = false;

	while($retryCount<3 && !$success) {
		if($stmt = mysqli_prepare($con,
			"INSERT INTO tokens (token, type, id, generatedTimestamp, generatedIp)
			VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?)")) {
	
			$randNum = uniqid(rand(), true);
			$tokenGenerated = hash('sha256', $randNum);
	
			mysqli_stmt_bind_param($stmt, 'ssis', $tokenGenerated, $type, $id, $_SERVER['REMOTE_ADDR']);
			mysqli_stmt_execute($stmt);
	
			if($stmt->affected_rows > 0) {
				$success = true;
				$stmt->close();
			}
		} else {
			$retryCount++;
		}
	}
	$con->close();
	if($success) {
		return $tokenGenerated;
	}
	return false;
}

function token_is_active_u ($token, $username) {

	if(users_username_exists($username)) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
			"SELECT token.token
			FROM tokens AS token
				INNER JOIN users AS user
					ON user.id = token.userId
			WHERE token.token = ? AND user.username = ?")) {

			mysqli_stmt_bind_param($stmt, 'ss', $token, $username);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_store_result($stmt);

			if(mysqli_stmt_num_rows($stmt) == 1) {
				return true;
			}
		}
	}
	return false;	
}

function token_is_active ($token) {

	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"SELECT *
		FROM tokens AS token
		WHERE token.token = ?")) {

		mysqli_stmt_bind_param($stmt, 's', $token);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt) == 1) {
			return true;
		}
	}
	return false;	
}

function user_get_by_token ($token) {
	if(token_is_active($token)) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
		"SELECT user.id
		FROM users AS user
			LEFT JOIN tokens AS token ON token.id = user.id
		WHERE token.type = 'user' AND token.token = ?")) {

		mysqli_stmt_bind_param($stmt, 's', $token);
		mysqli_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt)==1) {
			mysqli_stmt_bind_result($stmt, $id);
			mysqli_stmt_fetch($stmt);
			return $id;
		}
	}
	return false;
	}
}

function device_is_assigned ($serial) {
	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"SELECT userId
		FROM devices
		WHERE serial = ? AND isActive = 1")) {

		mysqli_stmt_bind_param($stmt, 's', $serial);
		mysqli_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt) > 0) {
			mysqli_stmt_bind_result($stmt, $userId);
			mysqli_stmt_fetch($stmt);
			return $userId;
		}
	}
	return false;
}

function device_key_generate ($deviceId, $userId) {
	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"SELECT token
		FROM device_tokens
		WHERE userId = ? AND deviceId = ?")) {

		mysqli_stmt_bind_param($stmt, 'ii', $userId, $deviceId);
		mysqli_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt) <= 0) {
			$rand = $userId.uniqid(rand(), true).$deviceId;
			$key = hash('sha256', $rand);
			$keySub = substr($key, 15, 8);
			if(device_key_insert($userId, $deviceId, $keySub)) {
				return $keySub;
			}
		}
	}
	return false;
}

function device_key_insert ($userId, $deviceId, $deviceKey) {
	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"INSERT INTO device_tokens (deviceId, userId, token)
		VALUES (?, ?, ?)")) {

		mysqli_stmt_bind_param($stmt, 'iis', $deviceId, $userId, $deviceKey);
		mysqli_stmt_execute($stmt);

		if(mysqli_stmt_affected_rows($stmt) > 0) {
			return true;
		}
	}
	return false;
}

function device_login ($serial, $key) {
	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"SELECT device.id
		FROM devices AS device
			INNER JOIN device_tokens AS tokens
				ON tokens.deviceId = device.id
		WHERE device.serial = ? AND tokens.token = ?")) {

		mysqli_stmt_bind_param($stmt, 'ss', $serial, $key);
		mysqli_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt) > 0) {
			mysqli_stmt_bind_result($stmt, $deviceId);
			mysqli_stmt_fetch($stmt);

			if($token = token_generate($deviceId, 'device')) {
				return $token;
			}
		}
	}
	return false;
}

function device_user_add ($serial, $token) {
	if(token_is_active($token) && device_is_manufactured($serial) &&
		device_is_assigned($serial) == false) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
			"INSERT INTO devices (userId, serial, isActive)
			VALUES (?, ?, true)")) {

			$userId = user_get_by_token($token);

			mysqli_stmt_bind_param($stmt, 'is', $userId, $serial);
			mysqli_stmt_execute($stmt);

			if(mysqli_stmt_affected_rows($stmt) > 0) {
				$deviceId = device_get_by_serial($serial);
				s_device_status_create($deviceId);

				return true;
			}
		}
	}
	return false;
}

function device_is_manufactured ($serial) {
	require 'base.php';

	if($stmt = mysqli_prepare ($con,
		"SELECT *
		FROM manufactured_devices
		WHERE serial = ?")) {

		mysqli_stmt_bind_param($stmt, 's', $serial);
		mysqli_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt) == 1) {
			return true;
		}
	}
	return false;
}

function device_get_by_token ($token) {
	if(token_is_active($token)) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
		"SELECT device.id
		FROM devices AS device
			LEFT JOIN tokens AS token ON token.id = device.id
		WHERE token.type = 'device' AND token.token = ?")) {

		mysqli_stmt_bind_param($stmt, 's', $token);
		mysqli_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt)==1) {
			mysqli_stmt_bind_result($stmt, $id);
			mysqli_stmt_fetch($stmt);

			return $id;
		}
	}
	return false;
	}
}

function device_user_get_by_token ($token) {

	if(token_is_active($token)) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
		"SELECT device.userId
		FROM devices AS device
			LEFT JOIN tokens AS token ON token.id = device.id
		WHERE token.type = 'device' AND token.token = ?")) {

		mysqli_stmt_bind_param($stmt, 's', $token);
		mysqli_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt) == 1) {
			mysqli_stmt_bind_result($stmt, $id);
			mysqli_stmt_fetch($stmt);
			return $id;

		}
	}
	return false;
	}
}

function device_get_by_serial ($serial) {
	if(device_is_manufactured($serial)) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
		"SELECT id
		FROM devices
		WHERE serial = ?")) {

		mysqli_stmt_bind_param($stmt, 's', $serial);
		mysqli_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt)==1) {
			mysqli_stmt_bind_result($stmt, $id);
			mysqli_stmt_fetch($stmt);

			return $id;
		}
	}
	return false;
	}
}

function device_edit ($serial, $details, $token) {
	$userId = user_get_by_token($token);
	$deviceConfirmation = device_is_assigned($serial);
	if($userId == $deviceConfirmation) {

		$deviceId = device_get_by_serial($serial);

		require 'base.php';
		
		if(!is_null($details['nickname'])) {
			if($stmt = mysqli_prepare($con,
				"UPDATE devices
				SET nickname = ?
				WHERE id = ?")) {

				mysqli_stmt_bind_param($stmt, 'si', $details['nickname'], $deviceId);
				mysqli_stmt_execute($stmt);
			}
		}

		if(!is_null($details['icon']) && $details['icon'] >=0 && $details['icon'] <= 11) {
			if($stmt = mysqli_prepare($con,
				"UPDATE devices
				SET user_icon_id = ?
				WHERE id = ?")) {

				mysqli_stmt_bind_param($stmt, 'si', $details['icon'], $deviceId);
				mysqli_stmt_execute($stmt);

			}
		}

		return true;
	}
	return false;	
}

function icon_get_by_id ($iconId) {
	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"SELECT file
		FROM icons
		WHERE id = ?"))  {

		mysqli_bind_param($stmt, 'i', $iconId);
		mysqli_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt) > 0) {
			mysqli_stmt_bind_result($stmt, $filename);
			mysqli_stmt_fetch($stmt);

			return $filename;
		}
	}
	return false;
}

function device_edit_get_details ($serial) {
	if($deviceId = device_get_by_serial($serial)) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
			"SELECT device.nickname, device.user_icon_id,
					type.name, type.iconId, type.class
			FROM devices AS device
			INNER JOIN manufactured_devices AS dev
				ON dev.serial = device.serial
			INNER JOIN device_types AS type
				ON type.type = dev.type
			WHERE device.serial = ?")) {

			mysqli_stmt_bind_param($stmt, 's', $serial);
			mysqli_execute($stmt);
			mysqli_stmt_store_result($stmt);

			if(mysqli_stmt_num_rows($stmt) > 0) {
				mysqli_stmt_bind_result($stmt, $devNick, $devUserIcon,
					$devName, $devIcon, $devClass);
				mysqli_stmt_fetch($stmt);

				$returnArray = array();

				if ($devNick != null) {
					$returnArray['nickname'] = $devNick;
				}
				if ($devUserIcon != null) {
					$returnArray['userIconId'] = $devUserIcon;
				}
				$returnArray['name'] = $devName;
				$returnArray['iconId'] = $devIcon;
				$returnArray['class'] = $devClass;

				return json_encode($returnArray);
			}
		}
	}
	return false;
}

function device_unlink($serial, $token) {
	if($userId = user_get_by_token($token) && $deviceId = device_get_by_serial($serial)) {
		$userId = user_get_by_token($token);
		require 'base.php';
		if($stmt = mysqli_prepare($con,
			"DELETE FROM device_tokens
			WHERE deviceId = ? AND userId = ?")) {

			mysqli_stmt_bind_param($stmt, 'ii', $deviceId, $userId);
			mysqli_stmt_execute($stmt);

			//echo $userId;

			if(mysqli_stmt_affected_rows($stmt) > 0) {
				token_remove_by_device($deviceId);
				s_device_status_remove($deviceId);
				device_set_inactive($deviceId);

				return true;
			}
		}
	}
	return false;
}

function device_get_class_by_serial($serial) {
	if(device_is_manufactured($serial)) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
			"SELECT type.class
			FROM manufactured_devices AS device
			INNER JOIN device_types AS type
				ON device.type = type.type
			WHERE device.serial = ?")) {

			mysqli_bind_param($stmt, 's', $serial);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_store_result($stmt);

			if(mysqli_stmt_num_rows($stmt) == 1) {
				mysqli_stmt_bind_result($stmt, $class);
				mysqli_fetch($stmt);

				return $class;
			}
		}
	}
	return false;
}

function s_device_status_create ($deviceId) {
	if(!s_device_status_exists($deviceId)) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
			"INSERT INTO s_device_status (deviceId)
			VALUES (?)")) {

			mysqli_stmt_bind_param($stmt, 'i', $deviceId);
			mysqli_execute($stmt);

			if(mysqli_stmt_affected_rows($stmt) > 0 ) {
				return true;
			}
		}
	}
	return false;
}

function s_device_status_remove ($deviceId) {
	if(s_device_status_exists($deviceId)) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
			"DELETE FROM s_device_status
			WHERE deviceId = ?")) {
			mysqli_stmt_bind_param($stmt, 'i', $deviceId);
			mysqli_execute($stmt);

			if(mysqli_stmt_affected_rows($stmt) > 0) {
				return true;
			}
		}
	}
	return false;
}

function s_device_status_exists ($deviceId) {
	require 'base.php';
	if($stmt = mysqli_prepare($con,
		"SELECT *
		FROM s_device_status
		WHERE deviceId = ?")) {

		mysqli_stmt_bind_param($stmt, 'i', $deviceId);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_store_result($stmt);

		if(mysqli_stmt_num_rows($stmt) == 1) {
			return true;
		}
	}
	return false;
}

function device_get_status($serial, $token) {

	if(token_is_active($token) && device_is_assigned($serial)) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
			"SELECT s_device_status.pinData
			FROM s_device_status
			INNER JOIN devices AS device
				ON s_device_status.deviceId = device.id
			WHERE device.serial = ?")) {

			mysqli_stmt_bind_param($stmt, 's', $serial);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_store_result($stmt);

			if(mysqli_stmt_num_rows($stmt) == 1) {
				mysqli_stmt_bind_result($stmt, $pinData);
				mysqli_stmt_fetch($stmt);

				if($pinData == true) {
					return 'on';
				} else {
					return 'off';
				}
			}
		}
	}
	return false;
}

function device_set_status($serial, $token, $status) {
	if(token_is_active($token) && device_is_assigned($serial) &&
		($status == 'on' || $status == 'off')) {

		require 'base.php';

		if($status == 'on') {
			$newStatus = true;
		} else {
			$newStatus = false;
		}

		if($stmt = mysqli_prepare($con,
			"UPDATE s_device_status
			INNER JOIN devices AS device
				ON s_device_status.deviceId = device.id
			SET s_device_status.pinData = ?
			WHERE device.serial = ?")) {

			mysqli_stmt_bind_param($stmt, 'is', $newStatus, $serial);
			mysqli_stmt_execute($stmt);

			if(mysqli_stmt_affected_rows($stmt) >= 0) {
				return true;
			}
		}
	}
	return false;
}

function sensor_temphum_log($serial, $token, $temp, $hum) {

	if($userId = device_user_get_by_token($token) && $deviceId = device_get_by_serial($serial)) {
		require 'base.php';
		$userId = device_user_get_by_token($token);

		if($stmt = mysqli_prepare($con,
			"INSERT INTO sensor_log_temphum (deviceId, userId, temp, hum, timestamp)
			VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)")) {

			mysqli_stmt_bind_param($stmt, 'iidd', $deviceId, $userId, $temp, $hum);
			mysqli_stmt_execute($stmt);

			if(mysqli_stmt_affected_rows($stmt) > 0) {
				return true;
			}
		}
	}
	return false;
}

function sensor_motion_log($serial, $token, $motion) {
	if($userId = device_user_get_by_token($token) && $deviceId = device_get_by_serial($serial)) {
		require 'base.php';
		$userId = device_user_get_by_token($token);
		if($stmt = mysqli_prepare($con,
			"INSERT INTO sensor_log_motion (deviceId, userId, activity, timestamp)
			VALUES (?, ?, ?, CURRENT_TIMESTAMP)")) {

			mysqli_stmt_bind_param($stmt, 'iii', $deviceId, $userId, $motion);
			mysqli_stmt_execute($stmt);

			if(mysqli_stmt_affected_rows($stmt) > 0) {
				return true;
			}
		}
	}
	return false;
}

function sensor_temhum_get($serial, $token) {
	if(token_is_active($token) && ($deviceId = device_get_by_serial($serial)) &&
		($userId = user_get_by_token($token))) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
			"SELECT temp, hum, timestamp
			FROM sensor_log_temphum
			WHERE deviceId = ? AND userId = ?
			ORDER BY id DESC LIMIT 1")) {

			mysqli_stmt_bind_param($stmt, 'ii', $deviceId, $userId);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_store_result($stmt);

			if(mysqli_stmt_num_rows($stmt) > 0 ) {
				mysqli_stmt_bind_result($stmt, $temp, $hum, $time);
				mysqli_stmt_fetch($stmt);

				$returnArray = array();
				$returnArray['temp'] = $temp;
				$returnArray['hum'] = $hum;
				$returnArray['timestamp'] = $time;
				//echo $temp;

				return json_encode($returnArray);
			}
		}
	}
	return false;
}

function sensor_motion_get($serial, $token) {
	if(token_is_active($token) && ($deviceId = device_get_by_serial($serial)) &&
		($userId = user_get_by_token($token))) {
		require 'base.php';
		$userId = user_get_by_token($token);
		if($stmt = mysqli_prepare($con,
			"SELECT activity, timestamp
			FROM sensor_log_motion
			WHERE deviceId = ? AND userId = ?
			ORDER BY id DESC LIMIT 1")) {

			mysqli_stmt_bind_param($stmt, 'ii', $deviceId, $userId);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_store_result($stmt);

			if(mysqli_stmt_num_rows($stmt) > 0 ) {
				mysqli_stmt_bind_result($stmt, $motion, $time);
				mysqli_stmt_fetch($stmt);

				$returnArray = array();
				$returnArray['motion'] = $motion;
				$returnArray['timestamp'] = $time;

				return json_encode($returnArray);
			}
		}
	}
	return false;
}

function user_get_devices_status ($token) {
	//plus on status
	if($userId = user_get_by_token($token)) {
		require 'base.php';
		if($stmt = mysqli_prepare($con,
			"SELECT device.serial, dev.type
			FROM devices AS device
			INNER JOIN manufactured_devices AS dev
				ON device.serial = dev.serial
			WHERE device.userId = ?")) {

			mysqli_stmt_bind_param($stmt, 'i', $userId);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_store_result($stmt);

			if(mysqli_stmt_num_rows($stmt) > 0) {
				mysqli_stmt_bind_result($stmt, $devSerial, $devType);

				$idCount = 0;
				$returnArray = array();

				while(mysqli_stmt_fetch($stmt)) {
					$returnArray[$idCount]['serial'] = $devSerial;
					$returnArray[$idCount]['type'] = $devType;
					if($devType == 'plug' || $devType == 'switch' || $devType == 'dev') {
						$devStatus = device_get_status($devSerial, $token);
						$returnArray[$idCount]['status'] = $devStatus;
					} elseif($devType == 'motion') {
						$values = sensor_motion_get($devSerial, $token);
						$activity = json_decode($values, true);
						$returnArray[$idCount]['activity'] = $activity['motion'];
						$returnArray[$idCount]['time'] = $activity['timestamp'];
					} elseif ($devType == 'temphum') {
						$values = sensor_temhum_get($devSerial, $token);
						$values = json_decode($values, true);
						$returnArray[$idCount]['temp'] = $values['temp'];
						$returnArray[$idCount]['hum'] = $values['hum'];
						$returnArray[$idCount]['time'] = $values['timestamp'];
						//$returnArray[$idCount]['temp'] = $values['timestamp'];
					}
					$idCount++;
				}
				return json_encode($returnArray);
			}
		}
	}
	return false;
}

function user_get_device_list ($token) {

	if($userId = user_get_by_token($token)) {
		require 'base.php';
		if($stmt = mysqli_prepare($con,
			"SELECT device.serial, device.nickname, device.user_icon_id,
					type.name, type.iconId, type.class,
					status.onStatus,
					dev.type
			FROM devices AS device
			INNER JOIN manufactured_devices AS dev
				ON device.serial = dev.serial
			INNER JOIN device_types AS type
				ON dev.type = type.type
			INNER JOIN s_device_status AS status
				ON device.id = status.deviceId
			WHERE device.userId = ?
			ORDER BY type.class ASC")) {

			mysqli_stmt_bind_param($stmt, 'i', $userId);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_store_result($stmt);

			if(mysqli_stmt_num_rows($stmt) > 0 ) {
				mysqli_stmt_bind_result($stmt, $devSerial, $devNickname, $devUserIcon,
						$devName, $devIcon, $devClass, $devIsOn, $devType);
				$idCount = 0;

				$returnArray = array();

				while(mysqli_stmt_fetch($stmt)) {
					if(!is_null($devNickname)) {
						$devName = $devNickname;
					}
					if(!is_null($devUserIcon)) {
						$devIcon = $devUserIcon;
					}

					$returnArray[$idCount]['serial'] = $devSerial;
					$returnArray[$idCount]['name'] = $devName;
					$returnArray[$idCount]['icon'] = $devIcon;
					$returnArray[$idCount]['class'] = $devClass;
					$returnArray[$idCount]['isOn'] = $devIsOn;
					$returnArray[$idCount]['type'] = $devType;

					$idCount++;
				}

				return json_encode($returnArray);
			}
		}
	}
	return false;
}

function device_set_inactive ($deviceId) {
	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"DELETE FROM devices
		WHERE id = ?")) {

		mysqli_stmt_bind_param($stmt, 'i', $deviceId);
		mysqli_stmt_execute($stmt);

		if(mysqli_stmt_affected_rows($stmt) > 0) {
			return true;
		}
	}
	return false;
}

function device_report_on($token) {
	if($deviceId = device_get_by_token($token)) {
		require 'base.php';

		if($stmt = mysqli_prepare($con,
			"UPDATE s_device_status
			SET lastOn = CURRENT_TIMESTAMP, onStatus = 1
			WHERE deviceId = ?")) {

			mysqli_stmt_bind_param($stmt, 'i', $deviceId);
			mysqli_stmt_execute($stmt);
			//echo mysqli_stmt_affected_rows($stmt);
			if(mysqli_stmt_affected_rows($stmt) > 0) {
				return true;
			}
		}
	}
	return false;
}

function token_remove ($token) {
//log_login(logout)
	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"ELETE FROM tokens
		WHERE token = ?")) {

		mysqli_stmt_bind_param($stmt, 's', $token);
		mysqli_stmt_execute($stmt);

		if(mysqli_stmt_affected_rows($stmt) > 0) {
			return true;
		}
	}
	return false;
}

function token_remove_by_device ($deviceId) {
//log_login(logout)
	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"ELETE FROM tokens
		WHERE type = 'device' AND id = ?")) {

		mysqli_stmt_bind_param($stmt, 'i', $deviceId);
		mysqli_stmt_execute($stmt);

		if(mysqli_stmt_affected_rows($stmt) > 0) {
			return true;
		}
	}
	return false;
}

function token_remove_by_user($userId) {
	//log_login(logout)
	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"ELETE FROM tokens
		WHERE type = 'user' AND id = ?")) {

		mysqli_stmt_bind_param($stmt, 'i', $userId);
		mysqli_stmt_execute($stmt);

		if(mysqli_stmt_affected_rows($stmt) > 0) {
			return true;
		}
	}
	return false;
}

function log_login ($type, $typeId, $comment) {

	require 'base.php';

	if($stmt = mysqli_prepare($con,
		"INSERT INTO logs_login (type, typeId, comment, timestamp, ip-address)
		VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?)")) {

		mysqli_stmt_bind_param($stmt, 'siss', $type, $typeId, $comment, $_SERVER['REMOTE_ADDR']);
		mysqli_stmt_execute($stmt);

		if($stmt->affected_rows > 0) {
			return true;
		}
	}
	return false;
}

?>