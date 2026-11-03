# Bootstrap 5 Assets

This directory contains the source SCSS and JavaScript files for the LimeSurvey Bootstrap 5 theme.

## Prerequisites

Make sure [Node.js](https://nodejs.org/) and [Yarn](https://yarnpkg.com/) are installed, then install all dependencies from the **project root**:

```bash
yarn install
```

## Build Commands

Run from the **project root** directory:

### One-time build

```bash
yarn gulp build_bootstrap
```

This runs the following tasks in parallel:
- **JS**: Browserifies `js/bootstrap_5.js`, transforms ES6 → ES5 via Babel, outputs bundled and minified files to `build/js/`.
- **CSS (LTR)**: Compiles `scss/bootstrap_5.scss` via Sass, autoprefixes and minifies with cssnano, outputs to `build/css/`.
- **CSS (RTL)**: Same as above but converted to RTL via `rtlcss`, output files are suffixed with `-rtl`.

### Watch mode (rebuild on file changes)

```bash
yarn gulp watch_bootstrap
```

Watches:
- `assets/bootstrap_5/js/**/*.js` → rebuilds JS bundle
- `assets/bootstrap_5/scss/**/*.scss` → rebuilds both LTR and RTL CSS


---

## Source Files

| File | Description |
|------|-------------|
| `scss/bootstrap_5.scss` | Main SCSS entry point |
| `js/bootstrap_5.js` | Main JavaScript entry point |

## Build Output

Built files are written to `assets/bootstrap_5/build/`:

| Output | Description |
|--------|-------------|
| `build/css/bootstrap_5.css` | Compiled CSS |
| `build/css/bootstrap_5.min.css` | Minified CSS (autoprefixed, cssnano) |
| `build/css/bootstrap_5-rtl.css` | Compiled RTL CSS |
| `build/css/bootstrap_5-rtl.min.css` | Minified RTL CSS |
| `build/js/bootstrap_5.js` | Bundled JavaScript (Browserify + Babel ES6→ES5) |
| `build/js/bootstrap_5.min.js` | Minified JavaScript |
