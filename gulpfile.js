// for bootstrap 5:
// gulp build / gulp watch
// for admintheme:
// gulp build_theme / gulp watch_theme
// for survey_theme_fruity:
// gulp build_survey_theme_fruity / gulp watch_survey_theme_fruity
// for survey_theme_ls6:
// gulp build_survey_theme_ls6 / gulp watch_survey_theme_ls6

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
const replace = require('gulp-replace');
const merge = require('merge-stream');
const sourcemaps = require('gulp-sourcemaps');
const babelify = require('babelify');
const source = require('vinyl-source-stream');
const buffer = require('vinyl-buffer');
const browserify = require('browserify');
const fs = require('fs');

function js_minify() {
    // browserify package handler
    return browserify({
        entries: ['assets/bootstrap_5/js/bootstrap_5.js']
    })
        // transform babelify ES6 to ES5 [@babel/preset-env]
        .transform(babelify, {
            presets: ['@babel/preset-env'],
            retainLines: false,
            compact: false,
            global: true

        })
        // bundle the transformed code
        .bundle()
        // sourcemap
        .pipe(source('assets/bootstrap_5/js/bootstrap_5.js'))
        // rename
        .pipe(rename('bootstrap_5.js'))
        // buffer
        .pipe(buffer())
        // distination
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
    return src(['assets/admin_themes/Sea_Green/sea_green.scss'])
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
    return src(['assets/admin_themes/Sea_Green/sea_green.scss'])
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

function survey_theme_fruity() {
    let variations = [
        ["apple_blossom", "#AA4340"],
        ["bay_of_many", "#214F7E"],
        ["black_pearl", "#071630"],
        ["free_magenta", "#C63678"],
        ["purple_tentacle", "#993399"],
        ["sea_green", "#328637"],
        ["sunset_orange", "#FE5B35"],
        ["skyline_blue", "#91dcff"]
    ];
    let plugins = [
        autoprefixer(),
        cssnano()
    ];
    let variationsFiles = variations.map(variation => {
        let variationName = variation[0];
        let variationColor = variation[1];
        return src(['assets/survey_themes/fruity/fruityThemeTemplate.scss'])
            .pipe(replace('$base-color: #ffffff;', '$base-color: ' + variationColor + ';'))
            .pipe(sass())
            .pipe(gulppostcss(plugins))
            .pipe(rename(variationName + '.css'))
            .pipe(dest('themes/survey/fruity/css/variations'));
    });
    return merge(variationsFiles);
}

exports.build_survey_theme_fruity = parallel(
    survey_theme_fruity
);

exports.watch_survey_theme_fruity = function () {
    watch('assets/survey_themes/fruity/src/**/*.scss', survey_theme_fruity);
};

function survey_theme_ls6() {
    let variations = [
        ["apple", "#14AE5C"],
        ["blueberry", "#5076FF"],
        ["grape", "#8146F6"],
        ["mango", "#ED5046"],
    ];
    let plugins = [
        autoprefixer(),
        // cssnano()
    ];

    let variationsFiles = variations.map(variation => {
        let variationName = variation[0];
        let variationColor = variation[1];
        return src(['assets/survey_themes/fruity_twentythree/theme_template.scss'])
            .pipe(replace('$base-color: #ffffff;', '$base-color: ' + variationColor + ';'))
            .pipe(sass())
            .pipe(gulppostcss(plugins))
            .pipe(rename('theme_' + variationName + '.css'))
            .pipe(dest('themes/survey/fruity_twentythree/css/variations'));
    });
    return merge(variationsFiles);
}

function survey_theme_ls6_rtl() {
    let variations = [
        ["apple", "#14AE5C"],
        ["blueberry", "#5076FF"],
        ["grape", "#8146F6"],
        ["mango", "#ED5046"],
    ];
    let plugins = [
        autoprefixer(),
        // cssnano()
    ];

    let variationsFiles = variations.map(variation => {
        let variationName = variation[0];
        let variationColor = variation[1];
        return src(['assets/survey_themes/fruity_twentythree/theme_template.scss'])
            .pipe(replace('$base-color: #ffffff;', '$base-color: ' + variationColor + ';'))
            .pipe(sass())
            .pipe(rtlcss())
            .pipe(gulppostcss(plugins))
            .pipe(rename('theme_' + variationName + '-rtl.css'))
            .pipe(dest('themes/survey/fruity_twentythree/css/variations'));
    });
    return merge(variationsFiles);
}

function survey_theme_ls6_js() {
    // browserify package handler
    return browserify({
        entries: ['assets/survey_themes/fruity_twentythree/theme_js_modules.js']
    })
        // transform babelify ES6 to ES5 [@babel/preset-env]
        .transform(babelify, {
            presets: ['@babel/preset-env'],
            retainLines: false,
            compact: false,
            global: true
        })
        // bundle the transformed code
        .bundle()
        // sourcemap
        .pipe(source('assets/survey_themes/fruity_twentythree/theme_js_modules.js'))
        // rename
        .pipe(rename('theme.js'))
        // buffer
        .pipe(buffer())
        // distination
        .pipe(replace(/^/, fs.readFileSync('assets/survey_themes/fruity_twentythree/theme_js_disclaimer.js')))
        .pipe(dest('themes/survey/fruity_twentythree/scripts/'));
}

exports.build_survey_theme_ls6 = parallel(
    survey_theme_ls6,
    survey_theme_ls6_rtl,
    survey_theme_ls6_js
);

exports.watch_survey_theme_ls6 = function () {
    watch('assets/survey_themes/fruity_twentythree/**/*.scss', survey_theme_ls6);
    watch('assets/survey_themes/fruity_twentythree/**/*.scss', survey_theme_ls6_rtl);
    watch('assets/survey_themes/fruity_twentythree/**/*.js', survey_theme_ls6_js);
};
