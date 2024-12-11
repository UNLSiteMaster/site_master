<?php
$can_edit = $context->canEdit();
?>

<h2>Members</h2>

<?php
if (!$context->members->count()) {
    ?>
    There are currently no members
    <?php
} else {
    ?>
    <ul>
        <?php
        foreach ($context->members as $member) {
            $user = $member->getUser();
            $roles = $member->getRoles();
            ?>
                <li>
                    <span class="member-name"><?php echo $user->getName($can_edit) ?></span>
                    <div class="options">
                        <?php
                        if ($can_edit) {
                            ?>
                            <a href="<?php echo $context->site->getURL()?>join/<?php echo $user->id;?>/">Edit Roles</a>
                            <?php
                        }
                        ?>
                    </div>
                    <ul>
                        <?php
                        foreach ($roles as $role) {
                            $approved = '<span class="pending">pending</span>';
                            if ($role->isApproved()) {
                                $approved = '';
                            }
                            ?>
                            <li>
                                <span class="role"><?php echo $role->getRole()->role_name ?></span> <?php echo $approved ?>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </li>
        <?php
        }
        ?>
    </ul>
<?php
}
?>

<?php
if ($can_edit) {
    ?>
    <form class="dcf-form dcf-mb-6" action="<?php echo $context->getEditURL(); ?>" method="POST">
        <h2>Approve Pending Roles</h2>
        <?php
        if (!$context->pending->count()) {
            echo "There are no pending roles.";
        } else {
            $csrf_helper->insertToken();
        ?>
            <ul>
                <?php
                foreach ($context->pending as $member_role) {
                    $role = $member_role->getRole();
                    $user = $member_role->getUser();
                    ?>
                    <li class="dcf-input-checkbox">
                        <input id="approve-<?php echo $member_role->id; ?>" type="checkbox" name="approve[]" value="<?php echo $member_role->id; ?>">
                        <label for="approve-<?php echo $member_role->id; ?>"><?php echo $user->getName(true) . ' - ' . $role->role_name ?></label>
                    </li>
                    <?php
                }
                ?>
            </ul>
            <input class="dcf-btn dcf-btn-primary" type="submit" value="Approve Selected" />
            <?php
        }
        ?>
        
        <h2>Other Options</h2>

        <a href="<?php echo $context->site->getURL()?>members/add/" class="dcf-btn button">Add A Member</a>
    </form>
    <?php
}
?>
