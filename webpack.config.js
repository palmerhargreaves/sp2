module.exports = {
	entry: __dirname + "/www/js/chats/dev/app.js",
	output: {
		path: __dirname + "/www/js/chats/prod/",
		filename: "bundle.min.js"
	},
	module: {
	    loaders: [
	      {
	        test: /\.js?$/,
	        exclude: /(node_modules|bower_components)/,
	        loader: 'babel-loader',
	        query: {
	          presets: ['es2015'],
	        }
	      }
	    ]
  	}
}