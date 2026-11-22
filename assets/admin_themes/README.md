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

This runs the following tasks in parallel, for every theme in the `adminThemes` list in `gulpfile.js`:
- **CSS (LTR)**: Compiles the entry file of the theme (e.g. `Sea_Green/sea_green.scss`) via Sass, autoprefixes and minifies with cssnano, outputs to the `css` directory of the theme (e.g. `themes/admin/Sea_Green/css/`).
- **CSS (RTL)**: Same as above but converted to RTL via `rtlcss`, output files are suffixed with `-rtl`.
- **Dark mode CSS (LTR and RTL)**: The same two tasks for the dark mode entry file of the theme (e.g. `Sea_Green/sea_green_dark.scss`), output files are suffixed with `_dark`.
- **Theme tokens**: Compiles `theme_tokens.scss` of the theme to `theme_tokens.css`, the colours of the theme as CSS variables. The React editor loads this file.

The compiled files are committed. After a change of the SCSS, run the build and commit the changed CSS files with it.

### Watch mode (rebuild on file changes)

```bash
yarn gulp watch_admintheme
```

Watches all `assets/admin_themes/**/*.scss` files and rebuilds all CSS files of all themes on any change.

---

## Themes

| Theme | Source File | Dark mode | Theme tokens |
|-------|-------------|-----------|--------------|
| `Sea_Green` | `Sea_Green/sea_green.scss` | `Sea_Green/sea_green_dark.scss` | `Sea_Green/theme_tokens.scss` |
| `Sea_Green_High_Contrast` | `Sea_Green_High_Contrast/sea_green_high_contrast.scss` | `Sea_Green_High_Contrast/sea_green_high_contrast_dark.scss` | `Sea_Green_High_Contrast/theme_tokens.scss` |

The high contrast theme is a variant of Sea Green: It sets its own variables and then uses the SCSS of `Sea_Green`.
A new theme that is built here needs an entry in the `adminThemes` list in `gulpfile.js`.

## Source Files

| File | Description |
|------|-------------|
| `Sea_Green/sea_green.scss` | Main SCSS entry point for the Sea Green admin theme |
| `Sea_Green/_sea_green_base.scss` | The rules of Sea Green, shared with its variants |
| `Sea_Green/sea_green_variables.scss` | The variables of Sea Green: The palette, then everything that is computed from it |
| `Sea_Green_High_Contrast/sea_green_high_contrast.scss` | Main SCSS entry point for the high contrast theme |
| `Sea_Green_High_Contrast/_high_contrast_variables.scss` | The colours and focus indicators of the high contrast theme |
| `Sea_Green_High_Contrast/_high_contrast_overrides.scss` | Rules that only exist in the high contrast theme |
| `_theme_tokens.scss` | Shared code of the theme tokens, used by `theme_tokens.scss` of each theme |

The files of the dark mode are listed in the "Dark mode" section below.

## Build Output

Compiled files are written directly into the theme directories under `themes/admin/`. Each file also has a minified
version (`.min.css`). `<name>` is `sea_green` in `Sea_Green` and `sea_green_high_contrast` in
`Sea_Green_High_Contrast`:

| Output | Description |
|--------|-------------|
| `themes/admin/<theme>/css/<name>.css` | Compiled CSS |
| `themes/admin/<theme>/css/<name>-rtl.css` | Compiled RTL CSS |
| `themes/admin/<theme>/css/<name>_dark.css` | Compiled dark mode CSS |
| `themes/admin/<theme>/css/<name>_dark-rtl.css` | Compiled dark mode RTL CSS |
| `themes/admin/<theme>/css/theme_tokens.css` | Theme tokens for the React editor |

---

## Dark mode

The appearance mode (light, dark or system) is a personal setting of each user, with a default in the global settings.
A theme supports the dark mode by listing dark mode CSS files in its `config.xml`:

```xml
<files>
    <css><filename>sea_green.css</filename></css>
    <dark>
        <css><filename>sea_green_dark.css</filename></css>
    </dark>
    <rtl>
        <css><filename>sea_green-rtl.css</filename></css>
        <dark>
            <css><filename>sea_green_dark-rtl.css</filename></css>
        </dark>
    </rtl>
</files>
```

The dark mode CSS files are loaded **after** the normal CSS files of the theme, only on screens (not for print):
always in the "dark" mode, and with `(prefers-color-scheme: dark)` in the "system" mode. A theme without these files
is always shown in the light mode.

The dark mode of Sea Green is Sea Green compiled a second time with dark colours:

| File | Description |
|------|-------------|
| `Sea_Green/sea_green_dark.scss` | Entry point of the dark mode |
| `Sea_Green/_sea_green_palette.scss` | The colours of Sea Green |
| `Sea_Green/_dark_palette.scss` | The dark colours, they replace the colours of the palette |
| `Sea_Green/_sea_green_components.scss` | Everything that is computed from the colours |
| `Sea_Green/_dark_variables.scss` | Bootstrap and Sea Green variables of the dark mode |
| `Sea_Green/_dark_overrides.scss` | Rules that only exist in the dark mode |
| `Sea_Green_High_Contrast/sea_green_high_contrast_dark.scss` | Entry point of the dark mode of the high contrast theme |
| `Sea_Green_High_Contrast/_high_contrast_dark_variables.scss` | The focus indicators of the high contrast theme in the dark mode |

Rules for the SCSS of the theme, so it works in both modes:

- Use `$surface-bg` for the background of cards, inputs, dropdowns and so on, not `$white`. `$white` is for text on coloured backgrounds.
- Use the `$g-*` greys for text, borders and backgrounds, not fixed colours.

The page has `data-bs-theme="light"` or `data-bs-theme="dark"` on the `html` element, for scripts that need to know the mode in use.
CSS that is not part of the theme (widgets, plugins) can use these CSS variables, they follow the mode:
`--ls-surface-bg`, `--ls-page-bg`, `--ls-text-color`, `--ls-muted-text-color` and `--ls-border-color`.

### React editor

The React editor (survey editor, responses, statistics) gets the appearance mode from the server and sets the same
`data-bs-theme` attribute. Its dark colours are in `editor/src/themes/dark-mode.scss`, they are the colours of
`Sea_Green/_dark_palette.scss`: change both files together.

The theme tokens of an admin theme (`_theme_tokens.scss`) set colours in `:root`. In the dark mode the editor's own
dark colours win over them. Rules of a theme that must be different in the dark mode go into
`:root[data-bs-theme="dark"]` (see the high contrast theme).
