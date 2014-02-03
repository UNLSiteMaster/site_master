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
            Select some roles for this site.
        </p>
        
        <p>
        <?php
        if ($context->approveRoles()) {
            echo 'The roles that you select will be approved';
        } else {
            echo 'The roles that you select will need to be approved';
        }
        ?>
        </p>
        
        <?php
        if ($context->needsVerification()) {
            ?>
            <p>
                You are not yet verified as a member of the site.  Once you add some roles, we will walk you though the verification process.
            </p>
            <?php
        }
        ?>
    </div>

    <input type="submit" value="Update roles" />
</form>