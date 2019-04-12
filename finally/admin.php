<?php
require __DIR__.'/lib/db.inc.php';
include_once('lib/db.inc.php');
include_once('lib/csrf.php');
session_start();
session_regenerate_id();

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

$res = ierg4210_cat_fetchall();
$options = '';
$res_prod = ierg4210_prod_fetchAll();
$options_prod = '';

foreach ($res as $value){
    $options .= '<option value="'.$value["catid"].'"> '.$value["name"].' </option>';
}


foreach ($res_prod as $value){
    $options_prod .= '<option value="'.$value["pid"].'"> '.$value["name"].' </option>';
}
?>


<html>


<form method="POST" action="auth-process.php?action=logout"enctype="multipart/form-data">
    <input type="text" readonly="readonly" value="<?php echo loggedin();?>" />
    <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />
    <input type="submit" value="Log Out" />
    <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />

</form>
<!--//////////////////////////* category insert *////////////////////////////////////////////////////-->
    <fieldset>
        <legend> New Category</legend>
        <form id="cat_insert" method="POST" action="admin-process.php?action=cat_insert"
        enctype="multipart/form-data">
<!--            <label for="cat_catid"> Category *</label>-->
<!--            <div> <select id="cat_catid" name="catid">--><?php //echo $options; ?><!--</select></div>-->
            <label for="cat_name"> Name *</label>
            <div> <input id="cat_name" type="text" name="name" required="required" pattern="^[\w\-]+$"/></div>
            <input type="submit" value="Submit"/>
            <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />

        </form>
    </fieldset>
<!--//////////////////////////* category insert *////////////////////////////////////////////////////-->

<!--//////////////////////////* category edit *////////////////////////////////////////////////////-->
    <fieldset>
        <legend> Update Category</legend>
        <form id="cat_edit" method="POST" action="admin-process.php?action=cat_edit"
              enctype="multipart/form-data">
            <label for="cat_catid"> Category *</label>
            <div> <select id="cat_catid" name="catid"><?php echo $options; ?></select></div>
            <label for="cat_name">New Name *</label>
            <div> <input id="cat_name" type="text" name="name" required="required" pattern="^[\w\-]+$"/></div>
            <input type="submit" value="Submit"/>
            <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />

        </form>
    </fieldset>
<!--//////////////////////////* category update *////////////////////////////////////////////////////-->
<!---->
<!--//////////////////////////* category delete *////////////////////////////////////////////////////-->
    <fieldset>
        <legend> Delete Category</legend>
        <form id="cat_delete" method="POST" action="admin-process.php?action=cat_delete"
              enctype="multipart/form-data">
            <label for="cat_catid"> Category *</label>
            <div> <select id="cat_catid" name="catid"><?php echo $options; ?></select></div>
<!--            <label for="cat_name">Name *</label>-->
<!--            <div> <input id="cat_name" type="text" name="name" required="required" pattern="^[\w\-]+$"/></div>-->
            <input type="submit" value="Delete"/>
            <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />

        </form>
    </fieldset>
<!--//////////////////////////* category delete *////////////////////////////////////////////////////-->

<!--//////////////////////////* product insert *////////////////////////////////////////////////////-->
<fieldset>
    <legend> New Product</legend>
    <form id="prod_insert" method="POST" action="admin-process.php?action=prod_insert"
          enctype="multipart/form-data">
        <label for="prod_catid"> Category *</label>
        <div> <select id="prod_catid" name="catid"><?php echo $options; ?></select></div>
        <label for="prod_name"> Name *</label>
        <div> <input id="prod_name" type="text" name="name" required="required" pattern="^[\w\-]+$"/></div>
        <label for="prod_price"> Price *</label>
        <div> <input id="prod_price" type="text" name="price" required="required" pattern="^\d+\.?\d*$"/></div>
        <label for="prod_desc"> Description *</label>
        <div> <input id="prod_desc" type="text" name="description"/> </div>
        <label for="prod_image"> Image * </label>
        <div> <input type="file" name="file" required="true" accept="image/jpeg"/> </div>
        <input type="submit" value="Submit"/>
        <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />

    </form>
</fieldset>
<!--//////////////////////////* product insert *////////////////////////////////////////////////////-->

<!--//////////////////////////* product delete by pid *////////////////////////////////////////////////////-->
<fieldset>
    <legend> Delete Product</legend>
    <form id="prod_delete" method="POST" action="admin-process.php?action=prod_delete"
          enctype="multipart/form-data">
<!--        <label for="prod_catid">Category * </label>-->
<!--        <div><select id="prod_catid" name="catid">--><?php //echo $options; ?><!--</select></div>-->
        <label for="prod_pid"> product </label>
        <div><select id="prod_pid" name="pid"><?php echo $options_prod; ?></select></div>
        <input type="submit" value="Delete"/>
        <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />

    </form>
</fieldset>
<!--//////////////////////////* product delete by catid  *//////////////////////////////-->

<!--//////////////////////////* product update by pid *////////////////////////////////////////////////////-->
<fieldset>
    <legend>Edit Product</legend>
    <form id="prod_edit" method="POST" action="admin-process.php?action=prod_edit" enctype="multipart/form-data">
<!--        <label for="prod_catid">Category * </label>-->
<!--        <div><select id="prod_edit" name="catid">--><?php //echo $options; ?><!--</select></div>-->
        <label for="prod_edit"> product </label>
        <div><select id="prod_name" name="catid"><?php echo $options_prod; ?></select></div>
        <div><input id="prod_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>

        <label for="prod_price">Price </label>
        <div><input id="prod_price" type="number" name="price" required="true" pattern="^[\d\.]+$" /></div>

        <label for="prod_desc">Description</label>
        <div><textarea id="prod_desc" name="description" pattern="^[\w\-,\. ]$"></textarea></div>

        <label for="prod_name">Image </label>
        <div><input type="file" name="file" required="true" accept="image/jpeg image/png image/gif" /></div>

        <input type="hidden" id="prod_pid" name="pid"/>
        <input type="submit" value="Submit" /> <input type="button" id="prod_edit_cancel" value="Cancel" />
        <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>" />

    </form>
</fieldset>
<!--//////////////////////////* product delete by category  *//////////////////////////////-->

<ul id="purchased-list">
    <p>Purchased List: </p>
    <?php
    $db = new PDO('sqlite:/var/www/cart.db');
    $q = $db->query("SELECT oid FROM orders ORDER BY oid DESC LIMIT 10");
    $oids = $q->fetchAll(PDO::FETCH_COLUMN, 0);
    $q = $db->query("SELECT username FROM orders ORDER BY oid DESC LIMIT 10");
    $users = $q->fetchAll(PDO::FETCH_COLUMN, 0);
    $q = $db->query("SELECT createdtime FROM orders ORDER BY oid DESC LIMIT 10");
    $createdtimes = $q->fetchAll(PDO::FETCH_COLUMN, 0);
    $q = $db->query("SELECT status FROM orders ORDER BY oid DESC LIMIT 10");
    $status = $q->fetchAll(PDO::FETCH_COLUMN, 0);

    $totalIncome = 0.0;
    for ($i = 0, $len = count($oids); $i < $len; $i++) {
        echo "<li>OrderID:".$oids[$i]."&emsp;"."Username:&nbsp;".$users[$i]."&emsp;".$createdtimes[$i]."&emsp;".$status[$i]."</li>";
        if ($status[$i] == 'Paid')
        {
            $q = $db->query("SELECT txn_id FROM orders WHERE oid = $oids[$i]");
            $txn_id = $q->fetchAll(PDO::FETCH_COLUMN, 0);

            $q = $db->prepare("SELECT * FROM purchased_list WHERE txn_id = ?");
            $q->execute(array($txn_id[0]));
            $p_pids = $q->fetchAll(PDO::FETCH_COLUMN, 1);
            $q->execute(array($txn_id[0]));
            $p_quan = $q->fetchAll(PDO::FETCH_COLUMN, 2);
            $q->execute(array($txn_id[0]));
            $p_price = $q->fetchAll(PDO::FETCH_COLUMN, 3);

            $sum = 0.0;
            for ($ind1 = 0, $leng1 = count($p_quan); $ind1 < $leng1; $ind1++) {
                $sum += $p_price[$ind1];
            }
            echo "&emsp;SumPrice:".$sum."HKD<br>";
            echo "&emsp;Product List:<br>";
            for ($ind = 0, $leng = count($p_pids); $ind < $leng; $ind++) {
                $q = $db->prepare("SELECT name FROM products WHERE pid = ?");
                $q->execute(array($p_pids[$ind]));
                $pname = $q->fetchAll(PDO::FETCH_COLUMN, 0);
                echo "&emsp;&emsp;Item".$ind.":".$pname[0]."&emsp;Amount:".$p_quan[$ind]."&emsp;Subtotal:".$p_price[$ind]."HKD<br>";
            }
            echo "<br>";
            $totalIncome += $sum;
        }
    }
    //   echo "<p id='totalIncome'>Total Income: ".$totalIncome."HKD</p>";
    ?>
</ul>
</html>