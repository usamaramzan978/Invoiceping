<?php

declare(strict_types=1);

namespace App\Actions\BussinessProfile;

use App\Models\BusinessProfile;

final class UpdateBusinessProfileAction
{
    public function execute(BusinessProfile $businessProfile, array $data): BusinessProfile
    {
        $businessProfile->update($data);

        return $businessProfile;
    }
}
