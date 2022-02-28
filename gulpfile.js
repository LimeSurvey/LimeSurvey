const {watch, series, parallel} = require('gulp');
const {src, dest} = require('gulp');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const sass = require('gulp-sass')(require('sass'));
const cssnano = require('gulp-cssnano');
const concat = require('gulp-concat');
const gulpIf = require('gulp-if');
const useref = require('gulp-useref');

function js_minify() {
    return src(['third_party/twbs/bootstrap/dist/js/bootstrap.min.js', 'assets/bootstrap_5/js/custom.js'])
        .pipe(concat('custom.js'))
        .pipe(dest('assets/bootstrap_5/build/js/'))
        .pipe(uglify())
        .pipe(rename({extname: '.min.js'}))
        .pipe(dest('assets/bootstrap_5/build/js/'));
}

function scss_transpile() {
    return src('assets/bootstrap_5/scss/custom.scss')
        .pipe(sass())
        .pipe(dest('assets/bootstrap_5/build/css'));
}

function scss_minify() {
    return scss_transpile().pipe(cssnano())
        .pipe(rename({extname: '.min.css'}))
        .pipe(dest('assets/bootstrap_5/build/css'));
}

exports.watch = function () {
    watch('assets/bootstrap_5/js/**/*.js', js_minify);
    watch('assets/bootstrap_5/scss/**/*.scss', scss_minify);
};

exports.build = parallel(
    js_minify,
    scss_minify
);
