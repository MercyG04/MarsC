<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use App\Services\ClientKycService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(
        protected ClientKycService $kyc,
    ) {}

    public function index(): View
    {
        $clients = Client::withCount('vehicles')->latest()->paginate(15);
        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = $this->kyc->onboard($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('success', "Client {$client->first_name} {$client->last_name} onboarded successfully.");
    }

    public function show(Client $client): View
    {
        $client->load('vehicles');
        return view('clients.show', compact('client'));
    }
}