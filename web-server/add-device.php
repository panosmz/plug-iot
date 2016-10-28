<?php

require 'site-functions/site-functions.php';
require_once 'api/plug_api_functions.php';

session_start();

if(user_access()) {
?>

<!doctype html>
<html lang="en">

<?php echo_head("Add a Device | Plug"); ?>

<body>
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-drawer
            mdl-layout--fixed-header">

<?php echo_navbar("Add a Device"); ?>

<main class="mdl-layout__content mdl-color--grey-100">
    <div class="mdl-grid overview-grid">
    <div class="mdl-card mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--6-col mdl-grid">
      <div class="mdl-card__title">
        <h2 id="serial-title" class="mdl-card__title-text">Serial</h2>
      </div>
      <div id="error-message" class="mdl-card__supporting-text" style="display: none; color: red;">
      </div>
      <div id="description-message" class="mdl-card__supporting-text">
      Enter the device's serial below:
      </div>
      <form id="add-device-form" action="#">
        <div class="serial-input mdl-textfield mdl-js-textfield">
        <input class="mdl-textfield__input" type="text" id="serial">
         <label class="mdl-textfield__label mdl-color-text--blue-grey-500" for="serial">Serial #</label>
        </div>
      </form>

      <p id="device-pair-code" style="display: none;"></p>

      <div class="mdl-card__actions mdl-card--border">
          <a id="ok-button" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
            Ok
          </a>
          <a href="/overview.php" id="return-button" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" style="display: none;">
            Devices
          </a>
      </div>
    </div>
    </div>
</main>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#ok-button').click(function() {
            postSerial();
        });
    })
    function postSerial() {
        showError('');
        var uSerial = $('#serial').val();

        if(uSerial === '') {
            showError('Serial field cannot be empty');
        } else {
            $.ajax({
                method: "POST",
                url: "api/plug_api_ajax.php",
                data: { action: "addDevice", token: userToken, serial: uSerial }
            })
            .done(function(msg){
                var parsedMsg = jQuery.parseJSON(msg);
                if(parsedMsg.success == "SUCCESS") {
                    showPairKey(parsedMsg.deviceKey);
                } else {
                    showError(parsedMsg.message);
                }
            });
        }
    }
    function showError(message) {
        $('#error-message').show();
        $('#error-message').html(message);
    }
    function showPairKey(pairKey) {
        $('#ok-button').hide();
        $('#error-message').hide();
        $('#return-button').show();
        $('#add-device-form').hide();
        $('#serial-title').html("Pair Key");
        $('#description-message').html("Use the key below to pair your device:")
        $('#device-pair-code').html(pairKey);
        $('#device-pair-code').show();
    }
</script>

</body>

<?php } ?>