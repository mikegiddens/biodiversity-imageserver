var nodeBIS = require('node_bis');

var bis = new nodeBIS("mygWjFcGitqfQ", "bis.silverbiology.com");

bis.create_object('C:/xampp/htdocs/bis/dev/sdk/node/no-image.gif', function(err, data) {
	if (err) {
		console.log(err);
	} else {
		console.log("Success", data);
	}
});