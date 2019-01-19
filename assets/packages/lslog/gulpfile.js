// globals process
'use strict';
const
    gulp = require('gulp'),
    pump = require('pump'),
    rename = require('gulp-rename'),
    uglify = require('gulp-uglify'),
    eslint = require('gulp-eslint'),
    sourcemaps = require('gulp-sourcemaps'),
    autoprefixer = require('gulp-autoprefixer'),
    runSequence = require('run-sequence'),
    babel = require('gulp-babel');

gulp.task('default', function (cb) {
    runSequence('compile:production', 'compile', cb);
});

//general combined tasks
gulp.task('compile', ['babel']);
gulp.task('compile:production', function (cb) {
    runSequence(['babel:production'], cb);
});
gulp.task('lint', ['js:lint']);


//Watcher tasks
gulp.task('watch', ['compile', 'lint', 'babel:watch']);

gulp.task('babel:watch', function () {
    gulp.watch(['./src/**/**.js', './src/**/**.vue'], ['webpack']);
});

gulp.task('lint:watch', function () {
    gulp.watch(['./src/**/**.js', './src/**/**.vue'], ['js:lint']);
});


//compile tasks

gulp.task('babel', function (cb) {
    process.env.NODE_ENV = 'developement';
    process.env.WEBPACK_ENV = 'developement';
    pump(
        [
            gulp.src('src/lslog.js'),
            babel({
                presets: [['env', {targets: {browsers: "last 2 versions"}}]]
            }),
            gulp.dest('build/')
        ],
        cb
    );
});
gulp.task('babel:production', function (cb) {
    process.env.NODE_ENV = 'production';
    process.env.WEBPACK_ENV = 'production';
    pump(
        [
            gulp.src('src/lslog.js'),
            sourcemaps.init(),
            babel({
                presets: [['env', {targets: {browsers: "last 2 versions"}}]]
            }),
            uglify(),
            rename('lslog.min.js'),
            gulp.dest('build/')
        ],
        cb
    );
});

//linter

gulp.task('js:lint', function (cb) {
    pump(
        [
            gulp.src(['./src/**/*.js', '!node_modules/**', ]),
            eslint(),
            eslint.formatEach(),
            eslint.failAfterError('compact', process.stderr),
        ], cb);
});

