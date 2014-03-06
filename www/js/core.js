$(document).ready(function() {
    $(".flexnav").flexNav();

    $("#delete-site").click(function(e) {
        return confirm('Are you sure you want to delete this site?');
    });

    $(".scan-site").click(function(e) {
        return confirm('Scanning a site may take a long time, do you wish to continue?');
    });

    $(".scan-page").click(function(e) {
        return confirm('Scanning a page may take a long time, do you wish to continue?');
    });
    
    $(".scan table, .metric-grade-details table").tablesorter();
    
    $(".close-action").click(function() {
        window.location.hash = '!';
        return false;
    });
});
