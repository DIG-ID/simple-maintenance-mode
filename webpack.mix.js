// webpack.mix.js

const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss');

mix
  .setPublicPath('./')
  .setResourceRoot('./')

  .sass('admin/sass/smm-admin.sass', 'admin/css')
  .sass('public/sass/smm-public.sass', 'public/css')
  .sass('assets/sass/smm-common.sass', 'assets/css')
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
    files: [
      "./**/*.php",
      "./admin/css/*.css",
      "./public/css/*.css",
      "./assets/css/*.css"
    ],
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