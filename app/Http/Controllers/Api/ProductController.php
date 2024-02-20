<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
       try {
        $perPage = $request->input('per_page', 30); // Default per page is 10
        $products = Product::inRandomOrder()->paginate($perPage);
        return response()->json([
            'code' => 200,
            'messages' => 'Data Fetched',
            'questionnaire' => $products,
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ], 200);
       } catch (\Throwable $th) {
        return response()->json(['error' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
       }
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            try {
                // Validate the incoming request data
                $validatedData = $request->validate([
                    'name' => 'required|string|max:255',
                    'description' => 'required|string',
                    'product_image' => 'required',
                ]);
            } catch (ValidationException $e) {
                // Handle validation errors
                return response()->json(['error' => $e->errors()], Response::HTTP_BAD_REQUEST);
            }
            $uploadedImage = $request->product_image;
            $imageName = time() . '_' . $uploadedImage->getClientOriginalName();
            $imageData = file_get_contents($uploadedImage->getRealPath());
            $fileExtension = $uploadedImage->getClientOriginalExtension();
            $imagePath = 'product/images/' . uniqid() . '.' . $fileExtension;
            Storage::put($imagePath, $imageData);
            $imageUrl = asset(str_replace('public', 'storage',  $imagePath));
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'user_id' =>  $user->id,
                'product_image' =>  $imageUrl
            ]);
            $response = [
                'code_key' => 'SUCCESS',
                'error' => FALSE,
                'success' => TRUE,
                'data' => [
                    $product 
                ],
            ];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

 
}
