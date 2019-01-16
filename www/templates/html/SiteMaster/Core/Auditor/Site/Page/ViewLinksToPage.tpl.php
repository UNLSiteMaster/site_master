<div>
    <p>
        This is a list of links to the page <a href="<?php echo $context->page->uri ?>" class="external"><?php echo $context->page->uri ?></a> that were found on other pages during this scan.  This list can help find which pages linked to a 404 page.  There will be an entry for each link found on each page.
    </p>
    <ul>
        <?php foreach ($context->links as $link): ?>
            <?php $page = $link->getPage(); ?>
            <li>
                <a href="<?php echo $page->getURL(); ?>"><?php echo $page->uri ?></a>
                <?php if ($link->isRedirect()): ?>
                    (this was a redirect with the original URL of: <?php echo $link->original_url; ?> )
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="<?php echo $context->page->getURL() ?>" class="button dcf-btn">Go back to the page scan</a>
</div>
