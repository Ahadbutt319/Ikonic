<?php

namespace App\Http\Controllers\Api;

use App\Models\Feedback;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreFeedbackRequest;
use App\Http\Requests\UpdateFeedbackRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
       try {
        $perPage = $request->input('per_page', 30); // Default per page is 10
        $feedback = Feedback::inRandomOrder()->paginate($perPage);
        
        return response()->json([
            'code' => 200,
            'messages' => 'Data Fetched',
            'questionnaire' => $feedback,
            'pagination' => [
                'total' => $feedback->total(),
                'per_page' => $feedback->perPage(),
                'current_page' => $feedback->currentPage(),
                'last_page' => $feedback->lastPage(),
                'from' => $feedback->firstItem(),
                'to' => $feedback->lastItem(),
            ],
        ], 200);
       } catch (\Throwable $th) {
        //throw $th;
       }
    }

    
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            try {
                $validatedData = $request->validate([
                    'title' => 'required|string|max:255',
                    'description' => 'required|string',
                    'category' => 'required',
                ]);
            } catch (ValidationException $e) {
                // Handle validation errors
                return response()->json(['error' => $e->errors()], Response::HTTP_BAD_REQUEST);
            }
            $data = Feedback::create([
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'product_id' => $request->product_id,
                'user_id' =>  $user->id,
            ]);
            $response = [
                'code_key' => 'SUCCESS',
                'error' => FALSE,
                'success' => TRUE,
                'data' => [
                    $data 
                ],
            ];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
