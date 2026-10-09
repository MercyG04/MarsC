@component('mail::message')
# Your Insurance Quote

Dear {{ $quote->prospect_name }},

Thank you for your interest. Here is the quote you requested.

**Quote Number:** {{ $quote->quote_number }}
**Valid Until:** {{ $quote->valid_until->format('d M Y') }}

## Vehicle

{{ $quote->vehicle_details['make'] }} {{ $quote->vehicle_details['model'] }}
({{ $quote->vehicle_details['year_of_manufacture'] }})
@if (!empty($quote->vehicle_details['registration_number']))
    · {{ $quote->vehicle_details['registration_number'] }}
@endif

## Cover

{{ $quote->policy_type->label() }}

## Premium Breakdown

| Item | Amount (KES) |
|------|--------------|
| Basic Premium | {{ number_format($breakdown['basic_premium'], 2) }} |
| Training Levy | {{ number_format($breakdown['training_levy'], 2) }} |
| PHCF | {{ number_format($breakdown['phcf'], 2) }} |
| Stamp Duty | {{ number_format($breakdown['stamp_duty'], 2) }} |
@foreach ($breakdown['add_ons'] as $addOn)
    | {{ $addOn['name'] }} | {{ number_format($addOn['charged_amount'], 2) }} |
@endforeach
| **Gross Premium** | **{{ number_format($breakdown['gross_premium'], 2) }}** |

To proceed, contact your agent or visit our office.

Thanks,
MarsC Insurance
@endcomponent