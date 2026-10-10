@component('mail::message')
# Your Motor Insurance Policy is Active

Dear {{ $client->first_name }},

Thank you for choosing MarsC Insurance. Your motor insurance policy has been issued and is now active.

**Policy Number:** {{ $policy->policy_number }}
**Period:** {{ $policy->start_date->format('d M Y') }} – {{ $policy->end_date->format('d M Y') }}

## Insured Vehicle

**{{ $vehicle->year_of_manufacture }} {{ $vehicle->make }} {{ $vehicle->model }}**
Registration: {{ $vehicle->registration_number }}

## Cover

**{{ $policy->policy_type->label() }}**

@if ($policy->addOns->isNotEmpty())
    **Optional Covers:**
    @foreach ($policy->addOns as $addOn)
        - {{ $addOn->name }} — KES {{ number_format($addOn->pivot->charged_amount, 2) }}
    @endforeach
@endif

## Premium Breakdown

| Item | Amount (KES) |
|------|--------------|
| Basic Premium | {{ number_format($policy->basic_premium, 2) }} |
| Training Levy | {{ number_format($policy->training_levy, 2) }} |
| PHCF | {{ number_format($policy->phcf, 2) }} |
| Stamp Duty | {{ number_format($policy->stamp_duty, 2) }} |
| **Gross Premium** | **{{ number_format($policy->gross_premium, 2) }}** |

@component('mail::button', ['url' => route('policies.show', $policy)])
View Policy
@endcomponent

If you have any questions, please contact your agent.

Drive safely,
**MarsC Insurance**
@endcomponent