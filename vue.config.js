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
      }
  }
})
