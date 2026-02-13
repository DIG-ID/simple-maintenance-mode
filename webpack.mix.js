// webpack.mix.js

const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss');

mix
  .setResourceRoot('./')
  .setPublicPath('dist')

  .sass('assets/sass/main.sass', 'css')
  .options({
    postCss: [ tailwindcss('./tailwind.config.js') ],
    processCssUrls: false,
  })

  .browserSync({
    proxy: {
      target: "https://testing.digid",
      ws: true,
    },
    https: true,
    files: ["./**/*.php", "./dist/css/*.css"],
    injectChanges: true,
  })
  .disableNotifications();
  

if (!mix.inProduction()) {
  mix
    .webpackConfig({
      devtool: "source-map"
    })
    .sourceMaps();
}

if (mix.inProduction()) {
  mix.version();
}