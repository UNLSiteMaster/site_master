<?php
namespace SiteMaster\Core;

interface PostHandlerInterface
{
    public function handlePost($get, $post, $files);

    /*
     * Should return an absolute url for this view.
     *
     * @return string url
     */
    public function getEditURL();
}