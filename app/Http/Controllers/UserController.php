<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\UploadImageRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserStatusResource;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BankService;
use App\Services\BaseService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $user;
    protected $bank;

    public function __construct(UserService $user, BankService $bank)
    {
        $this->user = $user;
        $this->bank = $bank;
    }

    public function show($userId)
    {
        try {
            return response()->json(new UserResource(User::findOrFail($userId)));
        } catch (\Throwable $th) {
            return "Not found";
        }
    }

    /**
     * @QAparam page nullable [0-9]+
     * @QAparam per_page nullable [0-9]+
     * @QAparam order_by string nullable 'name'|'email'|'identity_number'|'dob'|'gender'|'avatar'|'created_at'|'is_verify'|'email_verified_at'|'role'|'identity_image_front'|'identity_image_back'|'issue_place'|'issue_date'|'valid_date'
     * @QAparam sort_by string nullable 'desc'|'asc'
     * @QAparam email string nullable
     * @QAparam name string nullable
     */
    public function index(Request $request)
    {
        try {
            $perPage = 2;
            $orderBy = 'created_at'; // $fields
            $sortBy = 'desc'; // $orders
            $orders = ['desc', 'asc'];
            $fields = ['name', 'email', 'identity_number', 'dob', 'gender', 'avatar', 'created_at', 'is_verify', 'email_verified_at', 'role', 'identity_image_front', 'identity_image_back', 'issue_place', 'issue_date', 'valid_date'];

            if ($request->has('per_page') && is_numeric($request->input('per_page'))) {
                $perPage = $request->input('per_page');
            }

            if ($request->has('order_by') && in_array($request->input('order_by'), $fields)) {
                $orderBy = $request->input('order_by');
            }

            if ($request->has('sort_by') && in_array($request->input('sort_by'), $orders)) {
                $sortBy = $request->input('sort_by');
            }

            $query = User::orderBy($orderBy, $sortBy);

            if ($request->has('name') && $request->input('name')) {
                $query = $query->whereRaw("name ILIKE '%" . $request->input('name') . "%' ");
            }

            if ($request->has('email') && $request->input('email')) {
                $query = $query->whereRaw("email ILIKE '%" . $request->input('email') . "%' ");
            }

            $data = $query->paginate($perPage);
            $data->data = new UserCollection($data);

            return response()->json($data);
        } catch (\Throwable $th) {
            return ($th);
        }
    }

    /**
     * @QAParam ids array
     */
    public function remove(DeleteUserRequest $request)
    {
        $result = User::destroy($request->input('ids'));

        return $result;
    }

    public function update($userId, UpdateUserRequest $request)
    {
        try {
            $data = $request->validated();

            $user = User::findOrFail($userId);

            if (isset($data['email'])) {
                $user->email = $data['email'];
            }

            if (isset($data['active'])) {
                $user->email_verified_at = $data['active'] ? now() : null;
            }

            if (isset($data['role'])) {
                $user->role = $data['role'];
            }

            $user->save();

            return response()->json(new UserResource($user));
        } catch (\Throwable $th) {
            dd($th);
            return "Không thể cập nhật thông tin người dùng";
        }
    }

    public function destroy($userId)
    {
        try {
            $result = User::destroy([$userId]);

            return $result;
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function store(CreateUserRequest $request)
    {
        try {
            return User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role
            ]);
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function sendVerifyEmail($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $user->sendEmailVerificationNotification();

            return true;
        } catch (\Throwable $th) {

            return false;
        }
    }

    public function getUserInfo(Request $request)
    {
        return $this->user->ok(new UserResource($request->user()));
    }

    public function getAssetInfo(Request $request)
    {
        return $this->user->getAssetInfo();
    }

    public function getUserStatus(Request $request)
    {
        $userStatus = new UserStatusResource($request->user());

        return $this->user->ok($userStatus);
    }

    public function changeAvatar(UploadImageRequest $request)
    {
        return $this->user->changeAvatar($request->validated('image'));
    }

    public function uploadFrontIdentityImage(UploadImageRequest $request)
    {
        try {
            $frontIdCardImage = $request->validated('image');

            $response = $this->user->uploadEKycImage($frontIdCardImage);

            $currentUser = User::find(Auth::id());

            $currentUser->identity_image_front = $response['path'];

            $currentUser->identity_image_front_hash = $response['hash'];

            $currentUser->save();

            return new UserResource($currentUser);
        } catch (\Throwable $th) {
            return $this->user->error($th, BaseService::HTTP_INTERNAL_SERVER_ERROR, 'Image upload failure');
        }
    }

    public function uploadBackIdentityImage(UploadImageRequest $request)
    {
        try {
            $frontIdCardImage = $request->validated('image');

            $response = $this->user->uploadEKycImage($frontIdCardImage);

            $currentUser = User::find(Auth::id());

            $currentUser->identity_image_back = $response['path'];

            $currentUser->identity_image_back_hash = $response['hash'];

            $currentUser->save();

            return new UserResource($currentUser);
        } catch (\Throwable $th) {
            return $this->user->error($th, BaseService::HTTP_INTERNAL_SERVER_ERROR, 'Image upload failure');
        }
    }

    public function checkIdentityCardImage()
    {
        return $this->user->checkIdentityCardImage(Auth::user());
    }
}
