<?php

namespace App\Domain\App\Profile\Controllers;

use App\Domain\App\Profile\Models\Profile;
use App\Domain\App\Profile\Requests\UpdateCommunicationRequest;
use App\Domain\App\Profile\Resources\ProfileResource;
use App\Domain\Messages\Models\ClientCommunication;
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
        $profile = Profile::where('client_id', $id)->firstOrFail();

        return ProfileResource::make($profile);
    }

    /**
     * Profile: update phone/email + consent
     */
    public function updateCommunication(UpdateCommunicationRequest $request, int $id): ProfileResource
    {
        $data = $request->validated();

        $communication = ClientCommunication::where('client_id', $id)->firstOrFail();

        $communication->update([
            'client_mphone' => $data['phone'] ?? null,
            'client_smsuse' => $data['sms_allowed'] ?? false,
            'client_email' => $data['email'] ?? null,
            'subscriber_email_status' => $data['email_allowed'] ?? false,
        ]);

        $profile = Profile::where('client_id', $id)->firstOrFail();

        return ProfileResource::make($profile);
    }
}
