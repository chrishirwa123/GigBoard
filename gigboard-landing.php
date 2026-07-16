<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GigBoard — Short gigs. Real pay. No fees.</title>
<meta name="description" content="GigBoard connects employers and workers for short-term gigs in Kigali. Post a gig, apply in a tap, track your application, and chat once confirmed. 100% free.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#0A1220;
    --ink-2:#0D1728;
    --panel:#101C30;
    --panel-2:#142239;
    --panel-raised:#182A45;
    --brass:#C9A227;
    --brass-light:#E8C468;
    --amber:#F2B84B;
    --cream:#F4EFE2;
    --cream-dim:#B9C1CE;
    --slate:#8996A9;
    --line:rgba(201,162,39,0.16);
    --line-soft:rgba(244,239,226,0.08);
    --success:#4FA97C;
    --success-bg:rgba(79,169,124,0.12);
    --danger:#C1554B;
    --danger-bg:rgba(193,85,75,0.12);
    --pending:#C9A227;
    --pending-bg:rgba(201,162,39,0.12);
    --radius-sm:8px;
    --radius-md:14px;
    --radius-lg:22px;
    --shadow:0 20px 60px -20px rgba(0,0,0,0.6);
    --ease:cubic-bezier(.4,0,.2,1);
  }

  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{
    background:var(--ink);
    color:var(--cream);
    font-family:'Inter',sans-serif;
    line-height:1.5;
    overflow-x:hidden;
    background-image:
      radial-gradient(ellipse 900px 500px at 15% -5%, rgba(201,162,39,0.10), transparent 60%),
      radial-gradient(ellipse 700px 500px at 100% 10%, rgba(79,169,124,0.06), transparent 55%);
  }
  h1,h2,h3,h4{font-family:'Space Grotesk',sans-serif;font-weight:600;letter-spacing:-0.02em;color:var(--cream);}
  a{color:inherit;text-decoration:none;}
  ul{list-style:none;}
  img{max-width:100%;display:block;}
  button{font-family:inherit;cursor:pointer;}
  .mono{font-family:'IBM Plex Mono',monospace;}
  .wrap{max-width:1180px;margin:0 auto;padding:0 32px;}
  ::selection{background:var(--brass);color:var(--ink);}

  .eyebrow{
    display:inline-flex;align-items:center;gap:8px;
    font-family:'IBM Plex Mono',monospace;
    font-size:12px;letter-spacing:0.14em;text-transform:uppercase;
    color:var(--brass-light);
    padding:6px 12px;
    border:1px solid var(--line);
    border-radius:999px;
    background:rgba(201,162,39,0.06);
  }
  .eyebrow::before{content:"";width:6px;height:6px;border-radius:50%;background:var(--brass);box-shadow:0 0 8px var(--brass);}

  /* ===== Buttons ===== */
  .btn{
    display:inline-flex;align-items:center;justify-content:center;gap:8px;
    padding:13px 24px;border-radius:10px;
    font-weight:600;font-size:15px;
    border:1px solid transparent;
    transition:transform .2s var(--ease), box-shadow .2s var(--ease), background .2s var(--ease), border-color .2s var(--ease);
    white-space:nowrap;
  }
  .btn-primary{
    background:linear-gradient(180deg,var(--brass-light),var(--brass));
    color:#1A1304;
    box-shadow:0 8px 24px -8px rgba(201,162,39,0.55);
  }
  .btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 30px -8px rgba(201,162,39,0.7);}
  .btn-ghost{
    background:transparent;border-color:var(--line-soft);color:var(--cream);
  }
  .btn-ghost:hover{border-color:var(--brass);color:var(--brass-light);}
  .btn-sm{padding:9px 16px;font-size:13.5px;}
  .btn:focus-visible{outline:2px solid var(--brass-light);outline-offset:3px;}

  /* ===== Nav ===== */
  header{
    position:sticky;top:0;z-index:100;
    background:rgba(10,18,32,0.82);
    backdrop-filter:blur(14px);
    border-bottom:1px solid var(--line-soft);
  }
  nav{display:flex;align-items:center;justify-content:space-between;padding:16px 32px;max-width:1180px;margin:0 auto;}
  .logo{display:flex;align-items:center;gap:10px;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:19px;}
  .logo-mark{
    width:34px;height:34px;border-radius:8px;
    background:linear-gradient(160deg,var(--brass-light),var(--brass));
    display:flex;align-items:center;justify-content:center;
    font-family:'IBM Plex Mono',monospace;font-weight:600;font-size:13px;color:#1A1304;
    box-shadow:0 4px 14px -4px rgba(201,162,39,0.6);
  }
  .nav-links{display:flex;align-items:center;gap:32px;}
  .nav-links a{font-size:14.5px;color:var(--cream-dim);font-weight:500;transition:color .2s;}
  .nav-links a:hover{color:var(--cream);}
  .nav-actions{display:flex;align-items:center;gap:12px;}
  .nav-burger{display:none;background:none;border:none;color:var(--cream);padding:6px;}
  .mobile-menu{display:none;flex-direction:column;gap:2px;padding:8px 32px 20px;border-top:1px solid var(--line-soft);}
  .mobile-menu.open{display:flex;}
  .mobile-menu a{padding:12px 0;color:var(--cream-dim);font-size:15px;border-bottom:1px solid var(--line-soft);}
  .mobile-actions{display:flex;gap:10px;margin-top:12px;}

  @media (max-width:860px){
    .nav-links{display:none;}
    .nav-actions .btn-ghost{display:none;}
    .nav-burger{display:flex;}
  }

  /* ===== Hero ===== */
  .hero{padding:76px 0 40px;position:relative;}
  .hero-grid{display:grid;grid-template-columns:1.05fr 0.95fr;gap:56px;align-items:center;}
  @media (max-width:980px){.hero-grid{grid-template-columns:1fr;}}

  .hero h1{font-size:clamp(34px,4.6vw,56px);line-height:1.06;margin:20px 0 18px;}
  .hero h1 .accent{color:var(--brass-light);}
  .hero-sub{font-size:16.5px;color:var(--cream-dim);max-width:480px;margin-bottom:14px;}

  .typewriter-line{
    font-family:'IBM Plex Mono',monospace;
    font-size:14.5px;color:var(--brass-light);
    min-height:22px;margin-bottom:30px;
    display:flex;align-items:center;
  }
  .typewriter-line .cursor{display:inline-block;width:8px;height:16px;background:var(--brass-light);margin-left:3px;animation:blink 1s step-end infinite;}
  @keyframes blink{50%{opacity:0;}}

  .hero-cta{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:34px;}
  .hero-trust{display:flex;align-items:center;gap:18px;flex-wrap:wrap;}
  .hero-trust span{font-size:13px;color:var(--slate);display:flex;align-items:center;gap:6px;}
  .hero-trust svg{width:15px;height:15px;stroke:var(--success);}

  /* ===== Live Gigs Board (signature element) ===== */
  .board{
    background:linear-gradient(180deg,var(--panel-2),var(--panel));
    border:1px solid var(--line);
    border-radius:var(--radius-lg);
    box-shadow:var(--shadow);
    overflow:hidden;
  }
  .board-head{
    display:flex;align-items:center;justify-content:space-between;
    padding:16px 20px;border-bottom:1px solid var(--line-soft);
  }
  .board-title{font-family:'IBM Plex Mono',monospace;font-size:12.5px;letter-spacing:0.1em;text-transform:uppercase;color:var(--brass-light);display:flex;align-items:center;gap:8px;}
  .board-title .dot{width:7px;height:7px;border-radius:50%;background:var(--success);box-shadow:0 0 8px var(--success);animation:pulse 1.8s ease-in-out infinite;}
  @keyframes pulse{0%,100%{opacity:1;}50%{opacity:.4;}}
  .board-cols{
    display:grid;grid-template-columns:2.1fr 1.15fr 1fr 0.95fr;
    padding:10px 20px;font-family:'IBM Plex Mono',monospace;font-size:10.5px;letter-spacing:0.08em;text-transform:uppercase;color:var(--slate);
    border-bottom:1px solid var(--line-soft);
  }
  .board-body{padding:4px 8px;}
  .board-row{
    display:grid;grid-template-columns:2.1fr 1.15fr 1fr 0.95fr;
    align-items:center;
    padding:13px 12px;
    border-bottom:1px solid rgba(244,239,226,0.05);
    perspective:600px;
  }
  .board-row:last-child{border-bottom:none;}
  .flip-inner{
    transform:rotateX(0deg);
    transform-origin:center;
    transition:transform .24s ease-in;
    font-size:13.5px;
  }
  .c-job{font-family:'Space Grotesk',sans-serif;font-weight:500;color:var(--cream);font-size:13.5px;}
  .c-area{color:var(--cream-dim);font-family:'IBM Plex Mono',monospace;font-size:12px;}
  .c-pay{font-family:'IBM Plex Mono',monospace;color:var(--brass-light);font-size:12.5px;}
  .status-chip{
    font-family:'IBM Plex Mono',monospace;font-size:10.5px;letter-spacing:0.06em;text-transform:uppercase;
    padding:4px 9px;border-radius:999px;display:inline-block;
    background:var(--success-bg);color:var(--success);
  }
  .board-foot{
    padding:14px 20px;border-top:1px solid var(--line-soft);
    display:flex;align-items:center;justify-content:space-between;
    font-family:'IBM Plex Mono',monospace;font-size:11.5px;color:var(--slate);
  }
  .board-foot a{color:var(--brass-light);font-weight:600;}

  /* ===== Marquee strip ===== */
  .strip{
    border-top:1px solid var(--line-soft);border-bottom:1px solid var(--line-soft);
    background:var(--ink-2);padding:14px 0;overflow:hidden;
  }
  .strip-track{display:flex;gap:48px;width:max-content;animation:scroll 26s linear infinite;}
  .strip-track span{font-family:'IBM Plex Mono',monospace;font-size:12.5px;color:var(--slate);white-space:nowrap;display:flex;align-items:center;gap:10px;}
  .strip-track span::before{content:"◆";color:var(--brass);font-size:9px;}
  @keyframes scroll{from{transform:translateX(0);}to{transform:translateX(-50%);}}

  /* ===== Section basics ===== */
  section{padding:96px 0;}
  .section-head{max-width:600px;margin-bottom:52px;}
  .section-head h2{font-size:clamp(26px,3.4vw,38px);margin:14px 0 12px;}
  .section-head p{color:var(--cream-dim);font-size:15.5px;}

  .reveal{opacity:0;transform:translateY(22px);transition:opacity .7s var(--ease), transform .7s var(--ease);}
  .reveal.is-visible{opacity:1;transform:translateY(0);}

  /* ===== How it works (tabs) ===== */
  .tabs{display:flex;gap:8px;margin-bottom:44px;background:var(--panel);border:1px solid var(--line-soft);border-radius:12px;padding:5px;width:fit-content;}
  .tab-btn{
    padding:10px 20px;border-radius:8px;background:none;border:none;color:var(--cream-dim);font-weight:600;font-size:14px;
    transition:background .2s,color .2s;
  }
  .tab-btn[aria-selected="true"]{background:linear-gradient(180deg,var(--brass-light),var(--brass));color:#1A1304;}

  .steps{display:none;grid-template-columns:repeat(4,1fr);gap:20px;}
  .steps.active{display:grid;}
  @media (max-width:900px){.steps{grid-template-columns:1fr 1fr;}}
  @media (max-width:560px){.steps{grid-template-columns:1fr;}}
  .step-card{
    background:var(--panel);border:1px solid var(--line-soft);border-radius:var(--radius-md);
    padding:26px 22px;position:relative;transition:border-color .25s,transform .25s;
  }
  .step-card:hover{border-color:var(--line);transform:translateY(-4px);}
  .step-num{font-family:'IBM Plex Mono',monospace;font-size:12px;color:var(--brass);letter-spacing:0.08em;margin-bottom:14px;}
  .step-card h4{font-size:16.5px;margin-bottom:8px;}
  .step-card p{font-size:13.8px;color:var(--slate);}

  /* ===== Features grid ===== */
  .features{
    display:grid;grid-template-columns:repeat(3,1fr);gap:2px;
    background:var(--line-soft);border:1px solid var(--line-soft);border-radius:var(--radius-lg);overflow:hidden;
  }
  @media (max-width:900px){.features{grid-template-columns:1fr 1fr;}}
  @media (max-width:600px){.features{grid-template-columns:1fr;}}
  .feature{
    background:var(--panel);padding:32px 28px;transition:background .25s;
  }
  .feature:hover{background:var(--panel-raised);}
  .feature-icon{width:38px;height:38px;margin-bottom:18px;color:var(--brass-light);}
  .feature-icon svg{width:100%;height:100%;}
  .feature h4{font-size:16px;margin-bottom:8px;}
  .feature p{font-size:13.6px;color:var(--slate);}

  /* ===== Categories ===== */
  .cat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;}
  @media (max-width:900px){.cat-grid{grid-template-columns:1fr 1fr;}}
  @media (max-width:560px){.cat-grid{grid-template-columns:1fr;}}
  .cat-card{
    background:var(--panel);border:1px solid var(--line-soft);border-radius:var(--radius-md);
    padding:26px;display:flex;flex-direction:column;gap:14px;
    transition:transform .25s,border-color .25s,box-shadow .25s;
  }
  .cat-card:hover{transform:translateY(-5px);border-color:var(--brass);box-shadow:0 16px 34px -18px rgba(201,162,39,0.4);}
  .cat-icon{
    width:46px;height:46px;border-radius:10px;background:rgba(201,162,39,0.1);
    display:flex;align-items:center;justify-content:center;color:var(--brass-light);
  }
  .cat-icon svg{width:22px;height:22px;}
  .cat-card h4{font-size:16px;}
  .cat-card p{font-size:13.3px;color:var(--slate);}

  /* ===== CTA band ===== */
  .cta-band{
    background:linear-gradient(135deg,var(--panel-2),var(--panel));
    border:1px solid var(--line);border-radius:var(--radius-lg);
    padding:56px 48px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;
    position:relative;overflow:hidden;
  }
  .cta-band::before{
    content:"";position:absolute;inset:0;
    background:radial-gradient(circle at 85% 20%, rgba(201,162,39,0.16), transparent 55%);
  }
  .cta-band h3{font-size:clamp(22px,2.8vw,30px);position:relative;max-width:460px;}
  .cta-band-actions{display:flex;gap:14px;position:relative;flex-wrap:wrap;}

  /* ===== Footer ===== */
  footer{border-top:1px solid var(--line-soft);padding:60px 0 32px;background:var(--ink-2);}
  .foot-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:40px;margin-bottom:48px;}
  @media (max-width:700px){.foot-grid{grid-template-columns:1fr;gap:32px;}}
  .foot-brand p{color:var(--slate);font-size:13.8px;margin-top:12px;max-width:280px;}
  .foot-col h5{font-family:'IBM Plex Mono',monospace;font-size:11.5px;letter-spacing:0.1em;text-transform:uppercase;color:var(--brass-light);margin-bottom:16px;}
  .foot-col a{display:block;color:var(--cream-dim);font-size:14px;margin-bottom:11px;transition:color .2s;}
  .foot-col a:hover{color:var(--cream);}
  .foot-bottom{
    display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;
    padding-top:26px;border-top:1px solid var(--line-soft);
    font-size:12.5px;color:var(--slate);
  }
  .foot-badge{font-family:'IBM Plex Mono',monospace;color:var(--success);}

  /* ===== Language switcher ===== */
  .lang-switch{
    display:flex;align-items:center;gap:2px;
    background:var(--panel);border:1px solid var(--line-soft);border-radius:999px;padding:3px;
  }
  .lang-btn{
    font-family:'IBM Plex Mono',monospace;font-size:11.5px;font-weight:600;letter-spacing:0.03em;
    color:var(--slate);background:none;border:none;padding:6px 10px;border-radius:999px;
    transition:background .2s,color .2s;
  }
  .lang-btn.active{background:linear-gradient(180deg,var(--brass-light),var(--brass));color:#1A1304;}
  .lang-btn:hover:not(.active){color:var(--cream);}
  .mobile-lang{display:flex;align-items:center;gap:10px;margin-top:14px;}
  .mobile-lang .lang-switch{flex:1;justify-content:center;}

  @media (prefers-reduced-motion:reduce){
    *{animation-duration:0.001ms !important;animation-iteration-count:1 !important;transition-duration:0.001ms !important;scroll-behavior:auto !important;}
  }
</style>
</head>
<body>

<header>
  <nav>
    <a href="#" class="logo"><span class="logo-mark">GB</span>GigBoard</a>
    <div class="nav-links">
      <a href="#how-it-works" data-i18n="nav_how">How it works</a>
      <a href="#features" data-i18n="nav_features">Features</a>
      <a href="#categories" data-i18n="nav_gigs">Gigs board</a>
    </div>
    <div class="nav-actions">
      <div class="lang-switch" id="langSwitch" role="group" aria-label="Language">
        <button class="lang-btn active" data-lang="en">EN</button>
        <button class="lang-btn" data-lang="rw">RW</button>
        <button class="lang-btn" data-lang="fr">FR</button>
      </div>
      <a href="login.php" class="btn btn-ghost btn-sm" data-i18n="btn_login">Log in</a>
      <a href="register.php" class="btn btn-primary btn-sm" data-i18n="btn_register">Register</a>
    </div>
    <button class="nav-burger" id="burgerBtn" aria-label="Open menu" aria-expanded="false">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
  </nav>
  <div class="mobile-menu" id="mobileMenu">
    <a href="#how-it-works" data-i18n="nav_how">How it works</a>
    <a href="#features" data-i18n="nav_features">Features</a>
    <a href="#categories" data-i18n="nav_gigs">Gigs board</a>
    <div class="mobile-lang">
      <div class="lang-switch" id="langSwitchMobile" role="group" aria-label="Language">
        <button class="lang-btn active" data-lang="en">EN</button>
        <button class="lang-btn" data-lang="rw">RW</button>
        <button class="lang-btn" data-lang="fr">FR</button>
      </div>
    </div>
    <div class="mobile-actions">
      <a href="login.php" class="btn btn-ghost btn-sm" style="flex:1" data-i18n="btn_login">Log in</a>
      <a href="register.php" class="btn btn-primary btn-sm" style="flex:1" data-i18n="btn_register">Register</a>
    </div>
  </div>
</header>

<main>
  <section class="hero">
    <div class="wrap hero-grid">
      <div>
        <span class="eyebrow" data-i18n="hero_eyebrow">Kigali · Short-term work · 100% free</span>
        <h1 data-i18n="hero_h1">Short gigs.<br>Real pay.<br><span class="accent">No fees, ever.</span></h1>
        <p class="hero-sub" data-i18n="hero_sub">Employers post a gig in under a minute. Workers apply in one tap, track every application, and open a private chat the moment they're confirmed.</p>
        <div class="typewriter-line mono"><span id="typewriter"></span><span class="cursor"></span></div>
        <div class="hero-cta">
          <a href="login.php" class="btn btn-primary" data-i18n="btn_start">Start now</a>
          <a href="#categories" class="btn btn-ghost" data-i18n="btn_seegigs">See open gigs</a>
        </div>
        <div class="hero-trust">
          <span><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg><span data-i18n="trust_1">No commission, ever</span></span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg><span data-i18n="trust_2">Chat unlocks after confirmation</span></span>
        </div>
      </div>

      <div class="board reveal">
        <div class="board-head">
          <span class="board-title"><span class="dot"></span><span data-i18n="board_title">Live gigs board</span></span>
          <span class="mono" style="font-size:11px;color:var(--slate);">Kigali</span>
        </div>
        <div class="board-cols">
          <span data-i18n="board_col_gig">Gig</span><span data-i18n="board_col_area">Area</span><span data-i18n="board_col_pay">Pay (RWF)</span><span data-i18n="board_col_status">Status</span>
        </div>
        <div class="board-body" id="boardBody"></div>
        <div class="board-foot">
          <span data-i18n="board_updated">Updated moments ago</span>
          <a href="register.php" data-i18n="board_post">Post a gig →</a>
        </div>
      </div>
    </div>
  </section>

  <div class="strip">
    <div class="strip-track" id="stripTrack"></div>
  </div>

  <section id="how-it-works">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow" data-i18n="how_eyebrow">How it works</span>
        <h2 data-i18n="how_h2">Two sides, one simple flow.</h2>
        <p data-i18n="how_p">Whether you're hiring for the afternoon or looking for your next few hours of paid work, GigBoard keeps it to four steps.</p>
      </div>

      <div class="tabs" role="tablist">
        <button class="tab-btn" role="tab" aria-selected="true" data-tab="employer" data-i18n="tab_employer">I need help</button>
        <button class="tab-btn" role="tab" aria-selected="false" data-tab="worker" data-i18n="tab_worker">I want work</button>
      </div>

      <div class="steps active reveal" data-panel="employer">
        <div class="step-card"><div class="step-num">01</div><h4 data-i18n="emp1_h">Post your gig</h4><p data-i18n="emp1_p">Describe the job, pick an area, set the pay. Live on the board in under a minute.</p></div>
        <div class="step-card"><div class="step-num">02</div><h4 data-i18n="emp2_h">Review applicants</h4><p data-i18n="emp2_p">See who applied, at a glance, as applications come in.</p></div>
        <div class="step-card"><div class="step-num">03</div><h4 data-i18n="emp3_h">Confirm or decline</h4><p data-i18n="emp3_p">Pick the right person for the job. Everyone else is notified instantly.</p></div>
        <div class="step-card"><div class="step-num">04</div><h4 data-i18n="emp4_h">Chat privately</h4><p data-i18n="emp4_p">Coordinate time, place, and details once someone's confirmed.</p></div>
      </div>

      <div class="steps" data-panel="worker">
        <div class="step-card"><div class="step-num">01</div><h4 data-i18n="wk1_h">Browse gigs</h4><p data-i18n="wk1_p">Filter by area and pay. New gigs go up every day.</p></div>
        <div class="step-card"><div class="step-num">02</div><h4 data-i18n="wk2_h">Apply in one tap</h4><p data-i18n="wk2_p">No forms to fill out twice. Your profile does the work.</p></div>
        <div class="step-card"><div class="step-num">03</div><h4 data-i18n="wk3_h">Track your status</h4><p data-i18n="wk3_p">Pending, confirmed, or declined — always visible, never a guessing game.</p></div>
        <div class="step-card"><div class="step-num">04</div><h4 data-i18n="wk4_h">Chat once confirmed</h4><p data-i18n="wk4_p">Sort out the details directly with the employer, privately.</p></div>
      </div>
    </div>
  </section>

  <section id="features">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow" data-i18n="feat_eyebrow">Why GigBoard</span>
        <h2 data-i18n="feat_h2">Built to actually get you paid.</h2>
        <p data-i18n="feat_p">No subscriptions, no commission cuts, no locked features. Just a straight line from gig posted to gig done.</p>
      </div>

      <div class="features reveal">
        <div class="feature">
          <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
          <h4 data-i18n="f1_h">Always free</h4><p data-i18n="f1_p">No commission on any gig, no hidden charges, no premium tier. Free for workers and employers, permanently.</p>
        </div>
        <div class="feature">
          <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 11l3 3L22 4M21 12v6a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h11"/></svg></div>
          <h4 data-i18n="f2_h">Application tracking</h4><p data-i18n="f2_p">Every application shows a clear status — pending, confirmed, or declined — so nobody's left wondering.</p>
        </div>
        <div class="feature">
          <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg></div>
          <h4 data-i18n="f3_h">Confirm or decline</h4><p data-i18n="f3_p">Employers choose who fits the job. Workers know the outcome the moment it happens.</p>
        </div>
        <div class="feature">
          <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg></div>
          <h4 data-i18n="f4_h">Private chat</h4><p data-i18n="f4_p">Contact details and messaging stay locked until a gig is confirmed on both sides.</p>
        </div>
        <div class="feature">
          <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21c-4.5-3.5-8-7.2-8-11.2A8 8 0 0112 2a8 8 0 018 7.8c0 4-3.5 7.7-8 11.2z"/><circle cx="12" cy="9.8" r="2.5"/></svg></div>
          <h4 data-i18n="f5_h">Built for Kigali</h4><p data-i18n="f5_p">Post and search by neighborhood — Kacyiru, Remera, Nyamirambo, Kimironko — not zip codes.</p>
        </div>
        <div class="feature">
          <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M13 2L3 14h8l-1 8 11-14h-8l1-6z"/></svg></div>
          <h4 data-i18n="f6_h">Fast, both ways</h4><p data-i18n="f6_p">A gig can be live in under a minute. Applying takes one tap.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="categories">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow" data-i18n="cat_eyebrow">What's out there</span>
        <h2 data-i18n="cat_h2">Every kind of short gig.</h2>
        <p data-i18n="cat_p">A sample of what's typically posted on the board — new categories show up as employers need them.</p>
      </div>

      <div class="cat-grid reveal">
        <div class="cat-card">
          <div class="cat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path d="M3.27 6.96L12 12l8.73-5.04M12 22.08V12"/></svg></div>
          <h4 data-i18n="c1_h">Moving &amp; delivery</h4><p data-i18n="c1_p">Furniture, boxes, motorbike delivery runs across the city.</p>
        </div>
        <div class="cat-card">
          <div class="cat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 21h18M6 21V9l6-6 6 6v12M10 21v-6h4v6"/></svg></div>
          <h4 data-i18n="c2_h">Cleaning</h4><p data-i18n="c2_p">Homes, offices, and move-out cleaning, by the hour or the job.</p>
        </div>
        <div class="cat-card">
          <div class="cat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
          <h4 data-i18n="c3_h">Events &amp; catering</h4><p data-i18n="c3_p">Setup, service, and cleanup for weddings, parties, and functions.</p>
        </div>
        <div class="cat-card">
          <div class="cat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 22s8-4.5 8-11.8A8 8 0 004 10.2C4 17.5 12 22 12 22z"/><path d="M12 6v8M9 11h6"/></svg></div>
          <h4 data-i18n="c4_h">Gardening &amp; outdoor</h4><p data-i18n="c4_p">Yard work, planting, and general outdoor upkeep.</p>
        </div>
        <div class="cat-card">
          <div class="cat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/></svg></div>
          <h4 data-i18n="c5_h">Painting &amp; repairs</h4><p data-i18n="c5_p">Small painting jobs, fixes, and general handywork.</p>
        </div>
        <div class="cat-card">
          <div class="cat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg></div>
          <h4 data-i18n="c6_h">Tutoring &amp; errands</h4><p data-i18n="c6_p">Homework help, translation, shopping, and quick errands.</p>
        </div>
      </div>
    </div>
  </section>

  <section>
    <div class="wrap">
      <div class="cta-band reveal">
        <h3 data-i18n="cta_h3">Your next gig is one tap away.</h3>
        <div class="cta-band-actions">
          <a href="login.php" class="btn btn-primary" data-i18n="btn_start">Start now</a>
          <a href="register.php" class="btn btn-ghost" data-i18n="btn_createacc">Create an account</a>
        </div>
      </div>
    </div>
  </section>
</main>

<footer>
  <div class="wrap">
    <div class="foot-grid">
      <div class="foot-brand">
        <a href="#" class="logo"><span class="logo-mark">GB</span>GigBoard</a>
        <p data-i18n="foot_tagline">Connecting employers and workers for short-term gigs across Kigali. Post it, apply, confirm, and chat — no fees, no middleman.</p>
      </div>
      <div class="foot-col">
        <h5 data-i18n="foot_platform">Platform</h5>
        <a href="#how-it-works" data-i18n="nav_how">How it works</a>
        <a href="#categories" data-i18n="nav_gigs">Gigs board</a>
        <a href="#features" data-i18n="nav_features">Features</a>
      </div>
      <div class="foot-col">
        <h5 data-i18n="foot_account">Account</h5>
        <a href="login.php" data-i18n="btn_login">Log in</a>
        <a href="register.php" data-i18n="btn_register">Register</a>
      </div>
    </div>
    <div class="foot-bottom">
      <span data-i18n="foot_copy">© 2026 GigBoard · Kigali, Rwanda</span>
      <span class="foot-badge" data-i18n="foot_badge">● 100% free — no commission, ever</span>
    </div>
  </div>
</footer>

<script>
(function(){
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ================= i18n ================= */
  var translations = {
    en: {
      nav_how:'How it works', nav_features:'Features', nav_gigs:'Gigs board',
      btn_login:'Log in', btn_register:'Register', btn_start:'Start now', btn_seegigs:'See open gigs', btn_createacc:'Create an account',
      hero_eyebrow:'Kigali · Short-term work · 100% free',
      hero_h1:'Short gigs.<br>Real pay.<br><span class="accent">No fees, ever.</span>',
      hero_sub:"Employers post a gig in under a minute. Workers apply in one tap, track every application, and open a private chat the moment they're confirmed.",
      trust_1:'No commission, ever', trust_2:'Chat unlocks after confirmation',
      board_title:'Live gigs board', board_col_gig:'Gig', board_col_area:'Area', board_col_pay:'Pay (RWF)', board_col_status:'Status',
      board_updated:'Updated moments ago', board_post:'Post a gig →',
      how_eyebrow:'How it works', how_h2:'Two sides, one simple flow.',
      how_p:"Whether you're hiring for the afternoon or looking for your next few hours of paid work, GigBoard keeps it to four steps.",
      tab_employer:'I need help', tab_worker:'I want work',
      emp1_h:'Post your gig', emp1_p:'Describe the job, pick an area, set the pay. Live on the board in under a minute.',
      emp2_h:'Review applicants', emp2_p:'See who applied, at a glance, as applications come in.',
      emp3_h:'Confirm or decline', emp3_p:'Pick the right person for the job. Everyone else is notified instantly.',
      emp4_h:'Chat privately', emp4_p:"Coordinate time, place, and details once someone's confirmed.",
      wk1_h:'Browse gigs', wk1_p:'Filter by area and pay. New gigs go up every day.',
      wk2_h:'Apply in one tap', wk2_p:'No forms to fill out twice. Your profile does the work.',
      wk3_h:'Track your status', wk3_p:'Pending, confirmed, or declined — always visible, never a guessing game.',
      wk4_h:'Chat once confirmed', wk4_p:'Sort out the details directly with the employer, privately.',
      feat_eyebrow:'Why GigBoard', feat_h2:'Built to actually get you paid.',
      feat_p:'No subscriptions, no commission cuts, no locked features. Just a straight line from gig posted to gig done.',
      f1_h:'Always free', f1_p:'No commission on any gig, no hidden charges, no premium tier. Free for workers and employers, permanently.',
      f2_h:'Application tracking', f2_p:"Every application shows a clear status — pending, confirmed, or declined — so nobody's left wondering.",
      f3_h:'Confirm or decline', f3_p:'Employers choose who fits the job. Workers know the outcome the moment it happens.',
      f4_h:'Private chat', f4_p:'Contact details and messaging stay locked until a gig is confirmed on both sides.',
      f5_h:'Built for Kigali', f5_p:'Post and search by neighborhood — Kacyiru, Remera, Nyamirambo, Kimironko — not zip codes.',
      f6_h:'Fast, both ways', f6_p:'A gig can be live in under a minute. Applying takes one tap.',
      cat_eyebrow:"What's out there", cat_h2:'Every kind of short gig.',
      cat_p:"A sample of what's typically posted on the board — new categories show up as employers need them.",
      c1_h:'Moving &amp; delivery', c1_p:'Furniture, boxes, motorbike delivery runs across the city.',
      c2_h:'Cleaning', c2_p:'Homes, offices, and move-out cleaning, by the hour or the job.',
      c3_h:'Events &amp; catering', c3_p:'Setup, service, and cleanup for weddings, parties, and functions.',
      c4_h:'Gardening &amp; outdoor', c4_p:'Yard work, planting, and general outdoor upkeep.',
      c5_h:'Painting &amp; repairs', c5_p:'Small painting jobs, fixes, and general handywork.',
      c6_h:'Tutoring &amp; errands', c6_p:'Homework help, translation, shopping, and quick errands.',
      cta_h3:'Your next gig is one tap away.',
      foot_tagline:'Connecting employers and workers for short-term gigs across Kigali. Post it, apply, confirm, and chat — no fees, no middleman.',
      foot_platform:'Platform', foot_account:'Account',
      foot_copy:'© 2026 GigBoard · Kigali, Rwanda', foot_badge:'● 100% free — no commission, ever'
    },
    rw: {
      nav_how:'Uko bikora', nav_features:'Ibiranga', nav_gigs:'Imirimo ihari',
      btn_login:'Injira', btn_register:'Iyandikishe', btn_start:'Tangira nonaha', btn_seegigs:'Reba imirimo ihari', btn_createacc:'Fungura konti',
      hero_eyebrow:'Kigali · Imirimo y\'igihe gito · 100% ku buntu',
      hero_h1:'Imirimo migufi.<br>Ubwishyu nyabwo.<br><span class="accent">Nta mafaranga y\'ikoreshwa.</span>',
      hero_sub:'Abakoresha batangaza umurimo mu munota umwe. Abakozi basaba mu kanya gato, bakurikirana ubusabe bwabo, kandi bafungura ikiganiro cyihariye igihe basabwe bemejwe.',
      trust_1:'Nta magenzura y\'amafaranga', trust_2:'Ikiganiro gifungura nyuma yo kwemezwa',
      board_title:'Imirimo ihari ubu', board_col_gig:'Umurimo', board_col_area:'Aho biherereye', board_col_pay:'Ubwishyu (RWF)', board_col_status:'Uko bimeze',
      board_updated:'Byavuguruwe vuba', board_post:'Tangaza umurimo →',
      how_eyebrow:'Uko bikora', how_h2:'Impande ebyiri, uburyo bumwe bworoshye.',
      how_p:'Waba ushaka umukozi w\'igihe gito cyangwa ushaka umurimo w\'amasaha make, GigBoard igukoresha intambwe enye gusa.',
      tab_employer:'Nkeneye umukozi', tab_worker:'Nshaka umurimo',
      emp1_h:'Tangaza umurimo wawe', emp1_p:'Sobanura umurimo, hitamo aho uherereye, ushyireho ubwishyu. Ugaragara ku rutonde mu munota umwe.',
      emp2_h:'Reba abasabye', emp2_p:'Reba abasabye umurimo uko babyandikira.',
      emp3_h:'Emeza cyangwa wange', emp3_p:'Hitamo umuntu ukwiye umurimo. Abandi bose bamenyeshwa ako kanya.',
      emp4_h:'Ganira mu ibanga', emp4_p:'Wumvikane ku gihe, ahantu, n\'ibindi bisobanuro nyuma yo kwemeza umukozi.',
      wk1_h:'Reba imirimo ihari', wk1_p:'Shakisha ukurikije aho biherereye n\'ubwishyu. Imirimo mishya ijya iyongerwa buri munsi.',
      wk2_h:'Saba mu kanya gato', wk2_p:'Nta fomu wongera kwuzuza. Umwirondoro wawe ni wo ukora akazi.',
      wk3_h:'Kurikirana uko ubusabe bwawe bumeze', wk3_p:'Bitegerejwe, byemejwe, cyangwa byanzwe — ubona byose ako kanya, nta gutegereza mu bwoba.',
      wk4_h:'Ganira nyuma yo kwemezwa', wk4_p:'Wumvikane ku byerekeye umurimo n\'umukoresha, mu ibanga.',
      feat_eyebrow:'Impamvu GigBoard', feat_h2:'Yubatswe kugira ngo uhembwe by\'ukuri.',
      feat_p:'Nta bwishyu bwa buri kwezi, nta magenzura y\'amafaranga, nta bintu bihishwe. Uva aho utangaje umurimo ukagera aho urangiza.',
      f1_h:'Ku buntu burigihe', f1_p:'Nta magenzura ku murimo n\'umwe, nta mafaranga yihishe, nta rwego rwo kwishyura. Ku buntu ku bakozi n\'abakoresha, burigihe.',
      f2_h:'Gukurikirana ubusabe', f2_p:'Buri busabe bugaragaza uko bumeze — bitegerejwe, byemejwe, cyangwa byanzwe — nta muntu usigara atazi.',
      f3_h:'Kwemeza cyangwa kwanga', f3_p:'Abakoresha bahitamo uwabakwiriye. Abakozi bamenya ibisubizo ako kanya.',
      f4_h:'Ikiganiro cyihariye', f4_p:'Amakuru yo kuvugana n\'ubutumwa bihishwa kugeza igihe umurimo wemejwe n\'impande zombi.',
      f5_h:'Yubakiwe Kigali', f5_p:'Tangaza kandi ushakishe ukurikije akagari — Kacyiru, Remera, Nyamirambo, Kimironko — atari kode.',
      f6_h:'Byihuta ku mpande zombi', f6_p:'Umurimo ushobora kugaragara mu munota umwe. Gusaba bifata agahe gato.',
      cat_eyebrow:'Ibiboneka', cat_h2:'Buri bwoko bw\'umurimo mugufi.',
      cat_p:'Ingero z\'ibisanzwe bitangazwa ku rutonde — ibindi byiyongera uko abakoresha babikeneye.',
      c1_h:'Kwimura no gutwara ibintu', c1_p:'Ibikoresho byo mu rugo, udusanduku, no gutwara ibintu mu mujyi.',
      c2_h:'Gusukura', c2_p:'Amazu, ibiro, no gusukura igihe uvuye mu nzu, ku isaha cyangwa ku murimo.',
      c3_h:'Ibirori n\'ibyokurya', c3_p:'Gutegura, gukorera, no gusukura nyuma y\'ibirori n\'ubukwe.',
      c4_h:'Ubusitani n\'imirimo yo hanze', c4_p:'Imirimo yo mu busitani, gutera ibimera, no kwita ku isuku hanze.',
      c5_h:'Gusiga irangi no gusana', c5_p:'Imirimo mito yo gusiga irangi, gusana, n\'indi mirimo y\'intoki.',
      c6_h:'Kwigisha no gutwara ubutumwa', c6_p:'Gufasha amashuri, guhindura ururimi, kugura ibintu, n\'izindi nshingano ngufi.',
      cta_h3:'Umurimo ukurikira uri ku kanya kamwe.',
      foot_tagline:'Duhuza abakoresha n\'abakozi ku mirimo migufi hirya no hino i Kigali. Tangaza, saba, wemeze, uganire — nta mafaranga, nta wundi uhagarariye.',
      foot_platform:'Urubuga', foot_account:'Konti',
      foot_copy:'© 2026 GigBoard · Kigali, u Rwanda', foot_badge:'● 100% ku buntu — nta magenzura'
    },
    fr: {
      nav_how:'Comment ça marche', nav_features:'Fonctionnalités', nav_gigs:'Missions disponibles',
      btn_login:'Connexion', btn_register:"S'inscrire", btn_start:'Commencer', btn_seegigs:'Voir les missions', btn_createacc:'Créer un compte',
      hero_eyebrow:'Kigali · Emplois de courte durée · 100% gratuit',
      hero_h1:'Missions courtes.<br>Paiement réel.<br><span class="accent">Sans frais, jamais.</span>',
      hero_sub:'Les employeurs publient une mission en moins d\'une minute. Les travailleurs postulent en un clic, suivent chaque candidature, et ouvrent un chat privé dès la confirmation.',
      trust_1:'Aucune commission, jamais', trust_2:'Le chat s\'ouvre après confirmation',
      board_title:'Missions en direct', board_col_gig:'Mission', board_col_area:'Quartier', board_col_pay:'Paie (RWF)', board_col_status:'Statut',
      board_updated:'Mis à jour à l\'instant', board_post:'Publier une mission →',
      how_eyebrow:'Comment ça marche', how_h2:'Deux profils, un seul parcours simple.',
      how_p:'Que vous cherchiez de l\'aide pour l\'après-midi ou quelques heures de travail rémunéré, GigBoard tient en quatre étapes.',
      tab_employer:"J'ai besoin d'aide", tab_worker:'Je cherche du travail',
      emp1_h:'Publiez votre mission', emp1_p:'Décrivez le travail, choisissez un quartier, fixez la paie. En ligne en moins d\'une minute.',
      emp2_h:'Consultez les candidats', emp2_p:'Voyez qui a postulé, au fur et à mesure des candidatures.',
      emp3_h:'Confirmez ou refusez', emp3_p:'Choisissez la bonne personne pour le poste. Les autres sont notifiés instantanément.',
      emp4_h:'Discutez en privé', emp4_p:'Coordonnez horaire, lieu et détails une fois la personne confirmée.',
      wk1_h:'Parcourez les missions', wk1_p:'Filtrez par quartier et par paie. De nouvelles missions chaque jour.',
      wk2_h:'Postulez en un clic', wk2_p:'Pas de formulaire à remplir deux fois. Votre profil fait le travail.',
      wk3_h:'Suivez votre statut', wk3_p:'En attente, confirmé ou refusé — toujours visible, jamais à deviner.',
      wk4_h:'Discutez après confirmation', wk4_p:'Réglez les détails directement avec l\'employeur, en privé.',
      feat_eyebrow:'Pourquoi GigBoard', feat_h2:'Conçu pour vraiment vous faire payer.',
      feat_p:'Pas d\'abonnement, pas de commission, pas de fonctionnalité verrouillée. Juste une ligne droite entre la mission publiée et la mission terminée.',
      f1_h:'Toujours gratuit', f1_p:'Aucune commission sur les missions, aucun frais caché, aucun palier payant. Gratuit pour les travailleurs et les employeurs, en permanence.',
      f2_h:'Suivi des candidatures', f2_p:'Chaque candidature affiche un statut clair — en attente, confirmé ou refusé — sans jamais laisser de doute.',
      f3_h:'Confirmer ou refuser', f3_p:'Les employeurs choisissent qui convient. Les travailleurs connaissent le résultat aussitôt.',
      f4_h:'Chat privé', f4_p:'Les coordonnées et les messages restent verrouillés jusqu\'à la confirmation des deux côtés.',
      f5_h:'Pensé pour Kigali', f5_p:'Publiez et cherchez par quartier — Kacyiru, Remera, Nyamirambo, Kimironko — pas par code postal.',
      f6_h:'Rapide, dans les deux sens', f6_p:'Une mission peut être en ligne en moins d\'une minute. Postuler prend un instant.',
      cat_eyebrow:'Ce que l\'on trouve', cat_h2:'Tous les types de missions courtes.',
      cat_p:'Un aperçu de ce qui est habituellement publié — de nouvelles catégories apparaissent selon les besoins des employeurs.',
      c1_h:'Déménagement et livraison', c1_p:'Meubles, cartons, courses de livraison en moto dans toute la ville.',
      c2_h:'Ménage', c2_p:'Maisons, bureaux, et nettoyage de fin de bail, à l\'heure ou à la tâche.',
      c3_h:'Événements et traiteur', c3_p:'Installation, service et nettoyage pour mariages, fêtes et cérémonies.',
      c4_h:'Jardinage et extérieur', c4_p:'Travaux de jardin, plantation, et entretien général des extérieurs.',
      c5_h:'Peinture et réparations', c5_p:'Petits travaux de peinture, réparations, et bricolage général.',
      c6_h:'Soutien scolaire et courses', c6_p:'Aide aux devoirs, traduction, courses, et petites tâches rapides.',
      cta_h3:'Votre prochaine mission est à portée de clic.',
      foot_tagline:'Connecte employeurs et travailleurs pour des missions courtes à Kigali. Publiez, postulez, confirmez, discutez — sans frais, sans intermédiaire.',
      foot_platform:'Plateforme', foot_account:'Compte',
      foot_copy:'© 2026 GigBoard · Kigali, Rwanda', foot_badge:'● 100% gratuit — aucune commission'
    }
  };

  var boardTextByLang = {
    en: {status:'OPEN', jobs:{
      house_cleaning:'House cleaning', furniture_moving:'Furniture moving', event_waiter:'Event waiter', delivery_rider:'Delivery rider',
      garden_cleanup:'Garden cleanup', painting_helper:'Painting helper', photography:'Photography assist', construction:'Construction help',
      tutoring:'Tutoring, 2 hrs', errand:'Errand runner', catering:'Catering setup', office_cleaning:'Office cleaning'
    }},
    rw: {status:'BIRAKENEWE', jobs:{
      house_cleaning:'Gusukura inzu', furniture_moving:'Kwimura ibikoresho', event_waiter:'Gukorera ku birori', delivery_rider:'Gutwara ibintu',
      garden_cleanup:'Gusukura ubusitani', painting_helper:'Gufasha gusiga irangi', photography:'Gufasha gufotora', construction:'Gufasha kwubaka',
      tutoring:'Kwigisha, amasaha 2', errand:'Gutwara ubutumwa', catering:'Gutegura ibyokurya', office_cleaning:'Gusukura ibiro'
    }},
    fr: {status:'OUVERT', jobs:{
      house_cleaning:'Ménage à domicile', furniture_moving:'Déménagement de meubles', event_waiter:'Service événementiel', delivery_rider:'Livraison à moto',
      garden_cleanup:'Nettoyage de jardin', painting_helper:'Aide peinture', photography:'Assistant photographe', construction:'Aide construction',
      tutoring:'Soutien scolaire, 2h', errand:'Courses diverses', catering:'Installation traiteur', office_cleaning:'Ménage de bureau'
    }}
  };
  var boardAreas = ['Kacyiru','Remera','Kimironko','Nyamirambo','Kicukiro','Gikondo'];
  var altBoardAreas = ['Kimihurura','Gisozi','Kicukiro','Nyarutarama','Remera','Kacyiru'];
  var boardJobKeys = ['house_cleaning','furniture_moving','event_waiter','delivery_rider','garden_cleanup','painting_helper'];
  var altBoardJobKeys = ['photography','construction','tutoring','errand','catering','office_cleaning'];
  var boardPays = ['8,000','15,000','10,000','6,000','9,000','12,000'];
  var altBoardPays = ['14,000','11,000','7,000','5,000','13,000','8,500'];

  var currentLang = localStorage.getItem('gb_lang') || 'en';
  var boardToggleState = [];

  function applyStaticText(lang){
    document.documentElement.lang = lang;
    var dict = translations[lang] || translations.en;
    document.querySelectorAll('[data-i18n]').forEach(function(el){
      var key = el.getAttribute('data-i18n');
      if(dict[key] !== undefined){ el.innerHTML = dict[key]; }
    });
    document.querySelectorAll('.lang-btn').forEach(function(b){
      b.classList.toggle('active', b.getAttribute('data-lang') === lang);
    });
  }

  function buildBoardRow(i, useAlt){
    var lang = currentLang;
    var jobKeys = useAlt ? altBoardJobKeys : boardJobKeys;
    var areas = useAlt ? altBoardAreas : boardAreas;
    var pays = useAlt ? altBoardPays : boardPays;
    return {
      job: boardTextByLang[lang].jobs[jobKeys[i]],
      area: areas[i],
      pay: pays[i],
      status: boardTextByLang[lang].status
    };
  }

  function refreshBoardRows(){
    document.querySelectorAll('.board-row').forEach(function(row, i){
      var data = buildBoardRow(i, boardToggleState[i]);
      row.querySelector('.c-job').textContent = data.job;
      row.querySelector('.c-area').textContent = data.area;
      row.querySelector('.c-pay').textContent = data.pay;
      var chip = row.querySelector('.status-chip');
      if(chip){ chip.textContent = data.status; }
    });
  }

  var typewriterTimeouts = [];
  function clearTypewriter(){ typewriterTimeouts.forEach(clearTimeout); typewriterTimeouts = []; }
  function startTypewriter(){
    clearTypewriter();
    var twEl = document.getElementById('typewriter');
    var phraseKeys = {
      en:['Furniture move needed in Kacyiru — today','Waiter needed for event in Remera — tonight','Delivery rider needed in Kimironko — this afternoon','Garden cleanup in Nyamirambo — this weekend','Tutor needed in Kicukiro — 2 hours, evenings'],
      rw:['Bakeneye kwimura ibikoresho i Kacyiru — uyu munsi','Bakeneye umukozi w\'ibirori i Remera — none nijoro','Bakeneye utwara ibintu i Kimironko — nimugoroba','Gusukura ubusitani i Nyamirambo — iki cyumweru','Bakeneye umwigisha i Kicukiro — amasaha 2, nimugoroba'],
      fr:['Déménagement demandé à Kacyiru — aujourd\'hui','Serveur demandé pour un événement à Remera — ce soir','Livreur demandé à Kimironko — cet après-midi','Nettoyage de jardin à Nyamirambo — ce week-end','Professeur demandé à Kicukiro — 2h, en soirée']
    };
    var phrases = phraseKeys[currentLang] || phraseKeys.en;
    if(reduceMotion){
      twEl.textContent = phrases[0];
      return;
    }
    var pIndex = 0;
    function cycle(){
      var phrase = phrases[pIndex % phrases.length];
      var charIndex = 0;
      var typing = setInterval(function(){
        twEl.textContent = phrase.slice(0, charIndex+1);
        charIndex++;
        if(charIndex === phrase.length){
          clearInterval(typing);
          var t1 = setTimeout(function(){
            var deleting = setInterval(function(){
              charIndex--;
              twEl.textContent = phrase.slice(0, charIndex);
              if(charIndex <= 0){
                clearInterval(deleting);
                pIndex++;
                var t2 = setTimeout(cycle, 300);
                typewriterTimeouts.push(t2);
              }
            }, 22);
          }, 1700);
          typewriterTimeouts.push(t1);
        }
      }, 38);
    }
    cycle();
  }

  function refreshStrip(){
    var stripByLang = {
      en:['No commission on any gig','Applications tracked in real time','Private chat unlocks on confirmation','New gigs posted daily across Kigali','Free for workers, free for employers','From posted to confirmed in minutes'],
      rw:['Nta magenzura ku murimo n\'umwe','Ubusabe bukurikiranwa ako kanya','Ikiganiro cyihariye gifungura nyuma yo kwemeza','Imirimo mishya buri munsi hirya no hino i Kigali','Ku buntu ku bakozi n\'abakoresha','Uva aho watangaje ukagera aho wemeza mu minota mike'],
      fr:['Aucune commission sur les missions','Candidatures suivies en temps réel','Le chat privé s\'ouvre après confirmation','De nouvelles missions publiées chaque jour à Kigali','Gratuit pour les travailleurs, gratuit pour les employeurs','De la publication à la confirmation en quelques minutes']
    };
    var items = stripByLang[currentLang] || stripByLang.en;
    var track = document.getElementById('stripTrack');
    var html = '';
    for(var r=0;r<2;r++){
      items.forEach(function(t){ html += '<span>'+t+'</span>'; });
    }
    track.innerHTML = html;
  }

  function setLanguage(lang){
    if(!translations[lang]) return;
    currentLang = lang;
    localStorage.setItem('gb_lang', lang);
    applyStaticText(lang);
    refreshBoardRows();
    refreshStrip();
    startTypewriter();
  }

  document.querySelectorAll('.lang-btn').forEach(function(btn){
    btn.addEventListener('click', function(){ setLanguage(btn.getAttribute('data-lang')); });
  });

  /* Mobile menu */
  var burger = document.getElementById('burgerBtn');
  var mobileMenu = document.getElementById('mobileMenu');
  burger.addEventListener('click', function(){
    var open = mobileMenu.classList.toggle('open');
    burger.setAttribute('aria-expanded', open);
  });

  /* Tabs */
  var tabBtns = document.querySelectorAll('.tab-btn');
  var panels = document.querySelectorAll('.steps');
  tabBtns.forEach(function(btn){
    btn.addEventListener('click', function(){
      tabBtns.forEach(function(b){ b.setAttribute('aria-selected','false'); });
      btn.setAttribute('aria-selected','true');
      var target = btn.getAttribute('data-tab');
      panels.forEach(function(p){
        p.classList.toggle('active', p.getAttribute('data-panel') === target);
      });
    });
  });

  /* Scroll reveal */
  var revealEls = document.querySelectorAll('.reveal');
  if('IntersectionObserver' in window && !reduceMotion){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, {threshold:0.12});
    revealEls.forEach(function(el){ io.observe(el); });
  } else {
    revealEls.forEach(function(el){ el.classList.add('is-visible'); });
  }

  /* Live gigs board — split-flap style */
  var boardBody = document.getElementById('boardBody');
  var rows = [];
  for(var i=0;i<6;i++){
    boardToggleState[i] = false;
    var data = buildBoardRow(i, false);
    var row = document.createElement('div');
    row.className = 'board-row';
    row.innerHTML =
      '<span class="flip-inner c-job">'+data.job+'</span>'+
      '<span class="flip-inner c-area">'+data.area+'</span>'+
      '<span class="flip-inner c-pay">'+data.pay+'</span>'+
      '<span class="flip-inner"><span class="status-chip">'+data.status+'</span></span>';
    boardBody.appendChild(row);
    rows.push(row);
  }

  if(!reduceMotion){
    rows.forEach(function(row, i){
      setInterval(function(){
        boardToggleState[i] = !boardToggleState[i];
        var data = buildBoardRow(i, boardToggleState[i]);
        var cells = row.querySelectorAll('.flip-inner');
        cells.forEach(function(c){ c.style.transform = 'rotateX(-90deg)'; });
        setTimeout(function(){
          row.querySelector('.c-job').textContent = data.job;
          row.querySelector('.c-area').textContent = data.area;
          row.querySelector('.c-pay').textContent = data.pay;
          var chip = row.querySelector('.status-chip');
          if(chip){ chip.textContent = data.status; }
          cells.forEach(function(c){ c.style.transform = 'rotateX(0deg)'; });
        }, 240);
      }, 3400 + i*650);
    });
  }

  /* Init */
  applyStaticText(currentLang);
  refreshStrip();
  startTypewriter();
})();
</script>
</body>
</html>
