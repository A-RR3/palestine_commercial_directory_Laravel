<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function uploadVideoTest(Request $request)
    {
        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'error',
            ]);
        }

        $post = new Post();
        $post->p_title = 'test';
        $post->p_content = 'test';
        $post->p_user_id = 2;

        // upload video

        if ($post->save()) {
            return response()->json([
                'status' => true,
                'message' => 'done',
                'post' => $post,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'error in adding',
        ]);
    }
}
