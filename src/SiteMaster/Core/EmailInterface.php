<?php
namespace SiteMaster\Core;

/**
 * By default, the email body is rendered via savvy for this class.
 * 
 * Interface EmailInterface
 * @package SiteMaster\Core
 */
interface EmailInterface
{
    /**
     * Get the To address
     * 
     * Expected to return a email address as a string, or an array of array('email@example.org' => 'Name');
     * 
     * @return array|string
     */
    public function getTo();

    /**
     * Get the Subject of the email
     * 
     * @return mixed
     */
    public function getSubject();
}