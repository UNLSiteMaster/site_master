<form class="dcf-form dcf-mb-6" action="<?php echo $context->getEditURL(); ?>" method="POST">
    <fieldset>
        <legend>Select roles for this site <small class="dcf-required">Required</small></legend>
        <ul class="dcf-list-bare">
        <?php
        foreach ($context->all_roles as $role) {
            $checked = '';
            $pending = '';
            $max_limit_hit_or_not_distinct = '';
            if ($context->userHasRole($role->id)) {
                $checked = 'checked="checked"';
                $member_role = $context->join_user_membership->getRole($role->id);
                if (!$member_role->isApproved()) {
                    $pending = '(pending approval or self verification)';
                }
            }
            if (isset($role->max_number_per_site) && !$context->userHasRole($role->id)) {
                $members_with_role = $context->countNumberOfUsersWithRole($role->id);
                if ($members_with_role >= intval($role->max_number_per_site)) {
                    $max_limit_hit_or_not_distinct = 'disabled="disabled"';
                }
            }
            if (isset($role->distinct_from) && $context->userHasRole($role->distinct_from)) {
                $max_limit_hit_or_not_distinct = 'disabled="disabled"';
            }
            ?>
            <li class="dcf-input-checkbox">
                <input type="checkbox" id="role_<?php echo $role->id ?>" name="role_ids[]" value="<?php echo $role->id; ?>" <?php echo $checked; ?> <?php echo $max_limit_hit_or_not_distinct; ?>>
                <label for="role_<?php echo $role->id ?>">
                    <?php echo $role->role_name ?> - <?php echo $pending . ' ' . $role->description ?>
                </label>
            </li>
            <?php
        }
        ?>
        </ul>
    </fieldset>

    <div class="panel">
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
                    <a href="<?php echo $context->site->getURL(); ?>verify/" class="button dcf-btn">Verify Myself Now</a>
                    <?php
                }
                ?>
            </p>
            <?php
        }
        ?>
    </div>
    
    <?php $csrf_helper->insertToken(\SiteMaster\Core\Controller::urlToRequestURI($context->getEditURL())) ?>
    <input class="dcf-btn dcf-btn-primary" type="submit" value="Update Roles" />
</form>
