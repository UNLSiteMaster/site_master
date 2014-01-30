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

<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <h2>Approve Pending Roles</h2>

    <input type="submit" value="approve" />
    <h2>Other Options</h2>
    
    <a href="#" class="wdn-button button">Add a member</a>
</form>