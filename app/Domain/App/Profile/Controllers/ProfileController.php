<?php

namespace App\Domain\App\Profile\Controllers;

use App\Domain\App\Profile\Models\Profile;
use App\Domain\App\Profile\Requests\SearchProfileRequest;
use App\Domain\App\Profile\Requests\UpdateCommunicationRequest;
use App\Domain\App\Profile\Resources\ProfileResource;
use App\Domain\Messages\Models\ClientCommunication;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

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
     * Поиск клиентов по набору необязательных параметров, с пагинацией.
     *
     * CLIENT_PROPERTY (телефон/email) подключаем join'ом, а не через отдельную модель —
     * фильтр по контактам не должен исключать клиента, у которого просто нет строки
     * в CLIENT_PROPERTY (см. ClientCommunication).
     */
    public function search(SearchProfileRequest $request): JsonResponse
    {
        $query = Profile::query()
            ->leftJoin('CLIENT_PROPERTY', 'CLIENT_PROPERTY.client_id', '=', 'CLIENT.client_id')
            ->select('CLIENT.*', 'CLIENT_PROPERTY.client_mphone', 'CLIENT_PROPERTY.client_email')
            ->when($request->client_code, fn ($q, $v) => $q->where('CLIENT.client_code', 'like', "%{$v}%"))
            ->when($request->name, function ($q, $v) {
                $q->where(function ($q2) use ($v) {
                    $q2->where('CLIENT.client_name', 'like', "%{$v}%")
                        ->orWhere('CLIENT.client_last_name', 'like', "%{$v}%")
                        ->orWhere('CLIENT.client_middle_name', 'like', "%{$v}%");
                });
            })
            ->when($request->phone, fn ($q, $v) => $q->where('CLIENT_PROPERTY.client_mphone', 'like', "%{$v}%"))
            ->when($request->email, fn ($q, $v) => $q->where('CLIENT_PROPERTY.client_email', 'like', "%{$v}%"))
            ->when($request->birthday_from, fn ($q, $v) => $q->whereDate('CLIENT.client_birthday', '>=', $v))
            ->when($request->birthday_to, fn ($q, $v) => $q->whereDate('CLIENT.client_birthday', '<=', $v))
            // 1/0, не через SexEnum::id() — та объявлена под int-параметр и падает TypeError на строке 'man'/'woman'.
            ->when($request->sex, fn ($q, $v) => $q->where('CLIENT.client_sex', $v === 'man' ? 1 : 0))
            ->orderByDesc('CLIENT.client_id');

        $page = $query->paginate(50, page: $request->integer('page', 1));

        $page->getCollection()->transform(fn (Profile $profile) => [
            'id' => $profile->client_id,
            'code' => $profile->client_code,
            'name' => trim("{$profile->client_last_name} {$profile->client_name} {$profile->client_middle_name}"),
            'birthday' => $profile->client_birthday?->format('d.m.Y'),
            'sex' => $profile->client_sex,
            'phone' => $profile->client_mphone,
            'email' => $profile->client_email,
        ]);

        return response()->json($page);
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
