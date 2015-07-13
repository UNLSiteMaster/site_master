<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <ul>
        <li>
            <?php
            
            $value = '';
            if (isset($context->options['recommended'])) {
                $value = 'value="' . urldecode($context->options['recommended']) . '"';
            }
            
            $invalid = '';
            if (isset($context->errors['base_url'])) {
                $invalid = 'aria-invalid="true"';
            }
            ?>
            <label for="base_url"><span class="required">(required)</span> The base URL of the site (must end in a trailing slash)</label>
            <input type="url" id="base_url" name="base_url" placeholder="http://www.yoursite.edu/" autofocus required <?php echo $value ?> <?php echo $invalid ?>/>
        </li>
    </ul>

    <div class="panel">
        <p>
            Once the site is created, you can choose your role.  We will walk you though that process after you submit this form.
        </p>
    </div>
    
    <input type="submit" value="Add my site" />
</form>