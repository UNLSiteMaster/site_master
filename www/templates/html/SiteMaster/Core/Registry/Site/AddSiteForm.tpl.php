<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <label for="base_url">The base URL of the site</label>
    <input type="url" id="base_url" name="base_url" placeholder="http://www.yoursite.edu/" autofocus required />

    <div class="panel">
        <p>
            We will add you as a 'Manager' to the site.  You will have to verify that you are able to upload files to the site.  We will walk you though that process after you submit this form.
        </p>
    </div>
    
    <input type="submit" value="Add my site" />
</form>