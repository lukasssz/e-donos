<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$login = $_SESSION['user'];

$usersFile = __DIR__ . "/../secure/users.json";
$users = json_decode(file_get_contents($usersFile), true);

$user = $users[$login];
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Profil użytkownika</title>

<style>
    body {
        margin: 0;
        padding: 0;
        background: #0f1624;
        font-family: Arial, sans-serif;
        color: white;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
    }

    .container {
        width: 80vw;
        max-width: 600px;
        margin-top: 5vh;
        background: rgba(255,255,255,0.05);
        padding: 4vh 4vw;
        border-radius: 2vh;
        backdrop-filter: blur(1vh);
    }

    h1 {
        text-align: center;
        font-size: 4vh;
        margin-bottom: 3vh;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        padding: 1.5vh 0;
        font-size: 2.4vh;
        border-bottom: 1px solid rgba(255,255,255,0.2);
        transition: 0.2s;
    }

    .section-header:hover {
        opacity: 0.8;
    }

    .arrow {
        transition: transform 0.3s ease;
        font-size: 2.5vh;
    }

    .arrow.open {
        transform: rotate(180deg);
    }

    .section-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease;
    }

    .section-content.open {
        max-height: 500px;
    }

    label {
        display: block;
        margin-top: 1vh;
        font-size: 2vh;
    }

    input, textarea {
        width: 94%;
        padding: 1vh;
        margin-top: 0.5vh;
        border-radius: 1vh;
        border: none;
        background: rgba(255,255,255,0.1);
        color: white;
        font-size: 2vh;
    }

    textarea {
        height: 12vh;
        resize: none;
    }

    button {
        margin-top: 2vh;
        width: 100%;
        padding: 1.5vh;
        background: #1a2333;
        color: white;
        border: none;
        border-radius: 1vh;
        font-size: 2vh;
        cursor: pointer;
        transition: 0.2s;
    }

    button:hover {
        background: #25324a;
    }

    .info-box {
        margin-bottom: 3vh;
        padding-bottom: 2vh;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }

    
    .centerNote {
        position: fixed;
        top: 40vh;
        left: 50vw;
        transform: translate(-50%, -50%);
        background: rgba(20, 25, 40, 0.95);
        padding: 3vh 4vw;
        border-radius: 1.5vh;
        color: white;
        font-size: 2.5vh;
        opacity: 0;
        transition: 0.3s ease;
        text-align: center;
        z-index: 9999;
    }

    .centerNote.show {
        opacity: 1;
    }

    .centerNote.ok {
        border: 0.8vh solid #4cff4c;
    }

    .centerNote.err {
        border: 0.8vh solid #ff4c4c;
    }
   

</style>

</head>
<body>

<div class="container">


    <h1>Twój profil</h1>

    <div class="info-box">
        <p><strong>Login:</strong> <?= htmlspecialchars($login) ?></p>
        <p><strong>Bio:</strong> <?= htmlspecialchars($user['bio']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Status konta:</strong> <?= $user['verified'] ? "Zweryfikowane" : "Niezweryfikowane" ?></p>
    </div>

    
    <div class="section-header" onclick="toggleSection('emailSection', this)">
        <span>Zmień email</span>
        <span class="arrow">▼</span>
    </div>

    <div id="emailSection" class="section-content">
        <form id="emailForm" action="change_email.php" method="POST">
            <label>Nowy email:</label>
            <input type="email" name="new_email" required>

            <label>Hasło (dla bezpieczeństwa):</label>
            <input type="password" name="password" required>

            <button type="submit">Zmień email</button>
        </form>
    </div>

    
    <div class="section-header" onclick="toggleSection('passwordSection', this)">
        <span>Zmień hasło</span>
        <span class="arrow">▼</span>
    </div>

    <div id="passwordSection" class="section-content">
        <form id="passwordForm" action="change_password.php" method="POST">
            <label>Stare hasło:</label>
            <input type="password" name="old_password" required>

            <label>Nowe hasło:</label>
            <input type="password" name="new_password" required>

            <label>Powtórz nowe hasło:</label>
            <input type="password" name="new_password_confirm" required>

            <button type="submit">Zmień hasło</button>
        </form>
    </div>

    
    <div class="section-header" onclick="toggleSection('bioSection', this)">
        <span>Zmień bio</span>
        <span class="arrow">▼</span>
    </div>

    <div id="bioSection" class="section-content">
    <form id="bioForm" action="change_bio.php" method="POST">
        <label>Twoje bio:</label>
        <textarea name="bio" maxlength="50" required><?= htmlspecialchars($user['bio']) ?></textarea>
        <div id="bioCounter">0 / 50</div>

        <button type="submit">Zapisz bio</button>
    </form>
</div>

    
<div class="section-header" onclick="toggleSection('deleteSection', this)">
    <span style="color:#ff4c4c;">Usuń konto</span>
    <span class="arrow">▼</span>
</div>

<div id="deleteSection" class="section-content">
    <form id="deleteForm" action="delete_account.php" method="POST">
        <p style="font-size:2vh; color:#ff4c4c; margin-top:1vh;">
            Ta operacja jest <strong>nieodwracalna</strong>.<br>
            Wszystkie Twoje dane, komentarze i donosy zostaną usunięte.
        </p>

        <button type="submit" style="background:#8b0000;">Usuń konto</button>
    </form>
</div>

<div class="section-header" onclick="location.href='edonos.php'" style="cursor:pointer;">
    <span>Powrót</span>
</div>



</div>

<script>
function toggleSection(id, header) {
    const content = document.getElementById(id);
    const arrow = header.querySelector(".arrow");

    content.classList.toggle("open");
    arrow.classList.toggle("open");
}

function ajaxForm(form, url) {
    const formData = new FormData(form);

    fetch(url, {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(msg => {
        if (msg === "SUCCESS") {
            showCenterNote("Zmieniono pomyślnie!", true);
            form.reset();
        } else {
            showCenterNote(msg, false);
        }
    });
}

document.getElementById("passwordForm").addEventListener("submit", function(e) {
    e.preventDefault();
    ajaxForm(this, "change_password.php");
});

document.getElementById("emailForm").addEventListener("submit", function(e) {
    e.preventDefault();
    ajaxForm(this, "change_email.php");
});

document.getElementById("bioForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    fetch("change_bio.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(msg => {
        if (msg === "SUCCESS") {
            showCenterNote("Zmieniono pomyślnie!", true);
            form.reset();

            
            setTimeout(() => {
                location.reload();
            }, 1000);

        } else {
            showCenterNote(msg, false);
        }
    });
});

// Licznik znaków BIO
const bioTextarea = document.querySelector("textarea[name='bio']");
const bioCounter = document.getElementById("bioCounter");

function updateBioCounter() {
    bioCounter.textContent = bioTextarea.value.length + " / 50";
}

bioTextarea.addEventListener("input", updateBioCounter);
updateBioCounter(); // aktualizacja przy załadowaniu


document.getElementById("deleteForm").addEventListener("submit", function(e) {
    e.preventDefault();

    if (!confirm("Czy na pewno chcesz usunąć konto?")) {
        return;
    }

    const form = this;
    const formData = new FormData(form);

    fetch("delete_account.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(msg => {
        if (msg === "SUCCESS") {
            showCenterNote("Konto zostało usunięte.", true);

            
            setTimeout(() => {
                window.location.href = "edonos.php";
            }, 1500);

        } else {
            showCenterNote(msg, false);
        }
    });
});



function showCenterNote(msg, success) {
    const note = document.createElement("div");
    note.className = "centerNote " + (success ? "ok" : "err");
    note.innerText = msg;
    document.body.appendChild(note);

    setTimeout(() => note.classList.add("show"), 10);
    setTimeout(() => note.classList.remove("show"), 2500);
    setTimeout(() => note.remove(), 3000);
}
</script>

</body>
</html>
