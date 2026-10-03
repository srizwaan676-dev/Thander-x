<?php
session_start();
include("db.php");

/* =========================
   ADMIN LOGIN CHECK
========================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* =========================
   APPROVE DEPOSIT
========================= */

if (isset($_GET['approve']) && isset($_GET['txn_id'])) {

    $txn_id = (int)$_GET['txn_id'];

    $stmt = $conn->prepare("
        UPDATE wallet_transactions
        SET status = 'completed'
        WHERE id = ?
        AND type = 'deposit'
        AND status = 'pending'
    ");

    $stmt->bind_param("i", $txn_id);

    if ($stmt->execute()) {
        $message = "Deposit approved successfully!";
    } else {
        $message = "Failed to approve deposit!";
    }

    $stmt->close();
}


/* =========================
   REJECT DEPOSIT
========================= */

if (isset($_GET['reject']) && isset($_GET['txn_id'])) {

    $txn_id = (int)$_GET['txn_id'];

    $stmt = $conn->prepare("
        UPDATE wallet_transactions
        SET status = 'failed'
        WHERE id = ?
        AND type = 'deposit'
        AND status = 'pending'
    ");

    $stmt->bind_param("i", $txn_id);

    if ($stmt->execute()) {
        $message = "Deposit rejected successfully!";
    } else {
        $message = "Failed to reject deposit!";
    }

    $stmt->close();
}


/* =========================
   GET PENDING DEPOSITS
========================= */

$sql = "
    SELECT 
        w.id,
        w.user_id,
        w.amount,
        w.method,
        w.reference,
        w.status,
        w.created_at,
        c.name,
        c.email
    FROM wallet_transactions w
    LEFT JOIN contact c 
        ON c.id = w.user_id
    WHERE w.type = 'deposit'
    AND w.status = 'pending'
    ORDER BY w.id DESC
";

$pending = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Pending Deposits</title>

<style>

/* ─── Reset & Base ─── */
*,
*::before,
*::after {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --bg-primary: #080c14;
    --bg-card: #0f1822;
    --bg-table-header: #1a2634;
    --bg-row-hover: rgba(255, 215, 0, 0.04);
    --border-color: rgba(255, 215, 0, 0.12);
    --gold: #d4af37;
    --gold-light: #f0d060;
    --gold-dark: #b8960f;
    --gold-glow: rgba(212, 175, 55, 0.20);
    --text-primary: #f1f5f9;
    --text-secondary: #94a3b8;
    --text-muted: #475569;
    --success: #22c55e;
    --success-bg: rgba(34, 197, 94, 0.10);
    --danger: #ef4444;
    --danger-bg: rgba(239, 68, 68, 0.10);
    --warning: #f59e0b;
    --warning-bg: rgba(245, 158, 11, 0.10);
    --radius: 14px;
    --radius-sm: 8px;
    --shadow-card: 0 10px 40px rgba(0, 0, 0, 0.6);
    --transition: 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--bg-primary);
    color: var(--text-primary);
    min-height: 100vh;
    padding: 30px 20px;
    background-image: radial-gradient(ellipse at 20% 20%, rgba(212, 175, 55, 0.04) 0%, transparent 60%),
                      radial-gradient(ellipse at 80% 80%, rgba(59, 130, 246, 0.04) 0%, transparent 60%);
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
}

.container {
    margin-top:-222px;
    max-width: 1300px;
    width: 100%;
    background: var(--bg-card);
    border-radius: var(--radius);
    padding: 32px 30px 38px;
    box-shadow: var(--shadow-card), 0 0 60px var(--gold-glow);
    border: 1px solid rgba(255, 215, 0, 0.06);
    transition: border-color 0.4s ease;
}

.container:hover {
    border-color: rgba(255, 215, 0, 0.12);
}

h2 {
    margin: 0;
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.5px;
    background: linear-gradient(135deg, var(--gold-light), var(--gold), var(--gold-dark));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: flex;
    align-items: center;
    gap: 12px;
}

.message {
    padding: 14px 20px;
    border-radius: var(--radius-sm);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    animation: slideDown 0.4s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message.success {
    background: var(--success-bg);
    border-left: 4px solid var(--success);
    color: #86efac;
}

.message.error {
    background: var(--danger-bg);
    border-left: 4px solid var(--danger);
    color: #fca5a5;
}

.table-wrapper {
    overflow-x: auto;
    border-radius: var(--radius-sm);
    border: 1px solid rgba(255, 255, 255, 0.04);
    background: rgba(0, 0, 0, 0.20);
    -webkit-overflow-scrolling: touch;
}

.table-wrapper::-webkit-scrollbar {
    height: 6px;
}
.table-wrapper::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 10px;
}
.table-wrapper::-webkit-scrollbar-thumb {
    background: var(--gold-dark);
    border-radius: 10px;
}
.table-wrapper::-webkit-scrollbar-thumb:hover {
    background: var(--gold);
}

table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
    font-size: 14px;
    line-height: 1.6;
}

th {
    background: var(--bg-table-header);
    color: var(--gold);
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 16px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

td {
    padding: 14px 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    color: var(--text-secondary);
    transition: background var(--transition);
}

tr:hover td {
    background: var(--bg-row-hover);
}

.status {
    color: var(--warning);
    font-weight: 700;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.3px;
}

.btn {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
    border: none;
    cursor: pointer;
    color: #fff;
    margin-right: 6px;
}

.approve {
    background: var(--success);
    color: #0b0e14;
}
.approve:hover {
    background: #16a34a;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(34, 197, 94, 0.25);
}

.reject {
    background: var(--danger);
}
.reject:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.25);
}

.no-data {
    text-align: center;
    padding: 50px 20px;
    color: var(--text-muted);
}

.no-data::before {
    content: '\f0ce';
    font-family: 'Font Awesome 6 Free';
    font-weight: 400;
    font-size: 48px;
    display: block;
    opacity: 0.2;
    margin-bottom: 12px;
}

/* ─── Responsive ─── */

/* Tablet & small laptops */
@media (max-width: 992px) {
    .container {
        padding: 24px 20px 28px;
    }
    table {
        min-width: 800px;
        font-size: 13px;
    }
    th, td {
        padding: 12px 14px;
    }
}

/* Large phones & small tablets */
@media (max-width: 768px) {
    body {
        padding: 12px 10px;
    }
    .container {
        padding: 18px 14px 22px;
        border-radius: 10px;
    }
    h2 {
        font-size: 20px;
    }
    table {
        min-width: 700px;
        font-size: 13px;
    }
    th, td {
        padding: 10px 12px;
    }
    .btn {
        font-size: 12px;
        padding: 5px 12px;
        margin-right: 4px;
    }
    .btn-group {
        gap: 4px;
    }
}

/* Small phones */
@media (max-width: 480px) {
    .container {
        padding: 14px 10px 18px;
        border-radius: 8px;
    }
    h2 {
        font-size: 17px;
    }
    h2 i {
        font-size: 16px;
        margin-right: 6px;
    }
    table {
        min-width: 550px;
        font-size: 12px;
    }
    th, td {
        padding: 8px 10px;
    }
    th {
        font-size: 10px;
    }
    .btn {
        font-size: 10px;
        padding: 4px 10px;
        margin-right: 3px;
    }
    .btn i {
        display: none; /* hide icons on very small screens to save space */
    }
    .status {
        font-size: 10px;
    }
    .method {
        font-size: 10px !important;
        padding: 1px 8px !important;
    }
}

/* Extra small (below 380px) – rarely needed, but safe */
@media (max-width: 380px) {
    .container {
        padding: 10px 6px 14px;
    }
    table {
        min-width: 480px;
        font-size: 11px;
    }
    th, td {
        padding: 6px 8px;
    }
    .btn {
        font-size: 9px;
        padding: 3px 8px;
    }
}

/* ─── Reduce motion for accessibility ─── */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
    }
}

/* ─── Print styles ─── */
@media print {
    body {
        background: #fff !important;
        padding: 10px !important;
        margin: 0 !important;
    }
    .container {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
        background: #fff !important;
        padding: 20px !important;
        border-radius: 0 !important;
    }
    h2 {
        -webkit-text-fill-color: #b8860b !important;
        color: #b8860b !important;
        background: none !important;
    }
    th {
        background: #eee !important;
        color: #000 !important;
        border-bottom: 2px solid #aaa !important;
    }
    td {
        color: #000 !important;
        border-bottom: 1px solid #ddd !important;
    }
    .approve,
    .reject {
        border: 1px solid #aaa !important;
        background: #f0f0f0 !important;
        color: #000 !important;
        box-shadow: none !important;
    }
    .no-data::before {
        display: none !important;
    }
    .btn i {
        display: inline !important; /* show icons in print */
    }
}
</style>

</head>

<body>

<div class="container">

<h2>Pending Deposits</h2>


<?php if (isset($message)) { ?>

<div class="message">
    <?= htmlspecialchars($message) ?>
</div>

<?php } ?>


<div class="table-wrapper">

<table>

<tr>

    <th>ID</th>

    <th>Name</th>

    <th>Email</th>

    <th>Amount</th>

    <th>Method</th>

    <th>Reference</th>

    <th>Date</th>

    <th>Status</th>

    <th>Action</th>

</tr>


<?php if ($pending && mysqli_num_rows($pending) > 0) { ?>


<?php while ($row = mysqli_fetch_assoc($pending)) { ?>

<tr>

    <td>
        <?= (int)$row['id'] ?>
    </td>

    <td>
        <?= htmlspecialchars($row['name'] ?? 'Unknown') ?>
    </td>

    <td>
        <?= htmlspecialchars($row['email'] ?? '-') ?>
    </td>

    <td>
        ₹<?= number_format((float)$row['amount'], 2) ?>
    </td>

    <td>
        <?= htmlspecialchars($row['method']) ?>
    </td>

    <td>
        <?= htmlspecialchars($row['reference'] ?? '-') ?>
    </td>

    <td>
        <?= htmlspecialchars($row['created_at']) ?>
    </td>

    <td class="status">
        <?= htmlspecialchars($row['status']) ?>
    </td>

    <td>

        <a
            class="btn approve"
            href="?approve=1&txn_id=<?= (int)$row['id'] ?>"
            onclick="return confirm('Are you sure you want to approve this deposit?')"
        >
            Approve
        </a>


        <a
            class="btn reject"
            href="?reject=1&txn_id=<?= (int)$row['id'] ?>"
            onclick="return confirm('Are you sure you want to reject this deposit?')"
        >
            Reject
        </a>

    </td>

</tr>

<?php } ?>


<?php } else { ?>

<tr>

    <td colspan="9" class="no-data">
        No pending deposits found.
    </td>

</tr>

<?php } ?>

</table>

</div>

</div>

</body>

</html>