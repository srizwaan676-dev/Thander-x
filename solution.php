<?php
include("db.php");

// Products ka data
$sql = "SELECT id, seller_name, seller_number, price, seller_address FROM products";
$result = mysqli_query($conn, $sql);


// Products ka total count
$count_sql = "SELECT count(*) AS user_id FROM products";
$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$user_id = $count_row['user_id'];


// Products ka total name
$count_sql = "SELECT count(*) AS user_id FROM products";

$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$seller_name = $count_row['user_id'];



// Products ka total number
$count_sql = "SELECT count(*) AS user_id FROM products";
$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$seller_number = $count_row['user_id'];



// Products ka total price
$count_sql = "SELECT sum(price) AS user_id FROM products ";

$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$total_products = $count_row['user_id'];



/// level count
$count_sql = "SELECT COUNT(*) AS user_id
              FROM products WHERE seller_address = 'level'";

$count_result = mysqli_query($conn, $count_sql);

$count_row = mysqli_fetch_assoc($count_result);

$seller_address = $count_row['user_id'];



////incme cont
$count_sql = "SELECT COUNT(*) AS user_id    
              FROM products WHERE seller_address = 'income'";

$count_result = mysqli_query($conn, $count_sql);

$count_row = mysqli_fetch_assoc($count_result);

$seller = $count_row['user_id'];




////income total price
$count_sql = "SELECT sum(price) AS user_id
              FROM products WHERE seller_address = 'income'";

$count_result = mysqli_query($conn, $count_sql);

$count_row = mysqli_fetch_assoc($count_result);

$seller_price = $count_row['user_id'];


///level total price
$count_sql = "SELECT sum(price) AS user_id
 FROM products WHERE seller_address = 'level'";
$count_result = mysqli_query($conn, $count_sql);

$count_row = mysqli_fetch_assoc($count_result);

$seller_level = $count_row['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Products</title>

    <style>
    body {
        font-family: Arial, sans-serif;
    }

    /* TABLE CONTAINER */

    .pp {
        display: flex;
        gap: 30px;
        margin: 30px;
    }

    /* TABLE */

    table {
        width: 50%;
        border-collapse: collapse;
    }

    th {
        background: red;
        color: white;
        padding: 10px;
    }

    td {
        padding: 10px;
        text-align: center;
    }

    /* CARDS */

    .kk {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin: 30px;
    }

    .box {
        min-height: 100px;
        background: red;
        padding: 20px;
        color: white;
        border-radius: 20px;
        text-align: center;
    }

    .box h1 {
        margin: 0;
    }

    .box h3 {
        margin-top: 10px;
    }
    </style>

</head>

<body>


    <!-- ==========================
     TABLE 1
========================== -->

    <div class="pp">

        <table border="1">

            <tr>
                <th>ID</th>
                <th>Seller Name</th>
                <th>Seller Number</th>
                <th>Price</th>
                <th>Address</th>
            </tr>

            <?php

while ($row = mysqli_fetch_assoc($result)) {

?>

            <tr>

                <td> <?php echo $row['id']; ?></td>

                <td> <?php echo $row['seller_name']; ?></td>

                <td> <?php echo $row['seller_number']; ?> </td>

                <td> <?php echo $row['price']; ?></td>

                <td> <?php echo $row['seller_address']; ?></td>

            </tr>

            <?php

}

?>

        </table>


        <!-- ==========================
     TABLE 2
========================== -->

        <table border="1">

            <tr>
                <th>ID</th>
                <th>Seller Name</th>
                <th>Seller Number</th>
                <th>Price</th>
                <th>Address</th>
            </tr>

            <?php

/* SECOND QUERY */

$sql2 = "SELECT id, seller_name, seller_number, price, seller_address 
         FROM products";

$result2 = mysqli_query($conn, $sql2);

while ($row = mysqli_fetch_assoc($result2)) {

?>

            <tr>

                <td><?php echo $row['id']; ?></td>

                <td><?php echo $row['seller_name']; ?></td>

                <td> <?php echo $row['seller_number']; ?> </td>

                <td> <?php echo $row['price']; ?> </td>

                <td> <?php echo $row['seller_address']; ?></td>

            </tr>

            <?php

}

?>

        </table>

    </div>


    <!-- ==========================
     CARDS
========================== -->

    <div class="kk">


        <div class="box">

            <h1><?php echo $user_id; ?> </h1>

            <h3> Total ID</h3>

        </div>


        <div class="box">

            <h1> <?php echo $seller_name; ?></h1>

            <h3> Total Name</h3>

        </div>


        <div class="box">

            <h1><?php echo $seller_number; ?> </h1>

            <h3> Total Number </h3>

        </div>


        <div class="box">

            <h1> <?php echo $total_products; ?></h1>

            <h3> Total Price</h3>

        </div>


        <div class="box">

            <h1> <?php echo $seller_address; ?> </h1>

            <h3>Total Level</h3>

        </div>


        <div class="box">

            <h1><?php echo $seller; ?></h1>

            <h3>Total Income</h3>

        </div>


        <div class="box">

            <h1><?php echo $seller_price; ?></h1>

            <h3> Income Price </h3>

        </div>


        <div class="box">

            <h1> <?php echo $seller_level; ?> </h1>

            <h3>Level Price</h3>

        </div>


    </div>

</body>

</html>
/////////////////////////
login
//////////////////
<?php
   session_start();
   include('config.php');
   error_reporting(0);
   
   ?>
<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <title>Login</title>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="">
    <meta property="og:type" content="">
    <meta property="og:url" content="">
    <meta property="og:image" content="">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="admin/picture/logoavs.png">
    <!-- Template CSS -->
    <link rel="stylesheet" href="assets/css/main.css?v=3.4">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome Icon Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-16689228327"></script>
    <script>
    //   window.dataLayer = window.dataLayer || [];
    //   function gtag(){dataLayer.push(arguments);}
    //   gtag('js', new Date());

    //   gtag('config', 'AW-16689228327');
    </script>
</head>

<body>
    <?php
 include('header.php');
 include('config.php');



	if(isset($_POST['login']))
	{
		
		$email=$_POST['email'];
		$password=$_POST['password'];
		
		$select=mysqli_query($con,"SELECT * FROM `customer_management` WHERE `email`='$email' OR `mobile` = '$email'  AND `password`='$password'")or die(mysqli_error($con));
		$row=mysqli_fetch_array($select);
		$res=mysqli_num_rows($select);

			if($res>0)
			{
               echo  $_SESSION['id']=$row['id'];
			 echo "<script>alert('Login Successfully')
             window.location.href='index.php'
             </script>";
             die();
			}
			else
			{
				echo "<script>
						alert('Something Went Wrong');
					</script>";
			}
		}
 ?>





    /////////////////
    register
    //////////////////////


    <?php
   session_start();
   include('config.php');
   error_reporting(0);
   
   ?>

    <?php
// session_start();
include('config.php');

$date = date('d/m/y');

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];

    // Check if mobile number or email already exists
    $result = mysqli_query($con, "SELECT * FROM `customer_management` WHERE `mobile`='$mobile' OR `email`='$email'");
    $existingUser = mysqli_fetch_assoc($result);

    if ($existingUser) {
        // Mobile number or email already exists
        echo "<script>alert('This Mobile Number or Email Already Exists')</script>";
    } else {
        // Insert new user into database
        $stmt = $con->prepare("INSERT INTO `customer_management` (`name`, `email`, `mobile`, `password`, `date`) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $mobile, $password, $date);
        $insert = $stmt->execute();

        if ($insert) {
            // Registration successful
            echo "<script>alert('Registration Successful')
                    window.location.href='account_login.php'</script>";
        } else {
            // Registration failed
            echo "<script>alert('Something Went Wrong')</script>";
        }
    }
}
?>


    ///////////////////