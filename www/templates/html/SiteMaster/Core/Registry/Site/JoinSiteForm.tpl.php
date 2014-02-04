<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <ul>
    <?php
    foreach ($context->all_roles as $role) {
        $checked = '';
        $pending = '';
        if ($context->userHasRole($role->id)) {
            $checked = 'checked="checked"';
            $member_role = $context->join_user_membership->getRole($role->id);
            if (!$member_role->isApproved()) {
                $pending = '(pending approval or self verification)';
            }
        }
        ?>
        <li>
            <label>
                <input type="checkbox" name="role_ids[]" value="<?php echo $role->id; ?>" <?php echo $checked ?>>
                <?php echo $role->role_name ?> - <?php echo $pending . ' ' . $role->description ?>
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
                You are not yet verified as a member of the site. Once you add some roles, we will walk you though the verification process.
                <?php
                if ($context->user_roles && $context->user_roles->count()) {
                    ?>
                    <a href="<?php echo $context->site->getURL(); ?>verify/" class="button wdn-button">Verify Myself Now</a>
                    <?php
                }
                ?>
            </p>
            <?php
        }
        ?>
    </div>

    <input type="submit" value="Update roles" />
</form>