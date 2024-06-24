<?php

namespace Hexters\Auth;

use App\Models\User;

class CurrentUser
{

    public static function get($guard)
    {
        $user = config("auth.guards.{$guard}.provider");
        $user = config("auth.providers.{$user}.model");
        return app($user ?? User::class);
    }

    public static function class($guard)
    {
        $user = config("auth.guards.{$guard}.provider");
        $user = config("auth.providers.{$user}.model");
        return $user ?? User::class;
    }
}
