<?php
session_start();
include("../db.php");  // ← Parent folder se db.php

// Check if seller is logged in
// if (!isset($_SESSION['seller_name'])) {
//     header("Location: user_product_edit.php");
//     exit();
// }

$product_id = isset($_GET['id']) ? $_GET['id'] : 0;
// echo $product_id;exit;
$seller_name = $_SESSION['seller_name'];

// Delete ONLY if product belongs to this seller
$sql = "DELETE FROM products WHERE id ='$product_id'";

if (mysqli_query($conn, $sql)) {
    header("Location: http://localhost/mywebsites/Alfra/user/user_product_edit.php?msg=Product deleted successfully");
} else {
    header("Location: http://localhost/mywebsites/Alfra/view_product.php?error=You cannot delete this product");
}
exit();
?>