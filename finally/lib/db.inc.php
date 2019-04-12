<?php
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




function ierg4210_cat_fetchall() {
    // DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM categories LIMIT 100;");
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_prod_fetchAll(){
    // DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM products LIMIT 100;");
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_prod_fetchAll_by_catid() {
    global $db;
    $db = ierg4210_DB();
    $catid = $_GET['catid'];
    $q = $db->prepare("SELECT * FROM products WHERE catid = ? LIMIT 100");
    $q->bindParam(1,$catid);
    if ($q->execute())
        return $q->fetchAll();
}
function ierg4210_prod_fetchAll_by_pid() {
    global $db;
    $db = ierg4210_DB();
    $pid = $_GET['pid'];
    $q = $db->prepare("SELECT * FROM products WHERE pid = ? LIMIT 100");
    $q->bindParam(1,$pid);
    if ($q->execute())
        return $q->fetchAll();
}


//////////////////////////* product insert *////////////////////////////////////////////////////
//Since this form will take file upload, we use the tranditional (simpler) rather than AJAX form submission.
//Therefore, after handling the request (DB insert and file copy), this function then redirects back to admin.html
function ierg4210_prod_insert() {
    // input validation or sanitization

    // DB manipulation
    global $db;
    $db = ierg4210_DB();

    // TODO: complete the rest of the INSERT command
    if (!preg_match('/^\d*$/', $_POST['catid']))
        throw new Exception("invalid-catid");
    $_POST['catid'] = (int) $_POST['catid'];
    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
        throw new Exception("invalid-name");
    if (!preg_match('/^[\d\.]+$/', $_POST['price']))
        throw new Exception("invalid-price");
    if (!preg_match('/^[\w\- ]+$/', $_POST['description']))
        throw new Exception("invalid-text");

    // Copy the uploaded file to a folder which can be publicly accessible at incl/img/[pid].jpg
    if ($_FILES["file"]["error"] == 0
        && $_FILES["file"]["type"] == "image/jpeg"
        && mime_content_type($_FILES["file"]["tmp_name"]) == "image/jpeg"
        && $_FILES["file"]["size"] < 5000000) {


        $catid = $_POST["catid"];
        $name = $_POST["name"];
        $price = $_POST["price"];
        $desc = $_POST["description"];
        $sql="INSERT INTO products (catid, name, price, description) VALUES (?, ?, ? ,?);";
        $q = $db->prepare($sql);
        $q->bindParam(1, $catid);
        $q->bindParam(2, $name);
        $q->bindParam(3, $price);
        $q->bindParam(4, $desc);
        $q->execute();
        $lastId = $db->lastInsertId();

        // Note: Take care of the permission of destination folder (hints: current user is apache)
        if (move_uploaded_file($_FILES["file"]["tmp_name"], "images/" . $lastId . ".jpg")) {
            // redirect back to original page; you may comment it during debug

            header('Location: admin.php');
//            <script type="text/javascript">
//                alert("Success!");
//                window.location.href = "admin.php";
//            </script>
            exit();
        }
        else{
            header('Content-Type: text/html; charset=utf-8');
            echo 'Invalid file detected. <br/><a href="javascript:history.back();">Back to admin panel.</a>';
            exit();
        }
    }
    // Only an invalid file will result in the execution below
    // To replace the content-type header which was json and output an error message
//    header('Content-Type: text/html; charset=utf-8');
//    echo 'Invalid file detected. <br/><a href="javascript:history.back();">Back to admin panel.</a>';
//    exit();
}
//////////////////////////* product insert *////////////////////////////////////////////////////



//////////////////////////* category insert *////////////////////////////////////////////////////
function ierg4210_cat_insert(){
    // input validation or sanitization

    // DB manipulation
    global $db;
    $db = ierg4210_DB();

    if (!preg_match('/^\d*$/', $_POST['catid']))
        throw new Exception("invalid-catid");
    $_POST['catid'] = (int) $_POST['catid'];
    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
        throw new Exception("invalid-name");

    $catid = $_POST["catid"];
    $name = $_POST["name"];
    $sql="INSERT INTO categories (catid, name) VALUES (? , ?);";
    $q = $db->prepare($sql);
    $q->bindParam(1, $catid);
    $q->bindParam(2, $name);
    $q->execute();
    $lastId = $db->lastInsertId();
//    header('Location: admin_easy.php');
////    <a href="javascript:history.back();">Back to admin panel.</a>';
//        exit();

}
//////////////////////////* category insert *////////////////////////////////////////////////////

//////////////////////////* category edit *////////////////////////////////////////////////////
//function ierg4210_cat_edit(){
//    global $db;
//    $db = ierg4210_DB();
//
//    if (!preg_match('/^\d*$/', $_POST['catid']))
//        throw new Exception("invalid-catid");
//    $_POST['catid'] = (int) $_POST['catid'];
//    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
//        throw new Exception("invalid-name");
//    $catid = $_POST["catid"];
//    $name = $_POST["name"];
//    $sql="UPDATE categories SET name='$name' WHERE catid='$catid' VALUES(? , ?);";
//    $q = $db->prepare($sql);
//    $q->bindParam(1, $catid);
//    $q->bindParam(2, $name);
//    $q->execute($_POST['name']);
//    header('Location: admin_easy.php');
//    exit();
//}
function ierg4210_cat_edit() {
    // TODO: complete the rest of this function; it's now always says "successful" without doing anything
    // input validation or sanitization
    if (!preg_match('/^[\w\-, ]+$/', $_POST['name']))
        throw new Exception("invalid-name");
    $catID = (int) $_POST['catid'];

    // DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("UPDATE categories SET name = (?) WHERE catid = $catID");
    return $q->execute(array($_POST['name']));
    exit();
}
//////////////////////////* category edit *////////////////////////////////////////////////////

//////////////////////////* category delete *////////////////////////////////////////////////////
//function ierg4210_cat_delete(){
//    $_POST['catid'] = (int)$_POST['catid'];
//    global $db;
//    $db = ierg4210_DB();
//    if (!preg_match('/^\d*$/', $_POST['catid']))
//        throw new Exception("invalid-catid");
////    $catid = $_POST["catid"];
////    $sql = "DELETE FROM categories WHERE catid = '$catid' VALUES(?)";
//    $sql = "DELETE FROM categories WHERE catid = ?";
//    $q = $db->prepare($sql);
//    $q->bindParam(1, $catid);
//    $q->execute();
//    exit();
//}
function ierg4210_cat_delete() {

    // input validation or sanitization
    $_POST['catid'] = (int) $_POST['catid'];

    // DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("DELETE FROM categories WHERE catid = ?");
    return $q->execute(array($_POST['catid']));

}
//////////////////////////* category edit *////////////////////////////////////////////////////

//function ierg4210_prod_fetchByCat() {
//    //DB manipulation
//    global $db;
//    $db = ierg4210_DB();
//    $catID = $_POST['catID'];
//    $q = $db->prepare("SELECT * FROM products WHERE catid = $catID LIMIT 100");
//    if ($q->execute())
//        return $q->fetchAll();
//}
//function ierg4210_prod_fetchOne(){
//    global $db;
//    $db = ierg4210_DB();
//    $catid = $_POST["catid"];
//    $q = $db->prepare("SELECT * FROM products LIMIT 100 WHERE catid = '$catid' ;");
//    if ($q->execute())
//        return $q->fetchOne();
//}

//////////////////////////* product delete by catid*////////////////////////////////////////////////////
function ierg4210_prod_delete_by_catid(){}
//////////////////////////* product delete by catid*////////////////////////////////////////////////////
///
//////////////////////////* product edit by pid*////////////////////////////////////////////////////

function ierg4210_prod_edit()
{
    // input validation or sanitization
    if (!preg_match('/^[\w\-, ]+$/', $_POST['name']))
        throw new Exception("invalid-name");
    if (!preg_match('/^[\w\-,\. ]+$/', $_POST['description']))
        throw new Exception("invalid-description");
    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
        throw new Exception("invalid-name");
    if (!preg_match('/^[\d\.]+$/', $_POST['price']))
        throw new Exception("invalid-price");

    // DB manipulation
    global $db;
    $db = ierg4210_DB();

    $pid = (int)$_POST["pid"];
    $q = $db->prepare("UPDATE products SET catid=(?),name=(?),price=(?),details=(?) WHERE pid=$pid");
    $q->execute(array($_POST['catid'], $_POST['name'], $_POST['price'], $_POST['description']));

    // Copy the uploaded file to a folder which can be publicly accessible at incl/img/[pid].jpg
    if ($_FILES["file"]["error"] == 0
        && ($_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/png" || $_FILES["file"]["type"] == "image/gif")
        && $_FILES["file"]["size"] < 5000000) {

        // Note: Take care of the permission of destination folder (hints: current user is apache)
        if (move_uploaded_file($_FILES["file"]["tmp_name"], "pic/" . $pid . ".jpg")) {
            header('Location: admin.php');
            exit();
        }
    }
    // Only an invalid file will result in the execution below
    // TODO: remove the SQL record that was just inserted

    // To replace the content-type header which was json and output an error message
    header('Content-Type: text/html; charset=utf-8');
    echo 'Invalid file detected. <br/><a href="javascript:history.back();">Back to admin panel.</a>';
    exit();
}


function ierg4210_prod_delete() {
    // input validation or sanitization
    $_POST['pid'] = (int) $_POST['pid'];

    // DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("DELETE FROM products WHERE pid = ?");
    return $q->execute(array($_POST['pid']));
    header('Location: admin.php');
    exit();
}




?>
