const
    gulp = require("gulp"),
    uglify = require('gulp-uglify'),
    pump = require('pump'),
    concat = require("gulp-concat"),
    sass = require("gulp-sass");

gulp.task('sass', function () {
  return gulp.src('./scss/main.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(concat('lsadminpanel.css'))
    .pipe(gulp.dest('./build'));
});
 
gulp.task('compress', function (cb) {
  pump([
        gulp.src('build/*.js'),
        uglify(),
        concat('lsadminpanel.min.js'),
        gulp.dest('build')
    ],
    cb
  );
});
