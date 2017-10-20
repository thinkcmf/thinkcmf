const { mix } = require('laravel-mix');

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
// mix.setPublicPath('public');

const assetsDir = 'assets/';

mix.setPublicPath('public').options({
    fileLoaderDirs: {
        images: assetsDir+'images',
        fonts: assetsDir+'fonts'
    }
});

mix.js('assets/js/app.js', assetsDir+'js').sass('assets/sass/app.scss', assetsDir+'css');

var BrowserSyncPlugin = require('browser-sync-webpack-plugin');
var browserSync = new BrowserSyncPlugin(
        Object.assign({
            port: 3000,
            proxy: 'example.com',
            files: [
                'app/**/*.php',
                'public/themes/**/*.html',
                'public/example/**/*.html',
                'public/assets/js/**/*.js',
                'public/assets/css/**/*.css'
            ],
            snippetOptions: {
                rule: {
                    match: /(<\/body>|<\/pre>)/i,
                    fn: function (snippet, match) {
                        return snippet + match;
                    }
                }
            }
        }, Config.browserSync),
        { reload: false }
    );


mix.disableNotifications();
