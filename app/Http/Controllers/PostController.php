<?php

namespace App\Http\Controllers;

use App\Models\PostModel;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    protected $fileUploadService;

    // Inject the FileUploadService into the controller
    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function uploadVideoTest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video' => 'required|mimes:mp4,mov,avi,flv|max:204800',
            'title' => 'required',
            'content' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $post = new PostModel();
        $post->p_title = $request->input('title');
        $post->p_content = $request->input('content');
        $post->p_user_id = $request->input('user_id');

        if ($request->hasFile('video')) {
            $file_name = $this->fileUploadService->uploadVideo($request->file('video'), 'posts/videos');
            $post->p_video = $file_name;
        }

        // upload video

        if ($post->save()) {
            return response()->json([
                'status' => true,
                'message' => 'posed added successfully',
                'post' => $post,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'error in adding post',
        ]);
    }
}
