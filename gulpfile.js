var gulp = require('gulp');
var postcss = require('gulp-postcss');
var autoprefixer = require('autoprefixer');
var postcssPresetEnv = require('postcss-preset-env');
var postcssNesting = require('postcss-nesting');
var postcssMediaMixmax = require('postcss-media-minmax');

gulp.task('css', function () {
  console.log('happens css');
    var plugins = [postcssPresetEnv, postcssNesting, postcssMediaMixmax];
    return gulp.src('wordpress/wp-content/themes/tmrw-wp-theme/css/*.css')
        .pipe(postcss(plugins))
        .pipe(gulp.dest('wordpress/wp-content/themes/tmrw-wp-theme/dist/css'));
});


gulp.task('watch', function() {
    console.log('happens');

 gulp.watch('wordpress/wp-content/themes/tmrw-wp-theme/css/*.css', ['css']);
});


gulp.task('default', ['watch']);
