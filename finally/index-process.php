<?php
/**
 * Created by PhpStorm.
 * User: nora
 * Date: 5/12/2018
 * Time: 15:04
 */

function ierg4210_DB() {
    // connect to the database
    // TODO: change the following path if needed
    // Warning: NEVER put your db in a publicly accessible location
    $db = new PDO('sqlite:/var/www/cart.db');
//    /Users/norawang/Desktop/cart.db
    // /var/www/cart.db
    // enable foreign key support
    $db->query('PRAGMA foreign_keys = ON;');

    // FETCH_ASSOC:
    // Specifies that the fetch method shall return each row as an
    // array indexed by column name as returned in the corresponding
    // result set. If the result set contains multiple columns with
    // the same name, PDO::FETCH_ASSOC returns only a single value
    // per column name.
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $db;
}




//fetchbyID...
function ierg4210_prod_fetchByCat() {
    //DB manipulation
    global $db;
    $db = ierg4210_DB();
    $catID = $_POST['catID'];
    $q = $db->prepare("SELECT * FROM products WHERE catid = $catID LIMIT 100");
    if ($q->execute())
        return $q->fetchAll();
}
function ierg4210_prod_fetchByPid() {
    //DB manipulation
    global $db;
    $db = ierg4210_DB();
    $PID = $_GET['pID'];
    $q = $db->prepare("SELECT name,price FROM products WHERE pid = $PID");
    if ($q->execute())
        return $q->fetchAll();
}
function ierg4210_cat_fetchall() {
    // DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM categories LIMIT 100;");
    if ($q->execute())
        return $q->fetchAll();
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