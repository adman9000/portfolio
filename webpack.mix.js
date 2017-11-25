let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.scripts([
'resources/assets/vendor/jquery/jquery-3.1.1.min.js',
'resources/assets/vendor/bootstrap/js/bootstrap.js',
'resources/assets/vendor/metisMenu/jquery.metisMenu.js',
'resources/assets/vendor/pace/pace.js',
'resources/assets/vendor/slimscroll/jquery.slimscroll.min.js'
], 'public/js/code.js')
   .sass('resources/assets/sass/app.scss', 'public/css')
   .sass('resources/assets/sass/inspinia.scss', 'public/css')
   .sass('resources/assets/sass/admin.scss', 'public/css')
.js('resources/assets/js/frontend.js', 'public/js')
.js('resources/assets/js/admin.js', 'public/js')
.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/frontend.scss', 'public/css');
