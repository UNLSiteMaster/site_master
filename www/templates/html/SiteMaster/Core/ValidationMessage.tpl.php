<div class="alert alert-error">
    <h2>Validation Error</h2>
    <ul>
        <?php foreach ($context->messages as $element=>$message): ?>
            <li><a href="#<?php echo $element ?>"><?php echo $message ?></a></li>
        <?php endforeach ?>
    </ul>
</div>
