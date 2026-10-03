<?php
session_start();
include("../db.php");

/* =========================
   AJAX: FIND USER BY REFERRAL ID
========================= */

if (isset($_GET['get_user'])) {

    header('Content-Type: application/json');

    $referral_id = trim($_GET['referral_id'] ?? '');

    if ($referral_id == '') {
        echo json_encode([
            "success" => false,
            "message" => "Referral ID required"
        ]);
        exit;
    }

    $stmt = mysqli_prepare($conn, "
        SELECT id, referral_id, name, status
        FROM contact
        WHERE referral_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Database error"
        ]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "s", $referral_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {

        echo json_encode([
            "success" => true,
            "id" => $user['id'],
            "referral_id" => $user['referral_id'],
            "name" => $user['name'],
            "status" => strtolower(trim($user['status'] ?? 'inactive'))
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Referral ID not found"
        ]);
    }

    mysqli_stmt_close($stmt);
    exit;
}


/* =========================
   ACTIVATE USER
========================= */

$message = "";
$type = "";

if (isset($_POST['activate'])) {

    $referral_id = trim($_POST['referral_id'] ?? '');

    if ($referral_id == '') {

        $message = "Please enter Referral ID.";
        $type = "error";

    } else {

        /* First check user */

        $stmt = mysqli_prepare($conn, "
            SELECT id, referral_id, name, status
            FROM contact
            WHERE referral_id = ?
            LIMIT 1
        ");

        mysqli_stmt_bind_param($stmt, "s", $referral_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$user) {

            $message = "Referral ID not found.";
            $type = "error";

        } elseif (strtolower(trim($user['status'])) === 'active') {

            $message = "This ID is already active.";
            $type = "info";

        } else {

            /* Activate ONLY this Referral ID */

            $stmt = mysqli_prepare($conn, "
                UPDATE contact
                SET status = 'active'
                WHERE referral_id = ?
                LIMIT 1
            ");

            mysqli_stmt_bind_param($stmt, "s", $referral_id);

            if (mysqli_stmt_execute($stmt)) {

                $message = "ID {$user['referral_id']} - {$user['name']} Activated Successfully!";
                $type = "success";

            } else {

                $message = "Activation failed.";
                $type = "error";
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Activate ID</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f5f7fb;
}

.container {
    width: 100%;
    max-width: 550px;
    margin: 50px auto;
    padding: 20px;
}

.card {
    background: #ffffff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

.title {
    text-align: center;
    margin-bottom: 25px;
}

.title h2 {
    margin: 0;
    color: #111827;
}

.title p {
    color: #6b7280;
    margin-top: 8px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
}

input {
    width: 100%;
    padding: 13px 15px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 15px;
    outline: none;
}

input:focus {
    border-color: #2563eb;
}

.user-box {
    display: none;
    margin-top: 18px;
    padding: 18px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
}

.user-row {
    display: flex;
    justify-content: space-between;
    padding: 7px 0;
}

.status-active {
    color: #16a34a;
    font-weight: bold;
}

.status-inactive {
    color: #dc2626;
    font-weight: bold;
}

button {
    width: 100%;
    margin-top: 20px;
    padding: 14px;
    border: none;
    border-radius: 8px;
    background: #2563eb;
    color: white;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #1d4ed8;
}

button:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}

.message {
    margin-bottom: 20px;
    padding: 13px;
    border-radius: 8px;
    text-align: center;
    font-weight: 600;
}

.success {
    background: #dcfce7;
    color: #166534;
}

.error {
    background: #fee2e2;
    color: #991b1b;
}

.info {
    background: #dbeafe;
    color: #1e40af;
}

</style>

</head>

<body>

<div class="container">

    <div class="card">

        <div class="title">
            <h2>Activate ID</h2>
            <p>Enter Referral ID to activate user</p>
        </div>


        <?php if ($message != ""): ?>

            <div class="message <?php echo $type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <label>Referral ID</label>

            <input
                type="text"
                name="referral_id"
                id="referral_id"
                placeholder="Enter Referral ID"
                autocomplete="off"
                required
            >


            <div class="user-box" id="userBox">

                <div class="user-row">
                    <span>User Name</span>
                    <strong id="userName"></strong>
                </div>

                <div class="user-row">
                    <span>Referral ID</span>
                    <strong id="showReferral"></strong>
                </div>

                <div class="user-row">
                    <span>Status</span>
                    <strong id="userStatus"></strong>
                </div>

            </div>


            <button
                type="submit"
                name="activate"
                id="activateBtn"
                disabled
            >
                Activate ID
            </button>

        </form>

    </div>

</div>


<script>

const referralInput = document.getElementById("referral_id");
const userBox = document.getElementById("userBox");
const userName = document.getElementById("userName");
const showReferral = document.getElementById("showReferral");
const userStatus = document.getElementById("userStatus");
const activateBtn = document.getElementById("activateBtn");

let timer;


referralInput.addEventListener("input", function () {

    clearTimeout(timer);

    const referralId = this.value.trim();

    userBox.style.display = "none";

    activateBtn.disabled = true;

    activateBtn.textContent = "Activate ID";


    if (referralId === "") {
        return;
    }


    timer = setTimeout(function () {

        fetch("?get_user=1&referral_id=" + encodeURIComponent(referralId))

        .then(response => response.json())

        .then(data => {

            userBox.style.display = "block";


            if (data.success) {

                userName.textContent = data.name;

                showReferral.textContent = data.referral_id;

                userStatus.textContent =
                    data.status.toUpperCase();


                if (data.status === "active") {

                    userStatus.className = "status-active";

                    activateBtn.disabled = true;

                    activateBtn.textContent = "Already Active";

                } else {

                    userStatus.className = "status-inactive";

                    activateBtn.disabled = false;

                    activateBtn.textContent = "Activate ID";
                }


            } else {

                userName.textContent = "Referral ID Not Found";

                showReferral.textContent = "-";

                userStatus.textContent = "-";

                activateBtn.disabled = true;
            }

        })

        .catch(error => {

            console.log(error);

            userBox.style.display = "block";

            userName.textContent = "Error loading user";

            activateBtn.disabled = true;
        });

    }, 300);

});

</script>

</body>
</html>