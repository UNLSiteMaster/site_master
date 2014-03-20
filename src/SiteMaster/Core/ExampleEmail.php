<?php
namespace SiteMaster\Core;

class ExampleEmail implements EmailInterface
{
    public function getTo()
    {
        return 'mfairchild365@gmail.com';
    }

    public function getSubject()
    {
        return 'Test subject';
    }
}