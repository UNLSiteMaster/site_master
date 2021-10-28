<form class="dcf-form" action="<?php echo $context->getEditURL(); ?>" method="POST">
    <table class="dcf-table dcf-table-bordered">
        <tr>
            <th>Select</th>
            <th>Provider</th>
            <th>UID</th>
            <th>First Name</th>
            <th>Last Name</th>
        </tr>
    <?php
    foreach ($context->results as $key=>$result) {
        ?>
        <tr>
            <td>
                <div class="dcf-input-radio">
                    <input id="user-<?php echo $key; ?>" name="user" type="radio" value="<?php echo $key; ?>" required />
                    <label for="user-<?php echo $key; ?>"><span class="dcf-sr-only">Select <?php echo $result['first_name'] . ' ' . $result['last_name']?></span></label>
                </div>
            </td>
            <td><?php echo $result['provider'] ?></td>
            <td><?php echo $result['uid'] ?></td>
            <td><?php echo $result['first_name'] ?></td>
            <td><?php echo $result['last_name'] ?></td>
        </tr>
        <?php
    }
    ?>
    </table>

    <div class="panel">
        <p>
            Confirm the person that best matches your search
        </p>
    </div>

    <?php $csrf_helper->insertToken() ?>
    <input type="hidden" name="stage" value="2" />
    <a href="<?php echo $context->getURL()?>" class="button dcf-btn">Back</a>
    <input type="submit" value="add selected" />
</form>