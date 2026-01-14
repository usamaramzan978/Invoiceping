<?php

declare(strict_types=1);

namespace App\Actions\BussinessProfile;

use App\Models\BusinessProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class UpdateBusinessProfileAction
{
    public function execute(BusinessProfile $businessProfile, array $data): BusinessProfile
    {
        // Handle image upload if present
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Delete old image if exists
            if ($businessProfile->image && Storage::disk('public')->exists($businessProfile->image)) {
                Storage::disk('public')->delete($businessProfile->image);
            }

            // Store new image
            $imagePath = $data['image']->store('business-logos', 'public');
            $data['image'] = $imagePath;
        } else {
            // Remove image from data if not provided (keep existing)
            unset($data['image']);
        }

        $businessProfile->update($data);

        return $businessProfile;
    }
}
