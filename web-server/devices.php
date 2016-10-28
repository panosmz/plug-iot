<?php

require 'site-functions/site-functions.php';
require_once 'api/plug_api_functions.php';

session_start();

if(user_access()) {
?>
<!doctype html>
<html lang="en">
  <?php echo_head("Devices | Plug"); ?>
  <body>
    <div class="mdl-layout mdl-js-layout mdl-layout--fixed-drawer
      mdl-layout--fixed-header">
      <?php echo_navbar("Devices"); ?>
      <main class="mdl-layout__content mdl-color--grey-100">
      <div class="mdl-grid overview-grid">
        <?php
        if(isset($_GET['edit']) || isset($_GET['remove'])) {
          if(isset($_GET['edit'])) {
            $editSerial = $_GET['edit'];
            $deviceDetails = json_decode(device_edit_get_details($editSerial), true);
            $deviceName = $deviceDetails['name'];
            ?>
            <div class="mdl-card mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--6-col mdl-grid">
  <div class="mdl-card__title">
    <h2 class="mdl-card__title-text">Edit Device</h2>
  </div>
  <div class="mdl-card__supporting-text">
    <div class="mdl-textfield mdl-js-textfield">
          <input value="<?php echo $deviceName; ?>" class="mdl-textfield__input " type="text" id="deviceName">
          <label class="mdl-textfield__label mdl-color-text--blue-grey-500" for="deviceName">Device Nickname</label>
        </div><br>
  </div>
  <div class="mdl-card__actions mdl-card--border">
    <a id="editDeviceButton" data-serial="<?php echo $editSerial; ?>" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
      Save Changes
    </a>
    <a href="/devices.php" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
      Back
    </a>
  </div>
</div>
          <?php
          } else {
            $removeSerial = $_GET['remove'];
            $deviceDetails = json_decode(device_edit_get_details($removeSerial), true);
            $deviceName = $deviceDetails['name'];
            ?>
<div class="mdl-card mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--6-col mdl-grid">
  <div class="mdl-card__title">
    <h2 class="mdl-card__title-text">Remove Device?</h2>
  </div>
  <div class="mdl-card__supporting-text">
    Are you sure you want to remove '<?php echo $deviceName; ?>'?
  </div>
  <div class="mdl-card__actions mdl-card--border">
    <a id="removeDeviceButton" data-serial="<?php echo $removeSerial; ?>" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
      Confirm
    </a>
    <a href="/devices.php" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
      Back
    </a>
  </div>
</div>

            <?php
          }
        } else {
        if($deviceListJSON = user_get_device_list($_SESSION['token'])) {
        ?>
        <a href="/add-device.php" id="view-source" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-color--accent mdl-color-text--accent-contrast"><i class="material-icons">add</i></a>
        <?php
        $deviceList = json_decode($deviceListJSON, true);
        foreach($deviceList as $device) {
        $deviceSerial = $device['serial'];
        if($device['class'] == "Device") {
        $deviceColor = "mdl-color--deep-orange-400";
        } else {
        $deviceColor = "mdl-color--light-green";
        }
        ?>
        <div class="device-card-small mdl-card mdl-shadow--2dp mdl-cell mdl-cell--6-col mdl-grid">
          <div class="mdl-card__title mdl-card--expand <?php echo $deviceColor; ?>">
            <h2 class="mdl-card__title-text"><?php echo $device['name']; ?></h2>
          </div>
          <div class="mdl-card__supporting-text mdl-color--white">
            <?php
            if($device['type'] == 'temphum') {
            $deviceFullName = "Temperature-Humidity Module";
            } elseif($device['type'] == 'motion') {
            $deviceFullName = "Motion Sensor";
            } else {
            $deviceFullName = "On/Off Switch";
            }
            ?>
            <h5><?php echo $deviceFullName; ?></h5>
            <p>Serial Number: <?php echo $deviceSerial; ?></p>
          </div>
          <div class="mdl-card__menu">
            <button id="<?php echo $deviceSerial; ?>"
            class="mdl-button mdl-js-button mdl-button--icon">
            <i class="material-icons">more_vert</i>
            </button>
            <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect"
              for="<?php echo $deviceSerial; ?>">
              <a class="menu-link-a" href="devices.php?edit=<?php echo $deviceSerial; ?>"><li class="mdl-menu__item">Edit</li></a>
              <a class="menu-link-a" href="devices.php?remove=<?php echo $deviceSerial; ?>"><li class="mdl-menu__item">Remove</li></a>
            </ul>
          </div>
        </div>
        <?php
        }
        } else {
        echo_card_noDevices();
        }
      }
        ?>
      </div>
      </main>
    </div>
    <script type="text/javascript">
    $(document).ready(function() {
      $('#removeDeviceButton').click(function() {
        var removeSerial = $(this).data('serial');

        $.ajax({
          method: "POST",
          url: "api/plug_api_ajax.php",
          data: { action: "removeDevice" , token: userToken, serial: removeSerial }
        })
        .done(function(msg) {

          window.location.replace("/devices.php");
        })
      });
      $('#editDeviceButton').click(function() {
        var editSerial = $(this).data('serial');
        var editName = $('#deviceName').val();
        $.ajax({
          method: "POST",
          url: "api/plug_api_ajax.php",
          data: { action: "editDevice" , token: userToken, serial: editSerial, nickname: editName }
        })
        .done(function(msg) {
          window.location.replace("/devices.php");
        })
      })
    })
    </script>
  </body>
  <?php } ?>