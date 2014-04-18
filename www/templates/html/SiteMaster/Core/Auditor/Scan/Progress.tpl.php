<div id="scan-progress-<?php echo $context->scan->id?>" class="scan-progress">
</div>
<script type="text/javascript">
    var scan_progress_<?php echo $context->scan->id?> = new Nanobar({
        bg: '#acf',
        // left target blank for global nanobar
        target: document.getElementById('scan-progress-<?php echo $context->scan->id?>'),
        // id for new nanobar
        id: 'scan-progress-<?php echo $context->scan->id?>-bar'
    });

    function update_scan_progress_<?php echo $context->scan->id?>()
    {
        $.getJSON('<?php echo $context->getURL() ?>?format=json', function( data ) {
            scan_progress_<?php echo $context->scan->id?>.go(data.percent_complete);
            if (data.percent_complete < 100) {
                window.setTimeout(update_scan_progress_<?php echo $context->scan->id?>, 5000);
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
        update_scan_progress_<?php echo $context->scan->id?>();
    });
</script>
