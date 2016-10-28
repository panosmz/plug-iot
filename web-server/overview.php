<?php

require 'site-functions/site-functions.php';
require_once 'api/plug_api_functions.php';


session_start();

if(user_access()) {
?>

<!doctype html>
<html lang="en">

<?php echo_head("Overview | Plug"); ?>

<body>
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-drawer
            mdl-layout--fixed-header">

<?php echo_navbar("Overview"); ?>

<main class="mdl-layout__content mdl-color--grey-100">
    <div class="mdl-grid overview-grid">

<?php
	if($deviceListJSON = user_get_device_list($_SESSION['token'])) {
        $deviceList = json_decode($deviceListJSON, true);
        foreach($deviceList as $device) {
            $dataSerial = ' data-serial="'.$device['serial'].'" ';
            if($device['class'] == "Device") {
                if($device['isOn'] == 1) {
                    $onColor = "mdl-color--deep-orange-400";
                    $onSwitch = ' ';
                    $onText = '<p '.$dataSerial.' data-value-text="device-offline-text"></p>';
                } else {
                    $onSwitch = ' disabled ';
                    $onText = '<p '.$dataSerial.' data-value-text="device-offline-text">Device Offline</p>';
                    $onColor = "mdl-color--grey-400";
                }
                ?>

<div class="device-card-small mdl-card mdl-shadow--2dp mdl-cell mdl-cell--4-col mdl-grid">
    <div class="mdl-card__title mdl-card--expand <?php echo $onColor; ?>">
        <h2 class="mdl-card__title-text"><?php echo $device['name']; ?></h2>
    </div>
    <div class="mdl-card__supporting-text mdl-color--white">
        <h5>On/Off Switch</h5>
        <?php echo $onText; ?>
    </div>
    <div class="mdl-card__menu">
        <label id="<?php echo $device['serial']; ?>" for="switch-<?php echo $device['serial']; ?>" class="mdl-switch mdl-js-switch mdl-js-ripple-effect">
<input type="checkbox" id="switch-<?php echo $device['serial']; ?>" class="mdl-switch__input">
</label>
    </div>
</div>


                <?php
            } elseif ($device['class'] == "Sensor") {
             if($device['isOn'] == 1) {
                $onColor = "mdl-color--light-green";
                $onText = '<p '.$dataSerial.' data-value-text="device-offline-text"></p>';
            } else {
                $onColor = "mdl-color--grey-400";
                $onText = '<p '.$dataSerial.' data-value-text="device-offline-text">Device Offline</p>';
            }
            if($device['type'] == 'temphum') {
                $deviceTypeFull = 'Temperature-Humidity Module';
                $sensorValueHtml = '<h4><span data-value-text="temperature"'.$dataSerial.'">-</span>°C</h4><h4><span data-value-text="humidity"'.$dataSerial.'"> -</span>%</h4>';
            } elseif ($device['type'] == 'motion') {
                $deviceTypeFull = 'Motion Detector';
                $sensorValueHtml = '<h4>Motion Level: <span data-value-text="motion"'.$dataSerial.'>-</span></h4>';
            }
            ?>
            <div class="device-card-small mdl-card mdl-shadow--2dp mdl-cell mdl-cell--4-col mdl-grid">
        <div class="mdl-card__title mdl-card--expand <?php echo $onColor ?>">
          <h2 class="mdl-card__title-text"><?php echo $device['name']; ?></h2>
        </div>
        <div class="mdl-card__supporting-text mdl-color--white">
          <h5><?php echo $deviceTypeFull; ?></h5>
          <p>Last updated <span class="last-updated-text" <?php echo $dataSerial; ?> data-value-text="last-updated-text">-</span></p>
          <?php  echo $onText; ?>
        </div>
        <div class="mdl-card__menu">
          <?php echo $sensorValueHtml; ?>
        </div>
      </div>
            <?php
            }
        }
    } else {
        echo_card_noDevices();
    }
?>
</div>

</main>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        updateDevices();

         setInterval(function () {
            updateDevices();
             },3000);

         $('.mdl-switch').click(function() {
            var clickedSerial = jQuery(this).attr("id");
            var status;
            if(jQuery(this).hasClass('is-checked')) {
                status = 'off';
            } else {
                status = 'on';
            }
            setSDeviceStatus(clickedSerial, status);
         });
    })
    function updateDevices() {
        $.ajax({
            method: "POST",
            url: "api/plug_api_ajax.php",
            data: { action: "getDeviceUpdates", token: userToken }
        })
        .done(function(msg){
            var parsedMsg = jQuery.parseJSON(msg);
            if(parsedMsg.success == "SUCCESS") {

                for(var i = 0; i<parsedMsg.deviceList.length; i++) {
                    var type = parsedMsg.deviceList[i].type;
                    var serial = parsedMsg.deviceList[i].serial;
                    if(type === 'dev' || type === 'switch') {
                        var status = parsedMsg.deviceList[i].status;
                        if(status == 'on') {
                           $('#'+serial).addClass('is-checked');
                        } else {
                            $('#'+serial).removeClass('is-checked');
                        }
                    } else if (type === 'temphum') {   
                        $('*[data-value-text="temperature"][data-serial="'+serial+'"]').html(parsedMsg.deviceList[i].temp);
                        $('*[data-value-text="humidity"][data-serial="'+serial+'"]').html(parsedMsg.deviceList[i].hum);
                        $('*[data-value-text="last-updated-text"][data-serial="'+serial+'"]').html(parsedMsg.deviceList[i].time);
                    } else if (type === 'motion') {
                         $('*[data-value-text="motion"][data-serial="'+serial+'"]').html(parsedMsg.deviceList[i].activity);
                         $('*[data-value-text="last-updated-text"][data-serial="'+serial+'"]').html(parsedMsg.deviceList[i].time);
                    }
                }
            }
        });
    }
    function setSDeviceStatus(serial, status) {
        $.ajax({
            method: "POST",
            url: "api/plug_api_ajax.php",
            data: { action: "setSDevice", token: userToken, serial: serial, status: status }
        })
        .done(function(msg) {
            var parsedMsg = jQuery.parseJSON(msg);
            if(parsedMsg.success == "SUCCESS") {
                updateDevices();
            }
        });
    }
</script>
</body>


<?php } ?>