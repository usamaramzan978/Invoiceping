<?php

declare(strict_types=1);

namespace App\Actions\BussinessProfile;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class CreateBusinessProfileAction
{
    public function execute(#[CurrentUser] User $user, array $data): BusinessProfile
    {
        // Handle image upload if present
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $imagePath = $data['image']->store('business-logos', 'public');
            $data['image'] = $imagePath;
        }

        $businessProfile = $user->business()->create($data);
        $businessProfile->refresh();

        return $businessProfile;
    }
}
