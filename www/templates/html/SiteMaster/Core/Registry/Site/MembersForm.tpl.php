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
            <ul>
                <li>
                    <span class="member-name"><?php echo $user->getName() ?></span>
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
            </ul>
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
    <form action="<?php echo $context->getEditURL(); ?>" method="POST">
        <h2>Approve Pending Roles</h2>
        <?php
        if (!$context->pending->count()) {
            echo "There are no pending roles.";
        } else {
            ?>
            <ul>
                <?php
                foreach ($context->pending as $memberRole) {
                    $role = $memberRole->getRole();
                    ?>
                    <li>
                        <label>
                            <input type="checkbox" name="approve[]" value="<?php echo $memberRole->id; ?>">
                            <?php echo $role->role_name ?> - <?php echo $role->description ?>
                        </label>
                    </li>
                    <?php
                }
                ?>
            </ul>
            <input type="submit" value="approve selected" />
            <?php
        }
        ?>
        
        <h2>Other Options</h2>

        <a href="<?php echo $context->site->getURL()?>members/add/" class="wdn-button button">Add a member</a>
    </form>
    <?php
}
?>
