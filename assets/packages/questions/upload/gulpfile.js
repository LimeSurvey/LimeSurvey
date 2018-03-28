// globals process
'use strict';
const
    gulp =          require('gulp'),
    uglify =        require('gulp-uglify'),
    pump =          require('pump'),
    concat =        require('gulp-concat'),
    rename =        require('gulp-rename'),
    eslint =        require('gulp-eslint'),
    sourcemaps =    require('gulp-sourcemaps'),
    autoprefixer =  require('gulp-autoprefixer'),
    runSequence =   require('run-sequence'),
    babel =         require('gulp-babel');

//filename definitions
const 
    jsOutFile = 'uploadquestion.js',
    jsOutFileDebug = 'uploadquestion.debug.js',
    jsOutFileProducton = 'uploadquestion.min.js';

gulp.task('default', function (cb) {
    runSequence('compile:production', 'compile', cb);
});

//general combined tasks
gulp.task('compile', ['combine', 'babelify']);
gulp.task('compile:production', function (cb) {
    runSequence(['combine:production'], 'compress', cb);
});
gulp.task('lint', ['js:lint']);


//Watcher tasks
gulp.task('watch', ['compile', 'lint', 'combine:watch']);

gulp.task('combine:watch', function () {
    gulp.watch(['./src/**/**.js'], ['combine']);
});

gulp.task('lint:watch', function () {
    gulp.watch(['./src/**/**.js'], ['js:lint']);
});


//compile tasks

gulp.task('combine', function (cb) {
    process.env.NODE_ENV = 'developement';
    pump(
        [
            gulp.src('src/*.js'),
            concat(jsOutFile)
            gulp.dest('build/')
        ],
        cb
    );
});
gulp.task('combine:production', ['combine']);

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
