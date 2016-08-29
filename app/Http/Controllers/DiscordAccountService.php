<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Contracts\User as ProviderUser;
use App\Models\User;

class DiscordAccountService {

    public function createUser(ProviderUser $providerUser)
    {
        //TODO:update data if it has changed since last login
        $user = User::whereEmail($providerUser->getEmail())->first();
        if(!$user)
        {
            $user = User::create([
                'discord_id' => $providerUser->getId(),
                'name' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
                'nickname' => $providerUser->getNickname(),
                'avatar' => $providerUser->getAvatar(),
            ]);
        }
        return $user;
    }
}