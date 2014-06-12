

var sitemaster = {
    initAnchors : function() {
        /* scroll to elements in an accessible way.
         * via http://www.sitepoint.com/learning-to-focus/
         * */
        
         /* make sections focusable */
         $('section[id]').attr('tabindex', '0');
        
         $('body').on('click', 'a[href^=#]',function(e) {
            //Skip modal links
            if ($(e.target).is('.call-modal') || $(e.target)[0].href === '#') { return; }

            //Implement scroll logic
            var $linkElem = $(this);
            if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
                if (target.length) {
                    $('html,body').animate({
                        scrollTop: target.offset().top - 50
                    }, 1000, function() {
                        /* ADDED: focus the target */
                        target.focus();
                        /* end ADDED */
                        /* ADDED: update the URL */
                        if (window.history && history.replaceState) {
                            history.replaceState(null, null, "#" + $linkElem.attr('href').substring(1));
                        } else {
                            window.location.hash = $linkElem.attr('href').substring(1);
                        }
                        // window.location.hash = $(this).attr('href').substring(1, $(this).attr('href').length);
                        /* end ADDED */
                    });
                    return false;
                }
            }
        });
    },
    
    initInPageNav : function() {
        $(".in-page-nav").scrollToFixed({marginTop: 50, minWidth: 768});
    },
    
    initTables : function() {
        $(".scan table, .hot-spot table, .metric-grade-details table, table.sortable").tablesorter();
    }
};


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
    
    $(".close-action").click(function() {
        window.location.hash = '!';
        return false;
    });
    
    sitemaster.initAnchors();
    sitemaster.initInPageNav();
    sitemaster.initTables();
});
