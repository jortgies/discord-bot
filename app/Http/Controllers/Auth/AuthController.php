<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

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
    public function handleProviderCallback()
    {
        $user = Socialite::driver('discord')->user();

        $authUser = $this->findOrCreateUser($user);

        \Auth::login($authUser, true);

        return redirect()->action('FileController@listFiles', ['user' => $user]);
    }

    /**
     * Return user if exists; create and return if doesn't
     *
     * @param $user
     * @return User
     */
    private function findOrCreateUser($user)
    {

        if ($authUser = User::where('discord_id', $user->id)->first()) {
            $authUser->update([
                'discord_id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'nickname' => $user->getNickname(),
                'avatar' => $user->getAvatar(),
            ]);
            return $authUser;
        }

        return User::create([
            'discord_id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'nickname' => $user->getNickname(),
            'avatar' => $user->getAvatar(),
        ]);
    }

    public function getLogout()
    {
        \Auth::logout();
        return redirect('/');
    }
}
