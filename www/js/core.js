$(document).ready(function() {
    $(".flexnav").flexNav();

    $("#delete-site").click(function(e) {
        return confirm('Are you sure you want to delete this site?');
    });
    
    $(".scan table, .metric-grade-details table").tablesorter();
    
    $(".close-action").click(function() {     
        window.location.hash = '!';
        return false;
    });
});


