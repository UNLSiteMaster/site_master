<div id="scan-progress-<?php echo $context->scan->id?>" class="scan-progress">
</div>
<?php
  $savvy->loadScriptDeclaration("
    var scan_progress_" . $context->scan->id. " = new Nanobar({
        bg: '#acf',
        // left target blank for global nanobar
        target: document.getElementById('scan-progress-" . $context->scan->id . "'),
        // id for new nanobar
        id: 'scan-progress-" . $context->scan->id . "-bar'
    });

    function update_scan_progress_" . $context->scan->id ."()
    {
        $.getJSON('" . $context->getURL() . "?format=json', function( data ) {
            scan_progress_" . $context->scan->id . ".go(data.percent_complete);
            if (data.percent_complete < 100) {
                window.setTimeout(update_scan_progress_" . $context->scan->id . ", 5000);
                if (data.queue_position !== false) {
                    $('.scan-queue-position').text('-- position: ' + (data.queue_position+1));
                } else {
                    $('.scan-queue-position').empty();
                }
                $('.scan-status').text(data.status);
            } else {
                location.reload();
            }
        });
    }

    $(function() {
        //move bar
        update_scan_progress_" . $context->scan->id ."();
    });
  ");
?>
