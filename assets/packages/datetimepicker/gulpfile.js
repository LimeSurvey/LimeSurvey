// for bootstrap 5 datetimepicker eonasdan/tempus-dominus with popper.js:
// gulp build

const {parallel} = require('gulp');
const {src, dest} = require('gulp');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const gulppostcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const concat = require('gulp-concat');

function js_minify() {
    return src([
        '../../../node_modules/@popperjs/core/dist/umd/popper.js',
        '../../../node_modules/@eonasdan/tempus-dominus/dist/js/tempus-dominus.js'
    ])
        .pipe(concat('popper-tempus.js'))
        .pipe(dest('build'))
        .pipe(uglify())
        .pipe(rename({extname: '.min.js'}))
        .pipe(dest('build'));
}


function css_minify() {
    let plugins = [
        autoprefixer(),
        cssnano()
    ];
    return src([
        '../../../node_modules/@eonasdan/tempus-dominus/dist/css/tempus-dominus.css',
        'custom.css'
    ])
        .pipe(concat('tempus-dominus.css'))
        .pipe(gulppostcss(plugins))
        .pipe(rename({extname: '.min.css'}))
        .pipe(dest('build'));
}

exports.build = parallel(
    js_minify,
    css_minify
);

