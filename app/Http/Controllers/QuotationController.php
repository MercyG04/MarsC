<?php

namespace App\Http\Controllers;

use App\Enums\PolicyType;
use App\Enums\QuotationStatus;
use App\Http\Requests\StoreQuotationRequest;
use App\Models\AddOn;
use App\Models\Quote;
use Illuminate\Support\Facades\Auth;
use App\Services\QuoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;


class QuotationController extends Controller
{   public function __construct(
        protected QuoteService $quotes,
    ) {}
    

    public function index(Request $request): View
    {
    $user = $request->user();

        $search = $request->string('q')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $quotations = Quote::query()
            ->with(['client', 'createdBy'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('created_by', $user->id))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('quote_number', 'like', "%{$search}%")
                      ->orWhere('prospect_name', 'like', "%{$search}%")
                      ->orWhere('prospect_phone', 'like', "%{$search}%")
                      ->orWhere('prospect_email', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('quotations.index', [
            'quotations' => $quotations,
            'search'     => $search,
            'status'     => $status,
            'statuses'   => QuotationStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('quotations.create', [
            'types'  => PolicyType::cases(),
            'addOns' => AddOn::where('is_active', true)->orderBy('name')->get(),
        ]);
    }


    public function store(StoreQuotationRequest $request): RedirectResponse
    {
        $quote = $this->quotes->create(
            $request->validated() + ['created_by' => Auth::id()]
        );

        return redirect()
            ->route('quotations.show', $quote)
            ->with('success', "Quote {$quote->quote_number} created. You can now mark it as sent.");
    }
    public function show(Quotation $quotation): View
    {
        abort_if(
            ! auth()->user()->isAdmin() && $quotation->created_by !== auth()->id(),
            403,
            'You can only view quotes you created.'
        );

        $quotation->load(['client', 'createdBy', 'policy']);

        return view('quotations.show', compact('quotation'));
    }

    public function markSent(Quotation $quotation): RedirectResponse
    {
        abort_if(
            ! auth()->user()->isAdmin() && $quotation->created_by !== auth()->id(),
            403
        );

        try {
            $this->quotes->markAsSent($quotation);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', "Quote {$quotation->quote_number} marked as sent. Email dispatched.");
    }

    public function markDeclined(Quotation $quotation): RedirectResponse
    {
        abort_if(
            ! auth()->user()->isAdmin() && $quotation->created_by !== auth()->id(),
            403
        );

        try {
            $this->quotes->markAsDeclined($quotation);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', "Quote {$quotation->quote_number} marked as declined.");
    }




}