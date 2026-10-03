<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    die("User login nahi hai.");
}

$user_id = intval($_SESSION['user_id']);

$product_name = $_POST['product_name'];
$price = $_POST['price'];
$description = $_POST['description'];

$seller_name = $_POST['seller_name'];
$seller_number = $_POST['seller_number'];
$seller_address = $_POST['seller_address'];
$category = $_POST['category'];

$image = $_FILES['image']['name'];
$tmp = $_FILES['image']['tmp_name'];

$image1 = $_FILES['image1']['name'];
$tmp = $_FILES['image1']['tmp_name'];

$image2 = $_FILES['image2']['name'];
$tmp = $_FILES['image2']['tmp_name'];

if (move_uploaded_file($tmp, "uploads/" . $image)) {

    $sql = "INSERT INTO products
    (user_id, product_name, price, description, image, image1, image2, seller_name, seller_number, seller_address, category)
    VALUES
    ('$user_id', '$product_name', '$price', '$description', '$image', '$image1', '$image2', '$seller_name', '$seller_number', '$seller_address', '$category')";

    if (mysqli_query($conn, $sql)) {

        header("Location: blog.php");
        exit();

    } else {

        echo "Database Error: " . mysqli_error($conn);
    }

} else {

    echo "Image upload failed";
}
?>