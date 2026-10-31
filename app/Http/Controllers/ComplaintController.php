<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintRequest;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;
use Throwable;

class ComplaintController extends Controller
{
    public function store(StoreComplaintRequest $request): JsonResponse
    {
        try {
            $complaint = Complaint::create($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Keluhan gagal dikirim. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'message' => 'Keluhan Anda berhasil dikirim. Tim kami akan segera menindaklanjuti.',
            'complaint' => [
                'id' => $complaint->id,
                'status' => $complaint->status,
            ],
        ], 201);
    }
}
