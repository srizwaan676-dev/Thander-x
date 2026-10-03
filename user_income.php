<?php
session_start();
include("../db.php");

/* =========================
   LOGIN CHECK
========================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'User';


/* =========================
   INCOME FUNCTION
========================= */

function getIncome($conn, $user_id, $type)
{
    $sql = "
        SELECT COALESCE(SUM(amount), 0) AS total
        FROM income
        WHERE user_id = ?
        AND income_type = ?
        AND status = 'approved'
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $type);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['total'] ?? 0;
}


/* =========================
   INCOME TOTALS
========================= */

$direct_income = getIncome(
    $conn,
    $user_id,
    "Direct Income"
);

$welcome_bonus = getIncome(
    $conn,
    $user_id,
    "Welcome Bonus"
);

$level_income = getIncome(
    $conn,
    $user_id,
    "Level Income"
);

$salary_income = getIncome(
    $conn,
    $user_id,
    "Monthly Salary"
);


/* =========================
   TOTAL INCOME
========================= */

$sql = "
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM income
    WHERE user_id = ?
    AND status = 'approved'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$total_income = $row['total'] ?? 0;


/* =========================
   INCOME HISTORY
========================= */

$sql = "
    SELECT id, income_type, amount, description, status, date
    FROM income
    WHERE user_id = ?
    ORDER BY date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$income_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Income</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f5f7fb;
    color: #1f2937;
}


/* =========================
   CONTAINER
========================= */

.container {
    width: 94%;
    max-width: 1200px;
    margin: 35px auto;
}


/* =========================
   HEADER
========================= */

.page-header {
    margin-bottom: 25px;
}

.page-header h1 {
    font-size: 30px;
    font-weight: 700;
}

.page-header p {
    margin-top: 6px;
    color: #6b7280;
}


/* =========================
   TOTAL INCOME
========================= */

.total-card {
    background: linear-gradient(
        135deg,
        #6366f1,
        #4f46e5
    );

    color: #fff;

    padding: 28px;

    border-radius: 18px;

    margin-bottom: 25px;

    box-shadow:
        0 12px 30px rgba(79,70,229,.20);
}

.total-card small {
    font-size: 14px;
    opacity: .85;
}

.total-card h2 {
    font-size: 38px;
    margin-top: 8px;
}


/* =========================
   INCOME GRID
========================= */

.income-grid {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 18px;
}


.income-card {
    background: #fff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    padding: 22px;

    box-shadow:
        0 5px 20px rgba(0,0,0,.05);

    transition: .25s;
}

.income-card:hover {
    transform: translateY(-4px);

    box-shadow:
        0 12px 30px rgba(0,0,0,.08);
}


.icon {
    width: 46px;
    height: 46px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #eef2ff;

    color: #4f46e5;

    border-radius: 12px;

    font-size: 20px;

    margin-bottom: 15px;
}


.income-card h3 {
    font-size: 14px;

    color: #6b7280;

    margin-bottom: 8px;
}


.income-card .amount {
    font-size: 25px;

    font-weight: 700;

    color: #111827;
}


/* =========================
   HISTORY
========================= */

.history-card {

    margin-top: 30px;

    background: #fff;

    border: 1px solid #e5e7eb;

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 5px 20px rgba(0,0,0,.04);
}


.history-header {
    padding: 20px;

    border-bottom:
        1px solid #e5e7eb;
}


.history-header h2 {
    font-size: 20px;
}


.table-wrapper {
    overflow-x: auto;
}


table {
    width: 100%;

    border-collapse: collapse;
}


th {
    background: #f8fafc;

    color: #64748b;

    font-size: 13px;

    text-align: left;

    padding: 15px;
}


td {
    padding: 15px;

    border-top:
        1px solid #f1f5f9;

    font-size: 14px;
}


.amount-green {
    color: #16a34a;

    font-weight: 700;
}


.status {
    display: inline-block;

    padding: 5px 11px;

    border-radius: 20px;

    background: #dcfce7;

    color: #15803d;

    font-size: 12px;

    font-weight: 600;
}


.empty {
    text-align: center;

    padding: 35px;

    color: #6b7280;
}


/* =========================
   MOBILE
========================= */

@media(max-width: 900px) {

    .income-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

}


@media(max-width: 550px) {

    .container {
        width: 92%;
        margin: 25px auto;
    }

    .page-header h1 {
        font-size: 24px;
    }

    .income-grid {
        grid-template-columns: 1fr;
    }

    .total-card h2 {
        font-size: 30px;
    }

    th,
    td {
        padding: 12px;
    }

}

</style>

</head>


<body>


<div class="container">


    <!-- PAGE HEADER -->

    <div class="page-header">

        <h1>
            My Income
        </h1>

        <p>
            Welcome, <?php
            echo htmlspecialchars($user_name);
            ?>. Here is your income summary.
        </p>

    </div>


    <!-- TOTAL INCOME -->

    <div class="total-card">

        <small>
            Total Income
        </small>

        <h2>
            ₹ <?php
            echo number_format(
                $total_income,
                2
            );
            ?>
        </h2>

    </div>


    <!-- INCOME CARDS -->

    <div class="income-grid">


        <!-- DIRECT -->

        <div class="income-card">

            <div class="icon">
                ↗
            </div>

            <h3>
                Direct Income
            </h3>

            <div class="amount">
                ₹ <?php
                echo number_format(
                    $direct_income,
                    2
                );
                ?>
            </div>

        </div>


        <!-- WELCOME -->

        <div class="income-card">

            <div class="icon">
                🎁
            </div>

            <h3>
                Welcome Bonus
            </h3>

            <div class="amount">
                ₹ <?php
                echo number_format(
                    $welcome_bonus,
                    2
                );
                ?>
            </div>

        </div>


        <!-- LEVEL -->

        <div class="income-card">

            <div class="icon">
                📊
            </div>

            <h3>
                Level Income
            </h3>

            <div class="amount">
                ₹ <?php
                echo number_format(
                    $level_income,
                    2
                );
                ?>
            </div>

        </div>


        <!-- SALARY -->

        <div class="income-card">

            <div class="icon">
                💼
            </div>

            <h3>
                Monthly Salary
            </h3>

            <div class="amount">
                ₹ <?php
                echo number_format(
                    $salary_income,
                    2
                );
                ?>
            </div>

        </div>


    </div>


    <!-- INCOME HISTORY -->

    <div class="history-card">


        <div class="history-header">

            <h2>
                Income History
            </h2>

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Income Type</th>

                        <th>Amount</th>

                        <th>Description</th>

                        <th>Status</th>

                        <th>Date</th>

                    </tr>

                </thead>


                <tbody>


                <?php

                if ($income_result->num_rows > 0) {

                    $count = 1;

                    while (
                        $income =
                        $income_result->fetch_assoc()
                    ) {

                ?>

                    <tr>

                        <td>
                            <?php echo $count++; ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $income['income_type']
                            );
                            ?>
                        </td>


                        <td class="amount-green">

                            ₹ <?php
                            echo number_format(
                                $income['amount'],
                                2
                            );
                            ?>

                        </td>


                        <td>

                            <?php
                            echo htmlspecialchars(
                                $income['description']
                                ?? '-'
                            );
                            ?>

                        </td>


                        <td>

                            <span class="status">

                                <?php
                                echo htmlspecialchars(
                                    $income['status']
                                );
                                ?>

                            </span>

                        </td>


                        <td>

                            <?php

                            echo !empty(
                                $income['date']
                            )
                            ? date(
                                'd M Y, h:i A',
                                strtotime(
                                    $income['date']
                                )
                            )
                            : '-';

                            ?>

                        </td>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td
                            colspan="6"
                            class="empty"
                        >
                            No income history found.
                        </td>

                    </tr>

                <?php

                }

                ?>

                </tbody>

            </table>

        </div>

    </div>


</div>


</body>

</html>