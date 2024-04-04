<?php

namespace App\Domain\AppProfile\Controllers;

use App\Domain\AppProfile\Models\Profile;
use App\Domain\AppProfile\Resources\ProfileResource;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    /**
     * Profile
     *
     * Get a basic client profile
     */
    public function show(int $id): ProfileResource
    {
        $profile = Profile::where('client_id', $id)->first();

        return ProfileResource::make($profile);
    }
}
