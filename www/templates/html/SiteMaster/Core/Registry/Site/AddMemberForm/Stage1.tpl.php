<form class="dcf-form" action="<?php echo $context->getEditURL(); ?>" method="POST">
    <ul class="dcf-list-bare">
        <li>
            <label for="term">Enter your search term <small class="dcf-required">Required</small></label>
            <input type="text" id="term" name="term" autofocus required />
        </li>
    </ul>

    <div class="panel">
        <p>
            The search term can be any string. Only users with the same identity provider as you, and have public information will be included in the result. Examples include email addresses, UIDs, names, etc
        </p>
    </div>

    <?php $csrf_helper->insertToken() ?>
    <input type="hidden" name="stage" value="1" />
    <input class="dcf-btn dcf-btn-primary" type="submit" value="Search" />
</form>