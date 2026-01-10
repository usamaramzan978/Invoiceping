<?php

declare(strict_types=1);

namespace App\Actions\BussinessProfile;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;

final class CreateBusinessProfileAction
{
    public function execute(#[CurrentUser] User $user, array $data): BusinessProfile
    {
        $businessProfile = $user->business()->create($data);
        $businessProfile->refresh();

        return $businessProfile;
    }
}
