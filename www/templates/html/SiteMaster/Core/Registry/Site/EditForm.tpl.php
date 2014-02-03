<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <ol>
        <li>
            <label for="site_title">Site Title</label>
            <input type="text" id="site_title" name="title" value="<?php echo $context->site->title ?>" autofocus />
        </li>
        <li>
            <label for="support_email">Support Email Address</label>
            <input type="email" id="support_email" name="support_email" value="<?php echo $context->site->support_email ?>" />
        </li>
    </ol>

    <input type="hidden" name="action" value="edit" />
    <button type="submit">Edit</button>
</form>

<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <input type="hidden" name="action" value="delete" />
    <button type="submit" id="delete-site">Delete this site</button>
</form>