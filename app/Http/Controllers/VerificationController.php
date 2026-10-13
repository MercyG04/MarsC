<?php

namespace App\Http\Controllers;

use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(
        protected VerificationService $verification,
    ) {}

    /**
     * AJAX endpoint — verify a national ID via IPRS.
     */
    public function verifyIprs(Request $request): JsonResponse
    {
        $request->validate([
            'national_id' => ['required', 'string', 'max:20'],
        ]);

        $result = $this->verification->verifyIprs($request->input('national_id'));

        if (! $result) {
            return response()->json([
                'ok'    => false,
                'error' => 'National ID not found in IPRS.',
            ], 404);
        }

        return response()->json([
            'ok'   => true,
            'data' => $result,
        ]);
    }

    /**
     * AJAX endpoint — verify a DL number via NTSA.
     */
    public function verifyDriver(Request $request): JsonResponse
    {
        $request->validate([
            'dl_number' => ['required', 'string', 'max:30'],
        ]);

        $result = $this->verification->verifyDriver($request->input('dl_number'));

        if (! $result) {
            return response()->json([
                'ok'    => false,
                'error' => 'Driving licence not found in NTSA records.',
            ], 404);
        }

        return response()->json([
            'ok'   => true,
            'data' => $result,
        ]);
    }
    public function verifyVehicle(Request $request): JsonResponse
{
    $request->validate([
        'registration_number' => ['required', 'string', 'max:20'],
    ]);

    $result = $this->verification->verifyVehicle($request->input('registration_number'));

    if (! $result) {
        return response()->json([
            'ok'    => false,
            'error' => 'Vehicle not found in NTSA TIMS.',
        ], 404);
    }

    return response()->json([
        'ok'   => true,
        'data' => $result,
    ]);
    }
}