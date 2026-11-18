# Oola Systems — Architecture & Design Review

Date: 2026-11-19
Reviewer: OpenCode (AI engineering assistant)
Scope: Full-stack Laravel application — public website, admin panel, AI chatbot, content pipeline, deployment posture.
Method: Static review of codebase, routes, controllers, views, CSS, config (no production access).

---

## 1. Executive summary

Oola Systems is a **Laravel 11 + Blade + Bootstrap 5 monolith** with:

- A public marketing site (home, about, services, industries, portfolio, careers, contact).
- A **file-based CMS** for tutorials and OolaLabs project catalogues (~300 markdown chapters, parsed on demand).
- An **AI lead-capture pipeline** (chatbot → Gemini/Ollama → inquiry → queued analysis job).
- An **admin panel** for inquiries, contacts, careers, visitors, users, with visitor tracking.
- A single-purpose **PWA-adjacent static design system** (`public/design-system.html`).

The domain model is sound and the frontend is cohesive. The main risks are **security hygiene in dev/ops routes**, **unmeasured performance** (monolithic CSS, on-demand markdown parsing, heavy unoptimized imagery), and **zero automated testing**. None of this requires a rewrite — it requires disciplined, phased cleanup.

---

## 2. Current architecture at a glance

```
Browser
  └─ CDN: Bootstrap 5.3, Bootstrap Icons, Google Fonts (Inter, JetBrains Mono)
  └─ public/css/style.css            ← single 3,900-line design-system sheet (every page)
  └─ public/js (none) + inline JS    ← scripts.blade.php (~700 lines inline)
  └─ jQuery-free vanilla JS

Laravel (oolasystems.local → public/)
  routes/web.php
   ├─ Public pages  → PageController (home, about, services, industries, portfolio, …)
   ├─ Careers       → CareerController@publicIndex
   ├─ POST contact  → ContactController@store        (captcha, rate-limited)
   ├─ POST inquiry  → InquiryController@store        (captcha)
   ├─ POST chatbot  → ChatbotController@message|reset
   ├─ /login → AuthController                        (admin auth, Laravel session guard)
   ├─ /admin/* → auth-protected admin (inquiries, contacts, careers, users, visitors)
   ├─ /tutorials/* → TutorialController@index|show   (file-based markdown CMS)
   ├─ /industrial-training → ProjectController@index|show (file-based markdown catalogue)
   ├─ /refresh-captcha/{type}                        (session-based math captcha)
   └─ /dev/*                                         ⚠ see F-01

Content pipeline
  resources/views/content/**/*.md → parse at request time → CommonMark → HTML → Blade

AI pipeline
  ChatbotController → ChatbotService → AIProviderFactory → GeminiProvider|OllamaProvider
    └─ ready for inquiry → InquiryController@store (chatbot mode)
    └─ AnalyzeInquiryJob (queued, QUEUE_CONNECTION=database) → AI lead analysis → admin dashboard

Infra
  MySQL (XAMPP) + queue worker (required) + Ollama (docker-compose, optional)
  Local: APP_ENV=local, APP_DEBUG=true, APP_URL=127.0.0.1:8000
```

### Data & file layout notes
- Local MySQL database dumps (`oolasystems_db.sql`, `oolasystems_sample_data.sql`, `oolasystems_test_data.sql`) live in the project root — **not git-tracked** (verified) but physically present; treat as sensitive local data.
- `.env` is **not tracked** (verified) and contains live keys (Gemini, Slack, Postmark/Resend, AWS placeholders, DB creds).
- Single-commit history (`a7e49bd first commit`).

---

## 3. What is already good (keep)

- **Clear MVC layering** — thin controllers, dedicated services (`ChatbotService`, `AILeadService`), models only where needed.
- **Accessible-by-default frontend discipline** — skip link, `aria-current`, `prefers-reduced-motion` handling, semantic sections, alt text on brand assets.
- **SEO basics present** — per-page titles/meta descriptions, canonical URLs, og tags, Twitter card, `sitemap` page, robots.txt, sitemap-visible pages.
- **Captcha + inquiry flow separation** — chatbot inquiry form is cloned into DOM only when needed; IDs are prefixed to avoid collisions; captcha refresh is scoped.
- **AI provider abstraction** — factory pattern with retryable/non-retryable exception taxonomy and 120s timeouts; queued job keeps AI failures off the request path (DB queue).
- **Visitor tracking is cheap** — IP-hashed key with 30-min cache window; DB write only on first page view per IP per 30 minutes; failures never break the page.
- **Markdown content pipeline is elegant** — tutorials are versionable markdown; no DB migration burden for content changes.

---

## 4. Findings by severity

### 🔴 Critical

**F-01 — Public `/dev/*` endpoints can run migrations and clear caches**
`routes/web.php` exposes `/dev/run-migrate`, `/dev/run-migrate-careers`, `/dev/clear-cache`, each gated only by a hardcoded query-string key (`?key=oola@123`) committed to the repo. Anyone who reads the (public) source or guesses the key can:
- trigger `migrate --force` (schema mutation), and
- clear config/cache/view (DoS / cache poisoning after env changes).

This is not "hidden" — the key is in the git history. This must be removed before the site is deployed anywhere public.
(AGENTS.md forbids route changes without owner approval — flagging, not fixing.)

**F-02 — `/dev/*` are not the only public internals**
`laravel_commands.txt` (tracked) leaks local paths (`C:\Users\<user>\...`) and shell history snippets. Tracked files are world-readable to anyone with repo access; trivially removable.

### 🟠 High

**F-03 — One 60 KB raw (~10 KB gzip), 3,900-line stylesheet on every page**
`style.css` mixes public design system + admin dashboard + tutorials + chatbot + learnings mega menu + markdown tables. Public mobile users (primary audience, Gulf) pay for CSS they never use. Cache-busting is manual (`?v=1.0/1.1`) and inconsistent (already observed drift). Recommend splitting by audience (`public.css` critical path, `admin.css`, `tutorial.css`, `chatbot.css`) using the existing `@push('head')`/`@stack('head')` and loading admin/tutorial sheets only where needed.

**F-04 — Markdown re-parsed on every request, no caching**
Tutorial and project pages: `File::files()` scan → `preg_match` every chapter for its `# H1` to build the sidebar → CommonMark conversion of the requested chapter → HTML. No `Cache::remember` anywhere. For 30+ chapters/tutorial and ~300 files, TTFB is inflated and file IO is repeated on every keystroke-crawler hit. Fix: cache the chapter index and converted HTML, keyed on file mtime (or content hash).

**F-05 — Images are unoptimized**
- `images/home-hero.png` ≈ 976 KB (CSS background)
- `images/leaders/*.png` ≈ 1.4–1.5 MB each
- `images/services/*.png` ≈ 1.9–2.1 MB each
- `images/tutorials/**` many ≈ 1.5 MB each
- Portfolio ~350 KB each, screenshots elsewhere inflated
No WebP/AVIF, no responsive `srcset`, no `loading="lazy"` on below-the-fold `<img>`, hero as CSS `background` defeats lazy-loading. Directly impacts LCP, bandwidth cost (XAMPP/shared hosting), and Lighthouse scores.

**F-06 — Zero automated tests**
`tests/` contains only Laravel's skeleton `ExampleTest`. No feature tests for public pages, contact/inquiry validation, captcha flow, chatbot endpoints, admin auth, AI job. Every change is YOLO. The TODO file lists phases but nothing is machine-verified.

**F-07 — Google Search favicon broken (FIXED in this pass)**
Symptoms reported: Google shows a generic/wrong icon for `oolasystems.com`.
Root causes verified:
1. `public/favicon.ico` was **0 bytes** — the fallback URL Googlebot tries first served empty content.
2. Declared icon was `images/os-dark.ico` — a 361 KB, 16×16-first-frame "placeholder" marked "scheduled for replacement in the asset pass", dark-on-dark = muddy in Google's light circle.
3. `apple-touch-icon` pointed at the non-square 327×100 wordmark (Apple ignores it).
4. No `sizes`, no 48/192px PNG Google prefers, no manifest, no explicit 48px+ icon declared.
Fix applied: generated brand-accurate icon set from `public/images/oola-icon.webp` (converted to PNG, 1061×1020, navy brand artwork) — 16/32/48 multi-frame ICO, 48/180/192/512 PNGs, `site.webmanifest` with correct MIME via `.htaccess`, `head.blade.php` rewritten, verified live (links present, correct Content-Types, 200s, artwork visually checked).
**Note:** Google caches favicons; after deploy, the new icon appears only after re-crawl (days–weeks). Force it via Google Search Console → URL Inspection → "Request Indexing" for the homepage.

### 🟡 Medium

**M-01 — Dead/duplicate frontend code** (documented in `DESIGN-SYSTEM-CLEANUP.md`)
Chatbot v1 components unused; `learnings-mega-menu.css` duplicates `style.css`; static drafts (`resources.html`, `industries.html`) publicly reachable; `welcome.blade.php` + Vite/Tailwind scaffolding (`package.json`, `vite.config.js`, `resources/css|js`) unused — pages load `public/css/style.css` directly; `.section-purple` legacy alias; unused `os-logo-solid.png` (524 KB), `new-logo-solid.jpg`, `design-system.html` still publicly reachable.

**M-02 — `View::composer('*')` runs 3 DB queries for every view**
`AppServiceProvider` attaches to `*` — including admin (which may render many components), JSON-ish pages, and error pages. The counts only need the visitor counter; cache with 5-minute TTL. ⚠ Visitor counter is protected by AGENTS.md — propose, do not apply without approval.

**M-03 — No HTTP security headers**
Nothing sets `X-Frame-Options`/`frame-ancestors`, `X-Content-Type-Options`, `Referrer-Policy`, or a minimal CSP. `.htaccess` or middleware can add these safely (no behavior change).

**M-04 — `sitemap` is HTML, not XML; OG image is 327×100**
Google prefers `sitemap.xml` (HTML sitemap pages are fine for humans). og:image should be ≥1200×630 (current `os-logo.png` 327×100 renders tiny on LinkedIn/X). No JSON-LD (`Organization`/`ProfessionalService`) — easy rich-results/entity win for an agency. Fixing = new XML route (route change → needs approval) — but the XML can also be emitted from the existing HTML page controller? No — that changes behavior. Propose, don't apply.

**M-05 — Visitor tracking counts bots**
No UA filtering — crawlers inflate "Total Visitors". Cheap fix (skip `bot|crawler|spider` UA). ⚠ Visitor counter protected by AGENTS.md — propose only.

**M-06 — Local dev config looks production-plausible but isn't**
`APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=127.0.0.1:8000`, `QUEUE_CONNECTION=database`.
- Production must have `APP_ENV=production`, `APP_DEBUG=false`, real `APP_URL` (`https://www.oolasystems.com`), correct `SESSION_DOMAIN` (`.oolasystems.com`), and a **running queue worker** (`php artisan queue:work --queue=default` via supervisor/systemd) or `AnalyzeInquiryJob` never executes.
- Verify failed jobs (`failed_jobs` table) and set up alerting; verify `AI_PROVIDER=gemini` + key rotation policy; `APP_DEBUG=true` must never reach the public internet (it leaks stack traces, env values, SQL).

**M-07 — Chatbot fetch has 120s backend timeout; JS shows "thinking" indefinitely**
`GeminiProvider` uses `Http::timeout(120)`. If the provider hangs, the visitor waits up to 2 minutes (with a disabled input). Frontend has no client-side timeout/abort. UX risk — not a correctness bug.

### 🟢 Low

- **L-01** `laravel_commands.txt` tracked → delete (needs approval).
- **L-02** Inline styles in admin views (`inquiries/show`, `edit`) — works, inconsistent with design system.
- **L-03** `.eyebrow` font-size (`1.2rem` mono) too large vs brand spec; minor.
- **L-04** Admin dashboard CSS shipped to public pages (see F-03).
- **L-05** `theme-color #0A0A0A` fine; no `mask-icon` for Safari pinned tabs (optional).
- **L-06** `laravel_commands.txt` and `laraval_commands.txt`-style personal artifacts should never be committed; `.gitignore` covers `.env` but not such notes.

---

## 5. Refactor plan (phased, each independently shippable)

### Phase 0 — Done in this pass (2026-11-19)
- [x] Favicon rebuilt: 0-byte `/favicon.ico` → valid 3-frame 3.4 KB ICO; PNG set (48/180/192/512); `site.webmanifest` + correct MIME; `head.blade.php` rewritten; `robots.txt` verified crawlable; verified live in browser.
- **Note:** Google will show the new icon only after re-crawl — request indexing in Search Console.

### Phase 1 — Repo hygiene & safety (0.5–1 day, zero behavior change)
1. Remove `laravel_commands.txt` from tracking (`git rm`).
2. Decide fate of: `welcome.blade.php`, `public/resources.html`, `public/industries.html`, `public/design-system.html` (keep as internal reference? move to `storage/app/design/`?), `public/css/learnings-mega-menu.css`, chatbot v1 blades + CSS, unused Vite/Tailwind (`package.json`, `vite.config.js`, `resources/css|js`).
3. Add `.htaccess` security headers: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin` (no CSP yet — needs audit of inline scripts first).
4. Rotate dev/test keys if any were committed before cleanup (Gemini/Slack keys live in local `.env` only — confirm they never lived in git; they don't — single commit, `.env` ignored).

### Phase 2 — CSS architecture (1–2 days, visual parity required)
1. Split `style.css` into: `public.css` (critical: tokens, reset, typography, navbar, footer, forms, home sections), `admin.css` (dashboard, manage tables, KPI cards), `tutorial.css` (sidebar/navigation/code blocks/markdown), `chatbot.css` (ai-chatbot + inquiry form), `learnings.css` (mega menu — or keep inline in navbar).
2. Load selectively: public critical on all pages; admin only in admin layout; tutorial only in `layouts/tutorial.blade.php`; chatbot only in `layouts/app.blade.php` (already needed globally).
3. Remove duplicated rules and dead selectors identified in this review (nav brand merge, tutorial override removal, `.manage-table th` merge — reverted patch can be re-applied).
4. Consistent cache-busting: single `?v=` scheme, or better — hash-based filenames via a tiny build step (or rename on each release).

### Phase 3 — Media & performance (1–2 days)
1. Convert hero/services/leaders/portfolio/tutorials images to WebP (keep PNG fallback only where needed); expected 60–80% byte reduction.
2. `loading="lazy"` + `decoding="async"` on below-the-fold images; hero becomes `<img>` or keep CSS bg but compress (~150 KB target).
3. Add `og:image` 1200×630 + `og:image:width/height`; generate a real OG image (currently not crawlable as social card).
4. Fonts: keep Inter/JetBrains Mono; add `display=swap` (already present) — consider subsetting if LCP demands it.
5. Target Lighthouse ≥ 90 for Performance/Accessibility/SEO/Best-Practices on home + one tutorial + one project page.

### Phase 4 — Backend-light improvements (2–4 days)
1. Cache markdown: chapter index + converted HTML, keyed on `filemtime`; `Cache::rememberForever` with invalidation on deploy.
2. `View::composer('*')` → cache visitor stats 5 min (**needs owner approval — visitor counter**).
3. Add bot filtering to `TrackVisitor` (**needs owner approval — visitor counter**).
4. Confirm production queue worker: supervisor entry for `queue:work`, `failed_jobs` monitoring, retry backoff already set in AI exceptions — wire alerts.
5. XML sitemap: add `Route::get('/sitemap.xml', ...)` returning generated XML (**route change — needs owner approval**) or emit XML from `sitemap()` based on `Accept` header (also needs approval).
6. JSON-LD (`Organization`, `ProfessionalService`, `WebSite` with SearchAction) in `head.blade.php`.
7. `og:image` absolute URL + `og:image:width=1200` `og:image:height=630`.
8. M-07: add client-side `AbortController` (90s) + friendly retry message in chatbot JS.

### Phase 5 — Testing & CI (ongoing)
1. Feature tests: every public route returns 200; contact/inquiry validation (captcha, required fields, honeypot?); chatbot message endpoint (mock provider); admin auth redirect; visitor tracking doesn't blow up without DB.
2. Browser/Lighthouse CI job on PR (Performance/SEO/Accessibility ≥ 90).
3. Pest/PHPUnit conventions; run in `php artisan test`.
4. Pre-commit: `.editorconfig` already present — add Pint (`vendor/bin/pint`) for PSR-12.

### Phase 6 — Optional larger refactors (needs separate scoping)
1. Move tutorial/project content index into SQLite/MySQL with admin CRUD (kills file-scan per request; enables search, tags, view counts).
2. Split admin into its own Blade layout (`layouts/admin.blade.php`) — currently admin pages extend the public layout (navbar, chatbot, visitor tracking all fire on admin pages).
3. PWA: service worker + offline page for tutorials (learners on poor Gulf connections).
4. i18n: Arabic/English RTL support (target market is UAE/Saudi — strong differentiator).

---

## 6. What needs your explicit approval (not touched)

Per AGENTS.md I have **not** modified: routes, controllers, models, migrations, DB, auth, visitor counter, captcha, forms.
Approvals needed for:
- [ ] F-01: remove or harden `/dev/*` routes (strongest recommendation — security).
- [ ] M-02/M-05: cache visitor stats / filter bots (visitor counter).
- [ ] M-04/Phase 4-5: XML sitemap route; admin layout split; `View::composer` caching.
- [ ] Phase 1 file deletions (list above).
- [ ] Phase 6 larger refactors.

## 7. Suggested immediate next steps

1. **Today:** request re-indexing of the homepage in Google Search Console (favicon fix) + log F-01 as incident.
2. **This week:** Phases 1–2 (hygiene + CSS split, no behavior change, before/after screenshots).
3. **Next:** Phase 3 image optimization (highest user-visible win), then Phase 4 cache + queue worker verification, then Phase 5 tests.

---

*Prepared 2026-11-19. The only code change applied is the favicon fix (Phase 0), which is verified live. All other recommendations await sign-off.*
