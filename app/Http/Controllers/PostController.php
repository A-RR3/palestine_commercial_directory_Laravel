<?php

namespace App\Http\Controllers;

use App\Models\LikeModel;
use App\Models\Post;
use App\Models\PostModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    // public function uploadVideoTest(Request $request)
    // {
    //     $validator = Validator::make($request->all(), []);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'error',
    //         ]);
    //     }

    //     $post = new PostModel();
    //     $post->p_title = 'test';
    //     $post->p_content = 'test';
    //     $post->p_user_id = 2;

    //     // upload video

    //     if ($post->save()) {
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'done',
    //             'post' => $post,
    //         ]);
    //     }

    //     return response()->json([
    //         'status' => false,
    //         'message' => 'error in adding',
    //     ]);
    // }

    public function index()
    {
        // return PostModel::all();
        $posts = PostModel::orderBy('created_at', 'desc')
        ->with('user:u_id,u_name,u_image')
        ->withCount('likes')
        ->paginate(2);

    return response([
        'current_page' => $posts->currentPage(),
        'last_page' => $posts->lastPage(),
        'posts_count' => $posts->count(),
        'posts' => $posts->items(),

    ], 200);
    }

      // get single post
      public function getSinglePOst($id)
      {
        $post =  PostModel::where('id', '$id')->withCount('likes')->get();
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        print($post);
          return response([
              'post' => $post->items()
  
          ], 200);
      }


    public function store(Request $request)
    {
        $post = PostModel::create($request->all());
        return response()->json($post, 201);
    }

    public function update(Request $request, $id)
    {
        $post = PostModel::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        $post->update($request->all());
        return $post;
    }

    public function destroy($id)
    {
        $post = PostModel::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        $post->delete();
        return response()->json(['message' => 'Post deleted']);
    }

    public function like(Request  $request, $id)
    {
        $post = PostModel::find( $id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        $like = LikeModel::create([
            'p_id' => $id,
            'u_id' => $request->user_id
        ]);

        return response()->json(['message' => 'Liked'], 200);
    }

    public function unlike(Request $request, $id)
    {
        $post = PostModel::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
         // Assuming user_id is passed in the request body
         $like = LikeModel::where('p_id', $id)->where('u_id', $request->user_id)->first();
         if (!$like) {
             return response()->json(['message' => 'This post is not liked by user'], 404);
         }
 
         $like->delete();
         return response()->json(['message' => 'disliked'],200);
    }


}


