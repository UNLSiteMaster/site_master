<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <ul>
        <li>
            <label for="search">Enter your search term</label>
            <input type="text" id="term" name="term" autofocus required />
        </li>
    </ul>

    <div class="panel">
        <p>
            The search term can be any string.  Examples include email addresses, UIDs, names, etc
        </p>
    </div>

    <input type="hidden" name="stage" value="1" />
    <input type="submit" value="Search" />
</form>