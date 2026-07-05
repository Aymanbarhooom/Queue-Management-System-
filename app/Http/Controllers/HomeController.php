<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use App\Traits\ApiResponse;

class HomeController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $categories = Category::all();

        $topRatedBusinesses = Business::query()
            ->orderByDesc('avg_rating')
            ->take(3)
            ->get();

        return $this->apiResponse([
            'categories' => $categories,
            'businesses' => $topRatedBusinesses,
        ], 'Home data fetched successfully', 200);
    }
}
