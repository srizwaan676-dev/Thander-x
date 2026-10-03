<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

/* USER DETAILS */
$sql_user = "SELECT name, mobile, upi_number FROM contact WHERE id = ? LIMIT 1";
$stmt_user = mysqli_prepare($conn, $sql_user);
if (!$stmt_user) { die("Database Error: " . mysqli_error($conn)); }
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($result_user);

$name       = $user['name'] ?? '';
$mobile     = $user['mobile'] ?? '';
$upi_number = $user['upi_number'] ?? '';

/* WITHDRAWAL REPORT */
$sql = "SELECT id, amount, method, account AS upi_number, status, created_at
        FROM wallet_transactions
        WHERE user_id = ?
        AND type = 'withdrawal'
        ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) { die("Database Error: " . mysqli_error($conn)); }
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

/* TOTALS */
$total_withdrawn = 0; $pending_count = 0; $approved_count = 0; $rejected_count = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $total_withdrawn += (float)$row['amount'];
    $status = strtolower(trim($row['status']));
    if ($status === 'pending')       $pending_count++;
    elseif ($status === 'approved')  $approved_count++;
    elseif ($status === 'rejected')  $rejected_count++;
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Withdrawal Report | ThunderX</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ========================================
   ROOT
======================================== */
:root {
    --bg-dark:      #0a0e14;
    --bg-card:      #131c26;
    --bg-row:       #1a242f;
    --bg-row-hover: #202c38;
    --border:       #253040;
    --border-light: #2e3a4a;

    --yellow:       #f5c518;
    --yellow-dark:  #d9ad0e;
    --yellow-glow:  rgba(245, 197, 24, 0.35);

    --green:        #22c55e;
    --green-dark:   #16a34a;
    --red:          #ef4444;
    --red-dark:     #dc2626;

    --text:         #e5e7eb;
    --text-dim:     #9ca3af;
    --text-mute:    #6b7280;

    --shadow-lg:    0 30px 80px rgba(0,0,0,0.8);
    --radius-lg:    14px;
    --radius-xl:    18px;
    --radius-2xl:   24px;
    --radius-full:  9999px;
    --transition:   all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    min-height: 100vh;
    background: var(--bg-dark);
    padding: 30px 20px;
    color: var(--text);
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
}

.report-container { max-width: 1200px; margin: 0 auto; }

/* PAGE HEADER */
.page-header { margin-bottom: 22px; }
.page-header h1 {
    font-size: 26px; font-weight: 800;
    color: var(--yellow); letter-spacing: -0.5px;
    display: flex; align-items: center; gap: 12px;
}
.page-header p { color: var(--text-dim); font-size: 13px; margin-top: 4px; }

/* CARD */
.report-card {
    background: var(--bg-card);
    border-radius: var(--radius-2xl);
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
    border: 1px solid var(--border);
    animation: fadeUp 0.5s ease;
}

.report-header {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 20px;
    flex-wrap: wrap; gap: 14px;
}
.report-title {
    font-size: 20px; font-weight: 800;
    color: var(--yellow);
    display: flex; align-items: center; gap: 10px;
}

.report-stats { display: flex; gap: 10px; flex-wrap: wrap; }
.report-stats .stat-item {
    display: flex; align-items: center; gap: 6px;
    padding: 6px 14px;
    background: var(--bg-row);
    border-radius: var(--radius-full);
    font-size: 12px; font-weight: 600;
    color: var(--text-dim);
    border: 1px solid var(--border);
}
.report-stats .stat-item .stat-number { font-weight: 800; }
.report-stats .stat-item.total i,    .report-stats .stat-item.total .stat-number    { color: var(--yellow); }
.report-stats .stat-item.pending i,  .report-stats .stat-item.pending .stat-number  { color: #f59e0b; }
.report-stats .stat-item.approved i, .report-stats .stat-item.approved .stat-number { color: var(--green); }
.report-stats .stat-item.rejected i, .report-stats .stat-item.rejected .stat-number { color: var(--red); }

/* TABLE */
.table-wrapper {
    overflow-x: auto;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
}
table { width: 100%; border-collapse: collapse; min-width: 900px; }
table thead th {
    background: var(--bg-row);
    color: var(--yellow);
    padding: 14px 18px; text-align: left;
    font-size: 11px; font-weight: 800;
    text-transform: uppercase; letter-spacing: 1px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
table tbody td {
    padding: 16px 18px;
    color: var(--text);
    font-size: 13px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    white-space: nowrap;
}
table tbody tr { background: var(--bg-card); transition: var(--transition); cursor: pointer; }
table tbody tr:hover { background: var(--bg-row-hover); }
table tbody tr:last-child td { border-bottom: none; }

.badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: var(--radius-full);
    font-size: 10px; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.badge-pending  { background: rgba(245,158,11,0.15); color: #f59e0b; }
.badge-approved { background: rgba(34,197,94,0.15);  color: var(--green); }
.badge-rejected { background: rgba(239,68,68,0.15);  color: var(--red); }

.amount.negative { color: #f87171; font-weight: 700; }
.method-icon { display: inline-flex; align-items: center; gap: 6px; color: var(--text-dim); }
.method-icon i { font-size: 14px; color: var(--yellow); }

.no-data { text-align: center; padding: 60px 20px; color: var(--text-mute); }
.no-data i { font-size: 50px; opacity: 0.25; display: block; margin-bottom: 14px; color: var(--yellow); }

/* ========================================
   🌟 ULTRA PREMIUM MODAL
======================================== */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(5, 8, 12, 0.88);
    backdrop-filter: blur(16px) saturate(160%);
    -webkit-backdrop-filter: blur(16px) saturate(160%);
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
    z-index: 9999;
    opacity: 0; visibility: hidden;
    transition: opacity 0.4s ease, visibility 0.4s ease;
}
.modal-overlay.active { opacity: 1; visibility: visible; }

/* Floating blurred blobs behind modal */
.modal-blobs {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}
.modal-blobs span {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.35;
    animation: floatBlob 12s ease-in-out infinite;
}
.modal-blobs span:nth-child(1) {
    width: 260px; height: 260px;
    background: var(--yellow);
    top: 10%; left: 15%;
    animation-delay: 0s;
}
.modal-blobs span:nth-child(2) {
    width: 220px; height: 220px;
    background: #6366f1;
    bottom: 10%; right: 15%;
    animation-delay: -4s;
}
.modal-blobs span:nth-child(3) {
    width: 180px; height: 180px;
    background: var(--green);
    top: 50%; right: 25%;
    animation-delay: -8s;
    opacity: 0.2;
}

@keyframes floatBlob {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33%      { transform: translate(30px, -40px) scale(1.1); }
    66%      { transform: translate(-25px, 30px) scale(0.9); }
}

/* Modal Box */
.modal-box {
    position: relative;
    background: linear-gradient(180deg, #18222e 0%, #131c26 100%);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-2xl);
    width: 100%;
    max-width: 500px;
    box-shadow: var(--shadow-lg), 0 0 0 1px rgba(245,197,24,0.08);
    overflow: hidden;
    transform: translateY(50px) scale(0.88);
    opacity: 0;
    transition: transform 0.55s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.35s ease;
    isolation: isolate;
}
.modal-overlay.active .modal-box {
    transform: translateY(0) scale(1);
    opacity: 1;
}

/* Animated mesh gradient inside modal */
.modal-box::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 20% 0%, rgba(245,197,24,0.12) 0%, transparent 40%),
        radial-gradient(circle at 80% 100%, rgba(99,102,241,0.08) 0%, transparent 40%);
    animation: meshShift 8s ease-in-out infinite;
    pointer-events: none;
    z-index: 0;
}

@keyframes meshShift {
    0%, 100% { opacity: 0.6; transform: scale(1); }
    50%      { opacity: 1; transform: scale(1.05); }
}

/* Shimmer top line */
.modal-box::after {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 100%; height: 2px;
    background: linear-gradient(90deg, transparent, var(--yellow), transparent);
    animation: shimmer 3s linear infinite;
    z-index: 3;
}
@keyframes shimmer {
    0%   { left: -100%; }
    100% { left: 100%; }
}

/* Header */
.modal-header {
    padding: 30px 30px 22px;
    text-align: center;
    position: relative;
    border-bottom: 1px solid var(--border);
    z-index: 1;
}

/* Floating particles */
.modal-particles {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}
.modal-particles span {
    position: absolute;
    width: 3px; height: 3px;
    background: var(--yellow);
    border-radius: 50%;
    opacity: 0;
    animation: particleFloat 6s ease-in-out infinite;
    box-shadow: 0 0 6px var(--yellow);
}
.modal-particles span:nth-child(1) { left: 20%; top: 40%; animation-delay: 0s; }
.modal-particles span:nth-child(2) { left: 35%; top: 60%; animation-delay: 1.2s; }
.modal-particles span:nth-child(3) { left: 65%; top: 35%; animation-delay: 2.4s; }
.modal-particles span:nth-child(4) { left: 80%; top: 55%; animation-delay: 3.6s; }
.modal-particles span:nth-child(5) { left: 50%; top: 25%; animation-delay: 4.8s; }

@keyframes particleFloat {
    0%   { opacity: 0; transform: translateY(20px) scale(0.5); }
    50%  { opacity: 1; transform: translateY(-10px) scale(1); }
    100% { opacity: 0; transform: translateY(-30px) scale(0.5); }
}

/* SVG Progress ring + icon */
.modal-icon-wrap {
    width: 92px; height: 92px;
    margin: 0 auto 16px;
    position: relative;
    display: flex; align-items: center; justify-content: center;
}

.modal-icon-wrap svg.ring {
    position: absolute;
    inset: 0;
    transform: rotate(-90deg);
}
.modal-icon-wrap svg.ring circle {
    fill: none;
    stroke-width: 2.5;
    stroke-linecap: round;
}
.modal-icon-wrap svg.ring .bg { stroke: rgba(245,197,24,0.12); }
.modal-icon-wrap svg.ring .fg {
    stroke: var(--yellow);
    stroke-dasharray: 283;
    stroke-dashoffset: 283;
    filter: drop-shadow(0 0 6px var(--yellow-glow));
}
.modal-overlay.active .modal-icon-wrap svg.ring .fg {
    animation: ringDraw 1.6s cubic-bezier(0.4, 0, 0.2, 1) forwards 0.3s,
               ringSpin 3s linear infinite 1.9s;
}
@keyframes ringDraw {
    to { stroke-dashoffset: 70; }
}
@keyframes ringSpin {
    from { transform: rotate(-90deg); }
    to   { transform: rotate(270deg); }
}

.modal-icon-inner {
    width: 66px; height: 66px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(245,197,24,0.28) 0%, rgba(245,197,24,0.06) 70%);
    display: flex; align-items: center; justify-content: center;
    position: relative;
    box-shadow: 0 0 30px rgba(245,197,24,0.25), inset 0 0 20px rgba(245,197,24,0.1);
}
.modal-icon-inner i {
    font-size: 26px;
    color: var(--yellow);
    filter: drop-shadow(0 0 10px rgba(245,197,24,0.7));
}
.modal-overlay.active .modal-icon-inner {
    animation: iconEnter 0.9s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both;
}
@keyframes iconEnter {
    0%   { transform: scale(0) rotate(-180deg); }
    60%  { transform: scale(1.15) rotate(10deg); }
    100% { transform: scale(1) rotate(0deg); }
}

.modal-header h3 {
    font-size: 21px; font-weight: 800;
    color: var(--yellow);
    letter-spacing: -0.3px;
    margin-bottom: 6px;
}
.modal-header p {
    font-size: 12px;
    color: var(--text-dim);
    letter-spacing: 0.3px;
}
.modal-header p span {
    color: var(--yellow);
    font-weight: 800;
    font-family: 'JetBrains Mono', monospace;
    background: rgba(245,197,24,0.1);
    padding: 2px 8px;
    border-radius: 6px;
    border: 1px solid rgba(245,197,24,0.2);
}

.modal-close {
    position: absolute;
    top: 16px; right: 16px;
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
    color: var(--text-dim);
    border: 1px solid var(--border);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    transition: var(--transition);
    z-index: 5;
}
.modal-close:hover {
    background: var(--red);
    color: #fff;
    border-color: var(--red);
    transform: rotate(180deg) scale(1.12);
    box-shadow: 0 0 20px rgba(239,68,68,0.5);
}

/* Body */
.modal-body {
    padding: 18px 30px 10px;
    max-height: 44vh;
    overflow-y: auto;
    position: relative;
    z-index: 1;
}

.modal-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 4px;
    border-bottom: 1px solid var(--border);
    gap: 16px;
    transition: var(--transition);
    border-radius: 10px;
}
.modal-row:hover {
    background: rgba(245,197,24,0.03);
    padding-left: 10px;
    padding-right: 10px;
}
.modal-row:last-child { border-bottom: none; }

.modal-row .label {
    font-size: 11px; font-weight: 700;
    color: var(--text-mute);
    text-transform: uppercase;
    letter-spacing: 0.7px;
    display: flex; align-items: center; gap: 10px;
    flex-shrink: 0;
}
.modal-row .label i {
    width: 30px; height: 30px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(245,197,24,0.12), rgba(245,197,24,0.04));
    border: 1px solid rgba(245,197,24,0.2);
    color: var(--yellow);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 11px;
    transition: var(--transition);
}
.modal-row:hover .label i {
    background: linear-gradient(135deg, rgba(245,197,24,0.25), rgba(245,197,24,0.1));
    transform: scale(1.1) rotate(-5deg);
    box-shadow: 0 0 15px rgba(245,197,24,0.3);
}

.modal-row .value {
    font-size: 14px; font-weight: 600;
    color: var(--text);
    text-align: right;
    word-break: break-word;
}
.modal-row .value.amount-value {
    color: var(--yellow);
    font-size: 24px;
    font-weight: 800;
    letter-spacing: -0.8px;
    text-shadow: 0 0 25px rgba(245,197,24,0.35);
}
.modal-overlay.active .modal-row .value.amount-value {
    animation: amountPop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.5s both;
}
@keyframes amountPop {
    0%   { opacity: 0; transform: scale(0.6); }
    100% { opacity: 1; transform: scale(1); }
}

/* Footer */
.modal-footer {
    padding: 18px 30px 26px;
    display: flex;
    gap: 10px;
    border-top: 1px solid var(--border);
    background: rgba(0,0,0,0.2);
    position: relative;
    z-index: 1;
}

.modal-btn {
    flex: 1;
    padding: 14px 22px;
    border-radius: var(--radius-lg);
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    position: relative;
    overflow: hidden;
    isolation: isolate;
}

/* Ripple */
.modal-btn .ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.4);
    transform: scale(0);
    animation: rippleAnim 0.6s ease-out;
    pointer-events: none;
}
@keyframes rippleAnim {
    to { transform: scale(4); opacity: 0; }
}

.modal-btn.secondary {
    background: transparent;
    color: var(--text-dim);
    border: 1px solid var(--border);
    flex: 0.75;
}
.modal-btn.secondary:hover {
    background: var(--bg-row-hover);
    color: var(--text);
    border-color: var(--border-light);
}

.modal-btn.approve {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: #fff;
    box-shadow: 0 6px 24px rgba(34,197,94,0.35), inset 0 1px 0 rgba(255,255,255,0.25);
}
.modal-btn.approve:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(34,197,94,0.55), inset 0 1px 0 rgba(255,255,255,0.35);
}

.modal-btn.reject {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #fff;
    box-shadow: 0 6px 24px rgba(239,68,68,0.35), inset 0 1px 0 rgba(255,255,255,0.25);
}
.modal-btn.reject:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(239,68,68,0.55), inset 0 1px 0 rgba(255,255,255,0.35);
}

.modal-btn:active { transform: translateY(0) scale(0.97); }

/* ========================================
   CONFIRM STEP
======================================== */
.modal-confirm {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, #18222e 0%, #131c26 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 44px 32px;
    text-align: center;
    opacity: 0;
    visibility: hidden;
    transform: scale(0.88);
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index: 10;
    border-radius: var(--radius-2xl);
}
.modal-confirm.active {
    opacity: 1;
    visibility: visible;
    transform: scale(1);
}

.confirm-icon {
    width: 100px; height: 100px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 24px;
    font-size: 40px;
    color: #fff;
    position: relative;
}
.confirm-icon.approve {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    box-shadow: 0 12px 50px rgba(34,197,94,0.55), inset 0 0 30px rgba(255,255,255,0.15);
}
.confirm-icon.reject {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 12px 50px rgba(239,68,68,0.55), inset 0 0 30px rgba(255,255,255,0.15);
}
.confirm-icon::before,
.confirm-icon::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 2px solid currentColor;
    opacity: 0.5;
}
.confirm-icon.approve::before { color: #22c55e; animation: rippleRing 2s ease-out infinite; }
.confirm-icon.approve::after  { color: #22c55e; animation: rippleRing 2s ease-out infinite 1s; }
.confirm-icon.reject::before  { color: #ef4444; animation: rippleRing 2s ease-out infinite; }
.confirm-icon.reject::after   { color: #ef4444; animation: rippleRing 2s ease-out infinite 1s; }

@keyframes rippleRing {
    0%   { transform: scale(1);   opacity: 0.6; }
    100% { transform: scale(1.7); opacity: 0; }
}

.modal-confirm.active .confirm-icon {
    animation: confirmPop 0.65s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes confirmPop {
    0%   { transform: scale(0) rotate(-90deg); }
    60%  { transform: scale(1.18) rotate(8deg); }
    100% { transform: scale(1) rotate(0); }
}

.modal-confirm h4 {
    font-size: 22px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 10px;
    letter-spacing: -0.4px;
}
.modal-confirm p {
    font-size: 13px;
    color: var(--text-dim);
    max-width: 320px;
    margin-bottom: 30px;
    line-height: 1.7;
}
.modal-confirm p span {
    color: var(--yellow);
    font-weight: 800;
    font-size: 15px;
}

.confirm-actions {
    display: flex;
    gap: 10px;
    width: 100%;
    max-width: 340px;
}
.confirm-actions .modal-btn { flex: 1; }

/* Loading spinner for confirm */
.modal-btn.loading {
    pointer-events: none;
    opacity: 0.85;
}
.modal-btn .fa-spinner { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ========================================
   ANIMATIONS
======================================== */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(15px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ========================================
   RESPONSIVE
======================================== */
@media (max-width: 768px) {
    body { padding: 16px 12px; }
    .report-card { padding: 16px; }
    .page-header h1 { font-size: 20px; }
    .modal-box { max-width: 100%; }
}
@media (max-width: 480px) {
    .page-header h1 { font-size: 17px; }
    .modal-header { padding: 24px 22px 18px; }
    .modal-icon-wrap { width: 78px; height: 78px; }
    .modal-icon-inner { width: 56px; height: 56px; }
    .modal-icon-inner i { font-size: 22px; }
    .modal-body { padding: 12px 22px 6px; }
    .modal-footer { padding: 14px 22px 20px; flex-wrap: wrap; }
    .modal-btn.secondary { flex: 1 1 100%; order: 3; margin-top: 4px; }
    .modal-row .value.amount-value { font-size: 19px; }
    .modal-confirm { padding: 32px 22px; }
    .confirm-icon { width: 84px; height: 84px; font-size: 34px; }
}

::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: var(--bg-dark); }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 20px; }
::-webkit-scrollbar-thumb:hover { background: var(--yellow); }
</style>
</head>
<body>

<div class="report-container">

    <div class="page-header">
        <h1><i class="fas fa-file-invoice"></i> Withdrawal Report</h1>
        <p>View all your withdrawal requests and their current status</p>
    </div>

    <div class="report-card">
        <div class="report-header">
            <div class="report-title"><i class="fas fa-history"></i> Withdrawal History</div>
            <div class="report-stats">
                <span class="stat-item total"><i class="fas fa-coins"></i> Total: <span class="stat-number">₹<?php echo number_format($total_withdrawn, 2); ?></span></span>
                <span class="stat-item pending"><i class="fas fa-clock"></i> Pending: <span class="stat-number"><?php echo $pending_count; ?></span></span>
                <span class="stat-item approved"><i class="fas fa-check-circle"></i> Approved: <span class="stat-number"><?php echo $approved_count; ?></span></span>
                <span class="stat-item rejected"><i class="fas fa-times-circle"></i> Rejected: <span class="stat-number"><?php echo $rejected_count; ?></span></span>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Name</th><th>Amount</th><th>Method</th>
                        <th>Account / UPI</th><th>Mobile</th><th>Status</th><th>Date</th>
                    </tr>
                </thead>
                <tbody>

                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php $i = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <?php
                        $status = strtolower(trim($row['status']));
                        $status_class = 'pending'; $status_label = 'Pending'; $status_icon = 'fa-clock';
                        if ($status === 'approved' || $status === 'completed') {
                            $status_class = 'approved'; $status_label = 'Approved'; $status_icon = 'fa-check-circle';
                        } elseif ($status === 'rejected' || $status === 'failed') {
                            $status_class = 'rejected'; $status_label = 'Rejected'; $status_icon = 'fa-times-circle';
                        }
                        $method_icon = 'fa-university';
                        $method_label = htmlspecialchars($row['method']);
                        if (stripos($row['method'], 'upi') !== false)        $method_icon = 'fa-mobile-screen-button';
                        elseif (stripos($row['method'], 'paypal') !== false) $method_icon = 'fa-paypal';
                        elseif (stripos($row['method'], 'crypto') !== false) $method_icon = 'fa-bitcoin';
                        elseif (stripos($row['method'], 'bank') !== false)   $method_icon = 'fa-building-columns';
                        ?>
                        <tr
                            data-id="#<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"
                            data-name="<?php echo htmlspecialchars($name ?: '-'); ?>"
                            data-mobile="<?php echo htmlspecialchars($mobile ?: '-'); ?>"
                            data-amount="<?php echo number_format((float)$row['amount'], 2); ?>"
                            data-method="<?php echo $method_label; ?>"
                            data-account="<?php echo htmlspecialchars($upi_number ?: '-'); ?>"
                            data-status="<?php echo $status_label; ?>"
                            data-status-class="<?php echo $status_class; ?>"
                            data-status-icon="<?php echo $status_icon; ?>"
                            data-date="<?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?>"
                        >
                            <td><span style="color: var(--text-mute); font-weight:700;">#<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></span></td>
                            <td><?php echo htmlspecialchars($name ?: '-'); ?></td>
                            <td class="amount negative">₹<?php echo number_format((float)$row['amount'], 2); ?></td>
                            <td><span class="method-icon"><i class="fas <?php echo $method_icon; ?>"></i><?php echo $method_label; ?></span></td>
                            <td><?php echo htmlspecialchars($upi_number ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($mobile ?: '-'); ?></td>
                            <td><span class="badge badge-<?php echo $status_class; ?>"><i class="fas <?php echo $status_icon; ?>"></i> <?php echo $status_label; ?></span></td>
                            <td style="color: var(--text-mute); font-size: 12px;"><?php echo date("Y-m-d H:i:s", strtotime($row['created_at'])); ?></td>
                        </tr>
                    <?php $i++; endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="no-data">
                        <i class="fas fa-receipt"></i>
                        <div>No withdrawal records found</div>
                    </td></tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== ULTRA MODAL ===================== -->
<div class="modal-overlay" id="withdrawModal">

    <!-- Floating background blobs -->
    <div class="modal-blobs"><span></span><span></span><span></span></div>

    <div class="modal-box">

        <!-- ====== VIEW ====== -->
        <div id="modalView">
            <div class="modal-header">

                <!-- Floating particles -->
                <div class="modal-particles">
                    <span></span><span></span><span></span><span></span><span></span>
                </div>

                <button class="modal-close" onclick="closeModal()" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>

                <div class="modal-icon-wrap">
                    <svg class="ring" viewBox="0 0 100 100">
                        <circle class="bg" cx="50" cy="50" r="45"></circle>
                        <circle class="fg" cx="50" cy="50" r="45"></circle>
                    </svg>
                    <div class="modal-icon-inner">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>

                <h3>Withdrawal Details</h3>
                <p>Transaction <span id="m-id">#00</span></p>
            </div>

            <div class="modal-body">
                <div class="modal-row">
                    <span class="label"><i class="fas fa-user"></i> Name</span>
                    <span class="value" id="m-name">-</span>
                </div>
                <div class="modal-row">
                    <span class="label"><i class="fas fa-phone"></i> Mobile</span>
                    <span class="value" id="m-mobile">-</span>
                </div>
                <div class="modal-row">
                    <span class="label"><i class="fas fa-coins"></i> Amount</span>
                    <span class="value amount-value" id="m-amount">-</span>
                </div>
                <div class="modal-row">
                    <span class="label"><i class="fas fa-wallet"></i> Method</span>
                    <span class="value" id="m-method">-</span>
                </div>
                <div class="modal-row">
                    <span class="label"><i class="fas fa-mobile-screen-button"></i> Account / UPI</span>
                    <span class="value" id="m-account">-</span>
                </div>
                <div class="modal-row">
                    <span class="label"><i class="fas fa-info-circle"></i> Status</span>
                    <span class="value"><span class="badge" id="m-status-badge">-</span></span>
                </div>
                <div class="modal-row">
                    <span class="label"><i class="fas fa-calendar-alt"></i> Date</span>
                    <span class="value" id="m-date">-</span>
                </div>
            </div>

            <div class="modal-footer">
                <button class="modal-btn secondary" onclick="closeModal()">
                    <i class="fas fa-times"></i> Close
                </button>
                <button class="modal-btn approve" onclick="showConfirm('approve')">
                    <i class="fas fa-check"></i> Approve
                </button>
                <button class="modal-btn reject" onclick="showConfirm('reject')">
                    <i class="fas fa-times"></i> Reject
                </button>
            </div>
        </div>

        <!-- ====== CONFIRM ====== -->
        <div class="modal-confirm" id="modalConfirm">
            <div class="confirm-icon" id="confirmIcon">
                <i class="fas fa-check"></i>
            </div>
            <h4 id="confirmTitle">Approve Withdrawal?</h4>
            <p id="confirmText">
                You are about to <span>approve</span> withdrawal of <span id="confirmAmount">₹0</span>.
                This action cannot be undone.
            </p>
            <div class="confirm-actions">
                <button class="modal-btn secondary" onclick="hideConfirm()">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <button class="modal-btn approve" id="confirmBtn" onclick="executeAction(event)">
                    <i class="fas fa-check"></i> Yes, Confirm
                </button>
            </div>
        </div>

    </div>
</div>

<script>
let currentRowId   = null;
let currentAction  = null;
let currentAmount  = null;

document.addEventListener('DOMContentLoaded', function () {
    const rows  = document.querySelectorAll('tbody tr[data-id]');
    const modal = document.getElementById('withdrawModal');

    rows.forEach(function (row) {
        row.addEventListener('click', function () {
            const d = row.dataset;
            currentRowId  = d.id;
            currentAmount = d.amount;

            document.getElementById('m-id').textContent      = d.id;
            document.getElementById('m-name').textContent    = d.name;
            document.getElementById('m-mobile').textContent  = d.mobile;
            document.getElementById('m-amount').textContent  = '₹' + d.amount;
            document.getElementById('m-method').textContent  = d.method;
            document.getElementById('m-account').textContent = d.account;
            document.getElementById('m-date').textContent    = d.date;

            const badge = document.getElementById('m-status-badge');
            badge.className = 'badge badge-' + d.statusClass;
            badge.innerHTML = '<i class="fas ' + d.statusIcon + '"></i> ' + d.status;

            hideConfirm();
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
    });

    // Attach ripple to all modal buttons
    document.querySelectorAll('.modal-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            const r = document.createElement('span');
            r.className = 'ripple';
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            r.style.width = r.style.height = size + 'px';
            r.style.left = (e.clientX - rect.left - size / 2) + 'px';
            r.style.top  = (e.clientY - rect.top  - size / 2) + 'px';
            this.appendChild(r);
            setTimeout(() => r.remove(), 600);
        });
    });
});

function closeModal() {
    document.getElementById('withdrawModal').classList.remove('active');
    document.body.style.overflow = '';
    setTimeout(() => {
        hideConfirm();
        currentRowId = currentAction = currentAmount = null;
    }, 300);
}

function showConfirm(action) {
    currentAction = action;
    const confirmBox = document.getElementById('modalConfirm');
    const iconWrap   = document.getElementById('confirmIcon');
    const title      = document.getElementById('confirmTitle');
    const text       = document.getElementById('confirmText');
    const confirmBtn = document.getElementById('confirmBtn');

    if (action === 'approve') {
        iconWrap.className = 'confirm-icon approve';
        iconWrap.innerHTML = '<i class="fas fa-check"></i>';
        title.textContent = 'Approve Withdrawal?';
        text.innerHTML = 'You are about to <span>approve</span> the withdrawal of <span>₹' + currentAmount + '</span>. This action cannot be undone.';
        confirmBtn.className = 'modal-btn approve';
        confirmBtn.innerHTML = '<i class="fas fa-check"></i> Yes, Approve';
    } else {
        iconWrap.className = 'confirm-icon reject';
        iconWrap.innerHTML = '<i class="fas fa-times"></i>';
        title.textContent = 'Reject Withdrawal?';
        text.innerHTML = 'You are about to <span>reject</span> the withdrawal of <span>₹' + currentAmount + '</span>. This action cannot be undone.';
        confirmBtn.className = 'modal-btn reject';
        confirmBtn.innerHTML = '<i class="fas fa-times"></i> Yes, Reject';
    }

    confirmBox.classList.add('active');
}

function hideConfirm() {
    document.getElementById('modalConfirm').classList.remove('active');
    currentAction = null;
}

function executeAction(e) {
    if (!currentRowId || !currentAction) return;

    // 🔥 Backend call yahan lagayen:
    // window.location.href = 'update_status.php?id=' + currentRowId.replace('#','') + '&action=' + currentAction;

    const btn = document.getElementById('confirmBtn');
    const original = btn.innerHTML;

    btn.classList.add('loading');
    btn.innerHTML = '<i class="fas fa-spinner"></i> Processing...';

    setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-check"></i> Done!';
        setTimeout(() => {
            btn.classList.remove('loading');
            btn.innerHTML = original;
            closeModal();
        }, 900);
    }, 1000);
}
</script>

</body>
</html>