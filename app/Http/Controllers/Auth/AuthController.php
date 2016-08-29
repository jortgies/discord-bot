<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\DiscordAccountService;

class AuthController extends Controller
{
    /**
     * Redirect the user to the Discord authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('discord')->redirect();
    }

    /**
     * Obtain the user information from Discord.
     *
     * @return Response
     */
    public function handleProviderCallback(DiscordAccountService $service)
    {
        $user = $service->createUser(Socialite::driver('discord')->user());
        auth()->login($user);
        return redirect()->action('FileController@listFiles', ['user' => $user]);
    }

    public function getLogout()
    {
        \Auth::logout();
        return redirect('/');
    }
}
