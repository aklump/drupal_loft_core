const path = require('path');
const DEV = process.env.NODE_ENV !== 'production';
const dir_js = path.resolve(__dirname, 'js');
const dir_dist = path.resolve(__dirname, 'dist');

module.exports = {
  mode: DEV ? 'development' : 'production',
  entry: {
    clipboard: './js/clipboard.es6.js',
  },
  resolve: {
    modules: [dir_js, 'node_modules'],
  },
  externals: {
    jQuery: 'jQuery',
    Drupal: 'Drupal',
  },
  output: {
    path: dir_dist,
    filename: 'clipboard.min.js',
  },
  module: {
    rules: [
      {
        test: /\.es6\.js$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
          },
        },
      },
    ],
  },
};
