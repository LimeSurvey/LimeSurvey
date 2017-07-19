const
    gulp = require("gulp"),
    uglify = require('gulp-uglify'),
    pump = require('pump'),
    concat = require("gulp-concat"),
    sourcemaps = require("gulp-sourcemaps"),
    autoprefixer = require('gulp-autoprefixer'),
    runSequence = require('run-sequence'),
    babel = require("gulp-babel"),
    webpack = require('webpack')
    gulpWebpack = require("gulp-webpack"),
    sass = require("gulp-sass");

gulp.task('default', ['compile']);

//general combined tasks
gulp.task('compile', ['sass', 'webpack']);
gulp.task('compile:production', function(cb){ runSequence(['sass:production', 'webpack:production'],'compress',cb);});


//Watcher tasks
gulp.task('watch',['compile', 'webpack:watch','sass:watch']);
gulp.task('sass:watch', function () { 
  gulp.watch('./scss/*.scss', ['sass']);
});
gulp.task('webpack:watch', function () { 
  gulp.watch(['./src/**/*.js','./src/**/*.vue'], ['webpack']);
});


//compile tasks
gulp.task('sass', function () {
  return gulp.src('./scss/main.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(concat('lsadminpanel.css'))
    .pipe(gulp.dest('./build'));
});

gulp.task('sass:production', function (cb) {
  pump([
    gulp.src('./scss/main.scss'),
    sourcemaps.init(),
    sass({outputStyle: 'compressed'}).on('error', sass.logError),
    autoprefixer(),
    concat('lsadminpanel.css'),
    sourcemaps.write(),
    gulp.dest('./build')
  ], cb);
});

gulp.task('webpack', function (cb) {
    pump([
      gulp.src('src/main.js'),
      gulpWebpack( require('./webpack.config.js'), webpack),
      gulp.dest('build/')
    ],
    cb
  );
});
gulp.task('webpack:production', function (cb) {
    process.env.WEBPACK_ENV = 'production';
    pump([
      gulp.src('src/main.js'),
      gulpWebpack( require('./webpack.config.js'), webpack),
      gulp.dest('build/')
    ],
    function(){ process.env.WEBPACK_ENV = 'dev'; cb();}
  );
});

//production ready tasks

gulp.task('compress', function (cb) {
  pump([
        gulp.src('build/lsadminpanel.js'),
        sourcemaps.init(),
        babel({presets: ['es2015']}),
        uglify(),
        concat('lsadminpanel.min.js'),
        gulp.dest('build')
    ],
    cb
  );
});

