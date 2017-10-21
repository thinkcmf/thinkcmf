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

// 预定义目录名称
const assetsDir = 'assets/';

// 设置预定义发布目录
mix.setPublicPath('public').options({
    fileLoaderDirs: {
        images: assetsDir+'images',
        fonts: assetsDir+'fonts'
    }
});

// 加入需要发布的资源
mix.js('assets/js/app.js', assetsDir+'js')
.sass('assets/sass/app.scss', assetsDir+'css');

// BrowserSync 自动更新服务配置
var BrowserSyncPlugin = require('browser-sync-webpack-plugin');
var browserSync = new BrowserSyncPlugin(
        Object.assign({
            port: 3000,
            proxy: 'example.com', // 这里修改成当前项目域名
            // 这里配置监控目录，只要符合规则的文件被修改，会立即发布资源并刷新页面（只有在 npm run watch 模式下哦）
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

// 将 browserSync 配置注入到 webpack 配置
mix.webpackConfig({
    plugins : [browserSync]
});
