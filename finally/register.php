<?php
include_once('lib/csrf.php');
require __DIR__.'/lib/db.inc.php';

?>

<html>
<fieldset>
    <legend>Register Form</legend>
        <form name="RegisterForm" method="POST" action="auth-process.php?action=<?php echo ($action = 'register'); ?>">
        <label for="email">Email:</label>
        <input type="email" name="email" required="true" pattern="^[\w_]+@[\w]+(\.[\w]+){0,2}(\.[\w]{2,6})$" />

        <label for="pw">Password:</label>
        <input type="password" name="pw" required="true" pattern="^[A-Za-z_\d]\w{2,19}$" />
        <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
        <input type="submit" value="Register" />
        <a href="login.php"><input type="button" value="Back to Login" onclick="window.location.href('连接')"/></a>
    </form>



</fieldset>
</html>



<!--123@123.com-->
<!--123456-->



