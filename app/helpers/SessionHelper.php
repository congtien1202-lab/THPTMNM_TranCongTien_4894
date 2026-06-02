<?php
class SessionHelper
{
    public static function init()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn()
    {
        self::init();
        return isset($_SESSION['user_id']);
    }

    public static function login($user)
    {
        self::init();
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role ?? 'user';
    }

    public static function logout()
    {
        self::init();
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['role']);
        session_destroy();
    }

    public static function isAdmin()
    {
        self::init();
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public static function getUsername()
    {
        self::init();
        return $_SESSION['username'] ?? '';
    }
}
?>
