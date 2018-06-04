$(function() {
	console.log("Loading Students");
    
    function loadStudents() {
        $.getJSON( "/api/students/", function( students ) {
            console.log(students[0]);
            var message = "Nobody is here";
            if (students.length > 0) {
                message = students[0].fistName + " " + students[0].lastname;
            }
            $(".mb-3").text(message);
        }); 
    };
    loadStudents();
    setInterval( loadStudents, 1000);
});