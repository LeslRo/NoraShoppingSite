<?php
include_once('lib/csrf.php');
require __DIR__.'/lib/db.inc.php';

?>

<html>
<fieldset>
    <legend>ChangePassword Form</legend>
        <form name="ChangeForm" method="POST" action="auth-process.php?action=<?php echo ($action = 'change'); ?>">
        <label for="email">Email:</label>
        <input type="email" name="email" required="true" pattern="^[\w_]+@[\w]+(\.[\w]+){0,2}(\.[\w]{2,6})$"/>
        <label for="password">Original Password:</label>
        <input type="password" name="password" />
        <label for="password">New Password:</label>
        <input type="password" name="password2" required="true" pattern="^[A-Za-z_\d]\w{2,19}$" />
        <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
        <input type="submit" value="change the password" />
        <a href="index.php"><input type="button" value="back to the homepage"></a>

        <!--        <a href="AccountAdmin.php"><input type="submit" value="Register" /><input type="button" value="register" onclick="window.location.href('连接')"/></a>-->

    </form>
</fieldset>
</html>
