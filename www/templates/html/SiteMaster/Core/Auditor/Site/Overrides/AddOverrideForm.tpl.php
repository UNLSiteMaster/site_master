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

<form class="dcf-form" method="post">
    <fieldset>
        <legend>Scope <small class="dcf-required">Required</small></legend>
        <div class="dcf-input-radio">
            <input id="scope-element" type="radio" name="scope" value="ELEMENT" required checked>
            <label for="scope-element">Just this element on this page (matching the mark, value, page, and HTML context)</label>
        </div>
        <div class="dcf-input-radio">
            <input id="scope-page" type="radio" name="scope" value="PAGE">
            <label for="scope-page">Just this page (only matches this mark, value, and page)</label>
        </div>
        <div class="dcf-input-radio">
            <input id="scope-site" type="radio" name="scope" value="SITE" required>
            <label for="scope-site">Entire site (matches the mark and value on the entire site)</label>
        </div>
    </fieldset>
    <label for="reason">The reason for this override (describe how you determined that this is not an error) <small class="dcf-required">Required</small></label>
    <textarea id="reason" name="reason" required></textarea>
    <?php $csrf_helper->insertToken() ?>
    <button class="dcf-mt-6 dcf-btn dcf-btn-primary">Submit</button>
</form>
