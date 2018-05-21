var Chance = require('chance');
var chance = new Chance();

var express = require('express');
var app = express();
 
app.get('/dh', function(request, respond) {
	respond.send("SEND DH !! ");

});

app.get('/', function(request, respond) {
	respond.send("SEND BASE!! ");

});

app.listen(3000, function() {
	console.log("Accept HTTP requests");
});

// console.log("Bonjour " + chance.name());