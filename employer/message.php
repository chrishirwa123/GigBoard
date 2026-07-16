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

// Get every accepted application (= an open chat thread) for this employer,
// along with the latest message in that thread (if any).
$list = $conn->prepare("
SELECT
    applications.id AS application_id,
    jobs.title AS job_title,
    users.id AS worker_id,
    users.fullname AS worker_name,
    (SELECT message FROM messages WHERE messages.application_id = applications.id ORDER BY messages.created_at DESC LIMIT 1) AS last_message,
    (SELECT created_at FROM messages WHERE messages.application_id = applications.id ORDER BY messages.created_at DESC LIMIT 1) AS last_message_at,
    (SELECT sender_id FROM messages WHERE messages.application_id = applications.id ORDER BY messages.created_at DESC LIMIT 1) AS last_sender_id
FROM applications
INNER JOIN jobs
    ON applications.job_id = jobs.id
INNER JOIN users
    ON applications.worker_id = users.id
WHERE jobs.employer_id = ?
  AND applications.status = 'accepted'
ORDER BY last_message_at IS NULL, last_message_at DESC
");

$list->execute([$employer_id]);
$conversations = $list->fetchAll(PDO::FETCH_ASSOC);

// ---------- Helpers ----------
function gb_initials($name){
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach(array_slice($parts, 0, 2) as $p){
        if($p !== ''){ $out .= mb_strtoupper(mb_substr($p, 0, 1)); }
    }
    return $out !== '' ? $out : '?';
}

function gb_avatar_color($seed){
    $palette = ['#7C3AED', '#5B2C9E', '#9563F2', '#3E1F73', '#A855F7', '#6D28D9'];
    $hash = crc32((string)$seed);
    return $palette[$hash % count($palette)];
}

function gb_relative_time($datetime){
    if(!$datetime){ return ''; }
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if($diff < 60){ return 'now'; }
    if($diff < 3600){ return floor($diff / 60) . 'm'; }
    if($diff < 86400){ return floor($diff / 3600) . 'h'; }
    $today = date('Y-m-d');
    $msgDay = date('Y-m-d', $ts);
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    if($msgDay === $yesterday){ return 'Yesterday'; }
    if($diff < 604800){ return date('D', $ts); }
    return date('d M', $ts);
}

function gb_truncate($text, $len = 46){
    $text = trim($text);
    if(mb_strlen($text) <= $len){ return $text; }
    return mb_substr($text, 0, $len) . '…';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | GigBoard</title>
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
        --unread: #EC4899;
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
        min-height: 100vh;
        display: flex;
        align-items: stretch;
        justify-content: center;
        background:
            radial-gradient(ellipse 700px 500px at 15% 0%, rgba(124, 58, 237, 0.35), transparent 60%),
            radial-gradient(ellipse 700px 500px at 100% 100%, rgba(91, 44, 158, 0.35), transparent 60%),
            var(--purple-900);
    }

    .app {
        width: 100%;
        max-width: 480px;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background: var(--lavender-bg);
        position: relative;
    }

    @media (min-width:560px) {
        .page-shell {
            padding: 28px 0;
        }

        .app {
            min-height: calc(100vh - 56px);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 80px -30px rgba(0, 0, 0, 0.5);
        }
    }

    /* ---------- Header ---------- */
    .header {
        flex-shrink: 0;
        padding: 20px 20px 26px;
        background: linear-gradient(135deg, var(--purple-700), var(--purple-500));
        color: var(--white);
        position: relative;
    }

    .header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .icon-btn {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        background: rgba(255, 255, 255, 0.14);
        border: none;
        color: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: background .2s;
    }

    .icon-btn:hover {
        background: rgba(255, 255, 255, 0.24);
    }

    .icon-btn svg {
        width: 18px;
        height: 18px;
    }

    .header-title {
        font-family: var(--font-display);
        font-size: 19px;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .search-wrap {
        margin-top: 16px;
        display: none;
    }

    .search-wrap.open {
        display: block;
    }

    .search-wrap input {
        width: 100%;
        padding: 11px 16px;
        border-radius: 12px;
        border: none;
        background: rgba(255, 255, 255, 0.16);
        color: var(--white);
        font-family: var(--font-body);
        font-size: 14px;
        outline: none;
    }

    .search-wrap input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .search-wrap input:focus {
        background: rgba(255, 255, 255, 0.24);
    }

    /* ---------- List ---------- */
    .list {
        flex: 1;
        padding: 14px 14px 100px;
        overflow-y: auto;
    }

    .convo {
        display: flex;
        align-items: center;
        gap: 13px;
        background: var(--white);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 14px 15px;
        margin-bottom: 11px;
        text-decoration: none;
        color: inherit;
        transition: transform .15s ease, box-shadow .2s ease, border-color .2s;
    }

    .convo:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -14px rgba(91, 44, 158, 0.35);
        border-color: var(--purple-100);
    }

    .avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-display);
        font-weight: 700;
        font-size: 16px;
        color: var(--white);
        flex-shrink: 0;
    }

    .convo-body {
        flex: 1;
        min-width: 0;
    }

    .convo-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .convo-name {
        display: flex;
        align-items: center;
        gap: 7px;
        min-width: 0;
    }

    .convo-name span.name {
        font-family: var(--font-display);
        font-weight: 700;
        font-size: 15px;
        color: var(--ink);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .unread-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--unread);
        flex-shrink: 0;
        box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.15);
    }

    .convo-time {
        font-size: 11.5px;
        color: var(--muted);
        flex-shrink: 0;
    }

    .convo-job {
        font-size: 12px;
        color: var(--purple-600);
        font-weight: 600;
        margin: 2px 0 3px;
    }

    .convo-preview {
        font-size: 13px;
        color: var(--muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .convo-preview.unread-text {
        color: var(--ink);
        font-weight: 600;
    }

    /* ---------- Empty state ---------- */
    .empty {
        margin-top: 60px;
        text-align: center;
        color: var(--muted);
        padding: 0 30px;
    }

    .empty svg {
        width: 52px;
        height: 52px;
        color: var(--purple-400);
        margin-bottom: 14px;
    }

    .empty h3 {
        font-family: var(--font-display);
        color: var(--ink);
        font-size: 17px;
        margin: 0 0 6px;
    }

    .empty p {
        font-size: 13.5px;
        line-height: 1.5;
        margin: 0;
    }

    /* ---------- FAB ---------- */
    .fab {
        position: absolute;
        right: 22px;
        bottom: 26px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--purple-500), var(--purple-700));
        border: none;
        color: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 14px 30px -10px rgba(124, 58, 237, 0.6);
        transition: transform .18s ease;
    }

    .fab:hover {
        transform: translateY(-3px) scale(1.03);
    }

    .fab svg {
        width: 22px;
        height: 22px;
    }

    .no-results {
        display: none;
        text-align: center;
        color: var(--muted);
        font-size: 13.5px;
        margin-top: 40px;
    }

    .no-results.show {
        display: block;
    }
    </style>
</head>

<body>

    <div class="page-shell">
        <div class="app">

            <div class="header">
                <div class="header-row">
                    <button type="button" class="icon-btn" id="backBtn" aria-label="Back">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <line x1="3" y1="12" x2="21" y2="12" />
                            <line x1="3" y1="18" x2="21" y2="18" />
                        </svg>
                    </button>
                    <span class="header-title">Messages</span>
                    <button type="button" class="icon-btn" id="searchToggle" aria-label="Search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7" />
                            <path d="M21 21l-4.3-4.3" />
                        </svg>
                    </button>
                </div>

                <div class="search-wrap" id="searchWrap">
                    <input type="text" id="searchInput" placeholder="Search by name, job, or message…"
                        autocomplete="off">
                </div>
            </div>

            <div class="list" id="list">

                <?php if(empty($conversations)): ?>
                <div class="empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path
                            d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
                    </svg>
                    <h3>No conversations yet</h3>
                    <p>Once you accept an applicant for a job opening, your chat with them will show up here.</p>
                </div>
                <?php else: ?>

                <?php foreach($conversations as $c): ?>
                <?php
                $isUnread = $c['last_message'] !== null && (int)$c['last_sender_id'] !== (int)$employer_id;
                $preview = $c['last_message'] !== null ? gb_truncate($c['last_message']) : 'No messages yet — start the conversation';
                $searchBlob = strtolower($c['worker_name'] . ' ' . $c['job_title'] . ' ' . ($c['last_message'] ?? ''));
            ?>

                <a class="convo" href="chat.php?application_id=<?=(int)$c['application_id'];?>">
                    <div class="avatar" style="background:<?=gb_avatar_color($c['worker_id']);?>">
                        <?=htmlspecialchars(gb_initials($c['worker_name']));?>
                    </div>

                    <div class="convo-body">
                        <div class="convo-top">
                            <div class="convo-name">
                                <span class="name"><?=htmlspecialchars($c['worker_name']);?></span>
                                <?php if($isUnread): ?><span class="unread-dot"></span><?php endif; ?>
                            </div>
                            <span
                                class="convo-time"><?=htmlspecialchars(gb_relative_time($c['last_message_at']));?></span>
                        </div>
                        <div class="convo-job"><?=htmlspecialchars($c['job_title']);?></div>
                        <div class="convo-preview<?php echo $isUnread ? ' unread-text' : ''; ?>">
                            <?=htmlspecialchars($preview);?></div>
                    </div>
                </a>
                <?php endforeach; ?>

                <div class="no-results" id="noResults">No conversations match your search.</div>
                <?php endif; ?>

            </div>

            <button type="button" class="fab" id="fabBtn" aria-label="Scroll to top">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                    <line x1="12" y1="19" x2="12" y2="5" />
                    <polyline points="5 12 12 5 19 12" />
                </svg>
            </button>

        </div>
    </div>

    <script>
    (function() {
        var backBtn = document.getElementById('backBtn');
        var searchToggle = document.getElementById('searchToggle');
        var searchWrap = document.getElementById('searchWrap');
        var searchInput = document.getElementById('searchInput');
        var list = document.getElementById('list');
        var fabBtn = document.getElementById('fabBtn');
        var noResults = document.getElementById('noResults');

        backBtn.addEventListener('click', function() {
            if (window.history.length > 1) {
                window.history.back();
            }
        });

        searchToggle.addEventListener('click', function() {
            var open = searchWrap.classList.toggle('open');
            if (open) {
                searchInput.focus();
            } else {
                searchInput.value = '';
                filterList('');
            }
        });

        function filterList(query) {
            var q = query.trim().toLowerCase();
            var items = list.querySelectorAll('.convo');
            var visibleCount = 0;

            items.forEach(function(item) {
                var haystack = item.getAttribute('data-search') || '';
                var match = q === '' || haystack.indexOf(q) !== -1;
                item.style.display = match ? '' : 'none';
                if (match) {
                    visibleCount++;
                }
            });

            if (noResults) {
                noResults.classList.toggle('show', items.length > 0 && visibleCount === 0 && q !== '');
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterList(searchInput.value);
            });
        }

        fabBtn.addEventListener('click', function() {
            list.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    })();
    </script>
</body>

</html>