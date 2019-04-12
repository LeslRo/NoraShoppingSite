<?php
session_start();
include_once('lib/db.inc.php');
include_once('lib/csrf.php');

header('Content-Type: application/json');

////// input validation
if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
    echo json_encode(array('failed'=>'undefined'));
    exit();
}
////// The following calls the appropriate function based to the request parameter $_REQUEST['action'],
//////   (e.g. When $_REQUEST['action'] is 'cat_insert', the function ierg4210_cat_insert() is called)
////// the return values of the functions are then encoded in JSON format and used as output
try {

    // check if the form request can present a valid nonce
    if ($_REQUEST['action']=='login')
       if (!csrf_verifyNonce($_REQUEST['action'],$_POST['nonce']))
           throw new Exception("CSRF attack");
    if ($_REQUEST['action']=='logout')
        csrf_verifyNonce($_REQUEST['action'],$_POST['nonce']);
    if ($_REQUEST['action']=='change')
        csrf_verifyNonce($_REQUEST['action'],$_POST['nonce']);
    if ($_REQUEST['action']=='register')
        csrf_verifyNonce($_REQUEST['action'],$_POST['nonce']);

    //var_dump("this action is ".$_REQUEST['action']);

    // run the corresponding function according to action
    if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
        if ($db && $db->errorCode())
            error_log(print_r($db->errorInfo(), true));
        echo json_encode(array('failed'=>'1'));
    }
    echo 'while(1);' . json_encode(array('success' => $returnVal));
} catch(PDOException $e) {
    error_log("pdo:".$e->getMessage());
    echo json_encode(array('failed'=>'error-db'));
} catch(Exception $e) {
    echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
}


function ierg4210_login(){
    echo "through login function";
    if (empty($_POST['email']) || empty($_POST['pw'])
        || !preg_match('/^[\w_]+@[\w]+(\.[\w]+){0,2}(\.[\w]{2,6})$/', $_POST['email'])
        || !preg_match('/^[A-Za-z_\d]{2,19}$/', $_POST['pw'])){ //{}

        //  echo "3";
        throw new Exception('Wrong Credentials');}

    // Implement the login logic here
    else {
        echo "beforedb";
        global $db;
        $db = ierg4210_DB();
        $email = $_POST['email'];
        $q = $db->prepare("SELECT * FROM account WHERE email = ?");
        var_dump($q);
        $q->execute(array($email));
        $r = $q->fetch();

        var_dump($r);
        if (empty($r)) {
            header('Location:login.php', true, 302);
            throw new Exception('Wrong users');
        }
        else {
            $salt = $r['salt'];
            $savedPwd = $r['password'];

            echo "salt";
            var_dump($salt);
            $sh_pwd = hash_hmac('sha1', $_POST['pw'], $salt);
            if ($savedPwd == $sh_pwd) {
                session_regenerate_id();
                $exp = time()+3600*24*3;
                $token = array(
                    'em'=>$email,
                    'exp'=>$exp,
                    'k'=>hash_hmac('sha1', $exp.$savedPwd, $salt)
                );
                //create cookie, make it HTTP only
                //setcookie() must be called before printing anything out
                setcookie('t4210',json_encode($token),$exp,'/','s12.ierg4210.ie.cuhk.edu.hk',false,true);
                $_SESSION['t4210'] = $token;

                if ($email == "1155112965@link.cuhk.edu.hk") {
                    header('Location: admin.php', true, 302);
                    exit();
                }
                else {
                    header('Location: index.php', true, 302);
                    exit();
                }
            }
            else {
                header('Location:login.php', true, 302);
                throw new Exception('Wrong password');
            }
        }
    }
}

function ierg4210_logout(){
    echo "through logout function";
    // clear the cookies and session
    if (isset($_COOKIE['t4210'])) {
        unset($_COOKIE['t4210']);
        //delete cookie
        setcookie('t4210','',time()-1,'/','s12.ierg4210.ie.cuhk.edu.hk',false,true);

//delete session
        session_unset();
        session_destroy();
        // redirect to login page after logout

        header('Location:login.php', true, 302);
        exit();
    }
    else {
        header('Location:login.php', true, 302);
        exit();
    }
}
//if($_GET["do"]=="yes"){
//    ierg4210_logout();
//}

function ierg4210_register(){
    echo "through register function";

//    var_dump($_POST['txt']);
//    var_dump($_POST['email']);
//    var_dump($_POST['pw']);
    if (empty($_POST['email']) || empty($_POST['pw'])){
        echo "1";
        throw new Exception('Wrong Credentials');
        echo "2";
    }
    else{
//        echo "3";

        global $db;
        $db = ierg4210_DB();
        $email = $_POST['email'];
        $password = $_POST['pw'];
        //generate a random salt
        $salt = mt_rand();
//generate a hash value
        $password = hash_hmac('sha1', $password, $salt);

        $sql="INSERT INTO account (email, salt, password) VALUES (?,?,?);";
        $q = $db->prepare($sql);

        $q->bindParam(1, $email);
        $q->bindParam(2, $salt);
        $q->bindParam(3, $password);
        $q->execute();
        header('Location: login.php',true,302);

//        if($q){
//            // echo "<script>alert('注册成功！);window.location.href='login.php'</script>"; ->wrong 不弹窗口 能插入数据库
//            echo "<script type='text/javascript'>alert('successful');location='login.php';</script>";
//        }
//    if (!preg_match('/^[\w\-,\. ]+$/', $_POST['flag']))
//        throw new Exception("invalid-flag");

    }
}

function ierg4210_change(){
    echo "through change function";

    if (empty($_POST['email']) || empty($_POST['password'])){
        throw new Exception('Wrong Credentials');
    }

    else{
        global $db;
        $db = ierg4210_DB();
//        接受提交过来的用户名
        $email = $_POST['email'];
        $password = $_POST['password'];
        $newpassword = $_POST['password2'];

        $q = $db->prepare("SELECT * FROM account WHERE email = ?");
        $q->execute(array($email));
        $r = $q->fetch();
        if (empty($r)) {
            header('Location:login.php', true, 302);
            throw new Exception('Wrong users');
            //用户名不存在
//            echo "<script type='text/javascript'>alert('用户名或者密码错误');location='Change.php';</script>";
        }
        else {
//            用提交过来的密码与数据库中密码比较，相等的时候则正确


            //pwd (salted)from db
            $dbPwd = $r['password'];

            // 拿过来的psaaword再加盐
            $sh_pwd = hash_hmac('sha1', $password, $salt);
            if ($dbPwd == $sh_pwd){


//                产生新的password
                $newpassword = hash_hmac('sha1',$newpassword, $salt);
                $q = $db->prepare("UPDATE account SET salt=?, password=? WHERE email = ?");
                $q->execute(array($salt, $newpassword, $email));
                //跳转到logout函数，完成注销
                echo "<script type='text/javascript'>alert('成功修改密码,已注销登陆！')</script>";
                ierg4210_logout();
            }
            else {
                //用户名存在 原密码输错了
                echo "<script type='text/javascript'>alert('用户名或者密码错误');location='Change.php';</script>";
            }
        }
    }

}