<?php
require "../config/session.php";
require "../config/database.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

if($_SESSION['role'] !== "employer"){
    header("Location: ../login.php");
    exit();
}

$employer_id = $_SESSION['user_id'];

if(!isset($_GET['application_id'])){
    die("Application not found");
}

$application_id = (int)$_GET['application_id'];

// FIXED: Removed the incorrect applications.user_id JOIN
$check = $conn->prepare("
SELECT applications.*, jobs.title
FROM applications
INNER JOIN jobs ON applications.job_id = jobs.id
WHERE applications.id = ?
AND jobs.employer_id = ?
AND applications.status = 'accepted'
");

$check->execute([$application_id, $employer_id]);
$application = $check->fetch(PDO::FETCH_ASSOC);

if(!$application){
    die("You cannot access this chat.");
}

// Send message (text only)
if(isset($_POST['send'])){
    $message = trim($_POST['message'] ?? '');

    if($message !== ""){
        $send = $conn->prepare("
        INSERT INTO messages (application_id, sender_id, message)
        VALUES(?,?,?)
        ");
        $send->execute([$application_id, $employer_id, $message]);
    }

    // AJAX requests get a small JSON acknowledgement instead of a redirect,
    // so the page can send messages without a full reload. Regular
    // (non-JS) form submissions keep working exactly as before.
    if(!empty($_POST['ajax'])){
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }

    header("Location: chat.php?application_id=".$application_id);
    exit();
}

// Get messages
$msg = $conn->prepare("
SELECT messages.*, users.fullname 
FROM messages
INNER JOIN users ON messages.sender_id = users.id
WHERE application_id = ?
ORDER BY messages.created_at ASC
");
$msg->execute([$application_id]);
$messages = $msg->fetchAll(PDO::FETCH_ASSOC);

// Dynamically extract applicant's name from messages if available, else generic fallback
$chat_partner_name = "Applicant";
foreach($messages as $m) {
    if($m['sender_id'] != $employer_id) {
        $chat_partner_name = $m['fullname'];
        break;
    }
}

// Last message id already rendered server-side, so the poller only asks
// for messages newer than this.
$last_message_id = 0;
if(!empty($messages)){
    $last_message_id = (int)end($messages)['id'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?=htmlspecialchars($application['title']);?></title>
    <!-- FontAwesome for UI icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #2b0845 0%, #150721 100%);
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .chat-wrapper {
        width: 100%;
        max-width: 900px;
        height: 85vh;
        background: #f5f4fa;
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Top Navigation Header Bar */
    .chat-header {
        background: #62249c;
        padding: 15px 25px;
        display: flex;
        align-items: center;
        color: white;
        position: relative;
    }

    .back-btn {
        color: white;
        background: rgba(255, 255, 255, 0.15);
        border: none;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
        font-size: 16px;
        transition: 0.2s;
        margin-right: 15px;
    }

    .back-btn:hover {
        background: rgba(255, 255, 255, 0.25);
    }

    .avatar {
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.4);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
        margin-right: 15px;
    }

    .chat-info {
        flex-grow: 1;
    }

    .chat-info h3 {
        font-size: 16px;
        font-weight: 600;
    }

    .chat-info p {
        font-size: 12px;
        color: #d1b6ed;
        margin-top: 2px;
        display: flex;
        align-items: center;
    }

    .chat-info p::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        background: #4ade80;
        border-radius: 50%;
        margin-right: 6px;
    }

    .more-options {
        background: rgba(255, 255, 255, 0.15);
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Message Body Area */
    .chat-body {
        flex-grow: 1;
        padding: 25px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Outgoing message (You / Employer) */
    .msg-row {
        display: flex;
        align-items: flex-end;
        max-width: 80%;
    }

    .msg-row.mine {
        align-self: flex-end;
        justify-content: flex-end;
    }

    .msg-row.received {
        align-self: flex-start;
        gap: 10px;
    }

    .received-avatar {
        width: 28px;
        height: 28px;
        background: #e1daf2;
        color: #62249c;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .msg-box {
        padding: 12px 18px;
        border-radius: 16px;
        position: relative;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        max-width: 100%;
    }

    .msg-row.mine .msg-box {
        background: linear-gradient(135deg, #7833bd 0%, #561a8f 100%);
        color: white;
        border-bottom-right-radius: 4px;
    }

    .msg-row.received .msg-box {
        background: white;
        color: #211c26;
        border-bottom-left-radius: 4px;
    }

    .sender-name {
        font-size: 11px;
        font-weight: 700;
        color: #62249c;
        margin-bottom: 4px;
    }

    .msg-text {
        font-size: 14.5px;
        line-height: 1.4;
        word-break: break-word;
    }

    .msg-time {
        font-size: 10px;
        display: block;
        text-align: right;
        margin-top: 6px;
        opacity: 0.7;
    }

    .msg-row.received .msg-time {
        color: #7b7482;
    }

    /* Bottom Action Input Footer Area */
    .chat-footer {
        background: white;
        padding: 20px 25px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .footer-row {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .input-container {
        width: 100%;
        display: flex;
        align-items: center;
        background: #f0edf7;
        border-radius: 30px;
        padding: 6px 10px 6px 20px;
    }

    .input-container input {
        flex-grow: 1;
        border: none;
        background: transparent;
        padding: 10px 0;
        font-size: 14px;
        color: #333;
        outline: none;
    }

    .input-container input::placeholder {
        color: #9c95a6;
    }

    .send-btn {
        background: #62249c;
        color: white;
        border: none;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.2s;
        flex-shrink: 0;
    }

    .send-btn:hover {
        background: #4f1b80;
        transform: scale(1.05);
    }

    .send-btn:disabled {
        opacity: 0.6;
        cursor: default;
        transform: none;
    }

    .footer-hint {
        font-size: 11px;
        color: #928b9c;
    }
    </style>
</head>

<body>

    <div class="chat-wrapper">

        <!-- Header bar matching layout image details -->
        <div class="chat-header">
            <!-- Back link structure explicitly forced to employer/message.php -->
            <a href="message.php" class="back-btn" title="Back to messages">
                <i class="fa-solid fa-arrow-left"></i>
            </a>

            <div class="avatar">
                <?=strtoupper(substr(htmlspecialchars($chat_partner_name), 0, 1));?>
            </div>

            <div class="chat-info">
                <h3><?=htmlspecialchars($chat_partner_name);?></h3>
                <p><?=htmlspecialchars($application['title']);?></p>
            </div>

            <button class="more-options">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
        </div>

        <!-- Interactive UI Message Stream Block -->
        <div class="chat-body" id="chatBody" data-last-id="<?=$last_message_id;?>">
            <?php foreach($messages as $m): ?>
            <?php if($m['sender_id'] == $employer_id): ?>
            <!-- Outgoing message row (You) -->
            <div class="msg-row mine" data-message-id="<?=(int)$m['id'];?>">
                <div class="msg-box">
                    <?php if(!empty($m['message'])): ?>
                    <div class="msg-text"><?=htmlspecialchars($m['message']);?></div>
                    <?php endif; ?>
                    <span class="msg-time"><?=date('H:i', strtotime($m['created_at']));?></span>
                </div>
            </div>
            <?php else: ?>
            <!-- Incoming message row (Applicant) -->
            <div class="msg-row received" data-message-id="<?=(int)$m['id'];?>">
                <div class="received-avatar">
                    <?=strtoupper(substr(htmlspecialchars($m['fullname']), 0, 1));?>
                </div>
                <div class="msg-box">
                    <div class="sender-name"><?=htmlspecialchars($m['fullname']);?></div>
                    <?php if(!empty($m['message'])): ?>
                    <div class="msg-text"><?=htmlspecialchars($m['message']);?></div>
                    <?php endif; ?>
                    <span class="msg-time"><?=date('H:i', strtotime($m['created_at']));?></span>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Action Bar Input Footer Section -->
        <div class="chat-footer">
            <form method="POST" id="chatForm" style="width:100%;">
                <div class="footer-row">
                    <div class="input-container">
                        <input type="text" name="message" id="messageInput" placeholder="Write your message..."
                            autocomplete="off" required>
                    </div>
                    <button type="submit" name="send" class="send-btn" id="sendBtn">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </form>
            <div class="footer-hint">Enter to send · Updates automatically</div>
        </div>

    </div>

    <script>
    const EMPLOYER_ID = <?=json_encode($employer_id);?>;
    const APPLICATION_ID = <?=json_encode($application_id);?>;
    const POLL_INTERVAL_MS = 3000;

    const chatBody = document.getElementById('chatBody');
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');

    // Automatically force scroll frame to baseline view on load
    chatBody.scrollTop = chatBody.scrollHeight;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function isScrolledNearBottom() {
        return chatBody.scrollHeight - chatBody.scrollTop - chatBody.clientHeight < 120;
    }

    function renderMessage(m) {
        const wasNearBottom = isScrolledNearBottom();
        const row = document.createElement('div');
        row.setAttribute('data-message-id', m.id);

        if (String(m.sender_id) === String(EMPLOYER_ID)) {
            row.className = 'msg-row mine';
            row.innerHTML = `
                <div class="msg-box">
                    <div class="msg-text">${escapeHtml(m.message)}</div>
                    <span class="msg-time">${escapeHtml(m.time)}</span>
                </div>`;
        } else {
            row.className = 'msg-row received';
            const initial = escapeHtml((m.fullname || '?').charAt(0).toUpperCase());
            row.innerHTML = `
                <div class="received-avatar">${initial}</div>
                <div class="msg-box">
                    <div class="sender-name">${escapeHtml(m.fullname)}</div>
                    <div class="msg-text">${escapeHtml(m.message)}</div>
                    <span class="msg-time">${escapeHtml(m.time)}</span>
                </div>`;
        }

        chatBody.appendChild(row);
        if (wasNearBottom) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    }

    async function pollMessages() {
        const lastId = chatBody.getAttribute('data-last-id') || '0';

        try {
            const res = await fetch(
                `fetch_messages.php?application_id=${encodeURIComponent(APPLICATION_ID)}&after_id=${encodeURIComponent(lastId)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            if (!res.ok) return;

            const data = await res.json();
            if (!data.success || !Array.isArray(data.messages) || data.messages.length === 0) {
                return;
            }

            data.messages.forEach(m => {
                // Guard against ever rendering the same message twice.
                if (chatBody.querySelector(`[data-message-id="${m.id}"]`)) return;
                renderMessage(m);
                chatBody.setAttribute('data-last-id', m.id);
            });
        } catch (err) {
            // Silently ignore transient network errors; the next poll will retry.
            console.error('[chat-poll]', err);
        }
    }

    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const text = messageInput.value.trim();
        if (text === '') return;

        sendBtn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('message', text);
            formData.append('send', '1');
            formData.append('ajax', '1');

            const res = await fetch('chat.php?application_id=' + encodeURIComponent(APPLICATION_ID), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (res.ok) {
                messageInput.value = '';
                // Fetch immediately so the sent message (with its real id
                // and timestamp from the server) shows up right away.
                await pollMessages();
            } else {
                alert('Could not send the message. Please try again.');
            }
        } catch (err) {
            console.error('[chat-send]', err);
            alert('Could not send the message. Check your connection and try again.');
        } finally {
            sendBtn.disabled = false;
            messageInput.focus();
        }
    });

    setInterval(pollMessages, POLL_INTERVAL_MS);
    </script>

</body>

</html>