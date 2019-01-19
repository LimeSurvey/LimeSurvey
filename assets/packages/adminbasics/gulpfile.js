// globals process
'use strict';
const
    gulp = require('gulp'),
    uglify = require('gulp-uglify'),
    cleanCSS = require('gulp-clean-css'),
    pump = require('pump'),
    concat = require('gulp-concat'),
    rename = require('gulp-rename'),
    sassLint = require('gulp-sass-lint'),
    eslint = require('gulp-eslint'),
    sourcemaps = require('gulp-sourcemaps'),
    autoprefixer = require('gulp-autoprefixer'),
    runSequence = require('run-sequence'),
    eventStream = require('event-stream'),
    babel = require('gulp-babel'),
    webpack = require('webpack'),
    gulpWebpack = require('gulp-webpack'),
    sass = require('gulp-sass');

//filename definitions
const
    cssOutFile = 'adminbasics',
    cssOutFileProducton = 'adminbasics',
    jsOutFile = 'adminbasics.js',
    jsOutFileDebug = 'adminbasics.debug.js',
    jsOutFileProducton = 'adminbasics.min.js';

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
gulp.task('sass', function (cb) {

    const ltrCssStream = gulp.src('./css/*.css');
    const rtlCssStream = gulp.src('./css/rtl/*.css');
    const sassStream = gulp.src('./scss/main.scss').pipe(sass().on('error', sass.logError));

    pump([
        eventStream.merge(ltrCssStream, sassStream),
        concat(cssOutFile + '.css'),
        gulp.dest('./build')
    ], pump([
        eventStream.merge(rtlCssStream, sassStream),
        concat(cssOutFile + '.rtl.css'),
        gulp.dest('./build')
    ], cb));
});

gulp.task('sass:production', function (cb) {
    const ltrCssStream = function () {
        return gulp.src('./css/*.css');
    };
    const rtlCssStream = function () {
        return gulp.src('./css/rtl/*.css');
    };
    const sassStream = function () {
        return gulp.src('./scss/main.scss').pipe(sass().on('error', sass.logError));
    };

    pump([
        eventStream.merge(ltrCssStream(), sassStream()),
        sourcemaps.init(),
        cleanCSS({
            debug: true
        }, (details) => {
            console.log(`${details.name}: ${details.stats.originalSize}`);
            console.log(`${details.name}: ${details.stats.minifiedSize}`);
        }),
        autoprefixer(),
        concat(cssOutFileProducton + '.min.css'),
        sourcemaps.write(),
        gulp.dest('./build')
    ], pump(
        [
            eventStream.merge(rtlCssStream(), sassStream()),
            sourcemaps.init(),
            cleanCSS({
                debug: true
            }, (details) => {
                console.log(`${details.name}: ${details.stats.originalSize}`);
                console.log(`${details.name}: ${details.stats.minifiedSize}`);
            }),
            autoprefixer(),
            concat(cssOutFileProducton + '.rtl.min.css'),
            sourcemaps.write(),
            gulp.dest('./build')
        ], cb));
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

gulp.task('babelify', function (cb) {
    pump([
            gulp.src('build/' + jsOutFile),
            sourcemaps.init(),
            babel({
                presets: [
                    ['env', {
                        'targets': {
                            'browsers': ['last 2 versions', 'ie 10']
                        }
                    }]
                ]
            }),
            concat(jsOutFileDebug),
            gulp.dest('build')
        ],
        cb);
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
            gulp.src('build/' + jsOutFile),
            sourcemaps.init(),
            babel({
                presets: [
                    ['env', {
                        'targets': {
                            'browsers': ['last 2 versions', 'ie 10']
                        }
                    }]
                ]
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
                    presets: [
                        ['env', {
                            'targets': {
                                'browsers': ['last 2 versions', 'ie 10']
                            }
                        }]
                    ]
                }),
                uglify(),
                rename({
                    suffix: '.min'
                }),
                gulp.dest('build')
            ],
            cb
        )
    );
});
