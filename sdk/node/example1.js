var nodeBIS = require('node_bis');

var bis = new nodeBIS("mygWjFcGitqfQ", "http://bis.silverbiology.com/dev/resources/api/");

/*
bis.addImage('C:/xampp/htdocs/bis/dev/sdk/node/no-image.gif', function(err, data) {
	if (err) {
		console.log(err);
	} else {
		console.log("Success", data);
	}
});
*/

bis.listStorage(function(data) {
	console.log("Data: " , data);
});