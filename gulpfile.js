// for bootstrap 5:
// gulp build / gulp watch
// for admintheme:
// gulp build_theme / gulp watch_theme

const {watch, series, parallel} = require('gulp');
const {src, dest} = require('gulp');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const sass = require('gulp-sass')(require('sass'));
const gulppostcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const concat = require('gulp-concat');
const rtlcss = require('gulp-rtlcss');
const gulpIf = require('gulp-if');
const useref = require('gulp-useref');

function js_minify() {
    return src(['third_party/twbs/bootstrap/dist/js/bootstrap.bundle.min.js', 'assets/bootstrap_5/js/bootstrap_5.js'])
        .pipe(concat('bootstrap_5.js'))
        .pipe(dest('assets/bootstrap_5/build/js/'))
        .pipe(uglify())
        .pipe(rename({extname: '.min.js'}))
        .pipe(dest('assets/bootstrap_5/build/js/'));
}

function scss_transpile() {
    return src('assets/bootstrap_5/scss/bootstrap_5.scss')
        .pipe(sass());
}

function scss_minify() {
    let plugins = [
        autoprefixer(),
        cssnano()
    ];
    return scss_transpile()
        .pipe(dest('assets/bootstrap_5/build/css'))
        .pipe(gulppostcss(plugins))
        .pipe(rename({extname: '.min.css'}))
        .pipe(dest('assets/bootstrap_5/build/css'));
}

function scss_minify_rtl() {
    let plugins = [
        autoprefixer(),
        cssnano()
    ];
    return scss_transpile()
        .pipe(rtlcss())
        .pipe(rename({suffix: '-rtl'}))
        .pipe(dest('assets/bootstrap_5/build/css'))
        .pipe(gulppostcss(plugins))
        .pipe(rename({extname: '.min.css'}))
        .pipe(dest('assets/bootstrap_5/build/css'));
}

exports.watch = function () {
    watch('assets/bootstrap_5/js/**/*.js', js_minify);
    watch('assets/bootstrap_5/scss/**/*.scss', parallel(scss_minify, scss_minify_rtl));
};

exports.build = parallel(
    js_minify,
    scss_minify,
    scss_minify_rtl
);

function theme() {
    let plugins = [
        autoprefixer(),
        cssnano()
    ];
    return src(['assets/admin_themes/Sea_Green/lime-admin-colors.scss', 'assets/admin_themes/Sea_Green/statistics.scss', 'assets/admin_themes/Sea_Green/lime-admin-common.scss'])
        .pipe(sass())
        .pipe(dest('themes/admin/Sea_Green/css'))
        .pipe(gulppostcss(plugins))
        .pipe(rename({extname: '.min.css'}))
        .pipe(dest('themes/admin/Sea_Green/css'));
}

function theme_rtl() {
    let plugins = [
        autoprefixer(),
        cssnano()
    ];
    return src(['assets/admin_themes/Sea_Green/statistics.scss', 'assets/admin_themes/Sea_Green/lime-admin-common.scss'])
        .pipe(sass())
        .pipe(rtlcss())
        .pipe(rename({suffix: '-rtl'}))
        .pipe(dest('themes/admin/Sea_Green/css'))
        .pipe(gulppostcss(plugins))
        .pipe(rename({extname: '.min.css'}))
        .pipe(dest('themes/admin/Sea_Green/css'));
}

exports.watch_theme = function () {
    watch('assets/admin_themes/**/*.scss', theme);
    watch('assets/admin_themes/**/*.scss', theme_rtl);
};

exports.build_theme = parallel(
    theme,
    theme_rtl
);
