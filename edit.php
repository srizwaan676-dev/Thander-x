<?php
include("db.php");

$id = $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM contact WHERE id='$id'");
$row = mysqli_fetch_assoc($result);
if(isset($_POST['update'])){

    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $Email = $_POST['Email'];
    $password = $_POST['password'];

   $sql = "UPDATE contact SET
        name='$name',
        mobile='$mobile',
        Email='$Email',
        password='$password'
        WHERE id='$id'";

    if(mysqli_query($conn, $sql)){
        header("Location: admin.php");
        exit();
    } else {
        echo mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit User</title>
    <style>
    /* Google Font */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    padding:30px;
    background:
    radial-gradient(circle at top left,#3b82f655,transparent 35%),
    radial-gradient(circle at bottom right,#22c55e55,transparent 35%),
    linear-gradient(135deg,#020617,#0f172a,#111827);
}

/* Form Box */

.form{

    width:460px;

    background:rgba(255,255,255,.08);

    backdrop-filter:blur(20px);

    border:1px solid rgba(255,255,255,.15);

    border-radius:24px;

    padding:40px;

    box-shadow:
    0 25px 60px rgba(0,0,0,.35);

    animation:fade .6s ease;

}

@keyframes fade{

from{
opacity:0;
transform:translateY(30px);
}

to{
opacity:1;
transform:translateY(0);
}

}

/* Heading */

.form h2{

    color:#fff;

    text-align:center;

    font-size:32px;

    margin-bottom:30px;

    font-weight:700;

    letter-spacing:.5px;

}

.form h2::after{

    content:"";

    display:block;

    width:90px;

    height:4px;

    margin:12px auto 0;

    border-radius:20px;

    background:linear-gradient(90deg,#2563eb,#22c55e);

}

/* Inputs */

.form input{

    width:100%;

    height:55px;

    margin-bottom:18px;

    padding:0 18px;

    border-radius:12px;

    border:1px solid rgba(255,255,255,.15);

    background:rgba(255,255,255,.08);

    color:#fff;

    font-size:15px;

    outline:none;

    transition:.35s;

}

.form input::placeholder{

    color:#cbd5e1;

}

.form input:focus{

    border-color:#3b82f6;

    background:rgba(255,255,255,.12);

    box-shadow:0 0 0 4px rgba(59,130,246,.18);

}

/* Button */

.form button{

    width:100%;

    height:56px;

    border:none;

    border-radius:14px;

    background:linear-gradient(135deg,#2563eb,#22c55e);

    color:#fff;

    font-size:17px;

    font-weight:600;

    cursor:pointer;

    transition:.35s;

}

.form button:hover{

    transform:translateY(-4px);

    box-shadow:0 18px 35px rgba(37,99,235,.35);

}

/* Responsive */

@media(max-width:768px){

.form{

width:100%;

padding:30px 25px;

}

.form h2{

font-size:28px;

}

}

@media(max-width:480px){

body{

padding:15px;

}

.form{

padding:25px 20px;

border-radius:18px;

}

.form h2{

font-size:24px;

}

.form input{

height:50px;

font-size:14px;

}

.form button{

height:50px;

font-size:15px;

}

}
    </style>
</head>

<body>

    <div class="form">

        <h2>Edit User</h2>

        <form method="POST">
            <input type="text" name="name" placeholder="Enter Name" value="<?php echo $row['name']; ?>">

            <input type="number" name="mobile" placeholder="Enter Mobile Number" value="<?php echo $row['mobile']; ?>">

            <input type="email" name="Email" placeholder="Enter Email" value="<?php echo $row['Email']; ?>">

            <input type="password" name="password" placeholder="Enter Password" value="<?php echo $row['password']; ?>">

            <button type="submit" name="update">Update</button>

        </form>

    </div>

</body>

</html>