<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <label for="search">Enter your search term</label>
    <input id="term" name="term" autofocus required />

    <div class="panel">
        <p>
            The search term can be any string.  Examples include email addresses, UIDs, names, etc
        </p>
    </div>

    <input type="hidden" name="stage" value="1" />
    <input type="submit" value="Search" />
</form>