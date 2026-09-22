# RESPONSIVE-GUIDE — PLV-AlumNet (General · Alumni · Employer views)

This project's **general (guest), alumni and employer** pages are responsive on desktop, tablet, phone and small phone.
The **super-admin / admin views were not touched** (see §10).

The whole responsive layer is **one stylesheet, one script and three small partials**. Nothing else needs to be edited to
tweak sizes, breakpoints, the hamburger menu, footers, modals or toasts.

| | |
|---|---|
| Stylesheet (all media queries live here, only here) | `public/assets/css/responsive-public.css` |
| Script (hamburger / sidebar behaviour) | `public/assets/js/responsive-nav.js` |
| Loader (adds the CSS + JS + scope classes; included by the general/alumni/employer headers and the 4 auth pages) | `resources/views/partials/responsive-assets.blade.php` |
| Hamburger button | `resources/views/partials/rp-hamburger-button.blade.php` |
| Hamburger menu for logged-out visitors | `resources/views/partials/guest-sidebar.blade.php` |

---

## 1. WHERE TO FIND EVERYTHING

| What I want to change | File path | Section / search keyword |
|---|---|---|
| Breakpoint numbers (desktop / tablet / mobile / small) | `public/assets/css/responsive-public.css` | header comment **BREAKPOINTS**; search `@media (max-width` |
| Font sizes, spacing, header height, logo size, sidebar width, hero size, tap-target size **per breakpoint** | `public/assets/css/responsive-public.css` | **[ROOT VARIABLES]** (`--rp-fs-title` / `--rp-fs-heading` …, `--rp-page-pad`, `--rp-card-pad`, `--rp-header-h`, `--rp-sidebar-w`, `--rp-tap`, `--rp-hero-title`, …) |
| Mobile header (logo left + hamburger right), header height, logo size | `public/assets/css/responsive-public.css` | **[HEADER / NAVBAR]** |
| Header markup (logo, nav links, bell/messages icons) | `resources/views/partials/header-general.blade.php`, `header-alumni.blade.php`, `header-employer.blade.php` | classes `rp-header`, `rp-header-logo`, `rp-header-nav`, `rp-nav-link`, `rp-header-actions`, `rp-profile-toggle` |
| Where the header switches to the hamburger (1023px) | `public/assets/css/responsive-public.css` **and** `public/assets/js/responsive-nav.js` | CSS: `@media (max-width: 1023px)`; JS: `COMPACT_QUERY` (**change both together**) |
| Hamburger button look / size | `public/assets/css/responsive-public.css` | **[HEADER / NAVBAR]** → `.rp-hamburger` |
| Hamburger menu panel width / spacing / row height / close button | `public/assets/css/responsive-public.css` | **[HAMBURGER SIDEBAR / MENU]** |
| Hamburger menu behaviour (open/close, ESC, overlay, scroll lock, aria, resize reset) | `public/assets/js/responsive-nav.js` | `init()`, `applyState()`, `forceClose()`, `rp-menu-open` |
| **Links shown in the mobile menu** (alumni + employer) | `resources/views/partials/user-sidebar.blade.php` | `$rpNav` (per role) → `<nav class="rp-only-compact …">` |
| **Links shown in the mobile menu** (logged-out visitors) | `resources/views/partials/guest-sidebar.blade.php` | `$rpGuestLinks` |
| Clickable **profile container** at the top of the alumni/employer menu | markup: `resources/views/partials/user-sidebar.blade.php` (`<a … class="rp-profile-link">`, route `user.profile`); style: `responsive-public.css` | **[HAMBURGER SIDEBAR / MENU]** → `(A) CLICKABLE PROFILE CONTAINER` |
| **ALL-CAPS menu labels** (switch to Title Case / as-written) | `public/assets/css/responsive-public.css` | **[HAMBURGER SIDEBAR / MENU]** → `(B) CONSISTENT ALL-CAPS LABELS` — one `text-transform` rule with the switch instructions |
| Footer (2-column tablet / stacked mobile) | CSS: `responsive-public.css`; markup: `resources/views/partials/footer.blade.php`, `footer-alumni.blade.php`, `footer-employer.blade.php` | **[FOOTER]**; classes `rp-footer*` |
| Hero banners, About page, landing sections, "fixed" backgrounds | `public/assets/css/responsive-public.css` | **[LANDING / GENERAL PAGES]** |
| Job cards, job-board tabs, alumni-directory table, tiny-text floor (12px) | `public/assets/css/responsive-public.css` | **[CARDS & LISTINGS]**; classes `rp-job-*`, `rp-tabs`, `hide-sm`, `hide-md`, `rp-table-directory` |
| Form fields (16px inputs, stacked columns) | `public/assets/css/responsive-public.css` | **[FORMS]** |
| Tables (sideways-scroll wrappers, hidden low-priority columns) | `public/assets/css/responsive-public.css` | **[TABLES]** and **[CARDS & LISTINGS]** → `.hide-sm` / `.hide-md` |
| Charts | `public/assets/css/responsive-public.css` | **[CHARTS]** (`.rp-chart`; these views contain no charts today) |
| **Modals** — fit, scroll, z-index, pinned buttons, touch targets | `public/assets/css/responsive-public.css` | **[MODALS]** |
| Modal open/close animation & JS timeouts (**unchanged**) | the modal's own file (see §6) and `resources/views/partials/ui-animations.blade.php` | `openAnimatedModal`, `closeAnimatedModal`, `duration-200` |
| **Success / error / validation toasts**, alert modal | CSS: `responsive-public.css` → **[MESSAGES / TOASTS / ALERTS]**; behaviour + timeouts (**unchanged**): `partials/success.blade.php`, `error.blade.php`, `error-toast.blade.php`, `partials/alert-modal.blade.php` | `setTimeout(closeSuccessToast, 5000)` etc. |
| Toast stacking when two show at once | `public/assets/js/responsive-nav.js` | `stackToasts()` |
| Alumni messaging page (list ↔ thread on phones) | CSS: `responsive-public.css` → **[MESSAGING PAGE]**; markup: `resources/views/alumni/messages.blade.php` | `rp-chat`, `data-rp-chat-state`, `rp-chat-back` |
| Pagination on phones (prev / current / next) | `public/assets/css/responsive-public.css` | **[PAGINATION]** |
| Login / registration / forgot / reset pages | `public/assets/css/responsive-public.css` | **[LOGIN & REGISTRATION]** |
| Employer applicants toolbar (bulk actions) | `public/assets/css/responsive-public.css` | **[JOB APPLICANTS]**; classes `rp-applicant-toolbar`, `rp-bulk-actions` |
| Heading / card-padding scale on small screens | `public/assets/css/responsive-public.css` | **[TYPOGRAPHY & SPACING SCALE]** |
| Floating chat button + panel | `public/assets/css/responsive-public.css` | **[MODALS]** → "Floating assistant" |
| Which pages load the responsive layer | `resources/views/partials/responsive-assets.blade.php` | `@include('partials.responsive-assets', ['rpRole' => …])` |

Tip: every section in the stylesheet starts with a banner such as `[MODALS]`, and the file's first comment is a table of
contents. Inside each section the order is always **Desktop → Tablet → Mobile → Small mobile**.

---

## 2. Breakpoints and size scale

| Name | Width | What changes |
|---|---|---|
| **Desktop** | ≥ 1024px | The original design, untouched — except the two sidebar changes below (they apply at every size). |
| **Tablet** | 768 – 1023px (`@media (max-width: 1023px)`) | Header collapses to logo + hamburger; footer becomes 2 columns; smaller headings/paddings. |
| **Mobile** | ≤ 767px (`@media (max-width: 767px)`) | Stacked layouts, 16px form inputs, stacked footer, one-pane messaging, simplified pagination. |
| **Small mobile** | ≤ 480px (`@media (max-width: 480px)`) | Slightly smaller paddings, 13px menu labels so the longest label never wraps at 320px. |

Sizes are CSS custom properties (`--rp-*`) defined once per breakpoint in **[ROOT VARIABLES]**. Example — "make the mobile header
taller": change `--rp-header-h` in the *Mobile ≤767px* block.

Why the hamburger starts at 1023px: the original desktop header (logo + 5 links + icons) only just fits at 1024px (its content is
about 960px wide plus 2 × 64px padding), so below that it would wrap or overflow. Because of that, 1024px is the smallest "desktop"
width. If you ever want desktop navigation on tablets you would have to shrink that header first; the two numbers to change are the
CSS media queries and `COMPACT_QUERY` in the script.

---

## 3. How the CSS is scoped (why admin pages cannot be affected)

1. `partials/responsive-assets.blade.php` is included **only** by the general / alumni / employer headers and the four auth pages.
   Admin and super-admin pages never load the stylesheet or the script.
2. It also adds `rp-scope` (and `role-general` / `role-alumni` / `role-employer`) to `<html>`. **Every** rule in the stylesheet is
   prefixed `html.rp-scope …`, so even if a shared partial (toasts, pagination, post-job modal…) is rendered in an admin page, no rule can
   match there.
3. The `html.rp-scope` prefix also lifts each selector above Tailwind's single-class utilities, so `!important` is not needed.
   The **only** `!important` in the file is the messaging card height (it overrides an inline `style`); it is commented.

Shared partials that are also used by admin pages (`success`, `error`, `error-toast`, `pagination`, `image-lightbox`, `post-job-modal`,
`alert-modal`, `action-dropdown-fix`) were **not edited**.

---

## 4. Header, hamburger menu and footer

* **Mobile / tablet header:** logo on the left, hamburger on the right (bell + messages stay visible for alumni/employer). The desktop nav links
  are hidden ≤1023px and live inside the menu instead.
* **One menu panel, not two.** The hamburger opens the *existing* `#userSidebar` panel (same slide-in animation, `duration-300 ease-in-out`,
  same `toggleSidebar()` function, same overlay). On ≤1023px the main navigation is merged into it: `HOME / EVENTS / ANNOUNCEMENTS / JOB BOARD /
  DIRECTORY` (alumni), `HOME / ANNOUNCEMENTS / JOB BOARD / MY JOB POSTINGS` (employer), then **ACCOUNT SETTINGS** and **LOG OUT** — as in the mockups.
  The merged block is `display:none` on desktop, so the desktop panel keeps its original content.
* **Logged-out visitors** get `guest-sidebar.blade.php` (same panel design, ids and animation): close ×, centred logo, SIGN UP (outlined) /
  LOG IN (filled), then `HOME / ABOUT / EVENTS / ANNOUNCEMENTS / JOB BOARD`.
* **Close on:** overlay click, the × button, a link click, `Esc`, and when the window is resized past the breakpoint. The page does not scroll
  behind the open menu (`html.rp-menu-open`), the menu sits above the sticky header and the floating chat button, and `aria-expanded` / `aria-controls` /
  `aria-hidden` / `inert` are kept in sync (`public/assets/js/responsive-nav.js`).
* **Profile container (change A, all screen sizes):** the avatar + name block at the top of the alumni/employer menu is now **one `<a>`** pointing to the
  existing View Profile route (`user.profile`). It has hover, pressed and keyboard-focus (`:focus-visible`) states, a ≥44px height and no nested links/buttons.
  The existing *View Profile* item further down was kept.
* **ALL-CAPS labels (change B, all screen sizes):** one CSS rule under `[HAMBURGER SIDEBAR / MENU]` sets `text-transform: uppercase` (+ one shared
  letter-spacing / weight) on the menu headings, links and log-out button. It is display-only: emails, company names, the user's typed data and stored data
  are not touched. To change the casing, edit that rule (comment shows `capitalize` / `none`).
* **Footer:** desktop unchanged; tablet = 2-column; mobile = the mockup's logo lock-up, one list (`HOME / ABOUT / PRIVACY POLICY / TERMS OF USE / FAQs`),
  seal + address + globe/Facebook icons, orange © bar. (Events / Announcements footer links are hidden on mobile to match the mockup.)

---

## 5. Cards, tables, forms, pagination

* **Tables:** strategy = *keep the table inside its own horizontal-scroll wrapper and hide low-priority columns*. The alumni directory hides **Program** and
  **College** below 768px (`.hide-sm`), keeps **Full name, Batch, Actions**. The page itself never scrolls sideways.
* **Cards:** job cards stack their meta, "Valid until" and buttons; employer cards wrap stats and buttons.
* **Forms:** inputs are 16px on phones (prevents iOS zoom-on-focus), two-column field grids stack at ≤480px, buttons are ≥40px tall.
* **Pagination:** phones show *previous · current · next* only; desktop keeps every page number.
* **Text floor:** nothing renders below 12px on compact screens (toasts and menu labels ≥13px).

---

## 6. Modals — what was and was not changed

Open/close mechanics, animation classes (`opacity-*`, `scale-*`, `transition-*`, `duration-*`) and every JS `setTimeout` are **exactly as they were**.
Only geometry is adjusted at ≤1023px: safe-area padding, `max-height: 90vh` **and** `90dvh` (the panel scrolls inside itself), pinned action rows,
z-index above the header/sidebar/chat button, ≥40px touch targets, `touch-action: manipulation`. Closed modals stay `hidden` / `pointer-events:none`.

| Modal (id) | File | Animation / timing (unchanged) |
|---|---|---|
| `alumniIdStatusModal`, `yearbookStatusModal` | `alumni/dashboard.blade.php` | `openAnimatedModal` / `closeAnimatedModal` (ui-animations): overlay fade + panel scale 200ms |
| `profileModal` | `alumni/directory.blade.php` | show/hide (no animation) |
| `resumeBuilderOverlay` | `alumni/resume-builder-modal.blade.php` | fade + scale, `duration-200` |
| `resumeEditorOverlay` | `alumni/resume-editor-modal.blade.php` | show/hide |
| `termsModal`, `dataPrivacyModal`, `successModal` | `auth/register.blade.php` | fade + scale, `duration-200` |
| `successModal` (forgot password) | `auth/forgotPassword.blade.php` | show/hide |
| `jobModal` | `partials/job-detail-modal.blade.php` (guest/alumni), `general/jobPostings.blade.php` (employer) | show/hide; share tooltip 2000ms |
| `jobApplyModal`, `jobApplySuccessModal` | `partials/job-apply-modal.blade.php` | show/hide; field expand `duration-300` |
| `companyReviewModal` | `partials/company-review-modal.blade.php` | as coded |
| `noticeDetailModal`, `interestConfirmModal` | `partials/notice-detail-modal.blade.php` | overlay fade + panel scale 200ms; interest confirmation auto-close 2500ms |
| `messagingGuidelinesModal`, `messageViolationModal` | `partials/messaging-guidelines-modal.blade.php`, `message-violation-modal.blade.php` | fade + scale, `duration-200` |
| `postJobModal`, `postConfirmModal`, `pendingModal` | `partials/post-job-modal.blade.php` | show/hide |
| `editPostModal` | `general/jobPostings.blade.php` | show/hide |
| `jobViewModal`, `applicationViewModal`, `bulkActionConfirmModal` | `general/jobApplicants.blade.php` | show/hide |
| `imageLightbox` | `partials/image-lightbox.blade.php` | show/hide |
| `siteAlertModal` | `partials/alert-modal.blade.php` | as coded |
| Sidebar `#userSidebar` + `#menuOverlay` | `partials/user-sidebar.blade.php`, `guest-sidebar.blade.php` | slide `duration-300 ease-in-out` |
| Notification popup `#notificationPopup` | `partials/user-sidebar.blade.php` | scale/opacity `duration-300` |

Modals whose form sits below a tall header (the resume **builder** wizard) keep their Save/Continue buttons at the end of the scrolling panel instead of pinned.

## 7. Messages / toasts — what was and was not changed

| Message | File | Fade/slide | Auto-dismiss (unchanged) |
|---|---|---|---|
| Success toast `#successToast` | `partials/success.blade.php` | 300ms | 5000ms |
| Session error toast `#sessionErrorToast` | `partials/error.blade.php` | 300ms | 7000ms |
| Validation toast `#errorToast` | `partials/error-toast.blade.php` | 300ms | 8000ms |
| Inline toasts (`showToast`, login / change password …) | the page itself | 300ms | 8000ms |
| Job-card "Link Copied!" tooltip | `partials/job-post-card.blade.php`, `job-detail-modal.blade.php` | 300ms | 2000ms |

On ≤1023px toasts get safe-area padding, `min(92vw, 28rem)` width, ≥13px text and 40px dismiss buttons; two toasts at once are stacked by `stackToasts()`
(margin only). They stay above everything except the alert modal.

---

## 8. Adding a new page or modal

* **New page:** include `partials.header-general` / `header-alumni` / `header-employer` (the responsive layer comes with it). If the page has its own
  `<head>` (like the auth pages) add `@include('partials.responsive-assets', ['rpRole' => 'general'])` before `</head>`.
* **New modal:** give the overlay `fixed inset-0` and an id ending in `Modal` or `Overlay` — the compact-screen rules (scroll, safe area, touch
  targets) are matched by that shape, so no CSS edit is needed. Put its buttons in the panel; use `hidden` for the closed state.
* **New table:** wrap it in `overflow-x-auto`; add `hide-sm` (hidden ≤767px) or `hide-md` (hidden ≤1023px) to `<th>`/`<td>` pairs that can be dropped.

---

## 9. Checking your work (browser DevTools)

Open DevTools → device toolbar and try: **320×568**, **360×800**, **375×667**, **390×844**, **414×896**, **768×1024**, **820×1180**, **1024×768**,
**1280×800**, **1440×900**, **1920×1080**, and the same phones **rotated**. On each: no horizontal scrollbar; menu opens/closes; modals fit and scroll;
toasts are readable. Desktop (≥1024px) should look exactly as before, apart from the two sidebar changes.

---

## 10. Scope

Files changed or added for this work are all in the general / alumni / employer layer:
`public/assets/css/responsive-public.css`, `public/assets/js/responsive-nav.js`, `resources/views/partials/{responsive-assets,rp-hamburger-button,guest-sidebar}.blade.php`
(new) and light markup hooks in `partials/header-*.blade.php`, `partials/footer*.blade.php`, `partials/user-sidebar.blade.php`, `partials/job-post-card.blade.php`,
`partials/alumni-directory-table.blade.php`, `auth/{login,register,forgotPassword,resetPassword}.blade.php`, `alumni/messages.blade.php`,
`general/{jobBoard,jobPostings,jobApplicants}.blade.php`. No controller, route, model, migration or admin / super-admin view was modified.
