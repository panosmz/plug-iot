<?php

require 'site-functions/site-functions.php';
require_once 'api/plug_api_functions.php';

session_start();

if(user_access()) {
?>

<!doctype html>
<html lang="en">

<?php echo_head("Account Settings | Plug"); ?>

<body>
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-drawer
            mdl-layout--fixed-header">

<?php echo_navbar("Account Settings"); ?>
<?php
    
    $userDetails = json_decode(user_edit_get_details($_SESSION['token']), true);

?>
<main class="mdl-layout__content mdl-color--grey-100">
    <div class="mdl-grid overview-grid  plug-simple-page">
    <div class="mdl-cell mdl-cell--10-col">
        <p>Edit your account details below.</p>
        <p>Username: <strong><?php echo $_SESSION['username']; ?></strong></p>
        <p class="login-error-message">Login errors</p>
        <form id="accountForm" action="#">
                <p>Email:</p>
                 <div class="mdl-textfield mdl-js-textfield">
                    <input value="<?php echo $userDetails['email']; ?>" class="mdl-textfield__input" type="text" id="email">
                    <label class="mdl-textfield__label mdl-color-text--blue-grey-500" for="email">Email</label>
                </div><br>
                <p>Name:</p>
                 <div class="mdl-textfield mdl-js-textfield">
                    <input value="<?php echo $userDetails['name']; ?>" class="mdl-textfield__input" type="text" id="name">
                    <label class="mdl-textfield__label mdl-color-text--blue-grey-500" for="name">Name</label>
                </div><br>
                <p>Surname:</p>
                 <div class="mdl-textfield mdl-js-textfield">
                    <input value="<?php echo $userDetails['surname']; ?>" class="mdl-textfield__input" type="text" id="surname">
                    <label class="mdl-textfield__label mdl-color-text--blue-grey-500" for="surname">Surname</label>
                </div><br>
        </form>
        <div class="edit-user-button"><button class="mdl-button mdl-js-button mdl-button--raised mdl-button--accent">Apply</button></div>
    </div>
    </div>
</main>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.edit-user-button').click(function() {
            postDetails();
        });
    })
    function postDetails() {
        showError('');
        var uEmail = $('#email').val();
        var uName = $('#name').val();
        var uSurname = $('#surname').val();

        if(uEmail === '') {
            showError('Email field cannot be empty');
        } else {
            $.ajax({
                method: "POST",
                url: "api/plug_api_ajax.php",
                data: { action: "editAccountDetails", token: userToken, name: uName, surname: uSurname, email: uEmail }
            })
            .done(function(msg){
                var parsedMsg = jQuery.parseJSON(msg);
                if(parsedMsg.success == "SUCCESS") {
                    location.reload();
                } else {
                    showError(parsedMsg.message);
                }
            });
        }
    }
    function showError(message) {
        $('.login-error-message').show();
        $('.login-error-message').html(message);
    }
</script>

</body>

<?php } ?>