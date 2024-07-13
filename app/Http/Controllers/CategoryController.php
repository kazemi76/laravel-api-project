<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories=Category::paginate(2);

        return $this->successResponse([
            'categories'=>CategoryResource::collection($categories),
            'links'=>CategoryResource::collection($categories)->response()->getData()->links,
            'meta'=>CategoryResource::collection($categories)->response()->getData()->meta,
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator=Validator::make($request->all(),[
            'name'=>'required',
            'parent_id'=>'required|integer'
        ]);

        if($validator->fails()){
            return $this->errorResponse($validator->messages(),422);
        }

        DB::beginTransaction();
       $category= Category::create([
            'name'=>$request->name,
            'parent_id'=>$request->parent_id,
            'description'=>$request->description
        ]);
        DB::commit();


        return $this->successResponse(new CategoryResource($category),200);

    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {

        return $this->successResponse(new CategoryResource($category),200);


    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {

        $validator=Validator::make($request->all(),[
            'name'=>'required',
            'parent_id'=>'required'
        ]);

        if($validator->fails()){
            return $this->errorResponse($validator->messages(),422);
        }
        DB::beginTransaction();
        $category->update([
             'name'=>$request->name,
             'parent_id'=>$request->parent_id
         ]);
         DB::commit();
        return $this->successResponse(new CategoryResource($category),200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( Category $category)
    {
        $category->delete();
        return $this->successResponse(new CategoryResource($category),200);

    }

    public function parent( Category $category)
    {

        return $this->successResponse(new CategoryResource($category->load('parent')),201);


    }

    public function children( Category $category)
    {
        return $this->successResponse(new CategoryResource($category->load('children')),201);

    }
    public function products( Category $category)
    {
        return $this->successResponse(new CategoryResource($category->load('products')),201);

    }

}
