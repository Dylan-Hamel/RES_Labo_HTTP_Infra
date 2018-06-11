$(function() {
	console.log("Loading IPv6");
    
    function loadCommand() {
        $.getJSON( "/api/students/", function( commands ) {
            console.log(commands[0]);
            var message = "Nobody is here";
            if (students.length > 0) {
                message = students[0].IPV6
            }
            $(".mb-3").text(message);
        }); 
    };
    loadCommand();
    setInterval( loadCommand, 1000);
});