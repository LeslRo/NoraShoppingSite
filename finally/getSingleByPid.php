<?php
// For index.html
include_once('lib/db.inc.php');
function ierg4210_prod_fetchByPid() {
    //DB manipulation
    global $db;
    $db = ierg4210_DB();
    $PID = $_POST['pID'];
    $q = $db->prepare("SELECT name,price FROM products WHERE pid = $PID");
    if ($q->execute()) {
        return $q->fetchAll();
    }
}

header('Content-Type: application/json');

// validation
if (empty($_REQUEST['action']) || $_SERVER["REQUEST_METHOD"]!="POST") {
    header('Location://s78.ierg4210.ie.cuhk.edu.hk', true, 302);
    exit();
}

try {
    $db = ierg4210_DB();
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

?>