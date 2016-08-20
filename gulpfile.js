var babel = require('babelify');
var browserify = require('browserify');
var uglify = require('gulp-uglify');
var buffer = require('vinyl-buffer');
var gulp = require('gulp');
var cleanCss = require('gulp-clean-css');
var plumber = require('gulp-plumber');
var rename = require('gulp-rename');
var sass = require('gulp-sass');
var source = require('vinyl-source-stream');
var sourcemaps = require('gulp-sourcemaps');

function onError(error) {
  console.error(error);
  this.emit('end');
}

function compile(module) {
  return browserify(`./app/Resources/scripts/${module}.js`, { debug: true })
    .transform(babel)
    .bundle()
    .on('error', onError)
    .pipe(source(`${module}.js`))
    .pipe(buffer())
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(uglify())
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./web/js'));
}

gulp.task('build-js', function () {
  compile('main');
});

gulp.task('watch-js', ['build-js'], function() {
  gulp.watch('./app/Resources/scripts/**/*.js', ['build-js']);
});

gulp.task('build-css', function() {
  return gulp.src('./app/Resources/styles/main.scss')
    .pipe(plumber({ errorHandler: onError }))
    .pipe(sass())
    .pipe(cleanCss())
    .pipe(rename('main.css'))
    .pipe(gulp.dest('./web/css'));
});

gulp.task('watch-css', ['build-css'], function() {
  gulp.watch('./app/Resources/styles/**/*.scss', ['build-css']);
});

gulp.task('build', ['build-js', 'build-css']);
gulp.task('watch', ['watch-js', 'watch-css']);
gulp.task('default', ['watch-js', 'watch-css']);
