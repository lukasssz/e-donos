<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$nick = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="logo.png">
  <title>Formularz zgłoszeniowy</title>

  <style>
    .regulamin-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease;
}

.regulamin-content.open {
    max-height: 200vh; 
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


    body {
      margin: 0;
      padding: 5vh;
      background: #f5f5f5;
      font-family: sans-serif;
    }

    .form-container {
      max-width: 650px;
      margin: auto;
      background: white;
      padding: 3vh;
      border-radius: 1vh;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }

    .question {
      margin-bottom: 3vh;
    }

    .question label {
      display: block;
      font-size: 2.2vh;
      margin-bottom: 1vh;
      font-weight: bold;
    }

    .question input,
    .question textarea {
      width: 100%;
      padding: 1.5vh;
      font-size: 2vh;
      border: 1px solid #ccc;
      border-radius: 0.8vh;
      box-sizing: border-box;
    }

    .locked-field {
      background: #e6e6e6;
      color: #555;
      cursor: not-allowed;
      opacity: 0.7;
    }

    textarea {
      resize: vertical;
      min-height: 10vh;
    }

    .regulamin-box {
      background: #fafafa;
      border: 1px solid #ccc;
      padding: 2vh;
      border-radius: 1vh;
      margin-bottom: 2vh;
      position: relative;
    }

    .toggle-arrow {
      cursor: pointer;
      font-size: 2.5vh;
      user-select: none;
      margin-top: 1vh;
      display: inline-block;
      transition: transform 0.3s ease;
    }

    .toggle-arrow.open {
      transform: rotate(180deg);
    }

    button {
      margin-top: 2vh;
      padding: 1.8vh 3vh;
      font-size: 2vh;
      background: #1a2333;
      color: white;
      border: none;
      border-radius: 0.8vh;
      cursor: pointer;
      transition: 0.2s;
    }

    button:disabled {
      background: #888;
      cursor: not-allowed;
    }

    button:hover:not(:disabled) {
      background: #0a0f1c;
}
  
  
     @media (max-width: 600px) {

    
    input[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        background-color: #fff;
        border: 2px solid #007bff;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: inline-block;
        position: relative;
        margin-right: 12px;
        vertical-align: middle;
    }

    /* Checkmark */
    input[type="checkbox"]:checked::after {
        content: "✔";
        font-size: 20px;
        color: #007bff;
        position: absolute;
        top: -2px;
        left: 4px;
    }

    label {
        font-size: 1.3rem;
        line-height: 1.6rem;
    }
}




  </style>
</head>
<body>
<button class="powrot" onclick="location.href='edonos.php'">Powrót</button>

  <div class="form-container">
    <h2>Formularz zgłoszeniowy</h2>

    <form action="send.php" method="POST">

      <div class="question">
        <label>1. Twój pseudonim:</label>
        <input type="text" name="t_imię" value="<?= htmlspecialchars($nick) ?>" readonly class="locked-field">
      </div>

      <div class="question">
  <label>2. Twoja klasa:</label>
  <input type="text" name="t_klasa" id="t_klasa" pattern="^[1-4][a-c]$" required>
</div>


      <div class="question">
        <label>3. Pseudonim postaci zgłaszanej:</label>
        <input type="text" name="j_imię">
      </div>

      <div class="question">
        <label>4. Gdzie miała miejsce sytuacja?</label>
        <input type="text" name="miejsce">
      </div>

      <div class="question">
        <label>5. Data zdarzenia:</label>
        <input type="date" name="data" id="dataField">
      </div>

      <div class="question">
           <label>6. Opisz dokładnie, co się stało:</label>
          <textarea name="opis" id="opis" maxlength="500" required></textarea>
           <div id="opisCounter">0 / 500</div>
       </div>


        <div class="regulamin-box">
          <strong>Regulamin (kliknij, aby rozwinąć)</strong>
  
          <div class="regulamin-content" id="regulaminText">
            <h1>Regulamin serwisu e-Donos</h1>
          
            <h2>§1. Informacje ogólne</h2>
            <p>
              Serwis internetowy e-Donos (dalej: „Serwis”) ma charakter humorystyczny, satyryczny i parodystyczny.
              Serwis nie jest narzędziem do zgłaszania rzeczywistych zdarzeń, osób ani instytucji.
              Wszelkie treści publikowane w Serwisie stanowią fikcję literacką i są tworzone wyłącznie w celach rozrywkowych.
            </p>
          
            <h2>§2. Charakter treści</h2>
            <p>Wszystkie zgłoszenia, opisy, postacie oraz zdarzenia publikowane w Serwisie:</p>
            <ol>
              <li>są fikcyjne,</li>
              <li>nie odnoszą się do rzeczywistych osób, szkół ani wydarzeń,</li>
              <li>nie mogą umożliwiać identyfikacji jakiejkolwiek osoby fizycznej.</li>
            </ol>
            <p>Jakiekolwiek podobieństwo do osób, instytucji lub zdarzeń rzeczywistych jest przypadkowe i niezamierzone.</p>
          
            <h2>§3. Zakazy</h2>
            <p>Użytkownikom zabrania się w szczególności:</p>
            <ul>
              <li>
                podawania prawdziwych danych osobowych, w tym imion, nazwisk, pseudonimów, adresów, numerów klas,
                nazw szkół lub innych informacji umożliwiających identyfikację osoby fizycznej;
              </li>
              <li>
                publikowania treści naruszających dobra osobiste, o których mowa w art. 23 i 24 Kodeksu cywilnego;
              </li>
              <li>
                zamieszczania treści mogących stanowić pomówienie lub zniesławienie w rozumieniu art. 212 Kodeksu karnego;
              </li>
              <li>
                przesyłania zdjęć, nagrań, dokumentów lub innych materiałów przedstawiających rzeczywiste osoby lub miejsca;
              </li>
              <li>
                opisywania rzeczywistych zdarzeń, konfliktów szkolnych, relacji między uczniami lub nauczycielami.
              </li>
            </ul>
          
            <h2>§4. Odpowiedzialność użytkownika</h2>
            <p>Użytkownik oświadcza, że:</p>
            <ol>
              <li>wszystkie zamieszczone przez niego treści są fikcyjne,</li>
              <li>korzysta z Serwisu wyłącznie w celach humorystycznych,</li>
              <li>ponosi pełną odpowiedzialność prawną za przesyłane treści.</li>
            </ol>
            <p>Użytkownik przyjmuje do wiadomości, że ponosi odpowiedzialność za naruszenie przepisów prawa, w szczególności:</p>
            <ul>
              <li>Kodeksu cywilnego,</li>
              <li>Kodeksu karnego,</li>
              <li>ustawy z dnia 18 lipca 2002 r. o świadczeniu usług drogą elektroniczną.</li>
            </ul>
          
            <h2>§5. Odpowiedzialność administratora</h2>
            <p>Administrator Serwisu:</p>
            <ol>
              <li>nie weryfikuje treści przed ich publikacją,</li>
              <li>nie jest inicjatorem treści przekazywanych przez użytkowników.</li>
            </ol>
            <p>
              Zgodnie z art. 14 ustawy o świadczeniu usług drogą elektroniczną administrator nie ponosi odpowiedzialności
              za treści użytkowników, o ile nie posiada wiedzy o ich bezprawnym charakterze.
              Po uzyskaniu informacji o bezprawnym charakterze treści administrator zobowiązuje się do niezwłocznego
              usunięcia lub zablokowania dostępu do takich treści.
            </p>
          
            <h2>§6. Procedura zgłaszania naruszeń</h2>
            <p>Każda osoba może zgłosić treść naruszającą prawo lub regulamin poprzez kontakt z administratorem.</p>
            <p>Zgłoszenie powinno zawierać:</p>
            <ul>
              <li>wskazanie treści,</li>
              <li>opis naruszenia.</li>
            </ul>
            <p>Administrator rozpatruje zgłoszenia bez zbędnej zwłoki.</p>
          
            <h2>§7. Dane osobowe</h2>
            <p>Serwis nie jest przeznaczony do przetwarzania danych osobowych.</p>
            <p>
              Użytkownikom zabrania się wprowadzania danych osobowych w rozumieniu art. 4 pkt 1 Rozporządzenia Parlamentu
              Europejskiego i Rady (UE) 2016/679 (RODO).
            </p>
            <p>W przypadku przypadkowego zamieszczenia danych osobowych administrator dokona ich niezwłocznego usunięcia.</p>
          
            <h2>§8. Uprawnienia administratora</h2>
            <p>Administrator zastrzega sobie prawo do:</p>
            <ol>
              <li>usuwania treści bez podania przyczyny,</li>
              <li>blokowania dostępu do Serwisu wybranym użytkownikom,</li>
              <li>czasowego lub stałego zawieszenia działania Serwisu.</li>
            </ol>
          
            <h2>§9. Postanowienia końcowe</h2>
            <p>Regulamin obowiązuje od momentu opublikowania w Serwisie.</p>
            <p>Korzystanie z Serwisu oznacza akceptację niniejszego regulaminu.</p>
            <p>Regulamin ma charakter tymczasowy i może ulec zmianie.</p>
          </div>

        <span class="toggle-arrow" id="toggleArrow">▼</span>
      </div>
     
      <label>
        <input type="checkbox" id="rules1">
        Oświadczam, iż zapoznał*m się z regulaminem
      </label>

      <br>

      <label>
        <input type="checkbox" id="rules2">
        Akceptuję regulamin
      </label>

      <br>

      <button type="submit" id="submitBtn" disabled>Wyślij</button>

    </form>
</div>

<script>
  const arrow = document.getElementById("toggleArrow");
  const regulamin = document.getElementById("regulaminText");

  const rules1 = document.getElementById("rules1");
  const rules2 = document.getElementById("rules2");
  const submitBtn = document.getElementById("submitBtn");

  const field2 = document.querySelector('input[name="t_klasa"]');
  const field3 = document.querySelector('input[name="j_imię"]');
  const field4 = document.querySelector('input[name="miejsce"]');
  const field5 = document.querySelector('input[name="data"]');
  const field6 = document.getElementById("opis");
  const counter = document.getElementById("opisCounter");

  // --- KONFIGURACJA FILTRA SŁÓW ---
  // Wpisz tutaj słowa, które mają blokować wysyłanie
  const badWords = [
    "kurwa","kurwy","kurwo","kurew","kvrwa","kvrwy","k.u.r.w.a","k u r w a","ku.rwa","kur*w*a","k0rwa","kórwa",
"kutas","kutasy","kvtas","k.u.t.a.s","k0tas","kut@s","ku+as",
"chuj","chuja","chuje","chvj","huj","h.u.j","chu*j","ch0j","chuy",
"pizda","pizdy","p1zda","p1zdy","pi.zda","piźda","p!zda","piz.d.a",
"jebac","jebać","j3bac","jebany","jebana","jebane","jebani","jebie","jebią","jebiesz","jebiecie","jebal","jebala","jebalo","j.e.b.a.c","je8ac",
"pierdol","pierdoli","pierdolę","pierdolony","pierdolona","pierdolone","pierdolec","pier.dol","p1erdol","p!erdol",
"skurwiel","skurwysyn","skurwysyny","skur.wiel","skurvysyn","skurw*el",
"sukinsyn","sukin.syn","suk!nsyn",
"dziwka","dziwki","dziwk@","dz1wka","dzi.wka","dz!wka",
"szmata","szmaty","szma.ta","szm@ta",
"cwel","cwelu","cwele","cewl","cw3l","c.w.e.l",
"debil","debile","d3bil","deb1l",
"idiota","idioci","idiotka","id10ta","!diota",
"gówno","gowno","gówna","gowna","g0wno","g0wn0","g*wno",
"fuck","fuk","f*ck","f**k","f.u.c.k","fuuck","f0ck","f@ck",
"fucking","fuck3r","fucker","motherfucker","m0therfucker","mfucker",
"shit","sh1t","sh!t","bullshit","crap","sh.it","s h i t","sh!7",
"bitch","bitches","biatch","b1tch","b!tch","b*tch",
"asshole","assholes","a s s h o l e",
"dick","dicks","d1ck","d!ck","dickhead","d!ckhead",
"bastard","bas.tard","b@stard",
"slut","s1ut","sl*t",
"whore","wh0re","who.re","wh*re",
"nigger","nigga","n1gger","n1gga","niga","niger","n!gga",
"retard","ret4rd","r3tard",
"faggot","f4ggot","f@g","fa.ggot",
"cunt","c*nt","kunt",
"cock","c0ck","c*ck",
"pierdoli","p1erdoli","pier.doli","p!erdoli",
"szon","sz0n","szoń",
"lamus","lamusy",
"frajer","fraj3r",
"kretyn","kr3tyn",
"palant","pa!ant",
"idiot","!diot",
"moron","m0ron",
"jerk","j3rk",
"jackass","j@ckass"
  ];

  function containsBadWords(text) {
    const lowerText = text.toLowerCase();
    return badWords.some(word => lowerText.includes(word.toLowerCase()));
  }
  // --------------------------------

  // blokada wpisywania daty
  field5.addEventListener("keydown", e => e.preventDefault());
  field5.addEventListener("paste", e => e.preventDefault());

  const classPattern = /^[1-4][a-cA-C]$/;

  function validateForm() {
    // 1. Walidacja klasy (regex)
    const classValid = classPattern.test(field2.value.trim());

    // 2. Czy wszystkie pola są uzupełnione?
    const allFilled =
      classValid &&
      field3.value.trim() !== "" &&
      field4.value.trim() !== "" &&
      field5.value.trim() !== "" &&
      field6.value.trim() !== "";

    // 3. Czy regulamin zaakceptowany?
    const rulesAccepted = rules1.checked && rules2.checked;

    // 4. Czy wykryto zakazane słowa w polach tekstowych?
    const hasProfanity = 
      containsBadWords(field3.value) || 
      containsBadWords(field4.value) || 
      containsBadWords(field6.value);

    // Aktualizacja licznika znaków i koloru ostrzeżenia
    const currentLength = field6.value.length;
    counter.textContent = currentLength + " / 500";

    if (hasProfanity) {
      counter.style.color = "red";
      counter.textContent += " - Wykryto niedozwolone słowa!";
    } else {
      counter.style.color = "black";
    }

    // FINALNA BLOKADA PRZYCISKU
    // Przycisk działa tylko gdy: wypełnione I zaakceptowane I BRAK wulgaryzmów
    submitBtn.disabled = !(allFilled && rulesAccepted && !hasProfanity);
  }

  // Rozwijanie regulaminu
  arrow.addEventListener("click", () => {
    regulamin.classList.toggle("open");
    arrow.classList.toggle("open");
  });

  // Nasłuchiwanie zmian na wszystkich polach (zamiast wielu osobnych linii)
  const allInputs = [rules1, rules2, field2, field3, field4, field5, field6];
  
  allInputs.forEach(input => {
    input.addEventListener("input", validateForm);
    input.addEventListener("change", validateForm);
  });
</script>