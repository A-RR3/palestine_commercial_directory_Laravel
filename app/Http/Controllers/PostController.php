<?php

namespace App\Http\Controllers;
use App\Models\LikeModel;
use App\Models\PostModel;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    protected $fileUploadService;

    // Inject the FileUploadService into the controller
    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    //posts that appear to logged user
    public function getPostsWithLikeStatus(Request $request, $id)
    {
        // $userId = $request->input('u_id');
        $userId = $id;


        $posts = PostModel::orderBy('created_at', 'desc')->with('likes:l_post_id,l_user_id')
            ->with('user:u_id,u_name,u_image,u_name_ar')
            ->withCount('likes')
            ->paginate($request->input('per_page'));

        foreach ($posts as $key) {
            $key->is_liked = $key->likes->contains('l_user_id', $userId);
        }

        return response()->json([
            'status' => true,
            'posts' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage()
            ],
            'posts_count' => null,
        ], 200);
    }

    //posts that appear to public user

    public function index(Request $request)
    {
        $perPage = $request->input('per_page');
        try {

            $posts = PostModel::orderBy('created_at', 'desc')
                ->with('user:u_id,u_name,u_image,u_name_ar')
                ->withCount('likes')
                ->paginate($perPage);
            
            $posts->map(function ($post) {
                $post->is_liked = false;
            });
            return response([
                'status' => true,
                'posts' => $posts->items(),
                'pagination' => [
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'per_page' => $posts->perPage()
                ],
                'posts_count' => $posts->count(),
            ], 200);
            // }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' =>
                __('auth.internal_server_error'),
                // $th->getMessage()

            ], 500);
        }

        // return response()->json([
        //     'status' => false,
        //     'message' =>
        //     __('auth.internal_server_error')


        // ], 500);
    }

    // get single post
    public function getSinglePost($id)
    {
        $post =  PostModel::where('p_id', '$id')->withCount('likes')->get();
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
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
            $file_name = $this->fileUploadService->uploadFile($request->file('video'), 'posts/videos');
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

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'p_title' => 'nullable|string|max:255',
                'p_content' => 'nullable|string|max:350',
                'p_content_ar' => 'nullable|string|max:350',
                'p_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
                'p_video' => 'nullable|mimetypes:video/mpeg,video/quicktime,video/mp4|max:10240',
            ],
            [
                'p_content.required' => 'content in english is required',
                'p_content_ar.required' => 'content in arabic is required',
                'p_image.max' => 'please select an image with less size',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $image = null;
        $video = null;

        //in front end send the post_type based on video or image
        if ($request->hasFile('p_video')) {
            $file_name = $this->fileUploadService->uploadFile($request->file('p_video'), 'posts/videos');
            $video = $file_name;
        }

        if ($request->hasFile('p_image')) {
            $file_name = $this->fileUploadService->uploadFile($request->file('p_image'), 'posts/images');
            $image = $file_name;
        }

        $user= User::Where('u_id',(int)$request->input('p_user_id'))->select('u_id', 'u_name', 'u_image', 'u_name_ar')
        ->first();
// return $user;
        $post = PostModel::create(
            [
                'p_title' => $request->input('p_title'),
                'p_content' => $request->input('p_content'),
                'p_content_ar' => $request->input('p_content_ar'),
                'p_user_id' => (int)$request->input('p_user_id'),
                'p_type' => $request->input('p_type'),
                'p_image' => $image,
                'p_video' => $video,
            ]
        );
        $post->is_liked  =false;
        $post->user=$user;

        if ($post) {
            return response()->json(
                [
                    'status' => true,
                    'message' =>  __('posts.postCreatedSuccessfully'),
                    'post' => $post,
                ],
                201
            );
        }

        return response()->json([
            'status' => false,
            'message' =>
            __('auth.internal_server_error')


        ], 500);
    }

    public function update(Request $request, $id)
    {
        $post = PostModel::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        try {
            $validator = Validator::make($request->all(), [
                'p_title' => 'nullable|string|max:255',
                'p_content' => 'required|string|max:350',
                'p_content_ar' => 'required|string|max:350',
                'p_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
                'p_video' => 'nullable|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4|max:10240',
            ], [
                'p_content.required' => 'content in english is required',
                'p_content_ar.required' => 'content in arabic is required'

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors(),
                ], 422);
            }

            // $post = new PostModel();
            $post->p_title = $request->input('p_title');
            $post->p_content = $request->input('p_content');
            $post->p_user_id = $request->input('p_user_id');
            if ($request->p_content_ar) {
                $post->p_content_ar = $request->input('p_content_ar');
            }

            if ($request->hasFile('p_video')) {
                $post->p_type = 'video';
                // Delete the old video if exists
                if ($post->p_video) {
                    Storage::delete('public/posts/videos/' . $post->p_video);
                }
                // Store the new video
                $file_name = $this->fileUploadService->uploadFile($request->file('p_video'), 'posts/videos');
                $post->p_video = $file_name;
            }

            if ($request->hasFile('p_image')) {
                $post->p_type = 'image';
                if ($post->p_video) {
                    Storage::delete('public/posts/images/' . $post->p_image);
                }
                $file_name = $this->fileUploadService->uploadFile($request->file('p_image'), 'posts/images');
                $post->p_image = $file_name;
            }
            $post->save();
            return response()->json([
                'status' => true,
                'message' => __('posts.postUpdatedSuccessfully'),
                'post' => $post,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' =>
                __('auth.internal_server_error'),
                // $th->getMessage()

            ], 500);
        }
    }

    public function destroy($id)
    {
        $post = PostModel::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
            // if ($post->save()) {
            //     return response()->json([
            //         'status' => true,
            //         'message' => 'posed added successfully',
            //         'post' => $post,
            //     ]);
            // }
        }

        $post->delete();
        return response()->json(['message' => 'Post deleted', 200]);
    }



    public function toggleLike(Request  $request)
    {
        $id = $request->p_id;
        $post = PostModel::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $like = LikeModel::where(
            [
                ['l_post_id', $id],
                ['l_user_id', $request->u_id]
            ]
        )->first();
        if (!$like) {
            $like = LikeModel::create([
                'l_post_id' => $id,
                'l_user_id' => $request->u_id,
            ]);
            return response()->json(['message' => 'Liked'], 200);
        } else {
            $like->delete();
            return response()->json(['message' => 'disliked'], 200);
        }

        return response()->json(['error' => 'server error'], 404);
    }
}
