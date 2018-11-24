var gulp = require('gulp');
var postcss = require('gulp-postcss');
var autoprefixer = require('autoprefixer');
var postcssPresetEnv = require('postcss-preset-env');
var postcssNesting = require('postcss-nesting');
var postcssMediaMixmax = require('postcss-media-minmax');
var concatCss = require('gulp-concat-css');
var cssnano = require('gulp-cssnano');


gulp.task('css', function () {
    var plugins = [postcssPresetEnv, postcssNesting, postcssMediaMixmax];
    return gulp.src('wordpress/wp-content/themes/tmrw-wp-theme-live/css/*.css')
        .pipe(postcss(plugins))
        .pipe(concatCss("bundle.css"))
        .pipe(cssnano())
        .pipe(gulp.dest('wordpress/wp-content/themes/tmrw-wp-theme-live/dist/css'));
});


gulp.task('watch', function() {
 gulp.watch('wordpress/wp-content/themes/tmrw-wp-theme-live/css/*.css', ['css']);
});


gulp.task('default', ['watch']);
