<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <table>
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
                <label>
                    <input name="user" type="radio" value="<?php echo $key; ?>" required />
                    <span class="hide wdn-hide">Select <?php echo $result['first_name'] . ' ' . $result['last_name']?></span>
                </label>
                
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