<?php
include_once('lib/csrf.php');
require __DIR__.'/lib/db.inc.php';

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Login Page</title>
</head>
<body>
<h1>Login</h1>
<fieldset>
    <legend>Login Form</legend>
        <form name="loginForm" method="POST" action="auth-process.php?action=<?php echo ($action = 'login'); ?>">
        <label for="email">Email:</label>
        <input type="email" name="email" required="true" pattern="^[\w_]+@[\w]+(\.[\w]+){0,2}(\.[\w]{2,6})$" />
        <label for="pw">Password:</label>
        <input type="password" name="pw" required="true" pattern="^[A-Za-z_\d]\w{2,19}$" />
          <input type="submit" value="Login" />
        <a href="register.php"><input type="button" value="register"></a>
<!--        <a href="extension.php"><input type="button" value="forget password?"></a>-->
        </form>
</fieldset>
</body>
</html>
