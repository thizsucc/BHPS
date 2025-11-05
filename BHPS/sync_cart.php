<?php
session_start();
// Accept cart JSON via POST and store in session
if (isset($_POST['cart'])) {
    $cart = json_decode($_POST['cart'], true);
    if (is_array($cart)) {
        $_SESSION['cart'] = $cart;
        echo 'OK';
        exit;
    }
}
echo 'ERROR';
exit;
