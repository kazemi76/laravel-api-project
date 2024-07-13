<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::paginate(2);

        return $this->successResponse([
            'products' => ProductResource::collection($products->load('images')),
            'links' => ProductResource::collection($products)->response()->getData()->links,
            'meta' => ProductResource::collection($products)->response()->getData()->meta,
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'brand_id' => 'required|integer',
            'category_id' => 'required|integer',
            'primary_image' => 'required|image',
            'description' => 'required|string',
            'price' => 'required|integer',
            'quantity' => 'required|integer',
            'delivery_amount' => 'required',
            'images.*' => 'required|image',
        ]);


        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }
        $primaryImageName = Carbon::now()->microsecond . '.' . $request->primary_image->extension();
        $request->primary_image->storeAs('images/products', $primaryImageName, 'public');
        if ($request->has('images')) {
            $fileNameImages = [];
            foreach ($request->images as $image) {
                $fileNameImage = Carbon::now()->microsecond . '.' . $image->extension();
                $image->storeAs('images/products', $fileNameImage, 'public');

                array_push($fileNameImages, $fileNameImage);
            }
        }
        DB::beginTransaction();

        $product = Product::create([
            'name' => $request->name,
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'primary_image' => $primaryImageName,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'delivery_amount' => $request->delivery_amount,
        ]);
        if ($request->has('images')) {

            foreach ($fileNameImages as $fileNameImage) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $fileNameImage
                ]);
            }
        }
        DB::commit();
        return $this->successResponse(new ProductResource($product), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return $this->successResponse(new ProductResource($product->load('images')), 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'brand_id' => 'required|integer',
            'category_id' => 'required|integer',
            'primary_image' => 'nullable|image',
            'description' => 'required|string',
            'price' => 'nullable|integer',
            'quantity' => 'required|integer',
            'delivery_amount' => 'required',
            'images.*' => 'nullable|image',
        ]);


        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        if ($request->has('primary_image')) {
            File::delete(env('PRODUCT_IMAGES_UPLOAD_PATH').$product->primary_image);
            $primaryImageName = Carbon::now()->microsecond . '.' . $request->primary_image->extension();
            $request->primary_image->storeAs('images/products', $primaryImageName, 'public');
        }

        if ($request->has('images')) {
            $fileNameImages = [];
            foreach ($request->images as $image) {
                $fileNameImage = Carbon::now()->microsecond . '.' . $image->extension();
                $image->storeAs('images/products', $fileNameImage, 'public');

                array_push($fileNameImages, $fileNameImage);
            }
        }
        DB::beginTransaction();

        $product->update([
            'name' => $request->name,
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'primary_image' => $request->has('primary_image') ? $primaryImageName : $product->primary_image,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'delivery_amount' => $request->delivery_amount,
        ]);
        if ($request->has('images')) {

            foreach ($product->images as $productImage) {

                File::delete(env('PRODUCT_IMAGES_UPLOAD_PATH').$productImage->image);

                $productImage->delete();
            }

            foreach ($fileNameImages as $fileNameImage) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $fileNameImage
                ]);
            }
        }
        DB::commit();
        return $this->successResponse(new ProductResource($product), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        $product->delete();
         DB::commit();
        return $this->successResponse(new ProductResource($product),200);
    }
}
