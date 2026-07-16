<?php

require "../config/session.php";

require "../config/database.php";


if(!isset($_SESSION['user_id'])){

    header("Location: ../login.php");
    exit();

}


if($_SESSION['role'] !== "worker"){

    header("Location: ../login.php");
    exit();

}


$worker_id = $_SESSION['user_id'];



// Check application id

if(!isset($_GET['application_id'])){

    die("Application not found");

}


$application_id = (int)$_GET['application_id'];





// Verify worker owns accepted application


$check = $conn->prepare("

SELECT

applications.*,

jobs.title,

users.fullname AS employer_name


FROM applications


INNER JOIN jobs

ON applications.job_id = jobs.id


INNER JOIN users

ON jobs.employer_id = users.id


WHERE applications.id = ?

AND applications.worker_id = ?

AND applications.status = 'accepted'


");



$check->execute([

    $application_id,
    $worker_id

]);



$application = $check->fetch(PDO::FETCH_ASSOC);



if(!$application){

    die("Chat is only available after job approval.");

}





// Send message


if(isset($_POST['send'])){


    $message = trim($_POST['message'] ?? '');


    $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';



    if($message != ""){


        $insert = $conn->prepare("

        INSERT INTO messages

        (

        application_id,

        sender_id,

        message

        )

        VALUES(?,?,?)

        ");



        $insert->execute([

            $application_id,

            $worker_id,

            $message

        ]);


    }



    if($is_ajax){

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'empty' => ($message === "")]);
        exit();

    }



    header("Location: chat.php?application_id=".$application_id);

    exit();


}



// AJAX: live polling endpoint — returns only messages newer than after_id,
// so the browser isn't re-downloading the whole conversation every 3s.

if(isset($_GET['poll'])){

    header('Content-Type: application/json');

    $after_id = isset($_GET['after_id']) ? (int)$_GET['after_id'] : 0;

    $poll_stmt = $conn->prepare("

    SELECT

    messages.*,

    users.fullname


    FROM messages


    INNER JOIN users

    ON messages.sender_id = users.id


    WHERE messages.application_id = ?

    AND messages.id > ?


    ORDER BY messages.created_at ASC

    ");

    $poll_stmt->execute([$application_id, $after_id]);

    $new_messages = $poll_stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = [];

    foreach($new_messages as $m){

        $out[] = [
            'id'        => (int)$m['id'],
            'sender_id' => (int)$m['sender_id'],
            'fullname'  => $m['fullname'],
            'message'   => $m['message'],
            'time'      => date("H:i", strtotime($m['created_at'])),
            'date_key'  => date("Y-m-d", strtotime($m['created_at'])),
            'mine'      => ((int)$m['sender_id'] === (int)$worker_id)
        ];

    }

    echo json_encode(['messages' => $out]);

    exit();

}



// Get messages


$msg = $conn->prepare("

SELECT

messages.*,

users.fullname


FROM messages


INNER JOIN users

ON messages.sender_id = users.id


WHERE messages.application_id = ?


ORDER BY messages.created_at ASC


");



$msg->execute([$application_id]);


$messages = $msg->fetchAll(PDO::FETCH_ASSOC);



// Last message id already rendered server-side; the poller only asks for
// anything newer than this from here on.

$last_message_id = 0;

if(!empty($messages)){

    $last_message_id = (int)end($messages)['id'];

}



// Small helper for avatar initials — additive only, doesn't touch existing logic

function gb_initials($name){

    $parts = preg_split('/\s+/', trim($name));

    $out = '';

    foreach(array_slice($parts, 0, 2) as $p){

        if($p !== ''){ $out .= mb_strtoupper(mb_substr($p, 0, 1)); }

    }

    return $out !== '' ? $out : '?';

}

?>



<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Private Chat | GigBoard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
    :root {
        --purple-900: #221041;
        --purple-800: #2E1857;
        --purple-700: #3E1F73;
        --purple-600: #5B2C9E;
        --purple-500: #7C3AED;
        --purple-400: #9563F2;
        --purple-100: #EDE6FB;
        --lavender-bg: #F4F1FB;
        --ink: #221A33;
        --muted: #736C87;
        --line: #E3DCF5;
        --success: #3FAE72;
        --white: #ffffff;
        --font-display: 'Space Grotesk', sans-serif;
        --font-body: 'Inter', sans-serif;
    }

    * {
        box-sizing: border-box;
    }

    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
        font-family: var(--font-body);
        color: var(--ink);
        background: var(--purple-900);
    }

    .page-shell {
        height: 100vh;
        display: flex;
        align-items: stretch;
        justify-content: center;
        background:
            radial-gradient(ellipse 700px 500px at 15% 0%, rgba(124, 58, 237, 0.35), transparent 60%),
            radial-gradient(ellipse 700px 500px at 100% 100%, rgba(91, 44, 158, 0.35), transparent 60%),
            var(--purple-900);
    }

    .chat-app {
        width: 100%;
        max-width: 760px;
        height: 100%;
        display: flex;
        flex-direction: column;
        background: var(--white);
        box-shadow: 0 30px 80px -30px rgba(0, 0, 0, 0.5);
    }

    @media (min-width:820px) {
        .page-shell {
            padding: 28px 0;
        }

        .chat-app {
            height: calc(100vh - 56px);
            border-radius: 20px;
            overflow: hidden;
        }
    }

    /* ---------- Header ---------- */
    .chat-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 20px;
        background: linear-gradient(135deg, var(--purple-700), var(--purple-600));
        color: var(--white);
        flex-shrink: 0;
    }

    .back-btn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.12);
        color: var(--white);
        text-decoration: none;
        font-size: 18px;
        flex-shrink: 0;
        transition: background .2s;
    }

    .back-btn:hover {
        background: rgba(255, 255, 255, 0.22);
    }

    .header-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.16);
        color: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-display);
        font-weight: 700;
        font-size: 14px;
        flex-shrink: 0;
        border: 1.5px solid rgba(255, 255, 255, 0.3);
    }

    .header-info {
        min-width: 0;
    }

    .header-info h2 {
        font-family: var(--font-display);
        font-size: 16.5px;
        font-weight: 700;
        margin: 0;
        color: var(--white);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .header-info p {
        margin: 2px 0 0;
        font-size: 12.5px;
        color: rgba(255, 255, 255, 0.78);
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .live-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 6px var(--success);
        animation: pulse 1.8s ease-in-out infinite;
        flex-shrink: 0;
    }

    .menu-btn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.12);
        border: none;
        color: var(--white);
        cursor: pointer;
        flex-shrink: 0;
        margin-left: auto;
        position: relative;
        transition: background .2s;
    }

    .menu-btn:hover {
        background: rgba(255, 255, 255, 0.22);
    }

    .menu-btn svg {
        width: 18px;
        height: 18px;
    }

    .menu-dropdown {
        position: absolute;
        top: 46px;
        right: 0;
        background: var(--white);
        border: 1px solid var(--line);
        border-radius: 12px;
        box-shadow: 0 16px 36px -14px rgba(34, 16, 65, 0.35);
        min-width: 190px;
        padding: 6px;
        display: none;
        z-index: 20;
    }

    .menu-dropdown.open {
        display: block;
    }

    .menu-dropdown a {
        display: block;
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 13.5px;
        color: var(--ink);
        text-decoration: none;
        white-space: nowrap;
    }

    .menu-dropdown a:hover {
        background: var(--lavender-bg);
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: .3;
        }
    }

    /* ---------- Messages ---------- */
    .messages {
        flex: 1;
        overflow-y: auto;
        padding: 22px 18px;
        background: var(--lavender-bg);
        display: flex;
        flex-direction: column;
    }

    .messages::-webkit-scrollbar {
        width: 8px;
    }

    .messages::-webkit-scrollbar-thumb {
        background: #D9CDF2;
        border-radius: 8px;
    }

    .messages::-webkit-scrollbar-track {
        background: transparent;
    }

    .date-sep {
        display: flex;
        justify-content: center;
        margin: 14px 0;
    }

    .date-sep span {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--muted);
        background: var(--white);
        border: 1px solid var(--line);
        padding: 5px 14px;
        border-radius: 999px;
    }

    .msg-row {
        display: flex;
        align-items: flex-end;
        gap: 9px;
        margin-bottom: 12px;
        max-width: 100%;
        animation: msgIn .25s ease both;
    }

    @keyframes msgIn {
        from {
            opacity: 0;
            transform: translateY(6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .msg-row.mine {
        justify-content: flex-end;
    }

    .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--purple-100);
        color: var(--purple-600);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-display);
        font-weight: 700;
        font-size: 11.5px;
        flex-shrink: 0;
    }

    .bubble {
        max-width: 72%;
        padding: 10px 14px;
        border-radius: 16px;
    }

    .msg-row.theirs .bubble {
        background: var(--white);
        border: 1px solid var(--line);
        border-bottom-left-radius: 4px;
        color: var(--ink);
    }

    .msg-row.mine .bubble {
        background: linear-gradient(135deg, var(--purple-500), var(--purple-700));
        color: var(--white);
        border-bottom-right-radius: 4px;
        box-shadow: 0 8px 20px -10px rgba(124, 58, 237, 0.55);
    }

    .bubble-name {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: var(--purple-600);
        margin-bottom: 3px;
    }

    .bubble-text {
        margin: 0;
        font-size: 14.5px;
        line-height: 1.48;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .bubble-time {
        display: block;
        font-size: 10.5px;
        margin-top: 4px;
        text-align: right;
        opacity: .7;
    }

    .msg-row.pending .bubble {
        opacity: .6;
    }

    .empty-state {
        margin: auto;
        text-align: center;
        color: var(--muted);
        font-size: 13.5px;
        max-width: 280px;
    }

    .empty-state svg {
        width: 40px;
        height: 40px;
        color: var(--purple-400);
        margin-bottom: 10px;
    }

    /* ---------- Composer ---------- */
    .composer {
        flex-shrink: 0;
        display: flex;
        align-items: flex-end;
        gap: 10px;
        padding: 14px 16px;
        background: var(--white);
        border-top: 1px solid var(--line);
    }

    .composer textarea {
        flex: 1;
        font-family: var(--font-body);
        font-size: 14.5px;
        padding: 13px 18px;
        border-radius: 24px;
        border: 1.5px solid var(--line);
        background: var(--lavender-bg);
        color: var(--ink);
        resize: none;
        outline: none;
        max-height: 140px;
        min-height: 48px;
        line-height: 1.4;
        transition: border-color .2s, box-shadow .2s;
    }

    .composer textarea:focus {
        border-color: var(--purple-500);
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.14);
        background: var(--white);
    }

    .send-btn {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, var(--purple-500), var(--purple-700));
        color: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        box-shadow: 0 8px 20px -8px rgba(124, 58, 237, 0.6);
        transition: transform .15s ease, opacity .2s;
    }

    .send-btn svg {
        width: 19px;
        height: 19px;
    }

    .send-btn:hover {
        transform: translateY(-2px);
    }

    .send-btn:active {
        transform: translateY(0) scale(.95);
    }

    .send-btn:disabled {
        opacity: .5;
        cursor: default;
        transform: none;
    }

    .composer-hint {
        font-size: 11px;
        color: var(--muted);
        text-align: center;
        padding: 0 16px 12px;
        background: var(--white);
        flex-shrink: 0;
    }

    @media (max-width:480px) {
        .bubble {
            max-width: 82%;
        }

        .header-info h2 {
            font-size: 15px;
        }
    }
    </style>

</head>

<body>

    <div class="page-shell">

        <div class="chat-app">

            <div class="chat-header">

                <a class="back-btn" href="messages.php" aria-label="Back to my applications">&larr;</a>

                <div class="header-avatar"><?=htmlspecialchars(gb_initials($application['employer_name']));?></div>

                <div class="header-info">
                    <h2><?=htmlspecialchars($application['employer_name']);?></h2>
                    <p><span class="live-dot"></span> <?=htmlspecialchars($application['title']);?></p>
                </div>

                <button type="button" class="menu-btn" id="menuBtn" aria-label="More options">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                        <circle cx="12" cy="5" r="1.2" />
                        <circle cx="12" cy="12" r="1.2" />
                        <circle cx="12" cy="19" r="1.2" />
                    </svg>
                    <div class="menu-dropdown" id="menuDropdown">
                        <a href="my_applications.php">← Back to My Applications</a>
                        <a href="messages.php">All conversations</a>
                    </div>
                </button>

            </div>

            <div class="messages" id="messagesList" data-last-id="<?=$last_message_id;?>">

                <?php if(empty($messages)): ?>

                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path
                            d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
                    </svg>
                    <p>No messages yet. Say hello and sort out the details for
                        <?=htmlspecialchars($application['title']);?>.</p>
                </div>

                <?php else: ?>

                <?php $lastDateKey = null; ?>

                <?php foreach($messages as $message): ?>

                <?php
                $dateKey = date("Y-m-d", strtotime($message['created_at']));
                if($dateKey !== $lastDateKey):
                    $lastDateKey = $dateKey;
                    $today = date("Y-m-d");
                    $yesterday = date("Y-m-d", strtotime("-1 day"));
                    if($dateKey === $today){ $label = "Today"; }
                    elseif($dateKey === $yesterday){ $label = "Yesterday"; }
                    else { $label = date("d M Y", strtotime($message['created_at'])); }
            ?>
                <div class="date-sep"><span><?=htmlspecialchars($label);?></span></div>
                <?php endif; ?>

                <?php $isMine = ((int)$message['sender_id'] === (int)$worker_id); ?>

                <div class="msg-row <?php echo $isMine ? 'mine' : 'theirs'; ?>" data-id="<?=(int)$message['id'];?>"
                    data-date="<?=htmlspecialchars($dateKey);?>">

                    <?php if(!$isMine): ?>
                    <div class="avatar"><?=htmlspecialchars(gb_initials($message['fullname']));?></div>
                    <?php endif; ?>

                    <div class="bubble">
                        <?php if(!$isMine): ?>
                        <span class="bubble-name"><?=htmlspecialchars($message['fullname']);?></span>
                        <?php endif; ?>
                        <p class="bubble-text"><?=htmlspecialchars($message['message']);?></p>
                        <span class="bubble-time"><?=date("H:i", strtotime($message['created_at']));?></span>
                    </div>

                </div>

                <?php endforeach; ?>

                <?php endif; ?>

            </div>

            <form method="POST" id="chatForm" class="composer">

                <textarea name="message" id="messageInput" placeholder="Write your message..." required
                    rows="1"></textarea>

                <button type="submit" name="send" id="sendBtn" class="send-btn" aria-label="Send message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L11 13" />
                        <path d="M22 2l-7 20-4-9-9-4 20-7z" />
                    </svg>
                </button>

            </form>

            <div class="composer-hint">Enter to send · Shift + Enter for a new line · Updates automatically</div>

        </div>

    </div>

    <script>
    (function() {

        var messagesEl = document.getElementById('messagesList');
        var chatForm = document.getElementById('chatForm');
        var messageInput = document.getElementById('messageInput');
        var sendBtn = document.getElementById('sendBtn');
        var menuBtn = document.getElementById('menuBtn');
        var menuDropdown = document.getElementById('menuDropdown');

        menuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            menuDropdown.classList.toggle('open');
        });
        document.addEventListener('click', function() {
            menuDropdown.classList.remove('open');
        });

        var applicationId = <?php echo (int)$application_id; ?>;
        var sendUrl = 'chat.php?application_id=' + applicationId;

        var renderedIds = new Set();
        var lastDateKey = null;
        var lastId = parseInt(messagesEl.getAttribute('data-last-id') || '0', 10);
        var pendingSends = [];

        Array.prototype.forEach.call(messagesEl.querySelectorAll('.msg-row[data-id]'), function(row) {
            renderedIds.add(row.getAttribute('data-id'));
            lastDateKey = row.getAttribute('data-date');
        });

        function pad(n) {
            return n < 10 ? '0' + n : '' + n;
        }

        function todayKey() {
            var d = new Date();
            return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
        }

        function nowTime() {
            var d = new Date();
            return pad(d.getHours()) + ':' + pad(d.getMinutes());
        }

        function dateLabel(key) {
            var today = todayKey();
            var y = new Date();
            y.setDate(y.getDate() - 1);
            var yKey = y.getFullYear() + '-' + pad(y.getMonth() + 1) + '-' + pad(y.getDate());
            if (key === today) {
                return 'Today';
            }
            if (key === yKey) {
                return 'Yesterday';
            }
            var parts = key.split('-');
            var d2 = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
            return d2.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function initialsOf(name) {
            return (name || '').trim().split(/\s+/).slice(0, 2).map(function(p) {
                return p.charAt(0).toUpperCase();
            }).join('') || '?';
        }

        function isNearBottom() {
            return (messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight) < 100;
        }

        function scrollToBottom(smooth) {
            messagesEl.scrollTo({
                top: messagesEl.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
        }

        function removeEmptyState() {
            var empty = messagesEl.querySelector('.empty-state');
            if (empty) {
                empty.remove();
            }
        }

        function appendMessage(m, isTemp) {
            var wasNearBottom = isNearBottom();
            removeEmptyState();

            if (m.date_key !== lastDateKey) {
                lastDateKey = m.date_key;
                var sep = document.createElement('div');
                sep.className = 'date-sep';
                var sepSpan = document.createElement('span');
                sepSpan.textContent = dateLabel(m.date_key);
                sep.appendChild(sepSpan);
                messagesEl.appendChild(sep);
            }

            var row = document.createElement('div');
            row.className = 'msg-row ' + (m.mine ? 'mine' : 'theirs') + (isTemp ? ' pending' : '');
            row.setAttribute('data-id', m.id);
            row.setAttribute('data-date', m.date_key);

            if (!m.mine) {
                var avatar = document.createElement('div');
                avatar.className = 'avatar';
                avatar.textContent = initialsOf(m.fullname);
                row.appendChild(avatar);
            }

            var bubble = document.createElement('div');
            bubble.className = 'bubble';

            if (!m.mine) {
                var nameEl = document.createElement('span');
                nameEl.className = 'bubble-name';
                nameEl.textContent = m.fullname;
                bubble.appendChild(nameEl);
            }

            var textEl = document.createElement('p');
            textEl.className = 'bubble-text';
            textEl.textContent = m.message;
            bubble.appendChild(textEl);

            var timeEl = document.createElement('span');
            timeEl.className = 'bubble-time';
            timeEl.textContent = m.time;
            bubble.appendChild(timeEl);

            row.appendChild(bubble);
            messagesEl.appendChild(row);

            if (isTemp || m.mine || wasNearBottom) {
                scrollToBottom(true);
            }

            return row;
        }

        function pollNow() {
            var pollUrl = 'chat.php?application_id=' + applicationId + '&poll=1&after_id=' + lastId;

            fetch(pollUrl, {
                    cache: 'no-store',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(data) {
                    (data.messages || []).forEach(function(m) {
                        var idStr = String(m.id);
                        if (renderedIds.has(idStr)) {
                            return;
                        }

                        if (m.mine && pendingSends.length) {
                            var p = pendingSends[0];
                            if (p.text === m.message) {
                                if (p.el && p.el.parentNode) {
                                    p.el.parentNode.removeChild(p.el);
                                }
                                pendingSends.shift();
                            }
                        }

                        renderedIds.add(idStr);
                        appendMessage({
                            id: idStr,
                            mine: m.mine,
                            fullname: m.fullname,
                            message: m.message,
                            time: m.time,
                            date_key: m.date_key
                        }, false);

                        if (m.id > lastId) {
                            lastId = m.id;
                        }
                    });
                })
                .catch(function() {});
        }

        function autoGrow() {
            messageInput.style.height = 'auto';
            messageInput.style.height = Math.min(messageInput.scrollHeight, 140) + 'px';
        }

        messageInput.addEventListener('input', autoGrow);

        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (typeof chatForm.requestSubmit === 'function') {
                    chatForm.requestSubmit();
                } else {
                    chatForm.dispatchEvent(new Event('submit', {
                        cancelable: true
                    }));
                }
            }
        });

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var text = messageInput.value.trim();
            if (!text) {
                return;
            }

            sendBtn.disabled = true;

            var tempId = 'temp-' + Date.now();
            var tempRow = appendMessage({
                id: tempId,
                mine: true,
                fullname: '',
                message: text,
                time: nowTime(),
                date_key: todayKey()
            }, true);

            pendingSends.push({
                text: text,
                el: tempRow
            });

            messageInput.value = '';
            autoGrow();

            var fd = new FormData();
            fd.append('message', text);
            fd.append('send', '1');

            fetch(sendUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                })
                .then(function() {
                    sendBtn.disabled = false;
                    pollNow();
                })
                .catch(function() {
                    sendBtn.disabled = false;
                });

            messageInput.focus();
        });

        scrollToBottom(false);
        setInterval(pollNow, 3000);

    })();
    </script>

</body>

</html>