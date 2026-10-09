<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Support\Facades\Auth; 
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class QuotationController extends Controller
{
    public function index(Request $request): View
{
    $user = $request->user();

    $quotes = Quote::query()
        ->when(! $user->isAdmin(), fn ($q) => $q->where('created_by', $user->id))
        ->with(['client', 'createdBy'])
        ->when($request->string('q')->toString(), function ($q, $search) {
            // search logic
        })
        ->latest()
        ->paginate(20)
        ->withQueryString();

    return view('quotes.index', compact('quotes'));
    }
    public function show(Quote $quote): View
    {
    abort_if(
        ! Auth::user()->isAdmin() && $quote->created_by !== Auth::id(),
        403,
        'You can only view quotes you created.'
    );

    $quote->load(['client', 'createdBy', 'policy']);

    return view('quotes.show', compact('quote'));
    }
}
