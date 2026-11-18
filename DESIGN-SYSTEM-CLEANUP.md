# Oola Systems — Design System Audit & Cleanup

Date: 2026-11-18
Scope: Frontend design tokens, CSS architecture, brand consistency, dead code.
Out of scope (per AGENTS.md): routes, controllers, models, migrations, database,
authentication, visitor counter, captcha, contact/inquiry forms, chatbot logic.

> **STATUS — REVERTED (2026-11-18, same day)**
> At the site owner's request, the tomato re-brand and all associated cleanup
> were rolled back to the original committed state (teal `#35d0ba` theme).
> The eight tracked files were restored verbatim via `git restore` and the site
> is back to its original appearance. A machine-readable copy of the rolled-back
> changes is preserved at:
> `C:\Users\irsha\AppData\Local\Temp\opencode\design-system-cleanup-2026-11-18.patch`
> This document is kept for the audit record only; the sections below describe
> the (now reverted) proposed state, not the current site.


## Brand decision

The project documented **Tomato Red (#FF6347)** in `AGENTS.md` and in
`public/design-system.html` (DS v1.0), but `style.css` had been silently switched to
teal (`#35D0BA`), leaving three competing accent families live at once:

- teal (most interactive elements, buttons, active nav)
- tomato remnants (focus ring, body glows, `.service-icon`, `.tech-badge:hover`, AI chatbot)
- blue gradients (hero headline, navbar tagline, copy button)

**Decision: standardize on Tomato Red `#FF6347`** (matches the documented design system).
Teal is fully removed from active stylesheets.

## Changes

### `public/css/style.css`
- `:root` is now the single source of truth for the palette:
  - `--color-primary: #ff6347`, plus new `--color-primary-hover` (`#e0573e`),
    `--color-primary-soft` (12% tint), `--color-accent-soft`.
  - New `--tutorial-*` tokens (sidebar surfaces, warm-neutral borders/hover, accent
    wired to `var(--color-primary)`), replacing the three incompatible definitions
    that previously existed (`.tutorial-page` scope that never matched, a stray
    `:root` override, and `#35d0ba` literals).
- Tutorial sidebar & chapter navigation: removed the ~200-line duplicate override
  block that relied on `!important` everywhere; consolidated into one canonical
  definition (same visual behaviour).
- Removed unused `.tutorial-page` scoping (it targeted a class no view renders).
- Recoloured the `.ai-chatbot-toggle` (live chatbot launcher), `AI CHATBOT` pulse
  keyframes and focus outline from teal to brand tomato.
- Markdown tables: replaced the off-brand "Oola Systems Purple Theme" with the
  brand theme (soft primary header, neutral rows, tomato hover).
- Admin dashboard: teal `rgba(53, 208, 186, …)` KPI/chart/metric accents → tomato.
- Hero headline `.hero-gradient`: blue gradient → warm white→peach→tomato gradient.
- Navbar tagline line-1 (white→ice-blue) → warm white→peach; line-2 (blue shimmer)
  → tomato shimmer; line-2 glow shadow matches.
- `.service-icon` / `.industry-icon` / `.resource-icon` (home page): unified to brand
  (were tomato / teal / teal).
- `.tech-badge:hover`, `.oola-dropdown__list a:hover`: hardcoded `#ff6347` → token.
- Fixed invalid declaration `padding: auto 10px` on `.oola-dropdown__list a:hover`.
- Merged duplicate rules: `.oola-navbar__brand`, `.oola-navbar__logo` (base
  `.oola-navbar__container` block vs later `Brand` block), `.manage-table th`.
- `globals`: added `transform: none` to the tutorial `prefers-reduced-motion` block.
- `body` keeps `scroll-behavior: smooth` (global reduced-motion reset already
  neutralises it, section 11).

### `public/css/oolalabs-menu.css`
- OolaLabs mega-menu accents (`#31cdb8`) → `var(--color-primary)`; hover wash
  `rgba(49,205,184,.1)` → `rgba(255,99,71,.1)`.

### `public/css/oolalabs-projects.css`
- Project tabs / card hover / icon chip accents (`#31cdb8` / `rgba(49,205,184,…)`)
  → `var(--color-primary)` / `rgba(255,99,71,…)`.

### Cache busting
- `style.css` `?v=1.0` → `?v=1.1` (head.blade.php, design-system.html, resources.html,
  industries.html).
- `oolalabs-menu.css` `?v=4` → `?v=5` (components/navbar.blade.php).
- `oolalabs-projects.css` `?v=4` → `?v=5` (projects/index.blade.php).

### Not changed (deliberately)
- No routes, controllers, models, views logic, forms, captcha or chatbot behaviour.
- No file deletions: the following dead code was identified but left in place
  pending sign-off — see "Remaining recommendations".
- `public/design-system.html`, `resources.html`, `industries.html`: kept, but note they
  are static drafts of Blade pages, publicly reachable at `/design-system.html` etc.

## Verification
- `http://oolasystems.local/` — HTTP 200, no console errors.
- Hero, services/industries/portfolio sections, CTA, footer counters, AI chatbot
  launcher: all render in the tomato brand.
- `/tutorials/agenticai` — sidebar + active chapter + nav buttons render correctly
  with the consolidated tutorial CSS (no `!important` needed).
- `/industrial-training` — OolaLabs catalogue, category tabs and project icons
  render on-brand; no console errors.
- Blade diffs contain only version-string bumps (no formatting/line-ending churn).

## Remaining recommendations (not applied — need sign-off)
1. Delete orphaned chatbot v1 files + their CSS (~170 lines):
   `components/chatbot/launcher|panel|message.blade.php` (only `widget.blade.php` is
   included), `.oola-chatbot-*` / `.oola-chat-bubble*` rules in `style.css`
   (approx. lines 2790–3260).
2. Delete unreferenced `public/css/learnings-mega-menu.css` (its content is already
   inlined in `style.css`, section "LEARNINGS MEGA MENU").
3. Remove static drafts `public/resources.html` and `public/industries.html` from the
   web root (or move to a non-public folder).
4. Remove `resources/views/welcome.blade.php` (Laravel default, unused) and decide on
   the unused Vite/Tailwind scaffolding in `resources/css|js` + `package.json`.
5. Favicon: `public/favicon.ico` is 0 bytes; `head.blade.php` references the 370 KB
   `images/os-dark.ico`. Generate a 32/180 px set and reference it.
6. Compress `public/images/home-hero.png` (~976 KB) and the unused
   `images/os-logo-solid.png` (~536 KB).
7. `.eyebrow` uses `1.2rem` mono with uppercase — consider `0.75–0.8rem` brand spec.
8. `.section-accent` / `.section-purple` legacy alias — keep until pages stop using it.
