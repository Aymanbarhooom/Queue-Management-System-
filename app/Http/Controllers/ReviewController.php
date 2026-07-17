<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    use ApiResponse;

    public function store(Request $request, Business $business): JsonResponse
    {
        $data = $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
        ]);

        $userId = auth()->id();

        $review = DB::transaction(function () use ($business, $data, $userId) {
            $review = Review::updateOrCreate(
                [
                    'user_id' => $userId,
                    'business_id' => $business->id,
                ],
                [
                    'rating' => $data['rating'],
                ]
            );

            $business->update([
                'avg_rating' => round((float) $business->reviews()->avg('rating'), 1),
            ]);

            return $review;
        });

        $message = $review->wasRecentlyCreated
            ? 'Review created successfully'
            : 'Review updated successfully';

        $status = $review->wasRecentlyCreated ? 201 : 200;

        return $this->apiResponse($review, $message, $status);
    }
}
