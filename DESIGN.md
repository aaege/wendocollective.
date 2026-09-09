# Wendo 2.0 — Design System

## Manifesto

Wendo 2.0 is the ticket landing page and merch storefront for the second edition of Wendo Collective's festival — "Ambũi, Daughter of Warmth, Beauty & Love" — happening 31 October 2026 at Kentmere Club, Kenya. Its job is narrow and specific: make someone who already knows what Wendo is feel the pull of this specific edition, then get them to a ticket (Tickets-Fest, external) or a piece of merch (our own Pesapal-backed store) with as little friction as possible. It is not a brand museum or a scroll-jacking spectacle — it's a ticket counter dressed as a keepsake, borrowing its visual logic from the actual paper ticket: cream card stock, a maroon ink band, gold foil, a stamped edition number.

## Three Principles

1. **The ticket is the metaphor, not a graphic.** Every structural choice — the "2.0" stamp, the ticket-line band, the fine print near the final CTA — comes from an actual ticket's anatomy, not decoration bolted onto a generic hero.
2. **Real content over placeholder confidence.** Where a fact isn't confirmed (venue was TBC, lineup is genuinely secret), the page says so plainly instead of faking specificity. Where it's confirmed (Kentmere Club, 31.10.26), it commits fully.
3. **Cheap to load, cheap to maintain.** This runs on shared hosting with no build step. Every KB and every animation has to earn its place — no motion or asset that doesn't serve the ticket-purchase or merch-purchase path.

## Pages / Sections

Single page (`index.php`), server-rendered, sections in order:
Nav (sticky) → Hero (ticket stamp + video) → Stats strip → Ambũi (etymology + story + short video) → Journey (3 concept cards + 1.0→2.0 flame chips, linking to `recap.html`) → Experience Zones (10 Konas) → Sound policy tags → Merch (CMS-driven, Pesapal checkout) → Reviews (CMS-driven, only renders if published reviews exist) → Tickets closing band + fine print → Social carousel (photos + IG/TikTok) → Footer.

Supporting pages: `recap.html` (static, Wendo 1.0), `/admin/*` (CMS, not public), `/pesapal/*` (checkout flow, not navigated to directly).

## Animation Plan

- **Hero load**: one sequential fade/blur-up on page load (`.hero-load` classes, staggered 0.1s–1.5s), never re-triggers.
- **Scroll reveals**: `.reveal` elements fade+rise once via `IntersectionObserver` the first time they cross the viewport. No re-trigger on scroll-up.
- **Micro-interactions**: card lift + border-color shift on hover for merch/Kona/carousel cards (`transform` + `box-shadow`/`border-color` only — no layout properties).
- **Countdown**: digits pulse briefly (`transform: translateY` + `opacity`) only on value change, once per second, cheap.
- **`prefers-reduced-motion: reduce`**: every one of the above is disabled — hero content and reveals appear instantly at full opacity, hover lift is removed, countdown digits update with no transition.

## Video Strategy

- Two real YouTube videos: hero announcement (starts at 5:29) and an Ambũi short.
- **Desktop**: iframe loads immediately, autoplay + muted (browser policy requires muted autoplay; a visible unmute control is YouTube's own).
- **Mobile (≤640px)**: no iframe loads until tapped. Instead, a static YouTube thumbnail (`img.youtube.com/vi/{id}/hqdefault.jpg`) with a play button overlay. Tapping swaps it for the real iframe with autoplay. This is the actual data saving — an unloaded iframe costs nothing.
- Both videos always have a text fallback link to watch on YouTube directly, for when embeds are blocked entirely.

## Mobile-First Layout (320px → 1920px)

- Breakpoints used: 480px (nav collapse), 640px (video poster swap, hero dvh), 760px, 900px (grid column drops — already in place for concept/ambui/merch/review/comparison grids).
- **Nav**: below 480px, links collapse behind a hamburger; logo + "Get Tickets" stay visible always, since ticket conversion shouldn't require opening a menu.
- **Hero**: `min-height: 100dvh` (with `100vh` fallback) below 640px so it reads as a full "opening screen"; above that, height is content-driven — no artificial stretching on desktop.
- **Grids**: merch/reviews/Konas/comparisons run 3–4 columns on desktop, collapsing to 1–2 columns by 760–900px, 1 column by ~480px. Typography uses `clamp()` throughout rather than fixed per-breakpoint sizes.
- **Touch targets**: nav links, buttons, and the hamburger are sized to a 44px minimum tap area on mobile.

## Design Tokens

```css
/* Color — light (default) */
--bg: #F0EAE0;            /* warm cream page ground */
--surface: #FBF7EF;       /* card surface, slightly lighter than bg */
--surface-2: #E4D9C4;     /* deeper cream, alt rows / hover */
--text: #3A1810;          /* deep maroon-brown ink */
--muted: #8A6F5C;         /* muted warm brown, secondary text */
--burgundy: #5C1A1A;      /* primary accent — headlines, bands, buttons */
--burgundy-strong: #3F1210; /* darkest accent — nav bg, dark bands */
--marigold: #D4AF37;      /* gold — primary CTA fill */
--marigold-text: #A6791F; /* darker gold, readable as text on cream */
--teal: #C8752E;          /* burnt-orange halo / gradient accent */
--line / --line-strong: rgba(58,24,16, .15 / .3)

/* Color — dark (prefers-color-scheme / [data-theme=dark]) */
--bg: #1C0F0A; --surface: #271510; --surface-2: #331C14;
--text: #F0EAE0; --muted: #C9B3A0;
--burgundy: #D96B4A; --burgundy-strong: #A83E2E;
--marigold: #E8C468; --marigold-text: #E8C468; --teal: #E08A4B;

/* Typography */
Display: 'Baloo 2' (500/600/700/800) — headlines, buttons, labels, numerals
Body: 'Schibsted Grotesk' — paragraphs
Utility/mono: 'IBM Plex Mono' — dates, tags, small caps labels
Body minimum: 16px. Headings scale via clamp(), e.g. hero tagline
clamp(1.2rem, 3vw, 1.8rem).

/* Spacing / radius */
Section padding: clamp(3.25rem, 7vw, 5rem) vertical.
No rounded corners on structural blocks (ticket/card edges stay square,
consistent with the "printed ticket" metaphor) — small radius (.15–.2em)
reserved for the "2.0" stamp only.

/* Breakpoints */
480px — nav collapses to hamburger
640px — video poster/tap-to-play swap, hero 100dvh
760px / 900px — grid column drops (existing)

/* Motion */
fadeUp: opacity 0→1, translateY 18px→0
blurUp: opacity 0→1, blur(8px)→0
Durations: .7–1s, cubic-bezier(.22,1,.36,1) for the heavier reveals.
All disabled under prefers-reduced-motion: reduce.
```
