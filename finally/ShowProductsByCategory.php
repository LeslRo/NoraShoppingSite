<?php
require __DIR__.'/lib/db.inc.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title> Nora's Shopping Website</title>
    <link rel="stylesheet" href="StyleSheet.css"/>
</head>
<body bgcolor="#f9ffff">
<!-- header-->
<div class="Header">
    <font size="15" color="#5f9ea0">Noraaa's Shop Website</font>
    <div id="main-header">
        <nav id="middle-navigation">
            <ul>
                <li class="blue">
                    <a>0Items Bought</a>
                </li>
                <li class="red">
                    <a>2Items Favorites</a>
                </li>
                <li class="orange"><a>17Items Shopping List</a>
                    <ul class="box-dropdown">
                        <li>
                            <div class="box-wrapper parent-border">
                                <p>Recently added item(s)</p>
                                <table class="cart-table">
                                    <tr>
                                        <td><img src="img/products/sample1.jpg" alt="pro
                                            <p>Product code PSBJ3</p>duct">
                                        </td>
                                        <td>
                                            <h6>digital product</h6>
                                        </td>
                                        <td>
                                            <input type="text" name="num" value="1"><span> x $79.00</span>
                                            <a href="#" class="parent-color">Remove</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><img src="img/products/sample1.jpg" alt="product"></td>
                                        <td>
                                            <h6>digital product</h6>
                                            <p>Product code PSBJ3</p>
                                        </td>
                                        <td>
                                            <input type="text" name="num" value="1"><span> x $79.00</span>
                                            <a href="#" class="parent-color">Remove</a>
                                        </td>

                                    </tr>
                                </table>
                            </div>
                            <table class="pull-right middleNavigateColor">
                                <tr>
                                    <td class="align-right">Tax:</td>
                                    <td>$0.00</td>
                                </tr>
                                <tr>
                                    <td class="align-right">Discount:</td>
                                    <td>$37.00</td>
                                </tr>
                                <tr>
                                    <td class="align-right"><strong>Total:</strong></td>
                                    <td><strong class="parent-color">$121.00</strong></td>
                                </tr>
                                <!--<div class="total-background">-->
                                <!--<tr>-->
                                <!--<td class="align-right"><strong>Total:</strong></td>-->
                                <!--<td><strong class="parent-color">$137.00</strong></td>-->
                                <!--</tr>-->
                                <!--</div>-->
                            </table>
                            <div>
                                <a id="Checkout" href="#">Checkout</a>
                                <a id="ViewCart" href="#">View Cart</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
    <div class="breadcrumbs">
        <p><a href="index.php">Home</a></p>
    </div>
</div>



<?php
    //$catid = $_GET['$catid'];
    $cats = ierg4210_prod_fetchAll_by_catid();

    foreach($cats as $arr){
        echo '<div class="divFloat">';
        echo '<div>'.'<img src="images/'.$arr["pid"].'.jpg">'.'</div>';
        echo '<div>'.$arr["price"].'</div>';
        echo '<div>'.$arr["description"].'</div>';
        echo '</div>';
    }
        ?>

<div id="sidebar-box-context2">
    <h4 class="sidebar-box-heading">Categories</h4>
    <ul>

        <?php
        $cats = ierg4210_cat_fetchall();
        foreach($cats as $arr){
            echo '<li><a href="ShowProductsByCategory.php?catid='.$arr["catid"].'">   '.$arr["name"].'</a></li>';
        }
        ?>
    </ul>
</div>

<footer >
    <div id="lower-footer">
        <p class="copyright">Copyright 2018 <a href="home.html">NoraaaShop</a>. All Rights Reserved.</p>
    </div>
</footer>
<!-- Footer -->

</body>

</html>

