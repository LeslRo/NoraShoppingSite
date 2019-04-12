<?php
session_start();
include_once('lib/db.inc.php');
include_once('lib/csrf.php');


function loggedin()
{
    if (!empty($SESSION['t4210']))
        return $_SESSION['t4210']['em'];
    if (!empty($_COOKIE['t4210'])) {
        // stripslashes returns a string with backslashes stripped off.
        //(\' becomes ' and so on)
        if ($t = json_decode(stripslashes($_COOKIE['t4210']), true)) {
            if (time() > $t['exp']) return false;
            if ($t['em']!='1155112965@link.cuhk.edu.hk') return false;
           $pdo = new PDO('sqlite:/var/www/cart.db');
            $q = $pdo->prepare("SELECT * FROM account WHERE email = ?");
            $q->execute(array($t['em']));
            if ($r = $q->fetch()) {
                $realk = hash_hmac('sha1', $t['exp'] . $r['password'], $r['salt']);
                if ($realk == $t['k']) {
                    $_SESSION['t4210'] = $t;
                    return $t['em'];
                }
            }
        }
    }
    return false;
}

if (!loggedin()) {
    // redirect to login
    header('Location:login.php');
    exit();
}


////
header('Content-Type: application/json');
////
////// input validation
if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
    echo json_encode(array('failed'=>'undefined'));
    exit();
}

////// The following calls the appropriate function based to the request parameter $_REQUEST['action'],
//////   (e.g. When $_REQUEST['action'] is 'cat_insert', the function ierg4210_cat_insert() is called)
////// the return values of the functions are then encoded in JSON format and used as output
try {

	if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
		if ($db && $db->errorCode())
			error_log(print_r($db->errorInfo(), true));
		echo json_encode(array('failed'=>'1'));
	}
	echo 'while(1);' . json_encode(array('success' => $returnVal));
} catch(PDOException $e) {
	error_log($e->getMessage());
	echo json_encode(array('failed'=>'error-db'));
} catch(Exception $e) {
	echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
}



