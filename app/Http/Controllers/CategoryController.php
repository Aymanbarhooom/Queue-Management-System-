<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
class CategoryController extends Controller
{
    use ApiResponse;
    public function index()
    {
        $categories = Category::query()
            ->latest()
            ->paginate(15);
        return $this->apiResponse($categories, 'Categories fetched successfully', 200);
    }


    public function store(Request $request)
    {
        $this->authorize('create', Category::class);

        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('category_images', 'public');
            $data['image'] = $path;
        }

        $category = Category::create($data);

        return $this->apiResponse($category, 'Category created successfully', 201);
    }


    public function show(Category $category)
    {
        return $this->apiResponse($category->load('businesses'), 'Category fetched successfully', 200);
    }


    public function update(Request $request, Category $category)
    {
        $this->authorize('update',$category);

        $data = $request->validate([
            'name'=>'sometimes|string|max:100|unique:categories,name,'.$category->id,
            'description'=>'nullable|string',
        ]);
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('category_images', 'public');
            $validated['image'] = $path;
        }

        $category->update($data);

        return $this->apiResponse($category, 'Category updated successfully', 200);
    }


    public function destroy(Category $category)
    {
        $this->authorize('delete',$category);

        $category->delete();

        return $this->apiResponse(null, 'Category deleted successfully', 200);
    }

    //Get businesses on category
    public function businesses(Category $category)
    {
        $businesses = $category->businesses()
            ->with([
                'category',
                'services',
                'manager',
            ])
            ->latest()
            ->paginate(10);
        return $this->apiResponse($businesses, 'Businesses fetched successfully', 200);
    }
}