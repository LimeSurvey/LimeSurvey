# Admin Themes Assets

This directory contains the SCSS source files for LimeSurvey admin themes.

## Prerequisites

Make sure [Node.js](https://nodejs.org/) and [Yarn](https://yarnpkg.com/) are installed, then install all dependencies from the **project root**:

```bash
yarn install
```

## Build Commands

Run from the **project root** directory:

### One-time build

```bash
yarn gulp build_admintheme
```

This runs the following tasks in parallel:
- **CSS (LTR)**: Compiles `Sea_Green/sea_green.scss` via Sass, autoprefixes and minifies with cssnano, outputs to `themes/admin/Sea_Green/css/`.
- **CSS (RTL)**: Same as above but converted to RTL via `rtlcss`, output files are suffixed with `-rtl`.

### Watch mode (rebuild on file changes)

```bash
yarn gulp watch_admintheme
```

Watches all `assets/admin_themes/**/*.scss` files and rebuilds both LTR and RTL CSS on any change.

---

## Themes

| Theme | Source File |
|-------|-------------|
| `Sea_Green` | `Sea_Green/sea_green.scss` |

## Source Files

| File | Description |
|------|-------------|
| `Sea_Green/sea_green.scss` | Main SCSS entry point for the Sea Green admin theme |

## Build Output

Compiled files are written directly into the theme directories under `themes/admin/`:

| Output | Description |
|--------|-------------|
| `themes/admin/Sea_Green/css/sea_green.css` | Compiled CSS |
| `themes/admin/Sea_Green/css/sea_green.min.css` | Minified CSS (autoprefixed, cssnano) |
| `themes/admin/Sea_Green/css/sea_green-rtl.css` | Compiled RTL CSS |
| `themes/admin/Sea_Green/css/sea_green-rtl.min.css` | Minified RTL CSS |
