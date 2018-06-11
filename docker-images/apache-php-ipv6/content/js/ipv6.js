$(function() {
        console.log("Loading IPv6");

    function loadCommand() {
        $.getJSON( "/api/ipv6/", function( commands ) {
            console.log(commands[0]);
            var message = "Nobody is here";
            if (commands.length > 0) {
                message = commands[0].IPV6
            }
            $(".mb-3").text(message);
        });
    };
    loadCommand();
    setInterval( loadCommand, 1000);
});