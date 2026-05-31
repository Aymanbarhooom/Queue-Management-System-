<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Review;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate($request, [
            'rating' => 'required|numeric|min:1|max:5',
        ]);
        $review = Review::create([
            'user_id' => auth()->user()->id,
            'business_id' => $request->business_id,
            'rating' => $data['rating'],
        ]);
        $business = Business::find($request->business_id);
        $reviews = $business->reviews;
        $avg = $reviews->avg('rating');
        $business->avg_rating = $avg;
        $business->save();

        return $this->apiResponse($review, 'Review created successfully', 201);
    }
}
