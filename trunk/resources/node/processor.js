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
var st = new silverTiles();


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
st.generateTiles();

app.listen(8888, function(){

});
