<?php
define("LOG_FILE", "/var/www/ipn.log");
include_once('lib/csrf.php');
include_once('lib/db.inc.php');
date_default_timezone_set("Asia/Hong_Kong");
session_start();

if ($_SERVER['HTTP_REFERER'] == "")
{
    header('Location://s12.ierg4210.ie.cuhk.edu.hk', true, 302);
    exit();
}


global $db;
$db = ierg4210_DB();
$msg = json_decode($_POST["message"],true);

if ($msg != null) {
    $sumPrice = 0.0;
    $order = "{";
    foreach ($msg as $pid => $number) {
        $q = $db->prepare("SELECT price FROM products WHERE pid = $pid");
        $q->execute();
        $pro_price = $q->fetchAll(PDO::FETCH_COLUMN, 0);
        $pro_price = $pro_price[0];
        settype($pro_price, "float");
        $mc_gross = $pro_price * $number;
        round(floatval($mc_gross), 2);

        $order .= $pid . ":{" . $number . "," . $mc_gross . "},";
        $sumPrice += $mc_gross;
    }
    $order .= "}";
    $salt = mt_rand();
    round(floatval($sumPrice), 2);
    $message = "HKD;2363354018-facilitator@qq.com;" . $salt . ";" . $order . ";" . $sumPrice;
    error_log(date("Y-m-d H:i:s"). "order-message " . $message. PHP_EOL, 3, LOG_FILE);

    $digest = hash('md5', $message);
    $createdtime = date("Y-m-d H:i:s");

    $q = $db->prepare("INSERT INTO orders (username,digest,salt,createdtime,status) VALUES (?,?,?,?,?)");
    $q->execute(array(loggedin(), $digest, $salt,$createdtime,'Un-paid'));
    $lastInsertId = $db->lastInsertId();
    $data = array(
        'id' => $lastInsertId,
        'digest' => $digest,
    );

    echo json_encode($data);
    exit;
}
?>
