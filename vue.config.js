const ImageMinimizerPlugin = require('image-minimizer-webpack-plugin');
const { defineConfig } = require('@vue/cli-service');
const path = require('path');
const webpack = require('webpack');

module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: "/",
  devServer: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        pathRewrite: {
          '^/api': ''
        }
      }
    }
  },
  configureWebpack: {
    resolve: {
        alias: {
            '@': path.resolve('src'),
            '@img': path.resolve('src/assets/img')
        },
    },
    optimization: {
      splitChunks: {
        cacheGroups: {
          // Separa three.js em chunk próprio (160KB+) - só carrega quando necessário
          three: {
            test: /[\\/]node_modules[\\/]three[\\/]/,
            name: 'three',
            chunks: 'async',
            priority: 20,
            enforce: true
          },
          // Separa Font Awesome em chunk próprio (async para não bloquear render)
          fontawesome: {
            test: /[\\/]node_modules[\\/]@fortawesome[\\/]/,
            name: 'fontawesome',
            chunks: 'async',
            priority: 15,
            enforce: true
          }
        }
      }
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
        // Definir variáveis de ambiente
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify(process.env.NODE_ENV || 'development'),
                VUE_APP_API_URL: JSON.stringify(process.env.VUE_APP_API_URL || '/api')
            }
        })
    ],
  }
})
