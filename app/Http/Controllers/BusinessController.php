<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessWorkingTime;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Category;

class BusinessController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
{
    $user = auth()->user();
    $query = Business::query(); 

    if ($user->isAdmin() || $user->isUser()) {
       
    } else if ($user->isManager()) {
       
        $query->where('user_id', $user->id);
    } else {
        return $this->apiError('not authorized', 403);
    }

    
    if ($request->has('search')) {
        $search = $request->get('search');
        
        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }
    }

    if ($request->has('category_id')) {
        $categoryId = $request->get('category_id');
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }
    }
    
    $businesses = $query->get();

    return $this->apiResponse($businesses, 'Businesses fetched successfully', 200);
}

public function store(Request $request)
{
    $this->authorize('create', Business::class);

    $data = $request->validate([
        'category_id' => 'required|exists:categories,id',
        'name' => 'required|string|max:150',
        'description' => 'nullable|string',
        'phone' => 'required|string|max:20',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'image' => 'nullable|string'
    ]);

    $data['user_id'] = auth()->id();

    // Create the business
    $business = Business::create($data);

    // Define business hours for each day
    $businessHours = [
        ['day_of_week' => 'Sunday',    'open_time' => '08:00', 'close_time' => '16:00', 'is_closed' => false],
        ['day_of_week' => 'Monday',    'open_time' => '08:00', 'close_time' => '16:00', 'is_closed' => false],
        ['day_of_week' => 'Tuesday',   'open_time' => '08:00', 'close_time' => '16:00', 'is_closed' => false],
        ['day_of_week' => 'Wednesday', 'open_time' => '08:00', 'close_time' => '16:00', 'is_closed' => false],
        ['day_of_week' => 'Thursday',  'open_time' => '08:00', 'close_time' => '16:00', 'is_closed' => false],
        ['day_of_week' => 'Friday',    'open_time' => null,     'close_time' => null,     'is_closed' => true],
        ['day_of_week' => 'Saturday',  'open_time' => null,     'close_time' => null,     'is_closed' => true],
    ];

   
    foreach ($businessHours as $hours) {
        $hours['business_id'] = $business->id;
        BusinessWorkingTime::create($hours);
    }

    return $this->apiResponse($business, 'Business created successfully', 201);
}

    public function show(Business $business)
    {
        $this->authorize('view',$business);
        $businessData = $business->load([
            'category',
            'services',
            'employees',
           // 'workingTimes'
        ]);
    
        
        return $this->apiResponse($businessData, 'Business fetched successfully', 200);
    }

    public function update(Request $request, Business $business)
    {
        $this->authorize(
            'update',
            $business
        );

        $data=$request->validate([
            'category_id'=>'sometimes|exists:categories,id',
            'name'=>'sometimes|string|max:150',
            'description'=>'nullable|string',
            'phone'=>'sometimes|string|max:20',
            'latitude'=>'nullable|numeric',
            'longitude'=>'nullable|numeric',
            'image'=>'nullable|string'
        ]);

        $business->update($data);

        return $this->apiResponse($business, 'Business updated successfully', 200);
    }



    public function destroy(Business $business)
    {
        $this->authorize(
            'delete',
            $business
        );

        $business->delete();

        return $this->apiResponse(null,'Business deleted successfully', 200);
    }

    public function getBusinessesOnCategory(Category $category)
    {
        $businesses = $category->businesses;
        return $this->apiResponse($businesses, 'Businesses fetched successfully', 200);
    }

    public function topRated()
    {
        $businesses = Business::orderBy('avg_rating', 'desc')->take(5)->get();
        return $this->apiResponse($businesses, 'Businesses fetched successfully', 200);
    }

  /*  public function workingTimes(Business $business)
{
    return $business
        ->workingTimes()
        ->orderBy('day_of_week')
        ->get();
}

public function updateWorkingTimes(
    Request $request,
    Business $business
)
{
    $this->authorize('update',$business);

    $data=$request->validate([
        'days'=>'required|array|min:7|max:7',

        'days.*.day_of_week'=>
            'required|string',

        'days.*.open_hour'=>
            'nullable|date_format:H:i',

        'days.*.close_hour'=>
            'nullable|date_format:H:i',

        'days.*.is_closed'=>
            'required|boolean',
    ]);


    DB::transaction(function() use($data,$business){

        foreach($data['days'] as $day){

            $business
              ->workingTimes()
              ->updateOrCreate(
                  [
                     'day_of_week'=>$day['day_of_week']
                  ],
                  [
                    'open_hour'=>$day['open_hour'],
                    'close_hour'=>$day['close_hour'],
                    'is_closed'=>$day['is_closed']
                  ]
              );
        }

    });

    return response()->json([
        'message'=>'Working hours updated',
        'data'=>$business
            ->workingTimes()
            ->orderBy('day_of_week')
            ->get()
    ]);
}*/
}