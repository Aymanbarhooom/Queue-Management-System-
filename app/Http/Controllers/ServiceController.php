<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Business;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class ServiceController extends Controller
{
    use ApiResponse;
    public function index()
    {
        $query = Service::query();
        return $this->apiResponse($query->get(), 'Services fetched successfully', 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'business_id'=>'required|exists:businesses,id',
            'name'=>'required|string|max:150',
            'description'=>'nullable|string',
            'price'=>'required|numeric|min:0',
            'base_duration'=>'required|integer|min:1'
        ]);

        $business = Business::findOrFail($data['business_id']);

        $this->authorize('create',[Service::class,$business]);

        $service = Service::create($data);

        return $this->apiResponse($service, 'Service created successfully', 201);
    }

    
    public function show(Service $service)
    {
        return $service->load('queues');
    }

    
    public function update(Request $request, Service $service)
    {
        $this->authorize('update',$service);

        $data = $request->validate([
            'name'=>'sometimes|string|max:150',
            'description'=>'nullable|string',
            'price'=>'sometimes|numeric|min:0',
            'base_duration'=>'sometimes|integer|min:1'
        ]);

        $service->update($data);

       return $this->apiResponse($service, 'Service updated successfully', 200);
    }

    
    public function destroy(Service $service, Request $request)
    {
        $this->authorize('delete',$service);
        $service->delete();
        return $this->apiResponse(null, 'Service deleted successfully', 200);
    }
}