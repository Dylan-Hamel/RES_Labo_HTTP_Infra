var Chance = require('chance');
var chance = new Chance();

var express = require('express');
var app = express();
 
app.get('/', function(request, respond) {
	respond.send(generateCommand());

});

app.listen(3000, function() {
	console.log("Accept HTTP requests");
});


function generateCommand() {
 	var numberOfCommand = chance.integer({min:0,max:10});
	console.log(numberOfCommand);
	var commands = [];
	for (var i=0;i<numberOfCommand;i++) {
		commands.push({IPV6:chance.ipv6()});
	};
	console.log(commands);
	return commands;
}
