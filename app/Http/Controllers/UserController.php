<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    // Add user 
    public function createUser(Request $request)
    {

        $validateUser = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'name_ar' => 'required|string',
                'phone' => 'required|string|unique:users,u_phone|between:9,50',
                'password' => 'required|string|min:2|max:60',
                'role' => 'required|integer',
                'image' => 'image|mimes:jpg,jpeg,png,svg',
            ],[
                'phone.between' => 'phone number must be wetween 9-50 digits',
                'phone.unique' => 'Phone number must be uniqe',
                'name.required' => 'English name is required.',
                'name_ar.required' => 'Arabic name is required.',
                'image.image' => 'only image is allowed',
                'image.mimes' => 'extention is not allowed for image',
            ],
        
        );

        if ($request->hasFile('image')) {
            $fileName = $this->saveImage($request, 'uploads/images');
        }

        if ($validateUser->fails()) {
      
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'error' => $validateUser->errors()
            ], 401);
        }
   
        $user = User::create(
             [
                    'u_name' => $request->name,
                    'u_name_ar' => $request->name_ar,
                    'u_phone' => $request->phone,
                    'password' => bcrypt($request->password),
                    'u_role_id' => $request->role,
                    'image' => $fileName
                ]
        );

        return response()->json([
            'message' => 'User Created',
            'user'=> $user
        ], 201);
    }

    // get all users
    public function getUsers(Request $request)
    {
    
        $users = User::where('u_status', '1', 'u_name_ar')->select(
            'u_id','u_phone',app()->getLocale() =="ar"? 'u_name_ar':'u_name','u_role_id','u_status','created_at','updated_at'
        )->with(['companies' => function($query) {
            $locale= app()->getLocale();
            $query->select('c_id', 'c_owner_id',$locale =="ar"? 'c_name_ar':'c_name','c_phone');
            
        }])
        ->withCount('companies')
        ->paginate($request->input('per_page'));
        // ->get();

    return response([
        'status'=> 200,
        'users' => $users->items(),
        'pagination' => [
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage()
        ]
    ], 200);
    }

      // get single user
      public function getSingleUser($id)
      {
        $user =  User::find($id)->with('companies')->withCount('companies')->get();
        if (!$user) {
            return response()->json(['message' => 'user not found'], 404);
        }

          return response([
              'user' => $user
          ], 200);
      }

    public function deactivateUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'user not found'], 404);
        }

        $user->update([
'u_status' => 0
        ]);
        return response()->json(['message' => 'User is Deactivated']);
    }

    // update user
    public function updateUser(Request $request, $id)
    {

        $user = User::find($id);

        if (!$user) {
            return response([
                'message' => 'user Not Found'
            ], 403);
        }

        $validateUser = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'phone' => 'required|string|unique:users,u_phone|between:9,50',
                'password' => 'required|string|min:2|max:60',
                'role' => 'required|integer',
                'image' => 'image|mimes:jpg,jpeg,png,svg',
            ],[
                'phone.between' => 'phone number must be wetween 9-50 digits',
                'phone.unique' => 'Phone number must be uniqe',
                'name.required' => 'name is required.',
                'image.image' => 'only image is allowed',
                'image.mimes' => 'extention is not allowed for image',
            ],
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        // if ($request->hasFile('video')) {
        //     $file_name = $this->fileUploadService->uploadVideo($request->file('video'), 'posts/videos');
        //     $post->p_video = $file_name;
        // }
        if ($request->hasFile('image')) {
            $fileName = $this->saveImage($request, 'uploads/images');
        }

        $user->update([
            'u_name' => $request->name,
            'u_phone' => $request->phone,
            'u_role_id' => $request->role,
        ]);

        return response([
            'status' => true,
            'message' => 'User updated.',
            'user' => $user,
        ], 200);

    
    }

    
    public function searchUser($name)
    {
        return User::where(app()->getLocale() =="ar"? 'u_name_ar':'u_name', 'like', '%'.$name.'%')->get();
    }
}
