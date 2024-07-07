<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Storage;
use App\Models\CompanyModel;

class UserController extends Controller
{

    protected $fileUploadService;

    // Inject the FileUploadService into the controller
    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    // Add user 
    public function createUser(Request $request)
    {


        $validateUser = Validator::make(
            $request->input(),
            [
                'name_en' => 'required|string',
                'name_ar' => 'required|string',
                'phone' => 'required|string|unique:users,u_phone|between:9,50',
                'password' => 'required|string|min:2|max:60',
                'role' => 'required|integer',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,svg',
            ],
            [
                'phone.between' => 'phone number must be wetween 9-50 digits',
                'phone.unique' => 'Phone number must be uniqe',
                'name_en.required' => 'English name is required.',
                'name_ar.required' => 'Arabic name is required.',
                'image.image' => 'only image is allowed',
                'image.mimes' => 'extention is not allowed for image',
            ],

        );

        if ($validateUser->fails()) {

            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'error' => $validateUser->errors()
            ], 401);
        }

        $user = new User();
        $user->u_name = $request->input('name_en');
        $user->u_name_ar = $request->input('name_ar');
        $user->u_phone = $request->input('phone');
        $user->u_role_id = $request->input('role');
        $user->password = bcrypt($request->input('password'));

        if ($request->hasFile('image')) {
            $file_name = $this->fileUploadService->uploadFile($request->file('image'), 'users/images');
            $user->u_image = $file_name;
        }

        $user->save();
        return response()->json([
            'message' => 'User Created Successfully',
            'user' => $user
        ], 201);
    }
    // get all users
    public function getUsers(Request $request)
    {
        $status = $request->input('status');
        $text = $request->only('text')['text'];
        $locale = app()->getLocale();

        if ($text != null || $text != '') {
           
            $users = User::where('u_status', $status)
                ->where($locale == "ar" ? 'u_name_ar' : 'u_name','like', $text . '%')
                ->select(
                    'u_id',
                    'u_phone',
                    'u_name_ar',
                    'u_name',
                    'u_role_id',
                    'u_status',
                    'created_at',
                    'updated_at',
                    'u_image'
                )->with(['companies' => function ($query) {
                    // $locale = app()->getLocale();
                    $query->select('c_id', 'c_owner_id','c_name_ar','c_name', 'c_phone')
                    ->with(['user' => function ($query) {
                        $query->select('u_id','u_name_ar','u_name');
                    }])->get();
                }])
                ->withCount('companies')
                ->paginate($request->input('per_page'));
        } else {
         
            $users = User::where('u_status', $status)->select(
                'u_id',
                'u_phone',
                'u_name_ar',
                'u_name',
                'u_role_id',
                'u_status',
                'created_at',
                'updated_at',
                'u_image'
            )->with(['companies' => function ($query) {
                $locale = app()->getLocale();
                $query->select('c_id', 'c_owner_id','c_name_ar','c_name', 'c_phone')
                ->with(['user' => function ($query) {
                    $query->select('u_id','u_name_ar','u_name');
                }])->get();
            }])
                ->withCount('companies')
                ->paginate($request->input('per_page'));
        }


        return response([
            'status' => true,
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
        $user = User::with('companies')->withCount('companies')->find($id);

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

        $uStatus = $user->u_status;
        if ($uStatus == '1') {
            $user->update([
                'u_status' => '0'
            ]);
            return response()->json([
                'status' => 0,
                'message' => 'User is Deactivated'
            ]);
        } else {
            $user->update([
                'u_status' => '1'
            ]);
            return response()->json([
                'status' => 1,
                'message' => 'User is Activated'
            ]);
        }
    }

    // update user
    public function updateUser(Request $request)
    {
        $user = User::findOrFail($request->id);
        $image = $user->u_image;
        //in order to make validation on updated phone

        $validateUser = Validator::make(
            $request->all(),
            [
                'name_en' => 'required|string',
                'name_ar' => 'required|string',
                'phone' => 'required|string|between:9,50|unique:users,u_phone,' . $request->id . ',u_id',
                'role' => 'required|integer',
                'image' => 'image|mimes:jpg,jpeg,png,svg',
            ],
            [
                'phone.between' => 'phone number must be wetween 9-50 digits',
                'phone.unique' => 'Phone number must be uniqe',
                'name_en.required' => 'name in english is required.',
                'name_ar.required' => 'name in arabic is required.',
                'image.image' => 'only image is allowed',
                'image.mimes' => 'extention is not allowed for image',
            ],
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 422);
        }

        $user->u_name = $request->input('name_en');
        $user->u_name_ar = $request->input('name_ar');
        $user->u_phone = $request->input('phone');
        $user->u_role_id = (int)$request->input('role');

     
        if ($request->hasFile('image')) {
            if ($user->u_image) {
                Storage::delete('public/users/images/' . $user->u_image);
            }

            $file_name = $this->fileUploadService->uploadFile($request->file('image'), 'users/images');
            $user->u_image = $file_name;
        }


        $user->save();
        return response()->json([
            'status' => true,
            'message' => __('user.userUpdatedSuccessfully'),
            'user' => $user,
        ], 200);
    }


    public function searchUser($name)
    {
        return User::where(app()->getLocale() == "ar" ? 'u_name_ar' : 'u_name', 'like', '%' . $name . '%')->get();
    }
}
