<?php
namespace SiteMaster\Plugin;

/**
 * All authentication plugins must implement this interface.
 * See the auth_google plugin for an example
 * 
 * Interface AuthenticationInterface
 * @package SiteMaster\Plugin
 */
interface AuthenticationInterface
{
    /**
     * Get the URL to log in using this authentication method
     * 
     * @return string
     */
    public function getLoginURL();

    /**
     * Get the name of the provider that this authentication method provides
     * This is what is stored in the users.provider table
     * 
     * @return string
     */
    public function getProviderMachineName();

    /**
     * Get the name of the authentication provider that this plugin provides, as
     * readable by humans
     * 
     * @return string
     */
    public function getProviderHumanName();
}