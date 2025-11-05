<?php
session_start();
if (isset ($_POST['cart])) {
    $cart = json_decode($_POST['cart'], true);
    if (is_array($cart)) {
    }
}
echo json_encode(['success' => false]);
exit;
