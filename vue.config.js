const ImageMinimizerPlugin = require('image-minimizer-webpack-plugin');
const { defineConfig } = require('@vue/cli-service');
const path = require('path');

module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: "/",
  configureWebpack: {
    resolve: {
        alias: {
            '@': path.resolve('src'),
            '@img': path.resolve('src/assets/img')
        },
    },
    plugins: [
        new ImageMinimizerPlugin({
            minimizer: {
            implementation: ImageMinimizerPlugin.imageminMinify,
            options: {
                plugins: [
                ['mozjpeg', { quality: 65 }],
                ['pngquant', { quality: [0.65, 0.9], speed: 4 }],
                ['svgo', {
                    plugins: [
                    {
                        name: 'removeViewBox',
                        active: false,
                    },
                    ]
                }],
                ['gifsicle', { optimizationLevel: 7, interlaced: false }],
                ],
            },
            },
        }),
    ],
  }
})
