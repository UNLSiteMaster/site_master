<?php
namespace SiteMaster\Core\User;

use SiteMaster\Core\RequiredLoginException;

class Session
{
    protected static $session;

    public static function logIn(User $user)
    {
        $session = self::getSession();
        $session->start();

        $session->set('user.id', $user->id);
    }

    public static function logOut()
    {
        $session = self::getSession();
        $session->clear();
        $session->invalidate();
    }

    /**
     * Get the currently logged in user
     * 
     * @return bool|\SiteMaster\Core\User\User
     */
    public static function getCurrentUser()
    {
        $session = self::getSession();

        return User::getByID($session->get('user.id'));
    }

    /**
     * Require login
     * 
     * @throws \SiteMaster\Core\RequiredLoginException
     */
    public static function requireLogin()
    {
        if (!self::getCurrentUser()) {
            throw new RequiredLoginException("You must be logged in to access this", 401);
        }
    }
    
    public static function start()
    {
        if (!self::$session) {
            self::$session = new \Symfony\Component\HttpFoundation\Session\Session();
            self::$session->start();
        }
    }

    public static function getSession()
    {
        self::start();

        return self::$session;
    }
}