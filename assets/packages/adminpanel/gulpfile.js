const
    gulp = require("gulp"),
    concat = require("gulp-concat"),
    sass = require("gulp-sass");

gulp.task('sass', function () {
  return gulp.src('./scss/main.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(concat('lsadminpanel.css'))
    .pipe(gulp.dest('./build'));
});
 
gulp.task('sass:watch', function () {
  gulp.watch('./scss/*.scss', ['sass']);
});
