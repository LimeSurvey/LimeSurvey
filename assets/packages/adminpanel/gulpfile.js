// globals process
'use strict';
const
    gulp = require('gulp'),
    uglify = require('gulp-uglify'),
    pump = require('pump'),
    concat = require('gulp-concat'),
    rename = require('gulp-rename'),
    sassLint = require('gulp-sass-lint'),
    eslint = require('gulp-eslint'),
    sourcemaps = require('gulp-sourcemaps'),
    autoprefixer = require('gulp-autoprefixer'),
    runSequence = require('run-sequence'),
    babel = require('gulp-babel'),
    webpack = require('webpack'),
    gulpWebpack = require('gulp-webpack'),
    sass = require('gulp-sass');

//filename definitions
const 
    cssOutFile = 'lsadminpanel.css',
    cssOutFileProducton = 'lsadminpanel.min.css',
    jsOutFile = 'lsadminpanel.js',
    jsOutFileDebug = 'lsadminpanel.debug.js',
    jsOutFileProducton = 'lsadminpanel.min.js';

gulp.task('default', function (cb) {
    runSequence('compile:production', 'compile', cb);
});

//general combined tasks
gulp.task('compile', ['sass', 'webpack', 'babelify']);
gulp.task('compile:production', function (cb) {
    runSequence(['sass:production', 'webpack:production'], 'compress', 'compresslibs', cb);
});
gulp.task('lint', ['sass:lint', 'js:lint']);


//Watcher tasks
gulp.task('watch', ['compile', 'lint', 'webpack:watch', 'sass:watch']);
gulp.task('sass:watch', function () {
    gulp.watch('./scss/*.scss', ['sass']);
});
gulp.task('webpack:watch', function () {
    gulp.watch(['./src/**/**.js', './src/**/**.vue'], ['webpack']);
});

gulp.task('lint:watch', function () {
    gulp.watch(['./src/**/**.js', './src/**/**.vue'], ['js:lint']);
});


//compile tasks
gulp.task('sass', function () {
    return gulp.src('./scss/main.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(concat(cssOutFile))
        .pipe(gulp.dest('./build'));
});

gulp.task('sass:production', function (cb) {
    pump(
        [
            gulp.src('./scss/main.scss'),
            sourcemaps.init(),
            sass({
                outputStyle: 'compressed'
            }).on('error', sass.logError),
            autoprefixer(),
            concat(cssOutFileProducton),
            sourcemaps.write(),
            gulp.dest('./build')
        ], cb);
});

gulp.task('webpack', function (cb) {
    process.env.NODE_ENV = 'developement';
    process.env.WEBPACK_ENV = 'developement';
    pump(
        [
            gulp.src('src/main.js'),
            gulpWebpack(require('./webpack.config.js'), webpack),
            gulp.dest('build/')
        ],
        cb
    );
});
gulp.task('webpack:production', function (cb) {
    process.env.NODE_ENV = 'production';
    process.env.WEBPACK_ENV = 'production';
    pump(
        [
            gulp.src('src/main.js'),
            gulpWebpack(require('./webpack.config.js'), webpack),
            gulp.dest('build/')
        ],
        cb
    );
});

gulp.task('babelify', function(cb){
    pump([
        gulp.src('build/'+jsOutFile),
        sourcemaps.init(),
        babel({
            presets: [['env', {'targets' : { 'browsers' :  ['last 2 versions', 'ie 10'] }}]]
        }),
        concat(jsOutFileDebug),
        gulp.dest('build')
    ],
    cb );
});

//linter

gulp.task('sass:lint', function (cb) {
    pump(
        [
            gulp.src('scss/**/*.s+(a|c)ss'),
            sassLint({
                options: {
                    formatter: 'stylish',
                    'merge-default-rules': false
                },
                files: {
                    ignore: '**/*.scss'
                },
                rules: {
                    'no-ids': 1,
                    'no-mergeable-selectors': 0
                }
            }),
            sassLint.format(),
            sassLint.failOnError()
        ], cb);
});

gulp.task('js:lint', function (cb) {
    pump(
        [
            gulp.src(['./src/**/*.js', '!node_modules/**', ]),
            eslint(),
            eslint.formatEach(),
            eslint.failAfterError('compact', process.stderr),
        ], cb);
});

//production ready tasks

gulp.task('compress', function (cb) {
    pump(
        [
            gulp.src('build/'+jsOutFile),
            sourcemaps.init(),
            babel({
                presets: [['env', {'targets' : { 'browsers' :  ['last 2 versions', 'ie 10'] }}]]
            }),
            uglify(),
            concat(jsOutFileProducton),
            gulp.dest('build')
        ],
        cb
    );
});

gulp.task('compresslibs', function (cb) {
    pump([
        gulp.src('lib/*.js'),
        gulp.dest('build')
    ],
    pump(
        [
            gulp.src('lib/*.js'),
            sourcemaps.init(),
            babel({
                presets: [['env', {'targets' : { 'browsers' :  ['last 2 versions', 'ie 10'] }}]]
            }),
            uglify(),
            rename({ suffix: '.min' }),
            gulp.dest('build')
        ],
        cb
    )
    );
});
