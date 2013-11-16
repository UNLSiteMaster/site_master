<?php
namespace SiteMaster;

interface ViewableInterface
{
    /*
     * Should return an absolute url for this view.
     *
     * @return string url
     */
    public function getURL();

    /*
     * Should return the page title
     *
     * @return string title
     */
    public function getPageTitle();
}