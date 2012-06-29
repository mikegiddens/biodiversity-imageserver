var nodeBIS = require('node_bis');

var bis = new nodeBIS("mygWjFcGitqfQ", "bis.silverbiology.com", "/dev/resources/api/");

bis.addImage('C:/xampp/htdocs/bis/dev/sdk/node/', 'Acer.JPG', 'Acer.JPG', '/myimages/', function(data, err) {
	if (data) {
		console.log("Success", data);
	} else {
		console.log(err);
	}
});

/*
var params = {
	barcode: 'USMS000018153'
}


bis.getImageInfo(params, function(data, err) {
	if (data) {
		console.log("Data: " , data);
	} else {
		console.log(err);
	}
});


/*
bis.getImageUrl(params, function(data, err) {
	if (data) {
		console.log("Data: " , data);
	} else {
		console.log(err);
	}
});
*/

/*
bis.listStorage(function(data, err) {
	if (data) {
		console.log("Data: " , data);
	} else {
		console.log(err);
	}
});
*/