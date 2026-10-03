<?php
session_start();
include("../db.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User login nahi hai.");
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$sender_id = (int)$_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$message = isset($_POST['messages']) ? trim($_POST['messages']) : '';

// Debug output
echo "Sender ID: " . $sender_id . "<br>";
echo "Receiver ID: " . $receiver_id . "<br>";
// echo "csrf_token: " . htmlspecialchars($csrf_token) . "<br><br>";

// Validate receiver ID
if ($receiver_id <= 0) {
    die("Receiver ID nahi mil rahi.");
}

// Validate message (not empty and not too long)
if ($message === '') {
    die("Messages empty hai.");
}

// Check max message length
if (strlen($message) > 5000) {
    die("Message too long (max 5000 characters).");
}

// FIX: Check if receiver exists - using correct table name
// The table is 'contact' not 'users' based on your other code
$check_sql = "SELECT id FROM contact WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);

if (!$check_stmt) {
    die("Prepare Error (check): " . mysqli_error($conn));
}

mysqli_stmt_bind_param($check_stmt, "i", $receiver_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) === 0) {
    die("Receiver does not exist.");
}
mysqli_stmt_close($check_stmt);

// FIX: Check if sender exists too (optional but good practice)
$check_sender_sql = "SELECT id FROM contact WHERE id = ?";
$check_sender_stmt = mysqli_prepare($conn, $check_sender_sql);

if ($check_sender_stmt) {
    mysqli_stmt_bind_param($check_sender_stmt, "i", $sender_id);
    mysqli_stmt_execute($check_sender_stmt);
    mysqli_stmt_store_result($check_sender_stmt);
    
    if (mysqli_stmt_num_rows($check_sender_stmt) === 0) {
        die("Sender does not exist.");
    }
    mysqli_stmt_close($check_sender_stmt);
}

// FIX: Check if messages table exists and has correct columns
$table_check = "SHOW TABLES LIKE 'messages'";
$table_result = mysqli_query($conn, $table_check);
if (mysqli_num_rows($table_result) == 0) {
    die("Messages table does not exist. Please create it first.");
}

// Insert message
$sql = "INSERT INTO messages (sender_id, receiver_id, messages, created_at) 
        VALUES (?, ?, ?, NOW())";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Prepare Error: " . mysqli_error($conn));
}

// FIX: Make sure we bind correctly
$bind_result = mysqli_stmt_bind_param($stmt, "iis", $sender_id, $receiver_id, $message);

if (!$bind_result) {
    die("Bind Error: " . mysqli_stmt_error($stmt));
}

if (mysqli_stmt_execute($stmt)) {
    $message_id = mysqli_insert_id($conn);
    echo "MESSAGE SUCCESSFULLY SEND HO GAYA! (ID: " . $message_id . ")";
    
    // Optional: Redirect back to chat
    // header("Location: chat.php?user_id=" . $receiver_id);
    // exit();
} else {
    
    die("Execute Error: " . mysqli_stmt_error($stmt));
}

// Clean up
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>