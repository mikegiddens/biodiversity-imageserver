/**
 * Module dependencies.
 */
var express =  require('express'),
 	fs = require('fs'),
	mkdirp = require('mkdirp'),
	_ = require('underscore'),
	path = require('path'),
	util = require('util'),
	sprintf = require('sprintf').sprintf
	// kue  = require('kue'),
	// jobs = kue.createQueue();
var app = module.exports = express.createServer();
var silverTiles = require('silvertiles');


// Configuration
app.configure(function(){
	app.use(express.bodyParser());
	app.use( express.static(__dirname + '/static') );  
});

app.configure('development', function(){
  app.use(express.errorHandler({ dumpExceptions: true, showStack: true }));
});

app.configure('production', function(){
  app.use(express.errorHandler());
});

function encodeCallback(str,callback) {
	if(typeof callback == 'undefined' || callback == '') {
		return str;
	} else {
		return callback + '(' + str + ')';
	}
}

// Routes
console.log('Testing');
app.all('/test', function(request, response){
	var st = new silverTiles({tileSize : 256, sourcePath : 'G:\\wamp\\www\\bis\\resources\\node\\cacheFolder\\',image : 'NLU0000002.jpg'});
	// st.getOriginalDimensions(function(dimensions){
		// console.log(dimensions);
	// });
	// st.createTiles();

	st.generateTiles();
	
	response.writeHead(200, { 'Content-Type': 'application/json' });
	response.end(JSON.stringify ({ success : true}));
});

app.listen(8888, function(){

});
