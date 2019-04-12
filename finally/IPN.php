<?php

date_default_timezone_set("Asia/Hong_Kong");

ini_set('display_errors', 'On');
ini_set("file_uploads", "On");
ini_set("error_log", "/var/www/php.errors");

define("DEBUG", 1);
define("LOG_FILE", "/var/www/ipn.log");

// Reading raw POST data from input stream instead.
$raw_post_data = file_get_contents('php://input');
echo "rawdata=".$raw_post_data;
error_log("rrrrrrrrrawdata is ".$raw_post_data, 3, LOG_FILE);
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode ('=', $keyval);
    if (count($keyval) == 2)
        $myPost[$keyval[0]] = urldecode($keyval[1]);
}
// read the IPN msg from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

if(function_exists('get_magic_quotes_gpc')) {
    $get_magic_quotes_exists = true;
}

foreach ($myPost as $key => $value) {
    if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
        $value = urlencode(stripslashes($value));
    } else {
        $value = urlencode($value);
    }
    $req .= "&$key=$value";
}

// Post IPN data back to PayPal to validate
$ch = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
if ($ch == FALSE) {
    return FALSE;
}
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
error_log("?????????? curl_setopt is ".$ch, 3, LOG_FILE);

// Set TCP timeout to 30 seconds
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

$res = curl_exec($ch);
if (curl_errno($ch) == 0) // cURL error
{
    // Log the entire HTTP response if debug is switched on.
    if(DEBUG == true) {
        error_log(date("Y-m-d H:i:s"). "HTTP request of validation request: $req" . PHP_EOL, 3, LOG_FILE);
        error_log(date("Y-m-d H:i:s"). "HTTP response of validation request: $res" . PHP_EOL, 3, LOG_FILE);
    }
    curl_close($ch);
}
// Inspect IPN validation result and act accordingly
// Split response headers and payload, a better way for strcmp
$tokens = explode("\r\n\r\n", trim($res));
$res = trim(end($tokens));

if (strcmp ($res, "VERIFIED") == 0) {
    $payment_status = $_POST['payment_status'];
    error_log("tttttttttttttthe post data is ".$payment_status, 3, LOG_FILE);

    $payment_amount = round(floatval($_POST['mc_gross']), 2);
    $payment_currency = $_POST['mc_currency'];
    $txn_id = $_POST['txn_id'];
    $receiver_email = $_POST['receiver_email'];
    $custom = $_POST['custom'];
    $txn_type = $_POST['txn_type'];
    $invoice = (int)$_POST['invoice'];

    $i = 1;
    $list_array = array();
    $results_array = array();
    $item_number = array();
    $quantity = array();
    $price = array();

    while(!empty($_POST['item_number' .$i. '']))
    {
        $item_number[$i] = $_POST['item_number'.$i.''];
        $quantity[$i] = $_POST['quantity'.$i.''];
        $price[$i] = round(floatval($_POST['mc_gross_'.$i.'']), 2);
        $i += 1;
    }

    $db = new PDO('sqlite:/var/www/cart.db');
    $q = $db->prepare("SELECT COUNT(*) FROM purchased_list WHERE txn_id=?");
    $q->execute(array($txn_id));
    $row_num = $q->fetchColumn();

    if ($row_num > 0){
        error_log(date("Y-m-d H:i:s") . $txn_id . " Duplicated Transaction Id" . PHP_EOL, 3, LOG_FILE);
        exit();
    }
    if ($txn_type != 'cart'){
        error_log(date("Y-m-d H:i:s") . $txn_id . " Transaction type is not cart" . PHP_EOL, 3, LOG_FILE);
        exit();
    }
    if ($payment_status != 'Completed'){
        error_log(date("Y-m-d H:i:s") . $txn_id . " Payment is not completed" . PHP_EOL, 3, LOG_FILE);
        $q = $db->prepare("UPDATE orders SET txn_id=(?), status='Un-paid' WHERE oid=(?)");
        $q->bindParam(1,$txn_id);
        $q->bindParam(2,$invoice);
        $q->execute();
       // exit();
    }

    $q = $db->prepare("SELECT digest, salt FROM orders WHERE oid=?");
    $q->execute(array($invoice));
    $row = $q->fetch();
    //error_log("tttttttttt" . PHP_EOL, 3, LOG_FILE);


    $salt_stored = $row['salt'];
    $digest_stored = $row['digest'];

    $order = "{";
    for ($j = 1; $j < $i; $j++){
        $order .= $item_number[$j].":{".$quantity[$j].",".$price[$j]."},";
    }
    $order .= "}";
    error_log("The order ". $txn_id . " is ". $order . PHP_EOL, 3, LOG_FILE);

    $message = $payment_currency.";".$receiver_email.";".$salt_stored.";".$order.";".$payment_amount;

//    error_log(date("Y-m-d H:i:s"). "TEST-message " . $message. PHP_EOL, 3, LOG_FILE);
    $digest_regenerated = hash('md5',$message);

    error_log("the input of digest is ".$message.PHP_EOL, 3, LOG_FILE);
    error_log("the old digest is: ".$digest_stored.PHP_EOL,3, LOG_FILE);
    error_log("the new digest is: ".$digest_regenerated.PHP_EOL, 3, LOG_FILE);



    if (strcmp($digest_regenerated, $digest_stored) == 0){
        error_log("the two digests are same");
        error_log("the transcation id is:".$txn_id);
        $db = new PDO('sqlite:/var/www/cart.db');
        $q = $db->prepare("UPDATE orders SET txn_id=(?), status='Paid' WHERE oid = $invoice");
        $q->bindParam(1,$txn_id);
        $q->execute();

        error_log(date(' [Y-m-d H:i e] ') . $txn_id . " Successfully Validated and Paid" . PHP_EOL, 3, LOG_FILE );

        for ($k = 1 ; $k < $i; $k++){
            $tmp_itemnum = (int)$item_number[$k];
            $tmp_itemquan = (int)$quantity[$k];
            $tmp_itempri = round( floatval($price[$k]), 2);
            $q = $db->prepare("INSERT INTO purchased_list (txn_id,pid,quantity,price) VALUES (?,?,?,?)");
            $q->execute(array($txn_id , $tmp_itemnum , $tmp_itemquan , $tmp_itempri));
        }
    }

    if(DEBUG == true) {
        error_log(date("Y-m-d H:i:s"). "Verified IPN: $req ". PHP_EOL, 3, LOG_FILE);
    }
} else if (strcmp ($res, "INVALID") == 0) {
    // log for manual investigation
    // Add business logic here which deals with invalid IPN messages
    if(DEBUG == true) {
        error_log(date("Y-m-d H:i:s"). "Invalid IPN: $req" . PHP_EOL, 3, LOG_FILE);
    }
}

?>