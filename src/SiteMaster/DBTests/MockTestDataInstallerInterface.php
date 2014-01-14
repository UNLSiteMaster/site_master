<?php
namespace SiteMaster\DBTests;

interface MockTestDataInstallerInterface
{
    /**
     * This function should execute commands to install mock data to the test database.
     */
    public function install();
}