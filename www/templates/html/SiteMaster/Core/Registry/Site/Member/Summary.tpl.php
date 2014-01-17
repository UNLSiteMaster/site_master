<?php
$user = $context->getUser();
$roles = $context->getRoles();
?>
<li>
    <?php echo $user->getName(); ?>
    <?php 
    if ($roles->count()) {
        ?>
        <ul>
            <?php
            foreach ($roles as $role) {
                ?>
                <li><?php echo $role->getRole()->role_name;?></li>
                <?php
            }
            ?>
        </ul>
        <?php
    }
    ?>
</li>