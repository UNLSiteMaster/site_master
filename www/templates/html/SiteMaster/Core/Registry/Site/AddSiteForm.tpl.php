<form class="dcf-form" action="<?php echo $context->getEditURL(); ?>" method="POST">
    <ul>
        <li class="dcf-form-group">
            <?php
            
            $value = '';
            if (isset($_POST['base_url'])) {
                $value = 'value="' . $savvy->escape($_POST['base_url']) . '"';
            }
            
            if (isset($context->options['recommended'])) {
                $value = 'value="' . $context->options['recommended'] . '"';
            }
            
            $invalid = '';
            if (isset($context->errors['base_url'])) {
                $invalid = 'aria-invalid="true"';
            }
            ?>
            <label for="base_url">The base URL of the site (must end in a trailing slash) <span class="dcf-required">required</span></label>
            <input type="url" id="base_url" name="base_url" placeholder="http://www.yoursite.edu/" autofocus required <?php echo $value ?> <?php echo $invalid ?>/>
        </li>
    </ul>

    <div class="panel">
        <p>
            Once the site is created, you can choose your role.  We will walk you though that process after you submit this form.
        </p>
    </div>

    <?php $csrf_helper->insertToken() ?>
    <input type="submit" value="Add my site" />
</form>