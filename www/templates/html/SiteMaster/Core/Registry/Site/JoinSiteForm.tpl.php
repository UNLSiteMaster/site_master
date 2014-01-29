<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <ul>
    <?php
    foreach ($context->all_roles as $role) {
        $checked = '';
        if ($context->userHasRole($role->id)) {
            $checked = 'checked="checked"';
        }
        ?>
        <li>
            <label>
                <input type="checkbox" name="role_ids[]" value="<?php echo $role->id; ?>">
                <?php echo $role->role_name ?> - <?php echo $role->description ?>
            </label>
        </li>
        <?php
    }
    ?>
    </ul>

    <div class="panel">
        <p>
            Once the site is created, you can choose your role.  We will walk you though that process after you submit this form.
        </p>
    </div>

    <input type="submit" value="Update my roles" />
</form>