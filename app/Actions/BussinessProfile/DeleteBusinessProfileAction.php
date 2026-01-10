<?php

declare(strict_types=1);

namespace App\Actions\BusinessProfile;

use App\Models\BusinessProfile;
use Illuminate\Support\Facades\DB;

final class DeleteBusinessProfileAction
{
    public function handle(BusinessProfile $businessProfile): void
    {
        DB::transaction(function () use ($businessProfile): void {

            $businessProfile->clients()->delete();
            $businessProfile->invoices()->delete();

            // $businessProfile->messageTemplates()->delete();
        });
    }
}
