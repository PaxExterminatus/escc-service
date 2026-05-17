<?php

namespace App\Domain\App\Profile\Controllers;

use App\Domain\App\Profile\Models\Profile;
use App\Domain\App\Profile\Resources\ProfileResource;
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
