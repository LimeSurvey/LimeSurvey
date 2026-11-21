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
