<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$products = db()->query('SELECT * FROM products ORDER BY sort_order, id')->fetchAll();
$reviews = db()->query('SELECT * FROM reviews WHERE is_published = 1 ORDER BY sort_order, id DESC')->fetchAll();
?>
<meta charset="UTF-8">
<title>Wendo 2.0</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Luckiest+Guy&family=Schibsted+Grotesk:ital,wght@0,400;0,500;0,600;0,700;1,400&family=IBM+Plex+Mono:wght@400;500;600&display=swap');

  :root{
    --bg:#FAF3BA;
    --surface:#FFFBEF;
    --surface-2:#F3E9A8;
    --text:#2A0D0D;
    --muted:#8C4A3A;
    --burgundy:#D70000;
    --burgundy-strong:#9E0000;
    --marigold:#F8DF01;
    --marigold-text:#A8600A;
    --teal:#11B3CE;
    --line:rgba(42,13,13,.15);
    --line-strong:rgba(42,13,13,.3);
    --focus:#D70000;
  }
  @media (prefers-color-scheme: dark){
    :root:not([data-theme="light"]){
      --bg:#1A0505; --surface:#2A0A0A; --surface-2:#3A0E0E;
      --text:#FAF3BA; --muted:#D9A8A0;
      --burgundy:#FF3B3B; --burgundy-strong:#C41E1E;
      --marigold:#FFE94D; --marigold-text:#FFC93C;
      --teal:#4DD9F0;
      --line:rgba(250,243,186,.15); --line-strong:rgba(250,243,186,.3);
      --focus:#FFE94D;
    }
  }
  :root[data-theme="dark"]{
    --bg:#1A0505; --surface:#2A0A0A; --surface-2:#3A0E0E;
    --text:#FAF3BA; --muted:#D9A8A0;
    --burgundy:#FF3B3B; --burgundy-strong:#C41E1E;
    --marigold:#FFE94D; --marigold-text:#FFC93C;
    --teal:#4DD9F0;
    --line:rgba(250,243,186,.15); --line-strong:rgba(250,243,186,.3);
    --focus:#FFE94D;
  }

  *{box-sizing:border-box;}
  .sr-only{ position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
  html{background:var(--bg);}
  body{
    margin:0;background:var(--bg);color:var(--text);
    font-family:'Schibsted Grotesk',system-ui,sans-serif;
    line-height:1.55; -webkit-font-smoothing:antialiased;
  }
  ::selection{ background:var(--burgundy); color:#fff; }
  a{ color:inherit; }
  a:focus-visible, button:focus-visible{ outline:3px solid var(--focus); outline-offset:3px; }
  h1,h2,h3{ font-family:'Luckiest Guy',system-ui,sans-serif; font-weight:400; text-wrap:balance; letter-spacing:.01em; }
  .mono{ font-family:'IBM Plex Mono',ui-monospace,monospace; }
  .eyebrow{
    font-family:'IBM Plex Mono',ui-monospace,monospace; font-size:.72rem;
    letter-spacing:.16em; text-transform:uppercase; color:var(--marigold-text);
  }
  .wrap{ max-width:1080px; margin:0 auto; padding:0 clamp(1.25rem,4vw,3rem); }

  /* ---- nav ---- */
  .site-nav{
    position:sticky; top:0; z-index:50; background:var(--burgundy-strong);
    border-bottom:2px solid rgba(0,0,0,.25);
  }
  .site-nav .wrap{ display:flex; align-items:center; justify-content:space-between; padding:.9rem clamp(1.25rem,4vw,3rem); gap:1rem; flex-wrap:wrap; }
  .nav-brand{ font-family:'IBM Plex Mono',monospace; font-size:.78rem; letter-spacing:.1em; text-transform:uppercase; color:var(--marigold); }
  .nav-right{ display:flex; align-items:center; gap:.75rem; }
  .nav-links{ display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap; }
  .nav-links a{ font-family:'IBM Plex Mono',monospace; font-size:.78rem; letter-spacing:.05em; text-transform:uppercase; color:#F8EFD8; text-decoration:none; padding:.6rem 0; }
  .nav-links a:hover{ color:var(--marigold); }
  .nav-links .btn{ padding:.5rem 1rem; font-size:.72rem; }
  .nav-toggle{
    display:none; flex-direction:column; justify-content:center; align-items:center; gap:5px;
    width:44px; height:44px; background:transparent; border:1px solid rgba(248,239,216,.35);
    cursor:pointer; padding:0; flex-shrink:0;
  }
  .nav-toggle span{ display:block; width:20px; height:2px; background:#F8EFD8; }
  @media (max-width:480px){
    .nav-toggle{ display:flex; }
    .nav-links{
      flex-basis:100%; flex-direction:column; align-items:flex-start; gap:0;
      max-height:0; overflow:hidden; transition:max-height .3s ease;
    }
    .nav-links.open{ max-height:220px; margin-top:.5rem; }
    .nav-links a{ width:100%; padding:.85rem 0; }
    .nav-links .btn{ display:none; } /* Get Tickets already lives outside the collapsible menu */
  }

  .ribbon{
    height:10px;
    background:repeating-linear-gradient(90deg,
      var(--burgundy) 0 18px, var(--marigold) 18px 36px, var(--surface) 36px 54px);
  }

  /* ---- hero ---- */
  .hero{ position:relative; background:var(--burgundy-strong); color:#F8EFD8; padding:clamp(2.5rem,6vw,4.5rem) 0 clamp(3rem,6vw,4.5rem); overflow:hidden; }
  .portrait-halo{
    position:absolute; top:-8%; right:-6%; width:56vw; height:56vw; max-width:600px; max-height:600px;
    border-radius:50%; overflow:hidden; z-index:0; pointer-events:none;
  }
  .portrait-halo img{ width:100%; height:100%; object-fit:cover; object-position:50% 22%; }
  .portrait-halo::after{
    content:""; position:absolute; inset:0; border-radius:50%;
    background:
      radial-gradient(circle, transparent 28%, var(--burgundy-strong) 82%),
      linear-gradient(15deg, color-mix(in srgb, var(--teal) 55%, transparent), transparent 65%);
  }
  .hero .wrap{ position:relative; z-index:1; }
  .hero .eyebrow{ color:var(--marigold); }
  .hero h1{ font-size:clamp(3rem,10vw,6rem); line-height:.9; margin:.7rem 0 .4rem; color:#F8EFD8; }
  .hero .sub{ font-family:'Luckiest Guy',sans-serif; font-weight:700; font-size:clamp(1.5rem,3.6vw,2.3rem); color:#FAF3BA; margin:0 0 .5rem; letter-spacing:.01em; }
  .hero .sub.sub-lg{ font-size:clamp(2rem,6vw,3.4rem); line-height:1.05; margin-top:.25rem; text-wrap:balance; }
  .hero .greeting{ font-size:1.05rem; color:#E7D8B8; margin:0 0 1.25rem; max-width:52ch; }
  .ticket-line{
    display:inline-block; font-family:'Luckiest Guy',sans-serif; font-weight:600; font-size:.95rem;
    background:var(--marigold); color:#241209; padding:.55rem 1.1rem; margin:0 0 1.5rem;
  }
  .hero-meta{ display:flex; flex-wrap:wrap; gap:.4rem 1.5rem; font-family:'IBM Plex Mono',monospace; font-size:.85rem; color:#E7D8B8; margin-bottom:2rem; }

  .video-frame{
    position:relative; width:100%; aspect-ratio:16/9;
    border:2px solid var(--marigold); box-shadow:0 20px 44px rgba(0,0,0,.35);
    background:#000; overflow:hidden;
  }
  .video-frame iframe{ position:absolute; inset:0; width:100%; height:100%; border:0; }
  .video-cap{ margin-top:.75rem; font-family:'IBM Plex Mono',monospace; font-size:.78rem; color:#D8C49A; }
  .video-cap a{ color:var(--marigold); text-decoration:underline; }

  .cta-row{ display:flex; flex-wrap:wrap; align-items:center; gap:1rem; margin-top:2rem; }
  .btn{
    display:inline-block; font-family:'IBM Plex Mono',monospace; font-weight:600; font-size:.9rem;
    letter-spacing:.04em; text-transform:uppercase; text-decoration:none;
    padding:.95rem 1.9rem; background:var(--marigold); color:#241209; border:2px solid var(--marigold);
  }
  .btn.ghost{ background:transparent; color:#F8EFD8; border-color:#F8EFD8; }
  .placeholder-note{ font-family:'IBM Plex Mono',monospace; font-size:.72rem; color:#C99; letter-spacing:.03em; }

  /* ---- sections ---- */
  section{ padding:clamp(3.25rem,7vw,5rem) 0; border-bottom:1px solid var(--line); }
  section:last-of-type{ border-bottom:none; }
  .section-head{ max-width:62ch; margin-bottom:2.25rem; }
  .section-head h2{ font-size:clamp(1.8rem,4.5vw,2.6rem); margin:0 0 .7rem; }
  .section-head p{ color:var(--muted); margin:0; font-size:1.05rem; }

  /* ---- stats ---- */
  .stats{ background:var(--surface-2); }
  .stats-line{ font-family:'IBM Plex Mono',monospace; font-size:.78rem; letter-spacing:.1em; text-align:center; color:var(--muted); margin-bottom:2rem; }
  .stats-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); gap:1px; background:var(--line-strong); border:1px solid var(--line-strong); }
  .stat{ background:var(--surface); padding:1.6rem 1rem; text-align:center; }
  .stat .num{ font-family:'Luckiest Guy',sans-serif; font-size:clamp(1.5rem,4vw,2.1rem); display:block; color:var(--burgundy); }
  .stat .label{ font-family:'IBM Plex Mono',monospace; font-size:.68rem; letter-spacing:.06em; text-transform:uppercase; color:var(--muted); }
  .stats-note{ text-align:center; margin-top:1.75rem; color:var(--muted); font-size:.95rem; }
  .stats-note strong{ color:var(--marigold-text); }

  /* ---- ambui ---- */
  .ambui{ display:grid; grid-template-columns:1.2fr .8fr; gap:clamp(2rem,5vw,3.5rem); align-items:start; }
  .etym-card{ background:var(--surface); border:1px solid var(--line-strong); padding:1.75rem; }
  .etym-card .term{ font-family:'Luckiest Guy',sans-serif; font-size:1.6rem; color:var(--burgundy); margin:0 0 .3rem; }
  .etym-card .ipa{ font-family:'IBM Plex Mono',monospace; font-size:.85rem; color:var(--muted); margin:0 0 1.25rem; }
  .etym-card h4{ font-family:'Schibsted Grotesk'; font-size:.85rem; text-transform:uppercase; letter-spacing:.06em; color:var(--marigold-text); margin:1.1rem 0 .4rem; }
  .etym-card p{ margin:0; }
  .short-frame{
    aspect-ratio:9/16; max-width:320px; margin:0 auto; border:2px solid var(--marigold);
    box-shadow:0 20px 44px rgba(36,18,9,.25); background:#000; position:relative; overflow:hidden;
  }
  .short-frame iframe{ position:absolute; inset:0; width:100%; height:100%; border:0; }

  .video-poster{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
  .video-play{
    position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
    width:64px; height:64px; border-radius:50%; border:2px solid #fff;
    background:rgba(0,0,0,.45); color:#fff; font-size:1.3rem; line-height:1;
    display:flex; align-items:center; justify-content:center; cursor:pointer;
    padding:0 0 0 4px; /* optically centers the triangle glyph */
  }
  .video-play:hover{ background:var(--burgundy); border-color:var(--burgundy); }
  .short-cap{ text-align:center; margin-top:.75rem; font-family:'IBM Plex Mono',monospace; font-size:.78rem; color:var(--muted); }
  .short-cap a{ color:var(--burgundy); }
  @media (max-width:760px){ .ambui{ grid-template-columns:1fr; } }

  /* ---- journey ---- */
  .concept-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:1px; background:var(--line-strong); border:1px solid var(--line-strong); margin-bottom:2.5rem; }
  .concept-card{ background:var(--surface); padding:1.6rem; }
  .concept-card h3{ font-family:'Schibsted Grotesk'; font-weight:700; font-size:1.02rem; margin:0 0 .6rem; color:var(--burgundy); }
  .concept-card p{ margin:0; font-size:.92rem; color:var(--muted); }
  @media (max-width:760px){ .concept-grid{ grid-template-columns:1fr; } }
  .flame-row{ display:flex; flex-wrap:wrap; gap:1.5rem; align-items:center; font-family:'IBM Plex Mono',monospace; font-size:.9rem; }
  .polaroid-wall{ display:grid; grid-template-columns:repeat(3,1fr); gap:1.75rem 1.25rem; max-width:760px; margin:2rem 0 1.5rem; }
  .polaroid{ margin:0; background:#fff; padding:8px 8px 22px; box-shadow:0 10px 24px rgba(42,13,13,.28); transition:transform .3s ease, box-shadow .3s ease; }
  .polaroid img{ display:block; width:100%; height:clamp(90px,14vw,150px); object-fit:cover; }
  .polaroid:nth-child(6n+1){ transform:rotate(-3deg); }
  .polaroid:nth-child(6n+2){ transform:rotate(2deg); }
  .polaroid:nth-child(6n+3){ transform:rotate(-2deg); }
  .polaroid:nth-child(6n+4){ transform:rotate(3deg); }
  .polaroid:nth-child(6n+5){ transform:rotate(-1.5deg); }
  .polaroid:nth-child(6n+6){ transform:rotate(2.5deg); }
  .polaroid:hover{ transform:rotate(0deg) scale(1.06); box-shadow:0 16px 32px rgba(42,13,13,.35); position:relative; z-index:2; }
  @media (max-width:640px){ .polaroid-wall{ grid-template-columns:repeat(2,1fr); gap:1.1rem; } }
  @media (prefers-reduced-motion:reduce){ .polaroid{ transition:none; } }
  .flame-chip{ display:flex; align-items:center; gap:.6rem; padding:.7rem 1.1rem; border:1px solid var(--line-strong); background:var(--surface); text-decoration:none; color:var(--text); }
  a.flame-chip:hover{ border-color:var(--burgundy); }
  .flame-chip .n{ font-family:'Luckiest Guy',sans-serif; font-size:1.1rem; color:var(--marigold-text); }
  .flame-arrow{ color:var(--muted); }

  /* ---- konas ---- */
  .konas{ display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:1px; background:var(--line-strong); border:1px solid var(--line-strong); }
  .kona{ background:var(--surface); padding:1.4rem 1.5rem; }
  .kona .idx{ font-family:'IBM Plex Mono',monospace; font-size:.75rem; color:var(--burgundy); }
  .kona h3{ font-family:'Schibsted Grotesk'; font-weight:700; font-size:1rem; margin:.3rem 0 .3rem; }
  .kona p{ margin:0; font-size:.88rem; color:var(--muted); }
  .kona.current{ background:var(--marigold); }
  .kona.current .idx, .kona.current p{ color:#241209; }
  .kona.current h3{ color:#241209; }

  /* ---- sound ---- */
  .genre-row{ display:flex; flex-wrap:wrap; gap:.6rem; }
  .genre{ font-family:'IBM Plex Mono',monospace; font-size:.85rem; padding:.6rem 1.1rem; border:1px solid var(--line-strong); color:var(--text); }

  /* ---- tickets band ---- */
  .tickets-band{ background:var(--burgundy-strong); color:#F8EFD8; text-align:center; }
  .tickets-band .eyebrow{ color:#EAC373; }
  .tickets-band h2{ color:#F8EFD8; font-size:clamp(2rem,6vw,3rem); margin:.6rem 0 1rem; }
  .tickets-band p{ max-width:50ch; margin:0 auto 1.75rem; color:#E7D8B8; }
  .fine-print{
    display:flex; justify-content:center; gap:clamp(2rem,6vw,4rem); flex-wrap:wrap;
    margin-top:2.5rem; padding-top:2rem; border-top:1px solid rgba(248,239,216,.2);
    text-align:left; max-width:640px; margin-left:auto; margin-right:auto;
  }
  .fine-print h5{ font-family:'IBM Plex Mono',monospace; font-size:.7rem; letter-spacing:.08em; text-transform:uppercase; color:var(--marigold); margin:0 0 .4rem; }
  .fine-print p{ margin:0; font-size:.8rem; color:#D8C49A; max-width:26ch; }
  .tickets-band .cta-row{ justify-content:center; flex-wrap:wrap; }

  /* ---- merch ---- */
  .merch-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1.5rem; }
  .merch-card{ background:var(--surface); border:1px solid var(--line-strong); overflow:hidden; transition:transform .35s cubic-bezier(.22,1,.36,1), box-shadow .35s ease; }
  .merch-card:hover{ transform:translateY(-5px); box-shadow:0 16px 32px rgba(42,13,13,.18); }
  .merch-art{
    aspect-ratio:1; position:relative; overflow:hidden;
    background:repeating-linear-gradient(45deg, var(--surface-2) 0 14px, var(--bg) 14px 28px);
  }
  .merch-ribbon{
    position:absolute; top:16px; right:-42px; transform:rotate(45deg);
    background:var(--burgundy); color:#fff; font-family:'IBM Plex Mono',monospace;
    font-size:.65rem; letter-spacing:.08em; text-transform:uppercase;
    padding:.4rem 3.2rem; box-shadow:0 2px 6px rgba(0,0,0,.25);
  }
  .merch-body{ padding:1.25rem 1.4rem 1.5rem; }
  .tag-soon{ font-family:'IBM Plex Mono',monospace; font-size:.68rem; letter-spacing:.08em; text-transform:uppercase; color:var(--marigold-text); }
  .merch-card h3{ margin:.4rem 0 .4rem; font-size:1.3rem; }
  .merch-card p{ margin:0; color:var(--muted); font-size:.9rem; }
  .merch-price{ font-family:'Luckiest Guy',sans-serif; font-size:1.3rem; color:var(--burgundy); margin:.6rem 0 .75rem !important; }
  .notify-row{ display:flex; gap:.75rem; margin-top:2rem; flex-wrap:wrap; max-width:480px; }
  .notify-input{
    flex:1; min-width:200px; padding:.9rem 1rem; border:1px solid var(--line-strong);
    background:var(--surface); color:var(--text); font-family:'IBM Plex Mono',monospace; font-size:.85rem;
  }
  .notify-input:disabled{ opacity:.6; cursor:not-allowed; }
  .btn:disabled{ opacity:.5; cursor:not-allowed; transform:none !important; }

  /* ---- reviews ---- */
  .reviews-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1.5rem; }
  .review-card{ background:var(--surface); border:1px solid var(--line-strong); overflow:hidden; }
  .review-media{ aspect-ratio:9/16; background:#000; position:relative; }
  .review-media video, .review-media iframe{ position:absolute; inset:0; width:100%; height:100%; border:0; object-fit:cover; }
  .review-media a.review-link{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; font-family:'IBM Plex Mono',monospace; font-size:.85rem; text-decoration:underline; }
  .review-body{ padding:1rem 1.2rem 1.2rem; }
  .review-body h3{ margin:0 0 .3rem; font-size:1.05rem; }
  .review-body p{ margin:0; color:var(--muted); font-size:.88rem; }

  footer{ padding:2.5rem 0 3rem; text-align:center; }
  .say-hello{ font-family:'Luckiest Guy',sans-serif; font-size:1.6rem; color:var(--burgundy); margin:0 0 1rem; }
  .social-icons{ display:flex; justify-content:center; gap:1.1rem; margin-bottom:1.5rem; }
  .social-icons a{ color:var(--burgundy); display:inline-flex; width:40px; height:40px; align-items:center; justify-content:center; border:1px solid var(--line-strong); border-radius:50%; transition:color .25s ease, border-color .25s ease; }
  .social-icons svg{ width:19px; height:19px; }
  .social-icons a:hover{ color:var(--marigold-text); border-color:var(--marigold-text); }
  footer .contact-row{ display:flex; flex-wrap:wrap; justify-content:center; gap:1.5rem; font-family:'IBM Plex Mono',monospace; font-size:.85rem; margin-bottom:1rem; }
  footer p.sign{ font-family:'IBM Plex Mono',monospace; font-size:.72rem; color:var(--muted); letter-spacing:.04em; }

  /* ---- animation layer ---- */
  .reveal{
    opacity:0; transform:translateY(26px); filter:blur(4px);
    transition:opacity .8s cubic-bezier(.22,1,.36,1), transform .8s cubic-bezier(.22,1,.36,1), filter .8s ease;
  }
  .reveal.in{ opacity:1; transform:translateY(0); filter:blur(0); }
  @media (prefers-reduced-motion:reduce){
    .reveal{ transition:none; opacity:1; transform:none; filter:none; }
    .hero-load{ animation:none !important; opacity:1 !important; filter:none !important; }
    .cd-num.flip{ animation:none !important; }
    .merch-card, .kona, .car-card, .review-card{ transition:none !important; }
  }

  .hero-load{ opacity:0; }
  .hero .sub.hero-load{ filter:blur(8px); animation:blurUp .9s ease .1s forwards; }
  .hero .greeting.hero-load{ animation:fadeUp .8s ease .3s forwards; }
  .hero .ticket-line.hero-load{ animation:fadeUp .7s ease .45s forwards; }
  .hero .hero-meta.hero-load{ animation:fadeUp .8s ease .6s forwards; }
  .hero .cd-wrap.hero-load{ animation:fadeUp .8s ease .75s forwards; }
  .hero .video-frame.hero-load{ animation:fadeUp 1s cubic-bezier(.22,1,.36,1) .95s forwards; }
  .hero .cta-row.hero-load{ animation:fadeUp .8s ease 1.15s forwards; }
  @keyframes fadeUp{ from{ opacity:0; transform:translateY(18px);} to{ opacity:1; transform:translateY(0);} }
  @keyframes blurUp{ from{ opacity:0; filter:blur(8px); transform:translateY(10px);} to{ opacity:1; filter:blur(0); transform:translateY(0);} }

  @media (max-width:640px){
    .hero{ min-height:100vh; min-height:100dvh; display:flex; align-items:center; }
    .hero .wrap{ width:100%; }
  }

  .btn{ transition:transform .3s cubic-bezier(.22,1,.36,1), background .3s ease, border-color .3s ease; }
  .btn:active{ transform:scale(.94); }

  .video-frame{ transition:box-shadow .4s ease, border-color .4s ease; }
  .video-frame:hover{ box-shadow:0 24px 56px rgba(232,163,61,.25); border-color:var(--marigold-text); }

  .stat{ transition:background .3s ease; }
  .stat .num{ font-variant-numeric:tabular-nums; }

  .kona{ transition:transform .35s cubic-bezier(.22,1,.36,1), background .3s ease; }
  .kona:hover:not(.current){ transform:translateY(-4px); background:var(--surface-2); }

  .short-frame, .etym-card{ transition:box-shadow .4s ease; }
  .short-frame:hover{ box-shadow:0 24px 56px rgba(232,163,61,.25); }

  .cd-wrap{ display:flex; gap:clamp(1rem,3vw,2rem); margin-top:.5rem; }
  .cd-unit{ text-align:left; }
  .cd-num{
    font-family:'Luckiest Guy',sans-serif; font-size:clamp(1.4rem,3.5vw,2rem); color:#F8EFD8;
    display:inline-block; font-variant-numeric:tabular-nums;
  }
  .cd-num.flip{ animation:flipNum .5s ease; }
  @keyframes flipNum{ 0%{ opacity:.25; transform:translateY(-6px);} 100%{ opacity:1; transform:translateY(0);} }
  .cd-tag{ font-family:'IBM Plex Mono',monospace; font-size:.65rem; letter-spacing:.1em; text-transform:uppercase; color:#D8C49A; margin-top:.3rem; }

  /* ---- carousel ---- */
  .carousel{ margin:0 calc(-1 * clamp(1.25rem,4vw,3rem)); }
  .car-track{
    display:flex; gap:1rem; overflow-x:auto; scroll-snap-type:x mandatory;
    padding:.25rem clamp(1.25rem,4vw,3rem) 1.25rem;
    scrollbar-width:thin; scrollbar-color:var(--line-strong) transparent;
  }
  .car-track::-webkit-scrollbar{ height:6px; }
  .car-track::-webkit-scrollbar-thumb{ background:var(--line-strong); }
  .car-card{
    flex:0 0 auto; width:250px; scroll-snap-align:start;
    border:1px solid var(--line-strong); background:var(--surface);
    text-decoration:none; color:var(--text); overflow:hidden;
    transition:transform .35s cubic-bezier(.22,1,.36,1), box-shadow .35s ease;
  }
  .car-card:hover{ transform:translateY(-5px); box-shadow:0 16px 32px rgba(36,18,9,.2); }
  .car-card img{ display:block; width:100%; height:250px; object-fit:cover; }
  .car-card figcaption{
    padding:.9rem 1rem; font-family:'IBM Plex Mono',monospace; font-size:.75rem;
    color:var(--muted); letter-spacing:.02em;
  }
  .car-social{ background:var(--burgundy); color:#F8EFD8; }
  .car-social.car-tiktok{ background:#241209; }
  .car-social-inner{
    height:250px; display:flex; flex-direction:column; align-items:center; justify-content:center;
    gap:.5rem; padding:1.5rem; text-align:center;
  }
  .car-social-icon{ width:42px; height:42px; color:var(--marigold); }
  .car-social-label{ font-family:'Luckiest Guy',sans-serif; font-size:1.1rem; }
  .car-social-handle{ font-size:.78rem; color:#D8C49A; }
  .car-social-cta{ font-family:'IBM Plex Mono',monospace; font-size:.72rem; letter-spacing:.08em; text-transform:uppercase; color:var(--marigold); margin-top:.4rem; }
  .carousel-note{ text-align:center; font-size:.72rem; color:var(--muted); letter-spacing:.04em; margin:0; }
  .skip-link{
    position:absolute; left:1rem; top:-3rem; z-index:100; background:var(--marigold); color:#241209;
    padding:.6rem 1.1rem; font-family:'IBM Plex Mono',monospace; font-size:.8rem; text-decoration:none;
    transition:top .2s ease;
  }
  .skip-link:focus{ top:1rem; }
</style>

<a class="skip-link" href="#main-content">Skip to content</a>

<nav class="site-nav" aria-label="Primary">
  <div class="wrap">
    <span class="nav-brand">Wendo Collective Presents</span>
    <div class="nav-right">
      <a class="btn" href="https://www.tickets-fest.com/events/wendo-2-0" target="_blank" rel="noopener">Get Tickets</a>
      <button type="button" class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="nav-links" aria-label="Open menu">
        <span></span><span></span><span></span>
      </button>
    </div>
    <div class="nav-links" id="nav-links">
      <a href="#merch">Merch</a>
      <?php if ($reviews): ?><a href="#reviews">Reviews</a><?php endif; ?>
      <a href="recap.html">See The 1.0 Recap →</a>
    </div>
  </div>
</nav>

<div class="ribbon"></div>

<header class="hero">
  <div class="portrait-halo" aria-hidden="true"><img src="assets/moment-1.jpg" alt=""></div>
  <div class="wrap">
    <h1 class="sr-only">Wendo 2.0: Ambũi Edition</h1>
    <p class="sub sub-lg hero-load">Ambũi: Daughter of Warmth, Beauty &amp; Love</p>
    <p class="greeting hero-load">Ni Thayũ. Peace be with you. Every Wendo carries one of Gĩkũyũ and Mũmbi's ten daughters forward: her name, her spirit, her welcome. This edition belongs to Ambũi, the daughter of warmth, beauty, and a love that makes a stranger feel like family.</p>
    <p class="ticket-line hero-load">Experience the gathering. Secure your passage.</p>
    <div class="hero-meta hero-load">
      <span>31.10.26</span>
      <span>Venue: Kentmere Club, Kenya</span>
    </div>

    <div class="cd-wrap hero-load">
      <div class="cd-unit"><span class="cd-num" id="cd-days">00</span><p class="cd-tag">Days</p></div>
      <div class="cd-unit"><span class="cd-num" id="cd-hours">00</span><p class="cd-tag">Hrs</p></div>
      <div class="cd-unit"><span class="cd-num" id="cd-mins">00</span><p class="cd-tag">Min</p></div>
      <div class="cd-unit"><span class="cd-num" id="cd-secs">00</span><p class="cd-tag">Sec</p></div>
    </div>

    <div class="video-frame hero-load" data-video-id="cCo4F36en7E" data-video-params="start=329" data-video-title="Wendo 2.0 announcement">
      <img class="video-poster" src="https://img.youtube.com/vi/cCo4F36en7E/hqdefault.jpg" alt="" loading="lazy">
      <button type="button" class="video-play" aria-label="Play the Wendo 2.0 announcement video">▶</button>
    </div>
    <p class="video-cap">Playing muted. Tap the speaker on the player to hear it, or watch on <a href="https://youtu.be/cCo4F36en7E?t=329" target="_blank" rel="noopener">YouTube</a>.</p>

    <div class="cta-row hero-load">
      <a class="btn" href="https://www.tickets-fest.com/events/wendo-2-0" target="_blank" rel="noopener">Get Tickets →</a>
      <a class="btn ghost" href="#merch">Shop The Drop</a>
    </div>
  </div>
</header>

<div class="stats">
  <div class="wrap" style="padding:clamp(3rem,7vw,4.5rem) 0;">
    <p class="stats-line">ONE STAGE · ONE CULTURE · ONE UNFORGETTABLE EXPERIENCE</p>
    <div class="stats-grid reveal" id="stats-grid">
      <div class="stat"><span class="num mono">8</span><span class="label">Live Artists</span></div>
      <div class="stat"><span class="num mono">5</span><span class="label">Deejays</span></div>
      <div class="stat"><span class="num mono">4</span><span class="label">Hosts</span></div>
      <div class="stat"><span class="num mono">1</span><span class="label">Visual Artist</span></div>
      <div class="stat"><span class="num mono">1</span><span class="label">Dance Group</span></div>
      <div class="stat"><span class="num mono">3</span><span class="label">Surprise Acts</span></div>
    </div>
    <p class="stats-note">The lineup remains under wraps. <strong>Reveal online.</strong> The experience speaks for itself.</p>
  </div>
</div>

<main id="main-content">

  <section class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Second Daughter</span>
      <h2>Meet W/Ambũi</h2>
      <p>Every edition of Wendo carries the name of one of Gĩkũyũ and Mũmbi's daughters. This one belongs to Ambũi.</p>
    </div>
    <div class="ambui reveal">
      <div class="etym-card">
        <p class="term">Ambũi</p>
        <p class="ipa">[am-boo-ee] · n. pl. (sing. Mũmbũi; also the proper noun Wambũi)</p>
        <p>The Daughter of Warmth, Beauty &amp; Love. She has the heart of gold that's full of love: friendly, bright, warm-hearted, the kind of woman who makes people smile.</p>
        <h4>Our Story</h4>
        <p>Ambũi, the second daughter, invites everyone home. She carries a heart of gold and a mind of wisdom, bringing all people together in celebration.</p>
        <h4>Etymology &amp; Language</h4>
        <p>Derived from Wambũi, one of the original ancestral daughters in Agĩkũyũ folklore, historically carrying a connotation tied to striped patterns, like the markings of a zebra.</p>
        <h4>Traditional Traits</h4>
        <p>Astute thinkers, eloquent negotiators, and strategic leaders with sharp memory and focus.</p>
      </div>
      <div>
        <div class="short-frame" data-video-id="f3LodYambh8" data-video-params="loop=1&playlist=f3LodYambh8" data-video-title="Wendo 2.0 short">
          <img class="video-poster" src="https://img.youtube.com/vi/f3LodYambh8/hqdefault.jpg" alt="" loading="lazy">
          <button type="button" class="video-play" aria-label="Play the Wendo 2.0 short video">▶</button>
        </div>
        <p class="short-cap">First look, also on <a href="https://www.youtube.com/shorts/f3LodYambh8" target="_blank" rel="noopener">YouTube Shorts</a>.</p>
      </div>
    </div>
  </section>

  <section class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">The Journey</span>
      <h2>Ten daughters, one story</h2>
      <p>Wendo is a ten-edition journey. Each edition is a chapter: a flame passed from one daughter to the next.</p>
    </div>
    <div class="concept-grid reveal">
      <div class="concept-card">
        <h3>A Celebration of Love, Culture &amp; Ancestry</h3>
        <p>A cultural experience rooted in wendo (love, heritage and storytelling), honouring one daughter of Gĩkũyũ and Mũmbi at a time.</p>
      </div>
      <div class="concept-card">
        <h3>Ancestral Meets Modern</h3>
        <p>Traditional African culture fused with contemporary creativity: fashion, décor, performance and storytelling, where heritage feels alive.</p>
      </div>
      <div class="concept-card">
        <h3>Journey Through Generations</h3>
        <p>A collective story of origin, leadership and unity, reimagined chapter by chapter for today's generation.</p>
      </div>
    </div>
    <div class="flame-row reveal">
      <a class="flame-chip" href="recap.html"><span class="n mono">1.0</span> Wanjirũ, The First Flame</a>
      <span class="flame-arrow">→</span>
      <div class="flame-chip"><span class="n mono">2.0</span> Ambũi, The Second Daughter</div>
      <span class="flame-arrow">→</span>
      <div class="flame-chip">…eight more to come</div>
    </div>
    <div class="polaroid-wall reveal">
      <figure class="polaroid"><img src="assets/moment-4-stage.jpg" alt="The Wendo 1.0 main stage production"></figure>
      <figure class="polaroid"><img src="assets/moment-2.jpg" alt="A guest in African-print fashion at Wendo 1.0"></figure>
      <figure class="polaroid"><img src="assets/moment-3.jpg" alt="The crowd at Wendo 1.0's night stage"></figure>
      <figure class="polaroid"><img src="assets/moment-5-daughters.jpg" alt="The Ten Daughters signage at the Wendo 1.0 grounds"></figure>
      <figure class="polaroid"><img src="assets/moment-6-group.jpg" alt="Friends together at Wendo 1.0's night stage"></figure>
      <figure class="polaroid"><img src="assets/moment-7-entrance.jpg" alt="The decorated entrance at Wendo 1.0, after dark"></figure>
    </div>
    <p style="margin-top:1rem;"><a href="recap.html" class="mono" style="color:var(--burgundy);text-decoration:underline;font-size:.85rem;">Missed 1.0? See the recap →</a></p>
  </section>

  <section class="wrap" id="zones">
    <div class="section-head reveal">
      <span class="eyebrow">The Grounds</span>
      <h2>The Kona Cia Mumbi</h2>
      <p>Ten experience zones, each embodying one of Mumbi's daughters: a symbolic corner reflecting her strength, gifts, and modern-day essence.</p>
    </div>
    <div class="konas reveal">
      <div class="kona"><span class="idx mono">1.0</span><h3>Wanjirũ Kona</h3><p>The First Flame</p></div>
      <div class="kona current"><span class="idx mono">2.0</span><h3>Wambũi Kona</h3><p>The Artist's Haven (Main Stage)</p></div>
      <div class="kona"><span class="idx mono">3.0</span><h3>Wanjikũ Kona</h3><p>The Fashion &amp; Identity Hub (Kinya Kona)</p></div>
      <div class="kona"><span class="idx mono">4.0</span><h3>Njeri Kona</h3><p>The Connection Lounge (Tuiya Kona)</p></div>
      <div class="kona"><span class="idx mono">5.0</span><h3>Nyambura Kona</h3><p>The Storytellers' Circle (Wathi Kona)</p></div>
      <div class="kona"><span class="idx mono">6.0</span><h3>Waithira Kona</h3><p>The Wellness &amp; Soul Lounge (Mwoyo wa Wendo)</p></div>
      <div class="kona"><span class="idx mono">7.0</span><h3>Wangarĩ Kona</h3><p>The Green Corner (Mama Mboga &amp; Sustainability)</p></div>
      <div class="kona"><span class="idx mono">8.0</span><h3>Wairimũ Kona</h3><p>The Culinary Garden (Nyama na Wendo)</p></div>
      <div class="kona"><span class="idx mono">9.0</span><h3>Wangũi Kona</h3><p>The Visionary Hub (Maono ma Wendo)</p></div>
      <div class="kona"><span class="idx mono">10.0</span><h3>Wamuyu Kona</h3><p>The Daughter of Tomorrow (Future &amp; Innovation Hub)</p></div>
    </div>
  </section>

  <section class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">The Sound</span>
      <h2>What's on the policy</h2>
      <p>The same range that carried Wendo 1.0, widened.</p>
    </div>
    <div class="genre-row reveal">
      <span class="genre">Mugithi</span>
      <span class="genre">Modern Mugithi</span>
      <span class="genre">Urban Music</span>
      <span class="genre">Spoken Word</span>
      <span class="genre">Local Music</span>
      <span class="genre">Soulful House</span>
      <span class="genre">Folk Fusion</span>
    </div>
  </section>

  <section class="wrap" id="merch">
    <div class="section-head reveal">
      <span class="eyebrow">The Drop</span>
      <h2>Merch</h2>
      <p>Official Wendo 2.0 gear. Prices and availability are managed live from the CMS.</p>
    </div>
    <div class="merch-grid reveal">
      <?php foreach ($products as $p): ?>
      <div class="merch-card">
        <div class="merch-art">
          <?php if (!empty($p['image_path'])): ?>
            <img src="<?= e($p['image_path']) ?>" alt="<?= e($p['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
          <?php endif; ?>
          <?php if (!$p['is_available']): ?><span class="merch-ribbon">Coming Soon</span><?php endif; ?>
        </div>
        <div class="merch-body">
          <span class="tag-soon"><?= e($p['category']) ?></span>
          <h3><?= e($p['name']) ?></h3>
          <p><?= e($p['description']) ?></p>
          <?php if ($p['is_available']): ?>
            <p class="merch-price"><?= format_price((float)$p['price_kes']) ?></p>
            <a class="btn" href="pesapal/initiate-payment.php?product_id=<?= (int)$p['id'] ?>">Buy Now</a>
          <?php else: ?>
            <p class="merch-price" style="opacity:.6;">Price TBA</p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <form class="notify-row" onsubmit="return false;">
      <input type="email" class="notify-input" placeholder="you@email.com" aria-label="Email address" disabled>
      <button class="btn" type="button" disabled>Notify Me</button>
    </form>
    <p class="placeholder-note">[ The notify-me box above isn’t wired up yet. Everything else in this shop (products, prices, availability, checkout) is live and managed from /admin. ]</p>
  </section>

  <?php if ($reviews): ?>
  <section class="wrap" id="reviews">
    <div class="section-head reveal">
      <span class="eyebrow">From The Crowd</span>
      <h2>In Their Words</h2>
      <p>Real reactions from the people who were actually there at Wendo 1.0.</p>
    </div>
    <div class="reviews-grid reveal">
      <?php foreach ($reviews as $r): ?>
      <div class="review-card">
        <div class="review-media">
          <?php if (!empty($r['video_path'])): ?>
            <video src="<?= e($r['video_path']) ?>" controls preload="metadata"></video>
          <?php elseif (!empty($r['video_url']) && ($embed = youtube_embed_url($r['video_url']))): ?>
            <iframe src="<?= e($embed) ?>" title="Review from <?= e($r['reviewer_name']) ?>" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"></iframe>
          <?php elseif (!empty($r['video_url'])): ?>
            <a class="review-link" href="<?= e($r['video_url']) ?>" target="_blank" rel="noopener">Watch video →</a>
          <?php endif; ?>
        </div>
        <div class="review-body">
          <h3><?= e($r['reviewer_name']) ?></h3>
          <?php if (!empty($r['caption'])): ?><p><?= e($r['caption']) ?></p><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="tickets-band">
    <div class="wrap reveal">
      <span class="eyebrow">Are You Ready?</span>
      <h2>Wendo 2.0: 31 October 2026</h2>
      <p>Kentmere Club, Kenya. Follow @wendo.collective for the drop.</p>
      <div class="cta-row">
        <a class="btn" href="https://www.tickets-fest.com/events/wendo-2-0" target="_blank" rel="noopener">Get Tickets →</a>
        <a class="btn ghost" href="#merch">Shop The Drop</a>
      </div>
      <div class="fine-print">
        <div>
          <h5>Disclaimers</h5>
          <p>Tickets are non-refundable. Host is not responsible for lost or stolen tickets.</p>
        </div>
        <div>
          <h5>Gate Restrictions</h5>
          <p>Strict "no re-entry" policy. No outside food or beverages. Standard security checks at all gates.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">From The Ground</span>
      <h2>Food, drinks &amp; the feed</h2>
      <p>A taste of 1.0's Wairimũ Kona (the Culinary Garden), plus wherever @wendo.collective is posting next.</p>
    </div>
    <div class="carousel">
      <div class="car-track">
        <figure class="car-card">
          <img src="assets/food-1.jpg" loading="lazy" alt="Guest exchanging a cup of fresh strawberries for a cup of orange juice at the Tunda stand">
          <figcaption>Strawberries &amp; fresh juice from Tunda</figcaption>
        </figure>
        <figure class="car-card">
          <img src="assets/food-2.jpg" loading="lazy" alt="Vendor in a Wendo hi-vis vest preparing a chocolate-drizzled dessert cup">
          <figcaption>Chocolate-drizzled, made to order</figcaption>
        </figure>
        <a class="car-card car-social" href="https://www.instagram.com/wendo.collective/" target="_blank" rel="noopener">
          <div class="car-social-inner">
            <svg class="car-social-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <rect x="2" y="2" width="20" height="20" rx="6"/>
              <circle cx="12" cy="12" r="5"/>
              <circle cx="17.4" cy="6.6" r="1.1" fill="currentColor" stroke="none"/>
            </svg>
            <span class="car-social-label">Instagram</span>
            <span class="car-social-handle mono">@wendo.collective</span>
            <span class="car-social-cta">Follow →</span>
          </div>
        </a>
        <figure class="car-card">
          <img src="assets/food-3.jpg" loading="lazy" alt="Guest holding a cup of strawberries at the Tunda stand, Wendo 1.0 vest visible behind her">
          <figcaption>Sweet stop, mid-festival</figcaption>
        </figure>
        <figure class="car-card">
          <img src="assets/food-4.jpg" loading="lazy" alt="Vendor plating fresh-cut strawberries with chocolate">
          <figcaption>Fresh-cut, plated on the spot</figcaption>
        </figure>
        <a class="car-card car-social car-tiktok" href="https://www.tiktok.com/@wendo.collective" target="_blank" rel="noopener">
          <div class="car-social-inner">
            <svg class="car-social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M14 3c.6 2.4 2.3 4 4.8 4.3v3c-1.7 0-3.3-.5-4.8-1.5v6.7c0 3.1-2.5 5.5-5.6 5.5S3 18.6 3 15.5 5.5 10 8.6 10c.4 0 .8 0 1.2.1v3.1c-.4-.1-.8-.2-1.2-.2-1.4 0-2.5 1.1-2.5 2.5s1.1 2.5 2.5 2.5 2.6-1.1 2.6-2.5V3h2.8z"/>
            </svg>
            <span class="car-social-label">TikTok</span>
            <span class="car-social-handle mono">@wendo.collective</span>
            <span class="car-social-cta">Follow →</span>
          </div>
        </a>
      </div>
    </div>
    <p class="carousel-note mono">Scroll for more · full feed lives on Instagram &amp; TikTok</p>
  </section>

</main>

<footer>
  <div class="wrap">
    <p class="say-hello">Say Hello!</p>
    <div class="social-icons">
      <a href="https://www.facebook.com/wendo.collective" target="_blank" rel="noopener" aria-label="Wendo Collective on Facebook">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M15 3h-2.5C10 3 8.5 4.7 8.5 7.3V10H6v3.5h2.5V21H12v-7.5h2.6l.4-3.5h-3V7.6c0-1 .3-1.7 1.7-1.7H15V3z"/></svg>
      </a>
      <a href="https://www.instagram.com/wendo.collective/" target="_blank" rel="noopener" aria-label="Wendo Collective on Instagram">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="6"/><circle cx="12" cy="12" r="5"/><circle cx="17.4" cy="6.6" r="1.1" fill="currentColor" stroke="none"/></svg>
      </a>
      <a href="https://www.tiktok.com/@wendo.collective" target="_blank" rel="noopener" aria-label="Wendo Collective on TikTok">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 3c.6 2.4 2.3 4 4.8 4.3v3c-1.7 0-3.3-.5-4.8-1.5v6.7c0 3.1-2.5 5.5-5.6 5.5S3 18.6 3 15.5 5.5 10 8.6 10c.4 0 .8 0 1.2.1v3.1c-.4-.1-.8-.2-1.2-.2-1.4 0-2.5 1.1-2.5 2.5s1.1 2.5 2.5 2.5 2.6-1.1 2.6-2.5V3h2.8z"/></svg>
      </a>
      <a href="https://x.com/wendo_collective" target="_blank" rel="noopener" aria-label="Wendo Collective on X">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 4l7 8.5L4.4 20H7l5-5.7 4 5.7h4l-7.3-8.9L19.8 4h-2.6l-4.6 5.2L8.4 4H4z"/></svg>
      </a>
      <a href="https://www.threads.net/@wendo.collective" target="_blank" rel="noopener" aria-label="Wendo Collective on Threads">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21c-4.4 0-7-2.8-7-8s2.7-9 7.3-9c3.6 0 5.9 1.8 6.4 4.6"/><path d="M16 12.5c0 3-1.6 4.6-4 4.6-1.8 0-3-1-3-2.5 0-1.8 1.8-2.6 4-2.6 1 0 1.9.1 2.6.3"/></svg>
      </a>
      <a href="https://www.youtube.com/@wendo.collective" target="_blank" rel="noopener" aria-label="Wendo Collective on YouTube">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="2" y="5.5" width="20" height="13" rx="4"/><path d="M10 9.5l6 3-6 3z" fill="var(--surface)"/></svg>
      </a>
    </div>
    <div class="contact-row">
      <span>wendocollective@gmail.com</span>
      <span>wendocollective.co.ke</span>
      <span>@wendo.collective</span>
    </div>
    <p class="sign">WENDO COLLECTIVE · TEN DAUGHTERS, ONE JOURNEY · 2.0 W/AMBŨI</p>
  </div>
</footer>

<script>
  // Video embeds: load immediately on desktop (autoplay), only on tap on
  // mobile: an unloaded iframe costs nothing, so this is the real data saving.
  (function(){
    const isDesktop = window.matchMedia('(min-width: 641px)').matches;
    document.querySelectorAll('[data-video-id]').forEach(el => {
      function load(){
        const id = el.dataset.videoId;
        const extra = el.dataset.videoParams ? '&' + el.dataset.videoParams : '';
        const iframe = document.createElement('iframe');
        iframe.src = 'https://www.youtube-nocookie.com/embed/' + id +
          '?autoplay=1&mute=1&playsinline=1&rel=0' + extra;
        iframe.title = el.dataset.videoTitle || 'Video';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;border:0;';
        el.innerHTML = '';
        el.appendChild(iframe);
      }
      if(isDesktop){
        load();
      } else {
        const btn = el.querySelector('.video-play');
        if(btn) btn.addEventListener('click', load, { once:true });
      }
    });
  })();

  // Mobile nav toggle
  (function(){
    const btn = document.getElementById('nav-toggle');
    const menu = document.getElementById('nav-links');
    if(!btn || !menu) return;
    btn.addEventListener('click', () => {
      const open = menu.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    menu.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
      menu.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    }));
  })();

  // Countdown to 31 Oct 2026 00:00 local
  (function(){
    const target = new Date('2026-10-31T00:00:00');
    const els = {
      d: document.getElementById('cd-days'),
      h: document.getElementById('cd-hours'),
      m: document.getElementById('cd-mins'),
      s: document.getElementById('cd-secs'),
    };
    let prev = {};
    function pad(n){ return String(n).padStart(2,'0'); }
    function tick(){
      const diff = Math.max(0, target - new Date());
      const d = Math.floor(diff / 86400000);
      const h = Math.floor(diff % 86400000 / 3600000);
      const m = Math.floor(diff % 3600000 / 60000);
      const s = Math.floor(diff % 60000 / 1000);
      const vals = { d: pad(d), h: pad(h), m: pad(m), s: pad(s) };
      for(const k in vals){
        if(prev[k] !== vals[k] && els[k]){
          els[k].textContent = vals[k];
          els[k].classList.remove('flip');
          void els[k].offsetWidth;
          els[k].classList.add('flip');
          prev[k] = vals[k];
        }
      }
    }
    tick();
    setInterval(tick, 1000);
  })();

  // Scroll reveal
  (function(){
    const items = document.querySelectorAll('.reveal');
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if(reduce){ items.forEach(el => el.classList.add('in')); return; }
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if(entry.isIntersecting){
          entry.target.classList.add('in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold:.2, rootMargin:'0px 0px -60px 0px' });
    items.forEach(el => io.observe(el));
  })();

  // Stat count-up
  (function(){
    const grid = document.getElementById('stats-grid');
    if(!grid) return;
    const nums = grid.querySelectorAll('.num');
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if(reduce) return; // numbers already show their final value in the markup
    let done = false;
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if(entry.isIntersecting && !done){
          done = true;
          nums.forEach(el => {
            const target = parseInt(el.textContent, 10);
            if(isNaN(target)) return;
            let cur = 0;
            const step = () => {
              cur += 1;
              el.textContent = cur;
              if(cur < target) requestAnimationFrame(step);
              else el.textContent = target;
            };
            requestAnimationFrame(step);
          });
          io.disconnect();
        }
      });
    }, { threshold:.4 });
    io.observe(grid);
  })();
</script>
