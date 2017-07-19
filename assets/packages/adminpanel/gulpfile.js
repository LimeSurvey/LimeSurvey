const
    gulp = require("gulp"),
    uglify = require('gulp-uglify'),
    pump = require('pump'),
    concat = require("gulp-concat"),
    sourcemaps = require("gulp-sourcemaps"),
    babel = require("gulp-babel"),
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
