<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <ul>
        <li>
            <label for="term"><span class="required">(required)</span> Enter your search term</label>
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
    <input type="submit" value="Search" />
</form>