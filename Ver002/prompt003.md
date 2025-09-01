# 🟣 Claude Code – System-Wide Style Unification (Plan → Execute)

**Instructions:**  
Switch to **`⏵⏵ accept edits on`** (Alt+M) and apply changes automatically—without confirmations.

---

## 0) Principles & References (For Guidance)

- Use **design tokens via CSS custom properties** (`--variables`) for colors, spacing, typography, radii, shadows. If Bootstrap is present, map relevant tokens to Bootstrap variables (`--bs-*`) for easy theming.
- Structure your styles following **ITCSS**: layers of organization from settings → tools → generic → elements → components → utilities. This promotes scalability and maintainability.
- Use **CSS logical properties** (e.g., `margin-inline`, `padding-inline`) wherever feasible to support both **LTR and RTL** languages.
- Configure **Chart.js** once through global defaults, sourcing colors and typography from these tokens, rather than setting styles per chart.

---

## 1) Visual Baseline

- Treat `app/views/dashboard/index.php` as the **visual reference**: focus on its structure, spacing, gradients, typography, cards, buttons, tables, forms, shadows.
- Apply its aesthetic consistently across **all** pages in the project.

---

## 2) Centralize Assets

- Create a single global stylesheet (e.g., `public/assets/css/system.css`) containing:
  - **Design tokens** (colors, spacing, radii, shadows, typography).
  - **Utility classes** (for spacing, visibility, text, buttons).
  - **Component styles** for cards, buttons, forms, tables, list groups, etc., matching the dashboard look.
  - Optionally, map tokens to Bootstrap variables (`--bs-*`) if Bootstrap is in use.
- Create a single global JavaScript file (e.g., `public/assets/js/system.js`) that:
  - Initializes common UI behaviors (tooltips, modals, etc.).
  - Sets **Chart.js global defaults** for consistent theming (colors, grid style, legend placement) based on the design tokens.

---

## 3) Unify Layout

- Ensure there's **one base layout** (e.g., `app/views/layouts/app.php` or `main.php`) that:
  - Loads `system.css` and `system.js` once.
  - Defines the header, navbar, footer, and container structure based on the dashboard.
- All existing and future pages must extend this layout. Remove redundant `<link>` and `<script>` tags from individual pages.

---

## 4) Refactor the Entire Codebase

- Search through the **entire repository**, including all folders and previously auto-generated content.
- For every view/template:
  - **Remove inline `<style>` blocks and page-specific CSS/JS files.**
  - Replace ad-hoc styling with semantic classes from the global stylesheet.
  - Substitute hard-coded values with **semantic tokens** (e.g., `primary`, `secondary`, `success`, `warning`, `info`, `danger`, `text`, `muted`).
  - Normalize:
    - Typography: heading scales, body text, muted text, link appearance.
    - Spacing: use margin/padding utilities derived from tokens.
    - Buttons: consistent visual style, radii, hover/active states.
    - Forms: input height, focus indicators, validation/help text.
    - Tables: compact spacing, row hover, responsive behavior.
    - Cards/containers: consistent shadow, radius, padding.
    - Alerts/badges: consistent color palette.
  - Use **logical CSS properties** for improved RTL compatibility.
- Specifically for the **dashboard page**:
  - Extract its inline styles into the global stylesheet, using reusable classes.
  - Ensure charts rely **only** on the global Chart.js theming.

---

## 5) RTL & Accessibility

- Favor logical CSS properties to stay neutral to text direction.
- Validate color contrast and ensure visible focus outlines across components.

---

## 6) Clean-Up Process

- Delete or disable redundant stylesheets and scripts that were previously per-page.
- Remove inline styles and duplicate CSS.
- Confirm only one global stylesheet and one global UI script remain in the project.

---

## 7) Chart.js Theme Management

- Use CSS tokens as the theme source for charts by configuring Chart.js in the global JS file. Avoid per-chart style settings.

---

## 8) Process Safety & QA

- Work in a new Git branch: `feat/system-wide-style-unification`.
- Commit in logical chunks (e.g., layout, token update, page group refactor).
- After each commit, visit key routes to catch visual regressions immediately.
- Maintain a **CHANGELOG** enumerating each file changed with rationale.
- List any pages or components requiring manual review in a summary.

---

## 9) Acceptance Criteria

- UI is powered by:
  - One base layout
  - One global CSS
  - One global JS
- All pages visually align with the Dashboard's design system.
- Theme adjustments are centralized and global.
- Chart styling is CD via Chart.js global defaults.
- No inline styles remain (unless briefly documented exceptions).
- RTL support is strengthened through logical layouts.

---

###  End Deliverables (Post-Execution by Claude)

Claude should return:
1. A **summary** of design tokens, utilities, and components created.
2. A **file-by-file CHANGELOG** detailing changes with brief rationale.
3. A list of **removed or merged CSS/JS assets**.
4. Any **manual-review items** that require human oversight.

---

**Proceed now. Apply these modifications automatically across the entire repository.**
