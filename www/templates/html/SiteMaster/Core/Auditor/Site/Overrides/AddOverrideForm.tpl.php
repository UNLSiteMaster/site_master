<p>By adding an override, you will prevent a specific error or notice from appearing on future scans. <strong>Overrides must only be added if you have manually verified that it is not in fact a problem</strong>. Most metrics only allow overrides for notices.</p>

<h2>Mark Details</h2>
<p>This override will match the following values, unless otherwise noted.</p>
<dl class="fix-mark-details">
    <dt>Mark</dt>
    <dd><?php echo $context->mark->name ?></dd>
    
    <dt>Page</dt>
    <dd>
        <?php echo $context->page->uri ?>)
    </dd>
    
    <dt>Value Found</dt>
    <dd><?php echo ($context->page_mark->value_found === null)?'(empty)':$context->page_mark->value_found ?></dd>
    
    <dt>HTML Context</dt>
    <dd>
        <?php if ($context->page_mark->context === null): ?>
            (empty)
        <?php else: ?>
            <pre><code><?php echo trim(htmlentities($context->page_mark->getRaw('context'), ENT_COMPAT | ENT_HTML401, 'UTF-8', false)) ?></code></pre>
        <?php endif; ?>
        
    </dd>
</dl>

<form method="post">
    <fieldset>
        <legend>(required) Scope</legend>
        <label>
            <input type="radio" name="scope" value="ELEMENT" required checked>
            Just this element on this page (matching the mark, value, page, and HTML context)
        </label>
        <br />
        <label>
            <input type="radio" name="scope" value="PAGE">
            Just this page (only matches this mark, value, and page)
        </label>
        <br />
        <label>
            <input type="radio" name="scope" value="SITE" required>
            Entire site (matches the mark and value on the entire site)
        </label>
    </fieldset>
    <label for="reason">(required) The reason for this override (describe how you determined that this is not an error)</label>
    <textarea id="reason" name="reason" required></textarea>
    <button>Submit</button>
</form>