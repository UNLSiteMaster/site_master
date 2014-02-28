<?php
namespace SiteMaster\Core\Auditor\Logger;

use DOMXPath;

use SiteMaster\Core\Auditor\Site\Page;

class PageTitle extends PageTitleInterface
{
    /**
     * Get the Page Title
     * 
     * @param DOMXPath $xpath the xpath of the page
     * @return bool|string the page title
     */
    public function getPageTitle(DOMXPath $xpath)
    {
        if (!$result = $xpath->query('//xhtml:title')) {
            return false;
        }

        if (!$result->length) {
            return false;
        }

        return $result->item(0)->textContent;
    }
}
