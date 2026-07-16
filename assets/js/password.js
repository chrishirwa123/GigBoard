document.addEventListener("DOMContentLoaded", function () {
  const passwordInput = document.getElementById("password");
  const confirmInput = document.getElementById("confirm_password");
  const suggestBtn = document.getElementById("suggestBtn");
  const toggleBtn = document.getElementById("toggleVisibility");
  const strengthBar = document.getElementById("strengthBar");
  const strengthLabel = document.getElementById("strengthLabel");
  const matchLabel = document.getElementById("matchLabel");

  /* ----------------------------------------
       Generate a strong random password
       Uses crypto.getRandomValues (not Math.random)
       Guarantees at least one lower, upper, digit, symbol
       ---------------------------------------- */
  function generatePassword(length = 14) {
    const lower = "abcdefghijkmnopqrstuvwxyz";
    const upper = "ABCDEFGHJKLMNPQRSTUVWXYZ";
    const digits = "23456789";
    const symbols = "!@#$%^&*()-_=+?";
    const all = lower + upper + digits + symbols;

    function randomChar(set) {
      const arr = new Uint32Array(1);
      crypto.getRandomValues(arr);
      return set[arr[0] % set.length];
    }

    let chars = [
      randomChar(lower),
      randomChar(upper),
      randomChar(digits),
      randomChar(symbols),
    ];
    for (let i = chars.length; i < length; i++) {
      chars.push(randomChar(all));
    }

    // Shuffle so the guaranteed characters aren't always first
    for (let i = chars.length - 1; i > 0; i--) {
      const j = new Uint32Array(1);
      crypto.getRandomValues(j);
      const rand = j[0] % (i + 1);
      [chars[i], chars[rand]] = [chars[rand], chars[i]];
    }

    return chars.join("");
  }

  /* ----------------------------------------
       Score password strength (0-4)
       ---------------------------------------- */
  function scorePassword(pw) {
    if (!pw) return 0;
    let score = 0;
    if (pw.length >= 10) score++;
    if (pw.length >= 14) score++;
    if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score++;
    if (/\d/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    return Math.min(score, 4);
  }

  const levels = [
    { label: "Too short", color: "#c65b5b", width: "10%" },
    { label: "Weak", color: "#c65b5b", width: "30%" },
    { label: "Fair", color: "#c98a2c", width: "55%" },
    { label: "Strong", color: "#4c8c6b", width: "80%" },
    { label: "Very strong", color: "#2f6b4f", width: "100%" },
  ];

  function updateStrength() {
    const score = scorePassword(passwordInput.value);
    const level = levels[score];
    strengthBar.style.width = passwordInput.value ? level.width : "0%";
    strengthBar.style.background = level.color;
    strengthLabel.textContent = passwordInput.value ? level.label : "\u00A0";
    strengthLabel.style.color = level.color;
    checkMatch();
  }

  function checkMatch() {
    if (!confirmInput.value) {
      matchLabel.textContent = "\u00A0";
      return;
    }
    if (confirmInput.value === passwordInput.value) {
      matchLabel.textContent = "Passwords match";
      matchLabel.style.color = "#4c8c6b";
    } else {
      matchLabel.textContent = "Passwords don't match yet";
      matchLabel.style.color = "#c65b5b";
    }
  }

  suggestBtn.addEventListener("click", function () {
    const pw = generatePassword(14);
    passwordInput.value = pw;
    confirmInput.value = pw;
    passwordInput.type = "text";
    confirmInput.type = "text";
    toggleBtn.textContent = "hide";
    updateStrength();
  });

  toggleBtn.addEventListener("click", function () {
    const showing = passwordInput.type === "text";
    passwordInput.type = showing ? "password" : "text";
    toggleBtn.textContent = showing ? "show" : "hide";
  });

  passwordInput.addEventListener("input", updateStrength);
  confirmInput.addEventListener("input", checkMatch);
});
