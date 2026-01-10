<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\BussinessProfile\CreateBusinessProfileAction;
use App\Actions\BussinessProfile\UpdateBusinessProfileAction;
use App\Http\Requests\StoreBusinessProfileRequest;
use App\Http\Requests\UpdateBusinessProfileRequest;
use App\Models\BusinessProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class BusinessProfileController extends Controller
{
    public function index(): RedirectResponse
    {
        $profile = auth()->user()->business;

        if ($profile) {
            return to_route('business-profile.show', $profile);
        }

        return to_route('business-profile.create');
    }

    public function create(): View|RedirectResponse
    {
        if (auth()->user()->business) {
            return to_route('business-profile.index');
        }

        return view('dashboard.business-profile.create');
    }

    public function store(StoreBusinessProfileRequest $request, CreateBusinessProfileAction $action): RedirectResponse
    {
        $action->execute($request->user(), $request->validated());

        return to_route('business-profile.index')->with('success', 'Business profile created successfully.');
    }

    public function show(BusinessProfile $businessProfile): View
    {
        return view('dashboard.business-profile.show', ['businessProfile' => $businessProfile]);
    }

    public function edit(BusinessProfile $businessProfile): View
    {
        return view('dashboard.business-profile.edit', ['businessProfile' => $businessProfile]);
    }

    public function update(UpdateBusinessProfileRequest $request, BusinessProfile $businessProfile, UpdateBusinessProfileAction $action): RedirectResponse
    {

        $action->execute($businessProfile, $request->validated());

        return to_route('business-profile.show', $businessProfile)->with('success', 'Business profile updated successfully.');
    }
}
