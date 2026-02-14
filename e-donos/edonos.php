<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>e-donos</title>

  <style>
    body {
      margin: 0;
      background: linear-gradient(135deg, #0a0f1c, #1a2333);
      min-height: 100vh;
      color: white;
      font-family: sans-serif;
      overscroll-behavior: none;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    /* ⭐ WSPÓLNY HEADER */
    .header {
      width: 100%;
      display: flex;
      justify-content: center;
      gap: 4vw;
      align-items: center;
      padding-top: 3vh;
      position: relative;
      z-index: 50;
    }

    /* ⭐ TOP BUTTON — TERAZ AUTOMATYCZNIE SZEROKI NA PC */
    .top {
      padding: 2vh 3vw;
      min-width: 28vw;              /* minimalna szerokość */
      width: auto;                  /* dopasowanie do treści */
      max-width: 600px;             /* mieści wszystkie przyciski */
      background: rgba(255, 255, 255, 0.1);
      border: 0.3vh solid rgba(255, 255, 255, 0.3);
      border-radius: 1.5vh;
      font-size: 2.2vh;
      cursor: pointer;
      transition: 0.3s ease;
      text-align: center;
      position: relative;
    }

    .top.active {
      width: 50vw;                  /* szeroki po rozwinięciu */
      max-width: 650px;
    }

    .title { transition: opacity 0.2s ease; }

    .buttons {
      display: none;
      width: 100%;
      margin-top: 1.5vh;
      flex-direction: row;
      gap: 2vh;
    }

    .button1, .button3, .button4, .button5 {
      flex: 1;
      padding: 3vh 1vh;
      background: rgba(255, 255, 255, 0.2);
      border: 0.3vh solid rgba(255, 255, 255, 0.4);
      border-radius: 1.2vh;
      font-size: 2vh;
      cursor: pointer;
      text-align: center;
    }

    .top.active .buttons { display: flex; }
    .top.active .title { opacity: 0; }

    /* ⭐ ACCOUNT BUTTON — SZEROKI + WYŚRODKOWANY */
    .butt_account {
      padding: 2vh 3vw;
      min-width: 28vw;
      width: auto;
      max-width: 600px;
      background: rgba(255, 255, 255, 0.1);
      border: 0.3vh solid rgba(255, 255, 255, 0.3);
      border-radius: 1.5vh;
      font-size: 2.2vh;
      cursor: pointer;
      transition: 0.3s ease;

      display: flex;
      justify-content: center;     /* WYŚRODKOWANIE */
      align-items: center;
      gap: 1vh;
      text-align: center;
      position: relative;
    }

    .arrow {
      font-size: 2.2vh;
      opacity: 0.8;
      transition: transform 0.2s ease;
    }

    .butt_account.open .arrow {
      transform: rotate(90deg);
    }

    /* ⭐ DROPDOWNY */
    .accountList {
      max-height: 0;
      opacity: 0;
      overflow: hidden;
      transform: translateY(-1vh);
      transition: max-height 0.35s ease, opacity 0.25s ease, transform 0.35s ease;
      background: white;
      border-radius: 1.2vh;
      padding: 0 2vh;
      color: black;
      font-size: 2vh;
      position: absolute;
      top: 100%;
      left: 0;
      width: 100%;
      z-index: 20;
    }

    .openList {
      max-height: 50vh;
      opacity: 1;
      transform: translateY(0);
      padding: 2vh;
    }

    .accountItem {
      padding: 1.5vh;
      background: #f0f0f0;
      border-radius: 1vh;
      margin-bottom: 1vh;
      cursor: pointer;
    }

    .accountItem:hover {
      background: #e0e0e0;
    }

    /* ⭐ MOBILE FIX */
    @media (max-width: 600px) {
      .header {
        flex-direction: column;
        gap: 2vh;
      }

      .top, .butt_account {
        width: 80%;
        max-width: none;
      }

      .top.active {
        width: 90%;
      }
    }

    /* ⭐ OSTRZEŻENIE */
    .warning-box {
      width: 70%;
      max-width: 700px;
      padding: 3vh 3vw;
      background: rgba(255,255,255,0.12);
      border-left: 1vw solid #ffcc00;
      border-radius: 2vh;
      box-shadow: 0 0 2vh rgba(0,0,0,0.25);
      font-size: 2.1vh;
      line-height: 1.5;
      text-align: center;
      margin-top: 10vh;
    }

    .warning-box h2 {
      margin-top: 0;
      font-size: 3vh;
    }

    .warning-box .red {
      color: #ff4444;
      font-weight: bold;
    }
  </style>
</head>

<body>

  <!-- ⭐ NOWY HEADER -->
  <div class="header">

    <div class="top" id="topBar">
      <div class="title">e-donosy</div>

      <div class="buttons">
        <div class="button1" onclick="location.href='szkola.php'">Chcę złożyć donos</div>
        <div class="button3" onclick="location.href='donosy.php'">Ostatnie donosy</div>
        <div class="button4" onclick="location.href='ranking.php'">Ranking</div>
        <div class="button5" onclick="location.href='user_list.php'">Użytkownicy</div>
      </div>
    </div>

    <div class="butt_account" id="accountBtn">
      Konto <span class="arrow">›</span>

      <div class="accountList" id="accountList">
        <?php if (!isset($_SESSION['user'])): ?>
          <div class="accountItem" onclick="location.href='login.html'">Zaloguj się</div>
          <div class="accountItem" onclick="location.href='sign_up.html'">Zarejestruj się</div>
        <?php else: ?>
          <div class="accountItem" onclick="location.href='logout.php'">Wyloguj</div>
          <div class="accountItem" onclick="location.href='my_donos.php'">Moje donosy</div>
          <div class="accountItem" onclick="location.href='my_profile.php'">Mój profil</div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- ⭐ OSTRZEŻENIE -->
  <div class="warning-box">
    <h2>⚠️ UWAGA – SERWIS SATYRYCZNY</h2>

    <p>e‑Donos to projekt humorystyczny i parodia formularzy zgłoszeniowych.<br>
    Wszystkie postacie, zdarzenia i opisy są fikcyjne.</p>

    <p class="red">❌ Serwis nie służy do zgłaszania prawdziwych osób, szkół ani wydarzeń.</p>

    <p>Korzystając z serwisu, akceptujesz regulamin.</p>
  </div>

  <script>
    const topBar = document.getElementById("topBar");
    const accountBtn = document.getElementById("accountBtn");
    const accountList = document.getElementById("accountList");

    topBar.addEventListener("click", () => {
      topBar.classList.toggle("active");
      accountList.classList.remove("openList");
      accountBtn.classList.remove("open");
    });

    accountBtn.addEventListener("click", (event) => {
      event.stopPropagation();
      accountList.classList.toggle("openList");
      accountBtn.classList.toggle("open");
      topBar.classList.remove("active");
    });

    document.addEventListener("click", (event) => {
      if (!topBar.contains(event.target) && !accountBtn.contains(event.target)) {
        topBar.classList.remove("active");
        accountList.classList.remove("openList");
        accountBtn.classList.remove("open");
      }
    });
  </script>

</body>
</html>
