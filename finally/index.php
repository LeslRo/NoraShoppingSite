<?php
    require __DIR__ . '/lib/db.inc.php';
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
                $db = ierg4210_DB();
                $q = $db->prepare("SELECT * FROM account WHERE email = ?");
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

    $db = ierg4210_DB();
    $q = $db->query("SELECT catid FROM categories");
    $catID = $q->fetchAll(PDO::FETCH_COLUMN,0);
    $q = $db->query("SELECT name FROM categories");
    $cat = $q->fetchAll(PDO::FETCH_COLUMN,0);
?>


<!DOCTYPE html>
<html>
<head>
    <title> Nora's Shopping Website</title>
    <link rel="stylesheet" href="StyleSheet.css"/>
</head>

<div id="fb-root"></div>
<script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = 'https://connect.facebook.net/zh_HK/sdk.js#xfbml=1&version=v3.2';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>

<div class="fb-like" data-href="https://secure.s12.ierg4210.ie.cuhk.edu.hk" data-layout="standard" data-action="like" data-size="small" data-show-faces="false" data-share="true"></div>

<body bgcolor="#f9ffff">
<!-- header-->
<div class="Header">
    <font size="15" color="#5f9ea0">Noraaa's Shop Website</font>

    <form name="LogoutForm" method="POST" action="auth-process.php?action=<?php echo ($action = 'logout'); ?>">
        <input class="username" type="text" readonly="readonly" value="<?php echo loggedin();?>" />
        <input class="logoutForm" type="submit" value="Log Out" />
        <a href="Change.php"><input type="button" value="Change password"></a>
        <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
    </form>

<!---------------------------------------------- shopping cart-------------------------------------------------->
    <nav>
        <button id="cartButton">Shopping Cart</button>
<!--        <div id="myDiv"></div>-->
        <div id="cartUL"></div>
    </nav>
<!---------------------------------------------- shopping cart-------------------------------------------------->

    <div class="breadcrumbs">
        <p><a href="HomePage.html">Home</a></p>
    </div>

</div>



<!-- header-->
<!--container -->
<div class="AppleImage">
    <img src="img/products/apple.jpg"/>
</div>
<!----------------------------------------------- product-------------------------------------------------------->
<div id="product">
    <?php
    parse_str($_SERVER['QUERY_STRING']);
    $cat = ierg4210_prod_fetchall();
    foreach($cat as $arr){

        echo '<div class="divFloat">';
        echo '<div>'.$arr["pid"].'</div>';
        echo '<div><a href="ShowProductsByDetails.php?pid=' . $arr["pid"] . '">' . $arr["name"] . '</a>' . '<img src="images/' . $arr["pid"] . '.jpg">' . '</div>';
        echo '<div>' . $arr["price"] . '</div>';
        echo '<div>' . $arr["description"] . '</div>';
        echo '<div><button type="button" name="myButton" onclick=addToCart(' . $arr["pid"] . ')>' . "AddToCart" . '</button></div>';
        /*        <button type="button" name="myButton" onclick="loadXMLDoc(<?php echo $row[0];?>);">添加到購物車</button>*/
        echo '</div>';


    }

    ?>
</div>
<!---------------------------------------------- product--------------------------------------------------------->


<!-------container------->
<!---------------------------------------------- category--------------------------------------------------------->
<div id="sidebar-box-context1">
    <h4 class="sidebar-box-heading">Categories</h4>
    <ul>

        <?php
        $cats = ierg4210_cat_fetchall();
        foreach ($cats as $arr) {
            echo '<li><a href="ShowProductsByCategory.php?catid=' . $arr["catid"] . '">   ' . $arr["name"] . '</a></li>';
        }
        ?>
    </ul>
</div>
<!------------------------------------------ category--------------------------------------------------------->
<!-------container------->
<!------ Footer --------->
<footer>
    <div id="lower-footer">
        <p class="copyright">Copyright 2018 <a href="home.html">NoraaaShop</a>. All Rights Reserved.</p>
    </div>
</footer>
<!------ Footer --------->



<script type="text/javascript" src="incl/myLib.js"></script>
<script type="text/javascript">
    function ClearCart() {
        localStorage.clear();
        reload_Cart();
    }
    function reload_Cart() {
        var tempCart;
        if(localStorage.cart != undefined)
            tempCart = JSON.parse(localStorage.cart);
        else return;
        var content = "<table><tr><th>Product</th><th>Price</th><th>Count</th><th>subTotal</th></tr>";
        var total = 0;
        var totalForOnePro = 0;
        for (var p in tempCart){
            totalForOnePro = tempCart[p].num * tempCart[p].price;
            content += "<tr><td>"+tempCart[p].name+"</td><td>$"+tempCart[p].price+"</td>" +
                "<td><input class=\"min\" type=\"button\" value=\"-\" onclick=\"updatePrice("+p+",this.nextElementSibling.value-1)\" />" +
                "<input class=\"count\" type=\"text\" readonly=\"readonly\" value="+tempCart[p].num+" onchange=\"updatePrice("+p+",this.value)\" />" +
                "<input class=\"add\" type=\"button\" value=\"+\" onclick=\"updatePrice("+p+",Number(this.previousElementSibling.value)+1)\" /></td>" +
                "<td>$"+totalForOnePro+"</td>" +
                "<td><button id=\"remove\" onclick=\'removeProduct("+p+")\'>Remove</td></tr>";
            total += totalForOnePro;
        }
        content += "</table>";
        content += "Total: $"+total;

        var form = "<form id=\"payForm\" action=\"https://www.sandbox.paypal.com/cgi-bin/webscr\" method=\"POST\" onsubmit=\"return cart_submit(this)\">";
        form += "<input type=\"hidden\" name=\"cmd\" value=\"_cart\">";
        form += "<input type=\"hidden\" name=\"upload\" value=\"1\">";
        form += "<input type=\"hidden\" name=\"business\" value=\"2363354018-facilitator@qq.com\">";
        form += "<input type=\"hidden\" name=\"currency_code\" value=\"HKD\">";
        form += "<input type=\"hidden\" name=\"charset\"  value=\"utf-8\">";

        var list_num = 1;
        for (var p1 in tempCart){
            form += "<input type=\"hidden\" name=\"item_name_"+ list_num +"\" value=\""+ tempCart[p1].name +"\"  >" ;
            form += "<input type=\"hidden\" name=\"item_number_"+ list_num +"\" value=\""+ p1 + "\" >";
            form += "<input type=\"hidden\" name=\"quantity_"+ list_num +"\" value=\""+ tempCart[p1].num +"\" >";
            form += "<input type=\"hidden\" name=\"amount_"+ list_num +"\" value=\""+ tempCart[p1].price +"\"  >" ;
            list_num += 1;
        }
        form += "<input type=\"hidden\" name=\"custom\" value=\"\">";
        form += "<input type=\"hidden\" name=\"invoice\" value=\"\">";
        form += "<input type=\"submit\" id=\"checkout\" value=\"Checkout\"></form> ";
        content += form;
        document.getElementById("cartUL").innerHTML = content;
    }
    reload_Cart();
    function addToCart(pid) {
        myLib.postK({action:'prod_fetchByPid', pID: pid}, function(json){
            var cart = localStorage.cart;
            if (cart == undefined)
                cart = {};
            else
                cart = JSON.parse(cart);
            if(cart[pid] == undefined)
                cart[pid] = {'num':0};
            var name = json[0].name.escapeHTML();
            var price = json[0].price.escapeHTML();
            cart[pid].name = name;
            cart[pid].price = price;
            cart[pid].num = cart[pid].num + 1;
            localStorage.cart = JSON.stringify(cart);
            reload_Cart();
        });
    }
    function updatePrice(p, number) {
        var tempCart = JSON.parse(localStorage.cart);
        if(number > 0){
            tempCart[p].num = number;
            localStorage.cart = JSON.stringify(tempCart);
        }
        else if(number == 0) {
            delete tempCart[p];
            localStorage.cart = JSON.stringify(tempCart);
        }
        else
            alert("Error on updating price !");
        reload_Cart();
    }
    function removeProduct(p){
        var tempCart = JSON.parse(localStorage.cart);
        delete tempCart[p];
        localStorage.cart = JSON.stringify(tempCart);
        reload_Cart();
    }

    function ajaxSend(){
        var xmlhttp =  new XMLHttpRequest();
        xmlhttp.onreadystatechange = function()  {
            if (xmlhttp.readyState ==  4   &&  xmlhttp.status  ==  200)    {
                var obj = JSON.parse(xmlhttp.responseText);
                if (obj.ifLogin == 0) {
                    alert("You should login first ^_^");
                    window.location.href = "login.php";
                }
                else {
//							alert(obj.id);
                    var form = document.getElementById("payForm");
                    form.elements.namedItem("invoice").value = obj.id;
                    form.elements.namedItem("custom").value = obj.digest;
                    form.submit();
                    ClearCart();
                }
            }
        };

        xmlhttp.open("POST",  "getOrder.php", true);
        //xmlhttp.setRequestHeader("Content-type",  "application/json");
        xmlhttp.setRequestHeader("Content-type",  "application/x-www-form-urlencoded");
        var tempCart = JSON.parse(localStorage.cart);
        var pair = {};
        for (var tp in tempCart) {
            pair[tp] = tempCart[tp].num;
        }
        pair = JSON.stringify(pair);
        var message = "message=" + pair;
        //		alert(message);
        xmlhttp.send(message);
    }
    function cart_submit(e) {
        reload_Cart();
        var tempCart = JSON.parse(localStorage.cart);
        var ps = {};
        var ind = 1;
        for (var p in tempCart) {
            ps[ind] = p;
            ind = ind + 1;
        }
        if (ind == 1){
            alert("No product to purchase !");
            return false;
        }
        else {
            ajaxSend();
        }
        return false;
    }

</script>

</body>

</html>
