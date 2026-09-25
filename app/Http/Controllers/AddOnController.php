<?php

namespace App\Http\Controllers;
use App\Enums\AddOnRateType;
use App\Http\Requests\StoreAddOnRequest;
use App\Http\Requests\UpdateAddOnRequest;
use App\Models\AddOn;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use Illuminate\Http\Request;

class AddOnController extends Controller
{
    public function index(): View
    {
        $addOns = AddOn::query()
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->paginate(20);

        return view('add-ons.index', compact('addOns'));
    }

    public function create(): View
    {
        return view('add-ons.create', [
            'rateTypes' => AddOnRateType::cases(),
        ]);
    }

    public function store(StoreAddOnRequest $request): RedirectResponse
    {
        $addOn = AddOn::create($request->validated());

        return redirect()
            ->route('add-ons.index')
            ->with('success', "Add-on '{$addOn->name}' created.");
    }

    public function edit(AddOn $addOn): View
    {
        return view('add-ons.edit', [
            'addOn'     => $addOn,
            'rateTypes' => AddOnRateType::cases(),
        ]);
    }

    public function update(UpdateAddOnRequest $request, AddOn $addOn): RedirectResponse
    {
        $addOn->update($request->validated());

        return redirect()
            ->route('add-ons.index')
            ->with('success', "Add-on '{$addOn->name}' updated.");
    }
    public function destroy(AddOn $addOn): RedirectResponse
    {
        // Soft-disable, don't hard-delete — policies reference this
        $addOn->update(['is_active' => false]);

        return redirect()
            ->route('add-ons.index')
            ->with('success', "Add-on '{$addOn->name}' deactivated.");
    }
}
