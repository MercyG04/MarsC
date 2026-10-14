@component('mail::message')
# Your Policy Has Been Endorsed

Dear {{ $client->first_name }},

Your policy **{{ $policy->policy_number }}** has been amended. The change is effective immediately.

**Endorsement Number:** {{ $endorsement->endorsement_number }}
**Effective Date:** {{ $endorsement->effective_date->format('d M Y') }}

## What Changed

{{ $endorsement->endorsement_type->label() }}

@if (!empty($endorsement->changes))
    @foreach ($endorsement->changes as $field => $change)
        **{{ ucfirst(str_replace('_', ' ', $field)) }}:** {{ is_array($change) ? json_encode($change) : $change }}
    @endforeach
@endif

## Financial Adjustment

@if ($endorsement->additional_premium > 0)
    - **Additional Premium:** KES {{ number_format($endorsement->additional_premium, 2) }}
@elseif ($endorsement->refund_premium > 0)
    - **Refund:** KES {{ number_format($endorsement->refund_premium, 2) }}
@else
    - No financial change.
@endif

## Reason

{{ $endorsement->reason }}

@component('mail::button', ['url' => route('policies.show', $policy)])
View Policy
@endcomponent

Thank you,
**MarsC Insurance**
@endcomponent