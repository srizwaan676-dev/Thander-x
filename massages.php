<?php
session_start();
include("../db.php");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$current_user_id = (int)$_SESSION['user_id'];

// Fetch all users except current, with unread message count
$sql = "SELECT 
            id, 
            name, 
            mobile,
            (SELECT COUNT(*) FROM messages 
             WHERE sender_id = contact.id 
             AND receiver_id = ? 
             AND is_read = 0) AS unread_count
        FROM contact 
        WHERE id != ?
        ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Prepare Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ii", $current_user_id, $current_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

// Calculate total unread messages
$total_unread = 0;
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $total_unread += (int)$row['unread_count'];
    $users[] = $row;
}
// Reset pointer for display
mysqli_data_seek($result, 0);

// Helper for avatar colors
function getAvatarColor($id) {
    $colors = ['#f43f5e', '#8b5cf6', '#475569', '#f59e0b', '#0ea5e9', '#10b981', '#2563eb', '#dc2626', '#d97706', '#0891b2'];
    return $colors[$id % count($colors)];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reels · Friends</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&family=Playfair+Display:wght@700&display=swap" rel="stylesheet" />

    <style>
        /* ============================================================
           REELS · FRIENDS — WITH NOTIFICATIONS
           ============================================================ */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            min-height: 100vh;
            background: #0b0e1a;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .reels-card {
            max-width: 480px;
            width: 100%;
            background: linear-gradient(145deg, #141b2b, #1e2740);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.04);
            animation: cardPop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            transition: transform 0.4s ease;
            max-height: 98vh;
            display: flex;
            flex-direction: column;
        }

        .reels-card:hover {
            transform: translateY(-4px);
        }

        @keyframes cardPop {
            from { opacity: 0; transform: scale(0.92) translateY(30px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* ---------- HEADER ---------- */
        .reels-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 22px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            flex-shrink: 0;
        }

        .reels-header .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .reels-header .brand .icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 18px;
            box-shadow: 0 4px 16px rgba(245, 87, 108, 0.3);
        }

        .reels-header .brand h1 {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.3px;
        }

        .reels-header .brand h1 span {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .reels-header .actions {
            display: flex;
            align-items: center;
            gap: 16px;
            color: #94a3b8;
            font-size: 18px;
        }

        .reels-header .actions .notification-bell {
            position: relative;
            cursor: pointer;
            transition: color 0.3s;
        }

        .reels-header .actions .notification-bell:hover {
            color: #f5576c;
        }

        .reels-header .actions .notification-bell .badge {
            position: absolute;
            top: -6px;
            right: -8px;
            background: #f5576c;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #1e2740;
            padding: 0 4px;
            line-height: 1;
        }

        .reels-header .actions .notification-bell .badge.hidden {
            display: none;
        }

        .reels-header .actions i {
            cursor: pointer;
            transition: color 0.3s, transform 0.3s;
        }

        .reels-header .actions i:hover {
            color: #f5576c;
            transform: scale(1.1);
        }

        /* ---------- TABS ---------- */
        .tabs {
            display: flex;
            padding: 0 22px;
            gap: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            flex-shrink: 0;
        }

        .tabs .tab {
            padding: 12px 0 10px;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
        }

        .tabs .tab.active {
            color: #fff;
            border-bottom-color: #f5576c;
        }

        .tabs .tab:hover:not(.active) {
            color: #cbd5e1;
        }

        /* ---------- SEARCH ---------- */
        .search-wrapper {
            padding: 10px 22px 8px;
            flex-shrink: 0;
            position: relative;
        }

        .search-wrapper input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.06);
            background: rgba(255,255,255,0.04);
            color: #fff;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-wrapper input::placeholder {
            color: #64748b;
        }

        .search-wrapper input:focus {
            outline: none;
            border-color: #f5576c;
            background: rgba(255,255,255,0.06);
        }

        .search-wrapper .search-icon {
            position: absolute;
            left: 30px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        /* ---------- CONTACT LIST ---------- */
        .contact-list {
            padding: 4px 0 12px;
            overflow-y: auto;
            flex: 1;
            scrollbar-width: thin;
            scrollbar-color: #f5576c transparent;
        }

        .contact-list::-webkit-scrollbar {
            width: 4px;
        }
        .contact-list::-webkit-scrollbar-track {
            background: transparent;
        }
        .contact-list::-webkit-scrollbar-thumb {
            background: #f5576c;
            border-radius: 10px;
        }

        /* ---------- CONTACT ITEM ---------- */
        .contact-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 22px;
            transition: all 0.3s ease;
            cursor: pointer;
            border-left: 3px solid transparent;
            position: relative;
        }

        .contact-item:hover {
            background: rgba(255, 255, 255, 0.03);
            border-left-color: #f5576c;
        }

        .contact-item .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 20px;
            color: #fff;
            position: relative;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .contact-item:hover .avatar {
            transform: scale(1.04);
        }

        .contact-item .avatar .status-dot {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #1e2740;
            background: #27ff0b;
        }

        .contact-item .avatar .status-dot.offline {
            background: #64748b;
        }

        .contact-item .info {
            flex: 1;
            min-width: 0;
        }

        .contact-item .info .name {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .contact-item .info .name .unread-badge {
            background: #f5576c;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            line-height: 1;
        }

        .contact-item .info .status {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .contact-item .info .status .highlight {
            color: #f5576c;
            font-weight: 600;
        }

        .contact-item .time {
            font-size: 11px;
            color: #64748b;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .contact-item .chat-btn-small {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.08);
            color: #cbd5e1;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            white-space: nowrap;
        }

        .contact-item .chat-btn-small:hover {
            background: #f5576c;
            border-color: #f5576c;
            color: #fff;
        }

        /* ---------- FOOTER ---------- */
        .reels-footer {
            padding: 12px 22px 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            flex-shrink: 0;
        }

        .reels-footer .followers {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
        }

        .reels-footer .followers .count {
            font-size: 18px;
            font-weight: 800;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .reels-footer .followers i {
            color: #f5576c;
            font-size: 16px;
        }

        .reels-footer .footer-actions {
            display: flex;
            gap: 14px;
            color: #64748b;
            font-size: 18px;
        }

        .reels-footer .footer-actions i {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .reels-footer .footer-actions i:hover {
            color: #f5576c;
            transform: scale(1.12);
        }

        /* ---------- RESPONSIVE ---------- */
        @media (max-width: 480px) {
            body { padding: 10px; }
            .reels-card { border-radius: 24px; }
            .reels-header { padding: 14px 16px 10px; }
            .reels-header .brand h1 { font-size: 18px; }
            .reels-header .brand .icon { width: 34px; height: 34px; font-size: 15px; }
            .tabs { padding: 0 16px; gap: 16px; }
            .tabs .tab { font-size: 12px; padding: 10px 0 8px; }
            .contact-item { padding: 10px 16px; gap: 12px; }
            .contact-item .avatar { width: 44px; height: 44px; font-size: 16px; }
            .contact-item .avatar .status-dot { width: 10px; height: 10px; }
            .contact-item .info .name { font-size: 13px; }
            .contact-item .info .status { font-size: 12px; }
            .contact-item .time { font-size: 10px; }
            .contact-item .chat-btn-small { font-size: 10px; padding: 4px 10px; }
            .reels-footer { flex-direction: column; align-items: stretch; text-align: center; }
            .reels-footer .followers { justify-content: center; }
            .reels-footer .footer-actions { justify-content: center; }
            .reels-header .actions .notification-bell .badge {
                font-size: 8px;
                min-width: 14px;
                height: 14px;
                top: -4px;
                right: -6px;
            }
        }

        @media (max-width: 380px) {
            .contact-item .info .name { font-size: 12px; }
            .contact-item .info .status { font-size: 11px; }
            .contact-item .avatar { width: 38px; height: 38px; font-size: 14px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .reels-card { animation: none !important; transform: none !important; }
            .contact-item .avatar { animation: none !important; transform: none !important; }
        }
    </style>
</head>
<body>

<div class="reels-card">

    <!-- HEADER -->
    <div class="reels-header">
        <div class="brand">
        
            <h1><span>·</span> Friends</h1>
        </div>
        <div class="actions">
            <div class="notification-bell" id="notificationBell" title="Unread messages">
                <i class="fas fa-bell"></i>
                <span class="badge <?php echo $total_unread > 0 ? '' : 'hidden'; ?>" id="totalUnreadBadge">
                    <?php echo $total_unread > 0 ? $total_unread : ''; ?>
                </span>
            </div>
            <i class="fas fa-ellipsis-v"></i>
        </div>
    </div>

    <!-- TABS -->
    <div class="tabs">
        <span class="tab active">For You</span>
        <span class="tab">Following</span>
        <span class="tab">Archived</span>
    </div>

    <!-- SEARCH -->
    <div class="search-wrapper">
        <span class="search-icon"><i class="fas fa-search"></i></span>
        <input type="text" id="searchInput" placeholder="Search contacts..." autocomplete="off" />
    </div>

    <!-- CONTACT LIST -->
    <div class="contact-list" id="contactList">

        <!-- ===== DATABASE USERS WITH UNREAD BADGES ===== -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($user = mysqli_fetch_assoc($result)):
                $user_id   = (int)$user['id'];
                $user_name = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
                $user_mobile = htmlspecialchars($user['mobile'], ENT_QUOTES, 'UTF-8');
                $unread = (int)$user['unread_count'];
                $color = getAvatarColor($user_id);
                $online = ($user_id % 2 == 0); // mock online status
            ?>
                <div class="contact-item" data-search="<?php echo strtolower($user_name . ' ' . $user_mobile); ?>">
                    <div class="avatar" style="background: <?php echo $color; ?>;">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                        <span class="status-dot <?php echo $online ? '' : 'offline'; ?>"></span>
                    </div>
                    <div class="info">
                        <div class="name">
                            <?php echo $user_name; ?>
                            <?php if ($unread > 0): ?>
                                <span class="unread-badge"><?php echo $unread; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="status">
                            <span>📱 <?php echo $user_mobile; ?></span>
                        </div>
                    </div>
                    <a href="chat.php?user_id=<?php echo $user_id; ?>" class="chat-btn-small">💬 Chat</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align:center; padding:30px; color:#64748b;">No other users found.</div>
        <?php endif; ?>

    </div>

    <!-- FOOTER -->
    <div class="reels-footer">
        <div class="followers">
            <i class="fas fa-user-plus"></i>
            <span class="count" id="followerCount"><?php echo mysqli_num_rows($result); ?></span>
            <span>followers</span>
        </div>
        <div class="footer-actions">
            <i class="fas fa-heart"></i>
            <i class="fas fa-comment"></i>
        </div>
    </div>

</div>

<?php
// Clean up
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!-- ===== JAVASCRIPT ===== -->
<script>
    (function() {
        'use strict';

        // ---------- FOLLOWER COUNTER ANIMATION ----------
        const followerEl = document.getElementById('followerCount');
        if (followerEl) {
            const target = parseInt(followerEl.textContent.replace(/,/g, ''), 10) || 0;
            let current = 0;
            const duration = 800;
            const startTime = performance.now();

            function animateCounter(time) {
                const elapsed = time - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                current = Math.floor(eased * target);
                followerEl.textContent = current.toLocaleString('en-US');
                if (progress < 1) {
                    requestAnimationFrame(animateCounter);
                } else {
                    followerEl.textContent = target.toLocaleString('en-US');
                }
            }
            setTimeout(() => requestAnimationFrame(animateCounter), 300);
        }

        // ---------- TAB SWITCHING ----------
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // ---------- SEARCH FILTER ----------
        const searchInput = document.getElementById('searchInput');
        const items = document.querySelectorAll('.contact-item');

        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            items.forEach(item => {
                const data = item.getAttribute('data-search') || '';
                if (data.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // ---------- CLICK FEEDBACK ----------
        items.forEach(item => {
            item.addEventListener('click', function(e) {
                if (e.target.closest('.chat-btn-small')) return;
                this.style.background = 'rgba(245, 87, 108, 0.06)';
                setTimeout(() => { this.style.background = ''; }, 300);
            });
        });

        // ---------- NOTIFICATION BELL CLICK ----------
        document.getElementById('notificationBell')?.addEventListener('click', function() {
            const firstUnread = document.querySelector('.unread-badge');
            if (firstUnread) {
                const parent = firstUnread.closest('.contact-item');
                if (parent) {
                    parent.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    parent.style.background = 'rgba(245, 87, 108, 0.08)';
                    setTimeout(() => parent.style.background = '', 1500);
                }
            } else {
                alert('No unread messages! 🎉');
            }
        });

        console.log('🎬 Reels · Friends with notifications loaded!');
    })();
</script>

</body>
</html> 