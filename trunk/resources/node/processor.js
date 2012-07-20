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
var BIS = require('bis');

var bis = new BIS;


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

var processQueueFlag = false;

// Routes
console.log('Testing');

app.all('/mysqlTest', function(request, response){
	var mysql      = require('mysql');
	var connection = mysql.createConnection({
	  host     : 'localhost',
	  user     : 'root',
	  password : '',
	  database : 'bis',
	});

	connection.connect();
	connection.query('SELECT * FROM users', function(err, rows, fields) {
		if (err) throw err;
		console.log('Query result: ', rows);
	});
	connection.end();
	
	response.writeHead(200, { 'Content-Type': 'application/json' });
	response.end(JSON.stringify ({ success : true}));

});


app.all('/tiles/generate', function(request, response){
	var st = new silverTiles({tileSize : 256, sourcePath : 'G:\\wamp\\www\\bis\\resources\\node\\cacheFolder\\',image : 'NLU0000002.jpg'});
	st.generateTiles();
	response.writeHead(200, { 'Content-Type': 'application/json' });
	response.end(JSON.stringify ({ success : true}));
});

app.all('/get_image_tiles', function(request, response){
	var callback = request.query['callback'] || request.param("callback") || '';
	var timeStart = Date.now();
	var imageId = request.query['image_id'] || request.param("image_id") || '';
	var tileSize = 256;
	var sourcePath = 'G:\\wamp\\www\\bis\\resources\\node\\cacheFolder\\';
	var image = 'LSU00082052.jpg';

	var st = new silverTiles({tileSize : tileSize, sourcePath : sourcePath, image : image});
	
	if(!st.cacheExist()) {
		st.createTiles(function(resp) {
			console.log(resp);
			console.log(st.getZoomLevel());
			response.writeHead(200, { 'Content-Type': 'application/json' });
			response.end(encodeCallback(JSON.stringify ({ success : true, processTime : Date.now() - timeStart, url : resp.url, tpl : resp.url + '{z}/tile_{i}.jpg', maxZoomLevel : resp.zoomLevel}),callback));
		});
	} else {
		st.touchCache(function(status){
			var url = st.getBaseUrl() + path.basename(image,'.jpg').toLowerCase() + '/';
			response.writeHead(200, { 'Content-Type': 'application/json' });
			response.end(encodeCallback(JSON.stringify ({ success : true, processTime : Date.now() - timeStart, url : url, tpl : url + '{z}/tile_{i}.jpg', maxZoomLevel : st.getZoomLevel()}),callback));
		});
	}

	
});

app.all('/process_queue/start', function(request, response){
	var callback = request.query['callback'] || request.param("callback") || '';
	
	if(processQueueFlag) {
		bis.getPQueueCount({},function(count) {
			response.writeHead(200, { 'Content-Type': 'application/json' });
			response.end(encodeCallback(JSON.stringify ({ success : true, message : 'Already Started', status : count}),callback));
		});
	} else {
		processQueueFlag = true;
		response.writeHead(200, { 'Content-Type': 'application/json' });
		response.end(encodeCallback(JSON.stringify ({ success : true}),callback));
	}
});

app.all('/process_queue/status', function(request, response){
	var callback = request.query['callback'] || request.param("callback") || '';
	var processType = request.query['processType'] || request.param("processType") || '';
	bis.getPQueueCount({processType : processType},function(count) {
		response.writeHead(200, { 'Content-Type': 'application/json' });
		response.end(encodeCallback(JSON.stringify ({ success : true, status : count}),callback));
	});
});

app.all('/process_queue/stop', function(request, response){
	var callback = request.query['callback'] || request.param("callback") || '';
	processQueueFlag = false;
	
	response.writeHead(200, { 'Content-Type': 'application/json' });
	response.end(encodeCallback(JSON.stringify ({ success : true}),callback));
});

app.listen(8888, function(){

});
