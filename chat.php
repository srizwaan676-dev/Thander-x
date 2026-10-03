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

// Handle AJAX request for new messages
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
    $receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;

    if ($receiver_id === 0) {
        echo json_encode(['error' => 'Invalid receiver']);
        exit();
    }

    // Fetch messages newer than last_id
    $sql = "SELECT * FROM messages 
            WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
            AND id > ?
            ORDER BY created_at ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiiii", $current_user_id, $receiver_id, $receiver_id, $current_user_id, $last_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = [
            'id' => (int)$row['id'],
            'sender_id' => (int)$row['sender_id'],
            'message' => htmlspecialchars($row['messages']),
            'created_at' => date("h:i A", strtotime($row['created_at'])),
            'is_sent' => ($row['sender_id'] == $current_user_id)
        ];
    }
    mysqli_stmt_close($stmt);

    // Mark any messages from receiver as read
    $update_sql = "UPDATE messages SET is_read = 1 
                   WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, "ii", $receiver_id, $current_user_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    }

    echo json_encode(['messages' => $messages]);
    exit();
}

// Normal page load
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    header("Location: users.php");
    exit();
}

$receiver_id = (int)$_GET['user_id'];

if ($receiver_id === $current_user_id) {
    header("Location: users.php");
    exit();
}

// Get receiver details
$user_sql = "SELECT id, name, mobile FROM contact WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_sql);
mysqli_stmt_bind_param($user_stmt, "i", $receiver_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);

if (!$user_result || mysqli_num_rows($user_result) == 0) {
    die("User not found");
}
$receiver = mysqli_fetch_assoc($user_result);
mysqli_stmt_close($user_stmt);

// Get initial messages
$message_sql = "SELECT * FROM messages
                WHERE (sender_id = ? AND receiver_id = ?)
                OR (sender_id = ? AND receiver_id = ?)
                ORDER BY created_at ASC";
$message_stmt = mysqli_prepare($conn, $message_sql);
mysqli_stmt_bind_param($message_stmt, "iiii", $current_user_id, $receiver_id, $receiver_id, $current_user_id);
mysqli_stmt_execute($message_stmt);
$message_result = mysqli_stmt_get_result($message_stmt);

// Mark unread messages as read
$update_sql = "UPDATE messages SET is_read = 1 
               WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
$update_stmt = mysqli_prepare($conn, $update_sql);
if ($update_stmt) {
    mysqli_stmt_bind_param($update_stmt, "ii", $receiver_id, $current_user_id);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
}

// Store messages in array for JS
$initial_messages = [];
while ($row = mysqli_fetch_assoc($message_result)) {
    $initial_messages[] = [
        'id' => (int)$row['id'],
        'sender_id' => (int)$row['sender_id'],
        'message' => htmlspecialchars($row['messages']),
        'created_at' => date("h:i A", strtotime($row['created_at'])),
        'is_sent' => ($row['sender_id'] == $current_user_id)
    ];
}
mysqli_stmt_close($message_stmt);
mysqli_close($conn);

// Helper for avatar color
function getAvatarColor($id) {
    $colors = ['#f43f5e', '#8b5cf6', '#475569', '#f59e0b', '#0ea5e9', '#10b981', '#2563eb', '#dc2626', '#d97706', '#0891b2'];
    return $colors[$id % count($colors)];
}
$avatarColor = getAvatarColor($receiver_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chat with <?php echo htmlspecialchars($receiver['name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&display=swap" rel="stylesheet" />
    <style>
        /* ============================================================
           PROFESSIONAL CHAT — REELS · FRIENDS STYLE
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
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .chat-container {
            width: 100%;
            max-width: 780px;
            height: 92vh;
            max-height: 820px;
            background: linear-gradient(145deg, #141b2b, #1e2740);
            border-radius: 32px;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.04);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: cardPop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            transition: transform 0.4s ease;
        }

        .chat-container:hover {
            transform: translateY(-4px);
        }

        @keyframes cardPop {
            from { opacity: 0; transform: scale(0.92) translateY(30px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* ---------- HEADER ---------- */
        .chat-header {
            padding: 16px 22px;
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            display: flex;
            align-items: center;
            gap: 14px;
            flex-shrink: 0;
        }

        .back-btn {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 22px;
            padding: 4px 8px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            color: #f5576c;
            background: rgba(245, 87, 108, 0.08);
        }

        .avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: <?php echo $avatarColor; ?>;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
            flex-shrink: 0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-info h3 {
            font-size: 17px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-info .status {
            font-size: 12px;
            font-weight: 500;
            color: #22c55e;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .user-info .status .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            display: inline-block;
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .header-actions {
            display: flex;
            gap: 6px;
        }

        .header-actions button {
            background: none;
            border: none;
            color: #64748b;
            font-size: 18px;
            padding: 6px 10px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .header-actions button:hover {
            background: rgba(255, 255, 255, 0.04);
            color: #f5576c;
        }

        /* ---------- MESSAGES AREA ---------- */
        .messages {
            flex: 1;
            padding: 20px 24px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 6px;
            background: rgba(0, 0, 0, 0.15);
        }

        .messages::-webkit-scrollbar {
            width: 6px;
        }
        .messages::-webkit-scrollbar-track {
            background: transparent;
        }
        .messages::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 999px;
        }
        .messages::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        .message {
            max-width: 78%;
            padding: 10px 16px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.5;
            word-wrap: break-word;
            animation: fadeSlide 0.25s ease-out;
            position: relative;
        }

        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(8px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .message.sent {
            align-self: flex-end;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .message.received {
            align-self: flex-start;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(8px);
            color: #e2e8f0;
            border-bottom-left-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.04);
        }

        .message .time {
            display: block;
            font-size: 10px;
            margin-top: 6px;
            opacity: 0.7;
            text-align: right;
        }

        .message.sent .time {
            color: rgba(255, 255, 255, 0.8);
        }

        .message.received .time {
            color: #94a3b8;
        }

        .message .sender-name {
            font-size: 11px;
            font-weight: 600;
            color: #f093fb;
            margin-bottom: 2px;
        }

        .date-divider {
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            padding: 12px 0 8px;
            position: relative;
        }

        .date-divider span {
            background: rgba(30, 39, 64, 0.6);
            padding: 0 16px;
            border-radius: 20px;
            backdrop-filter: blur(4px);
            color: #94a3b8;
        }

        .typing-indicator {
            display: none;
            align-self: flex-start;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(8px);
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.04);
            font-size: 13px;
            color: #94a3b8;
            gap: 6px;
            align-items: center;
        }

        .typing-indicator.active {
            display: flex;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dots span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #94a3b8;
            animation: typingBounce 1.4s infinite both;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }
        .typing-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typingBounce {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
            40% { transform: scale(1); opacity: 1; }
        }

        /* ---------- EMPTY STATE ---------- */
        .empty-chat {
            text-align: center;
            color: #64748b;
            padding: 50px 20px;
            align-self: center;
        }

        .empty-chat i {
            font-size: 48px;
            display: block;
            margin-bottom: 16px;
            color: #334155;
        }

        .empty-chat h4 {
            font-size: 18px;
            color: #cbd5e1;
            font-weight: 600;
            margin-bottom: 6px;
        }

        /* ---------- INPUT FORM ---------- */
        .chat-form {
            padding: 12px 20px 16px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(8px);
            border-top: 1px solid rgba(255, 255, 255, 0.04);
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }

        .chat-form .input-wrapper {
            flex: 1;
            position: relative;
        }

        .chat-form input[type="text"] {
            width: 100%;
            padding: 12px 18px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 24px;
            outline: none;
            font-size: 14px;
            font-family: inherit;
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            transition: all 0.3s ease;
        }

        .chat-form input[type="text"]::placeholder {
            color: #64748b;
        }

        .chat-form input[type="text"]:focus {
            border-color: #f5576c;
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 0 0 4px rgba(245, 87, 108, 0.08);
        }

        .chat-form button {
            padding: 0 28px;
            border: none;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: #fff;
            border-radius: 24px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            box-shadow: 0 4px 16px rgba(245, 87, 108, 0.25);
        }

        .chat-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(245, 87, 108, 0.35);
        }

        .chat-form button:active {
            transform: scale(0.96);
        }

        .chat-form button i {
            font-size: 15px;
        }

        /* ---------- RESPONSIVE ---------- */
        @media (max-width: 640px) {
            body { padding: 0; }
            .chat-container {
                max-height: 100vh;
                height: 100vh;
                border-radius: 0;
                max-width: 100%;
            }
            .chat-header { padding: 12px 16px; }
            .messages { padding: 12px 16px; }
            .chat-form { padding: 8px 12px 12px; }
            .message { max-width: 85%; font-size: 13px; padding: 8px 14px; }
            .chat-form button { padding: 0 18px; font-size: 13px; }
            .chat-form input[type="text"] { padding: 10px 14px; font-size: 13px; }
        }

        @media (max-width: 480px) {
            .user-info h3 { font-size: 15px; }
            .avatar { width: 38px; height: 38px; font-size: 17px; }
            .back-btn { font-size: 20px; }
        }
    </style>
</head>
<body>

<div class="chat-container">

    <!-- HEADER -->
    <div class="chat-header">
        <a href="massages.php" class="back-btn" aria-label="Back"><i class="fas fa-arrow-left"></i></a>
        <div class="avatar">
            <?php echo strtoupper(substr($receiver['name'], 0, 1)); ?>
        </div>
        <div class="user-info">
            <h3><?php echo htmlspecialchars($receiver['name']); ?></h3>
            <div class="status">
                <span class="dot"></span> Online
            </div>
        </div>
        <div class="header-actions">
            <button title="Call"><i class="fas fa-phone"></i></button>
            <button title="More"><i class="fas fa-ellipsis-v"></i></button>
        </div>
    </div>

    <!-- MESSAGES -->
    <div class="messages" id="messageContainer">
        <?php if (empty($initial_messages)): ?>
            <div class="empty-chat">
                <i class="fas fa-comment-dots"></i>
                <h4>No messages yet</h4>
                <p>Say hello to start the conversation</p>
            </div>
        <?php else: ?>
            <?php
            $last_date = '';
            foreach ($initial_messages as $msg):
                $msg_date = date("Y-m-d", strtotime($msg['created_at']));
                if ($last_date != $msg_date):
                    $last_date = $msg_date;
                    $display_date = date("l, F j, Y", strtotime($msg['created_at']));
            ?>
                <div class="date-divider"><span><?php echo $display_date; ?></span></div>
            <?php endif; ?>
            <div class="message <?php echo $msg['is_sent'] ? 'sent' : 'received'; ?>" data-id="<?php echo $msg['id']; ?>">
                <?php if (!$msg['is_sent']): ?>
                    <div class="sender-name"><?php echo htmlspecialchars($receiver['name']); ?></div>
                <?php endif; ?>
                <?php echo nl2br($msg['message']); ?>
                <span class="time"><?php echo $msg['created_at']; ?></span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <!-- typing indicator -->
        <div class="typing-indicator" id="typingIndicator">
            <span><?php echo htmlspecialchars($receiver['name']); ?> is typing</span>
            <div class="typing-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
    </div>

    <!-- INPUT FORM -->
    <form class="chat-form" id="chatForm" autocomplete="off">
        <input type="hidden" name="sender_id" value="<?php echo $current_user_id; ?>">
        <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
        <div class="input-wrapper">
            <input type="text" name="messages" id="messageInput" placeholder="Type a message..." required maxlength="5000">
        </div>
        <button type="submit" id="sendBtn">
            <i class="fas fa-paper-plane"></i> Send
        </button>
    </form>

</div>

<!-- ===== JAVASCRIPT ===== -->
<script>
    (function() {
        const container = document.getElementById('messageContainer');
        const form = document.getElementById('chatForm');
        const input = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const typingIndicator = document.getElementById('typingIndicator');

        const receiverId = <?php echo $receiver_id; ?>;
        const currentUserId = <?php echo $current_user_id; ?>;
        let lastMessageId = 0;
        let isTyping = false;
        let typingTimeout = null;

        // Get the highest message ID from current DOM
        function getLastMessageId() {
            const messages = container.querySelectorAll('.message');
            if (messages.length === 0) return 0;
            const last = messages[messages.length - 1];
            return parseInt(last.dataset.id) || 0;
        }

        // Scroll to bottom
        function scrollToBottom() {
            container.scrollTop = container.scrollHeight;
        }

        // Add a new message to the DOM
        function appendMessage(msg) {
            // Check if message already exists (prevent duplicates)
            const existing = container.querySelector(`.message[data-id="${msg.id}"]`);
            if (existing) return;

            // If it's a new day, add date divider
            // We'll handle date grouping later, but for simplicity we'll just append

            const div = document.createElement('div');
            div.className = `message ${msg.is_sent ? 'sent' : 'received'}`;
            div.dataset.id = msg.id;

            if (!msg.is_sent) {
                const nameSpan = document.createElement('div');
                nameSpan.className = 'sender-name';
                nameSpan.textContent = '<?php echo htmlspecialchars($receiver['name']); ?>';
                div.appendChild(nameSpan);
            }

            const textSpan = document.createElement('span');
            textSpan.innerHTML = msg.message.replace(/\n/g, '<br>');
            div.appendChild(textSpan);

            const timeSpan = document.createElement('span');
            timeSpan.className = 'time';
            timeSpan.textContent = msg.created_at;
            div.appendChild(timeSpan);

            // Remove empty state if present
            const empty = container.querySelector('.empty-chat');
            if (empty) empty.remove();

            container.appendChild(div);
            scrollToBottom();
        }

        // Fetch new messages
        function fetchNewMessages() {
            const lastId = getLastMessageId();
            fetch(`chat.php?ajax=1&receiver_id=${receiverId}&last_id=${lastId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            appendMessage(msg);
                        });
                        // Update last message id
                        lastMessageId = data.messages[data.messages.length - 1].id;
                    }
                })
                .catch(err => console.warn('Polling error:', err));
        }

        // Send message via AJAX
        function sendMessage(e) {
            e.preventDefault();
            const message = input.value.trim();
            if (message === '') return;

            const formData = new FormData(form);
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            fetch('send_messages.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // The new message will be picked up by polling, but we can also add optimistically
                    // For now, just clear input and poll immediately
                    input.value = '';
                    // Force poll to get the new message
                    setTimeout(fetchNewMessages, 300);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send';
                input.focus();
            });
        }

        // Typing indicator (simulate)
        input.addEventListener('input', function() {
            if (!isTyping) {
                isTyping = true;
                typingIndicator.classList.add('active');
            }
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                isTyping = false;
                typingIndicator.classList.remove('active');
            }, 1500);
        });

        // Form submit
        form.addEventListener('submit', sendMessage);

        // Initial scroll
        scrollToBottom();

        // Start polling every 3 seconds
        setInterval(fetchNewMessages, 300);

        // Also poll immediately after page load
        setTimeout(fetchNewMessages, 10);

        // Focus input on load
        input.focus();
    })();
</script>

</body>
</html>