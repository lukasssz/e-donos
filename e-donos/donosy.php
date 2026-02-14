<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}


function loadJsonSafe($path) {
    if (!file_exists($path)) return [];
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return (is_array($data) && json_last_error() === JSON_ERROR_NONE) ? $data : [];
}

$school = loadJsonSafe(__DIR__ . '/../secure/donosy1.json');

$donosyFile = __DIR__ . "/../secure/donosy1.json";
$donosy = json_decode(file_get_contents($donosyFile), true);


// Jeśli podano ID donosu — pokaż tylko ten jeden
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $single = null;

    foreach ($donosy as $d) {
        if ($d['id'] == $id) {
            $single = $d;
            break;
        }
    }

    if ($single) {
        // Nadpisujemy tablicę, aby wyświetlić tylko ten jeden donos
        $donosy = [$single];
    }
}


foreach ($school as &$d) {
    $d['source'] = 'school';

    if (!isset($d['id'])) {
        $d['id'] = uniqid();
    }

    $d['likes']    = $d['likes']    ?? 0;
    $d['dislikes'] = $d['dislikes'] ?? 0;

    $d['comments'] = $d['comments'] ?? [];
    foreach ($d['comments'] as &$c) {
        if (!isset($c['id'])) {
            $c['id'] = uniqid('c_');
        }
        $c['likes']    = $c['likes']    ?? 0;
        $c['dislikes'] = $c['dislikes'] ?? 0;
    }
    unset($c);
}
unset($d);

$all = $school;

$sort = $_GET['sort'] ?? 'newest';

usort($all, function($a, $b) use ($sort) {

    switch ($sort) {

        case 'likes':
            return ($b['likes'] ?? 0) - ($a['likes'] ?? 0);

        case 'dislikes':
            return ($b['dislikes'] ?? 0) - ($a['dislikes'] ?? 0);

        case 'comments':
            return count($b['comments'] ?? []) - count($a['comments'] ?? []);

        case 'newest':
        default:
            $dateA = isset($a['time']) ? $a['time'] : 0;
            $dateB = isset($b['time']) ? $b['time'] : 0;
            return $dateB - $dateA;
    }
});

?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Lista donosów</title>

<style>
body {
    background: #f5f5f5;
    font-family: sans-serif;
    padding: 5vh 4vw;
}

.powrot {
    padding: 1.5vh 2.5vw;
    font-size: 2vh;
    background: #1a2333;
    color: white;
    border: none;
    border-radius: 1vh;
    box-shadow: 0 0.8vh 2vh rgba(0,0,0,0.15);
    cursor: pointer;
    transition: 0.25s ease;
    position: fixed;
    top: 5vh;
    right: 4vw;
    z-index: 9999;
}

.card {
    background: white;
    padding: 3vh 2vw;
    margin-bottom: 3vh;
    border-radius: 2vh;
    box-shadow: 0 0 2vh rgba(0,0,0,0.1);
    width: 94%;
    max-width; 100%;
    word-wrap: break-word;
    overflow: hidden;
    word-break; break-word;
}


.time {
    color: #666;
    font-size: 1.8vh;
    margin-bottom: 1.5vh;
}

.reportBtn {
    margin-top: -4vh;
    padding: 1vh 2vw;
    background: #ff4444;
    color: white;
    border: none;
    border-radius: 1vh;
    cursor: pointer;
    font-size: 2vh;
    float: right;
}

/* POPUP DONOSU */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    justify-content: center;
    align-items: center;
    z-index: 99999;
}

.modal-content {
    background: white;
    color: black;
    padding: 3vh 3vw;
    border-radius: 1.5vh;
    width: 40vw;
    max-width: 500px;
}

.modal-content h2 {
    margin-top: 0;
}

.modal-content label {
    display: block;
    margin-top: 2vh;
    margin-bottom: 1vh;
}

.modal-content select,
.modal-content textarea {
    width: 100%;
    padding: 1.2vh 1vw;
    border-radius: 1vh;
    border: 0.2vh solid #ccc;
    font-size: 2vh;
}

.submitReport {
    margin-top: 2vh;
    padding: 1.5vh 2vw;
    background: #1a2333;
    color: white;
    border: none;
    border-radius: 1vh;
    cursor: pointer;
}

.closeBtn {
    margin-left: 1vw;
    margin-top: 2vh;
    padding: 1.5vh 2vw;
    background: #777;
    color: white;
    border: none;
    border-radius: 1vh;
    cursor: pointer;
}

/* KOMENTARZE – styl YouTube */
.comments-wrapper {
    margin-top: 3vh;
    border-top: 1px solid #ddd;
    padding-top: 2vh;
}

.comment-title {
    font-weight: bold;
    margin-bottom: 1.5vh;
    font-size: 2vh;
}

.comment {
    margin-bottom: 1.5vh;
}

.comment-body {
    background: #f9f9f9;
    border-radius: 1.2vh;
    padding: 1vh 1.5vh;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.comment-author-time {
    display: flex;
    gap: 1vh;
    align-items: center;
}

.comment-author {
    font-weight: bold;
}

.comment-time {
    color: #777;
    font-size: 1.5vh;
}

/* 3 kropki */
.comment-menu {
    position: relative;
}

.comment-menu-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 2vh;
    padding: 0 0.5vh;
}

.comment-menu-dropdown {
    position: absolute;
    right: 0;
    top: 2.5vh;
    background: white;
    border: 1px solid #ddd;
    border-radius: 0.8vh;
    box-shadow: 0 0 1vh rgba(0,0,0,0.15);
    padding: 0.5vh 0;
    display: none;
    z-index: 1000;
}

.comment-menu-dropdown button {
    background: none;
    border: none;
    width: 100%;
    text-align: left;
    padding: 0.8vh 1.5vh;
    font-size: 1.6vh;
    cursor: pointer;
}

.comment-menu-dropdown button:hover {
    background: #f0f0f0;
}

.comment-text {
    font-size: 1.8vh;
    margin-top: 0.5vh;
}

/* akcje pod komentarzem */
.comment-actions {
    display: flex;
    gap: 2vh;
    margin-top: 1vh;
    font-size: 1.7vh;
    color: #606060;
}

.comment-actions form {
    display: inline;
}

.comment-actions button {
    background: none;
    border: none;
    cursor: pointer;
    color: #606060;
}

.comment-actions button:hover {
    color: #000;
}

/* przycisk "pokaż więcej" */
.show-more-btn {
    background: none;
    border: none;
    color: #1a73e8;
    cursor: pointer;
    font-size: 1.7vh;
    padding: 0;
}

/* ⭐ MOBILE BOOST — większe i rozciągnięte przyciski w popupie */
@media (max-width: 600px) {

    .modal-content {
        width: 90vw !important;
        max-width: none !important;
        padding: 4vh 5vw !important;
        border-radius: 2.5vh !important;
    }

    .submitReport,
    .closeBtn {
        width: 100% !important;        /* pełna szerokość */
        padding: 2.5vh !important;     /* dużo większe */
        font-size: 2.6vh !important;   /* większy tekst */
        border-radius: 2vh !important; /* bardziej zaokrąglone */
        margin-top: 2vh !important;
    }

    .closeBtn {
        background: #666 !important;
    }
}


</style>

</head>
<body>

<button class="powrot" onclick="location.href='edonos.php'">Powrót</button>

<h1>Wszystkie donosy</h1>

<div style="margin-bottom: 3vh;">
    <form method="GET" style="display:flex; gap:1vw; flex-wrap:wrap;">

        <button type="submit" name="sort" value="newest"
            style="
                padding:1.2vh 2vw;
                border:none;
                border-radius:1vh;
                cursor:pointer;
                font-size:2vh;
                background: <?= ($_GET['sort'] ?? 'newest') === 'newest' ? '#1a2333' : '#e0e0e0' ?>;
                color: <?= ($_GET['sort'] ?? 'newest') === 'newest' ? 'white' : 'black' ?>;
            ">
            Najnowsze
        </button>

        <button type="submit" name="sort" value="likes"
            style="
                padding:1.2vh 2vw;
                border:none;
                border-radius:1vh;
                cursor:pointer;
                font-size:2vh;
                background: <?= ($_GET['sort'] ?? '') === 'likes' ? '#1a2333' : '#e0e0e0' ?>;
                color: <?= ($_GET['sort'] ?? '') === 'likes' ? 'white' : 'black' ?>;
            ">
            Najpopularniejsze
        </button>

        <button type="submit" name="sort" value="dislikes"
            style="
                padding:1.2vh 2vw;
                border:none;
                border-radius:1vh;
                cursor:pointer;
                font-size:2vh;
                background: <?= ($_GET['sort'] ?? '') === 'dislikes' ? '#1a2333' : '#e0e0e0' ?>;
                color: <?= ($_GET['sort'] ?? '') === 'dislikes' ? 'white' : 'black' ?>;
            ">
            Najbardziej nielubiane
        </button>

        <button type="submit" name="sort" value="comments"
            style="
                padding:1.2vh 2vw;
                border:none;
                border-radius:1vh;
                cursor:pointer;
                font-size:2vh;
                background: <?= ($_GET['sort'] ?? '') === 'comments' ? '#1a2333' : '#e0e0e0' ?>;
                color: <?= ($_GET['sort'] ?? '') === 'comments' ? 'white' : 'black' ?>;
            ">
            Najwięcej komentarzy
        </button>

    </form>
</div>


<?php if (empty($all)): ?>
    <p>Brak zgłoszeń.</p>
<?php endif; ?>

<?php foreach ($all as $d): ?>
<div class="card">
    <div class="time">
        <?= date("d.m.Y H:i", $d['time']) ?>
    </div>
    
    <p><b>Dodane przez:</b> <?= htmlspecialchars($d['sender'] ?? 'Nieznany użytkownik') ?></p>

    <p><b>Miejsce:</b> <?= htmlspecialchars($d['place'] ?? 'Brak danych') ?></p>

    <?php if (!empty($d['date'])): ?>
    <p><b>Data zdarzenia:</b> <?= htmlspecialchars($d['date']) ?></p>
    <?php endif; ?>

    <p><b>Opis:</b><br><?= nl2br(htmlspecialchars($d['description'] ?? 'Brak opisu')) ?></p>

    <div style="margin-top:2vh; font-size:3vh;">

        <a href="vote.php?type=like&id=<?= $d['id'] ?>"
           style="color:green; text-decoration:none; font-weight:bold;">▲</a>
        <span style="margin-right:3vw;"><?= $d['likes'] ?></span>

        <a href="vote.php?type=dislike&id=<?= $d['id'] ?>"
           style="color:red; text-decoration:none; font-weight:bold;">▼</a>
        <span><?= $d['dislikes'] ?></span>

    </div>

    <button class="reportBtn"
        onclick="openReportForm(
            '<?= $d['source'] ?>',
            '<?= $d['date'] ?? '' ?>',
            '<?= date('H:i', $d['time']) ?>'
        )">
        Zgłoś
    </button>

    <!-- KOMENTARZE -->
   <!-- KOMENTARZE -->
<div class="comments-wrapper">

    <?php
        $comments  = $d['comments'] ?? [];
        $total     = count($comments);
        $showLimit = 2;
    ?>

    <div class="comment-title">
        Komentarze (<?= $total ?>)
    </div>

    <?php if ($total === 0): ?>
        <p style="color:#777; font-size:1.8vh; margin-bottom:2vh;">
            Dodaj komentarz
        </p>
    <?php else: ?>

        <?php $visible = array_slice($comments, 0, $showLimit); ?>

        <?php foreach ($visible as $c): ?>
            <?php $cid = htmlspecialchars($c['id']); ?>
            <div class="comment">
                <div class="comment-body">
                    <div class="comment-header">
                        <div class="comment-author-time">
                            <span class="comment-author"><?= htmlspecialchars($c['author']) ?></span>
                            <span class="comment-time"><?= date("d.m.Y H:i", $c['time']) ?></span>
                        </div>
                        <div class="comment-menu">
                            <button class="comment-menu-btn" type="button"
                                onclick="toggleCommentMenu('menu-<?= $cid ?>')">⋮</button>
                            <div class="comment-menu-dropdown" id="menu-<?= $cid ?>">
    <?php if ($c['author'] === $_SESSION['user']): ?>
        <form method="POST" action="delete_comment.php" style="margin:0;">
            <input type="hidden" name="donos_id" value="<?= htmlspecialchars($d['id']) ?>">
            <input type="hidden" name="comment_id" value="<?= $cid ?>">
            <button type="submit" style="color:red;">Usuń komentarz</button>
        </form>
    <?php endif; ?>

    <button type="button"
        onclick="openCommentReport('<?= htmlspecialchars($d['id']) ?>', '<?= $cid ?>')">
        Zgłoś komentarz
    </button>
</div>

                        </div>
                    </div>
                    <div class="comment-text">
                        <?= nl2br(htmlspecialchars($c['text'])) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($total > $showLimit): ?>
            <button class="show-more-btn" type="button"
                onclick="this.style.display='none'; this.nextElementSibling.style.display='block';">
                Pokaż więcej komentarzy (<?= $total - $showLimit ?>)
            </button>

            <div style="display:none;">
                <?php foreach (array_slice($comments, $showLimit) as $c): ?>
                    <?php $cid = htmlspecialchars($c['id']); ?>
                    <div class="comment">
                        <div class="comment-body">
                            <div class="comment-header">
                                <div class="comment-author-time">
                                    <span class="comment-author"><?= htmlspecialchars($c['author']) ?></span>
                                    <span class="comment-time"><?= date("d.m.Y H:i", $c['time']) ?></span>
                                </div>
                                <div class="comment-menu">
                                    <button class="comment-menu-btn" type="button"
                                        onclick="toggleCommentMenu('menu-<?= $cid ?>-more')">⋮</button>
                                    <div class="comment-menu-dropdown" id="menu-<?= $cid ?>-more">
                                        <button type="button"
                                            onclick="openCommentReport('<?= htmlspecialchars($d['id']) ?>', '<?= $cid ?>')">
                                            Zgłoś komentarz
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="comment-text">
                                <?= nl2br(htmlspecialchars($c['text'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <!-- FORMULARZ DODAWANIA KOMENTARZA -->
    <form action="coments.php" method="POST" style="margin-top:2vh;">
        <input type="hidden" name="donos_id" value="<?= htmlspecialchars($d['id']) ?>">
        <textarea name="comment_text" placeholder="Dodaj publiczny komentarz..." required
            style="width:100%; min-height:6vh; resize:vertical; padding:1vh; border-radius:1vh; border:1px solid #ccc; font-size:1.8vh;"></textarea>
        <button type="submit"
            style="margin-top:1vh; padding:1vh 2vw; background:#1a2333; color:white; border:none; border-radius:1vh; cursor:pointer; font-size:1.8vh;">
            Dodaj komentarz
        </button>
    </form>

</div>

</div>
<?php endforeach; ?>

<!-- POPUP ZGŁOSZENIA DONOSU -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <h2>Zgłoś donos</h2>

        <form method="POST" action="send_report.php">
            <input type="hidden" name="source" id="reportSource">
            <input type="hidden" name="date" id="reportDate">
            <input type="hidden" name="time" id="reportTime">

            <label>Powód zgłoszenia:</label>
            <select name="reason" required>
                <option value="">Wybierz powód</option>
                <option value="spam">Spam</option>
                <option value="obraźliwe treści">Obraźliwe treści</option>
                <option value="fałszywe informacje">Fałszywe informacje</option>
                <option value="naruszenie regulaminu">Naruszenie regulaminu</option>
            </select>

            <label>Dodatkowy opis (opcjonalnie):</label>
            <textarea name="details" rows="4"></textarea>

            <button type="submit" class="submitReport">Wyślij zgłoszenie</button>
            <button type="button" class="closeBtn" onclick="closeReportForm()">Anuluj</button>
        </form>
    </div>
</div>

<!-- POPUP ZGŁOSZENIA KOMENTARZA -->
<div id="commentReportModal" class="modal">
    <div class="modal-content">
        <h2>Zgłoś komentarz</h2>

        <form method="POST" action="report_comment.php">
            <input type="hidden" name="donos_id" id="commentReportDonosId">
            <input type="hidden" name="comment_id" id="commentReportCommentId">

            <label>Powód zgłoszenia:</label>
            <select name="reason" required>
                <option value="">Wybierz powód</option>
                <option value="spam">Spam</option>
                <option value="obraźliwe treści">Obraźliwe treści</option>
                <option value="fałszywe informacje">Fałszywe informacje</option>
                <option value="naruszenie regulaminu">Naruszenie regulaminu</option>
            </select>

            <label>Dodatkowy opis (opcjonalnie):</label>
            <textarea name="details" rows="4"></textarea>

            <button type="submit" class="submitReport">Wyślij zgłoszenie</button>
            <button type="button" class="closeBtn" onclick="closeCommentReport()">Anuluj</button>
        </form>
    </div>
</div>

<script>
function openReportForm(source, date, time) {
    document.getElementById("reportSource").value = source;
    document.getElementById("reportDate").value = date;
    document.getElementById("reportTime").value = time;
    document.getElementById("reportModal").style.display = "flex";
}

function closeReportForm() {
    document.getElementById("reportModal").style.display = "none";
}

function openCommentReport(donosId, commentId) {
    document.getElementById("commentReportDonosId").value = donosId;
    document.getElementById("commentReportCommentId").value = commentId;
    document.getElementById("commentReportModal").style.display = "flex";
}

function closeCommentReport() {
    document.getElementById("commentReportModal").style.display = "none";
}

function toggleCommentMenu(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const visible = el.style.display === 'block';
    document.querySelectorAll('.comment-menu-dropdown').forEach(m => m.style.display = 'none');
    el.style.display = visible ? 'none' : 'block';
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.comment-menu')) {
        document.querySelectorAll('.comment-menu-dropdown').forEach(m => m.style.display = 'none');
    }
});
</script>

<?php if (isset($_GET['reported'])): ?>
<div id="reportSuccess" style="
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #1a2333;
    color: white;
    padding: 30px 40px;
    border-radius: 15px;
    box-shadow: 0 0 25px rgba(0,0,0,0.4);
    font-size: 22px;
    text-align: center;
    z-index: 999999;
    opacity: 1;
    animation: fadeOut 6s forwards;
">
    Zgłoszenie donosu zostało wysłane pomyślnie.<br>
    Dziękujemy za pomoc w utrzymaniu jakości treści.
</div>
<?php endif; ?>

<?php if (isset($_GET['reported_comment'])): ?>
<div id="reportCommentSuccess" style="
    position: fixed;
    top: 55%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #1a2333;
    color: white;
    padding: 25px 35px;
    border-radius: 15px;
    box-shadow: 0 0 25px rgba(0,0,0,0.4);
    font-size: 20px;
    text-align: center;
    z-index: 999999;
    opacity: 1;
    animation: fadeOut 6s forwards;
">
    Zgłoszenie komentarza zostało wysłane pomyślnie.
</div>
<?php endif; ?>

<style>
@keyframes fadeOut {
    0% { opacity: 1; }
    80% { opacity: 1; }
    100% { opacity: 0; }
}
</style>

</body>
</html>
