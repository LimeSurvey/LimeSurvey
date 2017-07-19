var webpack = require('webpack');
var path = require('path');


// Naming and path settings
var appName = 'lsadminpanel';
var entryPoint = ['./src/main.js'];
var exportPath = path.resolve(__dirname, './build');

// Enviroment flag
var env = process.env.WEBPACK_ENV;
var plugins = [
  // ...
  new webpack.DefinePlugin({
    'process.env': {
      NODE_ENV: '"'+env+'"'
    }
  })
];

  appName = appName + '.js';


// Main Settings config
module.exports = {
  entry: entryPoint,
  devtool: 'source-map',
  output: {
    path: exportPath,
    filename: appName
  },
  module: {
    rules: [
      {
        test: /\.scss$/,
        use: [
          {
            loader: "style-loader" // creates style nodes from JS strings
          }, {
            loader: "css-loader" // translates CSS into CommonJS
          }, {
            loader: "sass-loader" // compiles Sass to CSS
          }
        ]
      },
      {
        test: /\.vue$/,
        use: 'vue-loader'
      }
    ],
    loaders: [
      {
        test: /\.vue$/,
        loader: [
          'vue-loader',
          "eslint-loader",
          'babel'
          ],
      },
      {
        test: /\.js$/,
        exclude: /(node_modules|bower_components)/,
        loader: [
          "eslint-loader",
          'babel'
          ],
        query: {
          presets: ['es2015']
        }
      },
      {
        loader: "sass-loader",
        options: {
            data: "$env: " + process.env.NODE_ENV + ";"
        }
    }
    ]
  },
  resolve: {
    alias: {
      'vue$': 'vue/dist/vue.esm.js'
    }
  },
  plugins
};