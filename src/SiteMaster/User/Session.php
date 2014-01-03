<?php
namespace SiteMaster\User;

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

    public static function getCurrentUser()
    {
        $session = self::getSession();

        return User::getByID($session->get('user.id'));
    }

    public static function requireLogin()
    {
        if (!self::getCurrentUser()) {
            throw new RequiredLoginException();
        }
    }

    public static function getSession()
    {
        if (!self::$session) {
            self::$session = new \Symfony\Component\HttpFoundation\Session\Session();
        }

        return self::$session;
    }
}