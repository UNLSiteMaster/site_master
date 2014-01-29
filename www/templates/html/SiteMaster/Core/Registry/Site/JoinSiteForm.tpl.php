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
                <input type="checkbox" name="role_ids[]" value="<?php echo $role->id; ?>" <?php echo $checked ?>>
                <?php echo $role->role_name ?> - <?php echo $role->description ?>
            </label>
        </li>
        <?php
    }
    ?>
    </ul>

    <div class="panel">
        <p>
            Select some roles for this site.  Once you are added, you will need to verify your membership.  We will walk you though that step next.
        </p>
    </div>

    <input type="submit" value="Update my roles" />
</form>