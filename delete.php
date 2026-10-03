<?php
include("db.php");

$id = $_GET['id'];

$sql = "DELETE FROM contact WHERE id='$id'";

if(mysqli_query($conn,$sql)){
    header("Location: admin.php");
    exit();

    
}else{
    echo "Delete Failed";
}
?>