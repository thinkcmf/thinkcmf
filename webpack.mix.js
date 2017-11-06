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

// 设置预定义发布目录
mix.setPublicPath('public').options({
    fileLoaderDirs: {
        images: 'assets/dist/images',
        fonts: 'assets/dist/fonts'
    }
});

// 加入需要发布的资源
mix.js('public/assets/src/js/app.js', 'public/assets/dist/js')
.sass('public/assets/src/sass/app.scss', 'public/assets/dist/css');

mix.browserSync({
    port: 3000,
    proxy: 'example.com', // 这里修改成当前项目域名
    // 这里配置监控目录，只要符合规则的文件被修改，会立即发布资源并刷新页面（只有在 npm run watch 模式下哦）
    files: [
        'app/**/*.php',
        'public/themes/**/*.html',
        'public/example/**/*.html',
        'public/assets/dist/js/**/*.js',
        'public/assets/dist/css/**/*.css'
    ]
});