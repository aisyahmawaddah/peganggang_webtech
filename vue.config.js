module.exports = {
  configureWebpack: {
    plugins: []
  },
  chainWebpack: config => {
    // Remove the progress plugin that's causing issues
    config.plugins.delete('progress');
  }
}