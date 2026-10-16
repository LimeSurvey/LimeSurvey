# Survey Themes Assets

This directory contains the SCSS and JavaScript source files for LimeSurvey survey themes.

## Prerequisites

Make sure [Node.js](https://nodejs.org/) and [Yarn](https://yarnpkg.com/) are installed, then install all dependencies from the **project root**:

```bash
yarn install
```

All gulp commands must be run from the **project root** directory (where `gulpfile.js` and `package.json` live).

---

## Global survey theme package

CSS and JavaScript that is loaded by **all** survey themes (core and custom) lives in
`assets/packages/survey-theme-global/`. It is registered as the `survey-theme-global-ltr` /
`survey-theme-global-rtl` package and added automatically to every survey theme.

Because it applies to third-party themes too, keep additions narrowly scoped (low specificity,
namespaced selectors) so existing themes are not affected.

### Build Commands

```bash
# One-time build (CSS LTR + CSS RTL + JS in parallel)
yarn gulp build_survey_theme_global

# Watch mode
yarn gulp watch_survey_theme_global
```

### Source Files

| File | Description |
|------|-------------|
| `survey-theme-global/src/main.scss` | Global styles; automatically converted for RTL |
| `survey-theme-global/src/rtl-specific.scss` | Additional RTL-only adjustments |
| `survey-theme-global/src/survey-theme-global.js` | JavaScript entry point |

### Build Output

| Output | Description |
|--------|-------------|
| `assets/packages/survey-theme-global/build/survey-theme-global.css` | Compiled CSS (LTR) |
| `assets/packages/survey-theme-global/build/survey-theme-global-rtl.css` | Compiled CSS (RTL) |
| `assets/packages/survey-theme-global/build/survey-theme-global.js` | Bundled JavaScript (Browserify + Babel ES6→ES5) |

The build output is shown read-only in the theme editor under "Global files".

---

## Theme: fruity_twentythree

### Build Commands

```bash
# One-time build (global package first, then CSS LTR + CSS RTL + JS in parallel)
yarn gulp build_survey_theme_fruity_twentythree

# Watch mode
yarn gulp watch_survey_theme_fruity_twentythree
```

Watches:
- `assets/survey_themes/fruity_twentythree/**/*.scss` → rebuilds LTR and RTL CSS variations
- `assets/survey_themes/fruity_twentythree/**/*.js` → rebuilds the JS bundle

### Source Files

| File | Description |
|------|-------------|
| `fruity_twentythree/theme_template.scss` | SCSS template; the `$base-color` variable is replaced per variation at build time |
| `fruity_twentythree/theme_js_modules.js` | JavaScript entry point |
| `fruity_twentythree/theme_js_disclaimer.js` | Prepended as a header comment to the bundled JS output |

### Color Variations

| Variation | Base Color |
|-----------|------------|
| `apple` | `#14AE5C` |
| `blueberry` | `#5076FF` |
| `grape` | `#8146F6` |
| `mango` | `#ED5046` |

### Build Output

| Output | Description |
|--------|-------------|
| `themes/survey/fruity_twentythree/css/variations/theme_<variation>.css` | Compiled CSS per variation (LTR) |
| `themes/survey/fruity_twentythree/css/variations/theme_<variation>-rtl.css` | Compiled CSS per variation (RTL) |
| `themes/survey/fruity_twentythree/scripts/theme.js` | Bundled JavaScript (Browserify + Babel ES6→ES5) |

---

## Theme: fruity

### Build Commands

```bash
# One-time build
yarn gulp build_survey_theme_fruity

# Watch mode (rebuilds on any .scss change)
yarn gulp watch_survey_theme_fruity
```

### Source Files

| File | Description |
|------|-------------|
| `fruity/fruityThemeTemplate.scss` | SCSS template; the `$base-color` variable is replaced per variation at build time |

### Color Variations

| Variation | Base Color |
|-----------|------------|
| `apple_blossom` | `#AA4340` |
| `bay_of_many` | `#214F7E` |
| `black_pearl` | `#071630` |
| `free_magenta` | `#C63678` |
| `purple_tentacle` | `#993399` |
| `sea_green` | `#328637` |
| `sunset_orange` | `#FE5B35` |
| `skyline_blue` | `#91dcff` |

### Build Output

| Output | Description |
|--------|-------------|
| `themes/survey/fruity/css/variations/<variation>.css` | Compiled & minified CSS per variation |

