<?php

namespace App\Http\Controllers\Functional\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\User\Admin;

class AdminController extends Controller
{
    public function Create(Request $request) {
        $admin_id = Auth::user()->id;
        $first_name = $request['first_name'];
        $last_name = $request['last_name'];
        $email = $request['email'];
        $password = $request['password'];
        $password_confirmation = $request['password_confirmation'];

        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|numeric',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required','string','email','max:255','unique:users'],
            'password' => ['required','string','min:8','regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'],
            'password_confirmation' => 'required|same:password',
        ],
        [
            'admin_id.required' => 'Something is wrong re-login and try again',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character',
            'password_confirmation.same' => 'Password confirmation must match password',
        ]);
        
        if ($validator->fails()) {    
            $message = $validator->errors()->getMessages();
            return ApiResponse::error($message, 400);
        }
        
        DB::beginTransaction();

        try {
            $user = new User;
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->role = 'admin';
            $user->save();

            $admin = new Admin;
            $admin->user_id = $user->id;
            $admin->first_name = $first_name;
            $admin->last_name = $last_name;
            $admin->save();

            DB::commit();

            $data =  array();
            $message = 'Admin data has been successfully created';

            return ApiResponse::success($data, $message);
        } catch (\Exception $e) {
            DB::rollback();

            $message = 'Failed to create admin data.';

            return ApiResponse::error($message, 400);

        }
    }

    public function Read(Request $request) {
        $admin_id = $request['admin_id'];
        $search = $request['search'];
        $perPage = $request['perPage'];

        $query = User::query()->with('admin');

        if ($admin_id) {
            $query->where('users.id', '=', $admin_id);
        }

        if ($search) {
            $query->where('users.email', 'LIKE', '%'.$search.'%');
        }

        $datas = $query->paginate($perPage);

        $data = array();
        if ($datas->isEmpty()) {
            $message = 'Admin data not found';
            return ApiResponse::error($message, 404);
        }else{
            foreach ($datas->items() as $key => $value) {
                if($value->id == 1) continue;
                
                $no = $key + 1;
                $admin = array(
                    'no' => $no,
                    'admin_id' => $value->id,
                    'first_name' => $value->admin->first_name,
                    'last_name' => $value->admin->last_name,
                    'email' => $value->email,
                );
                array_push($data, $admin);
            };

            $message = 'Admin data has been retrieved successfully';
            return ApiResponse::success($data, $message, $datas);
        }
        
    }

    public function Update(Request $request) {
        $admin_id = $request['admin_id'];
        $first_name = $request['first_name'];
        $last_name = $request['last_name'];
        $email = $request['email'];
        $password = $request['password'];
        $password_confirmation = $request['password_confirmation'];

        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|integer',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required','string','email','max:255','unique:users,email,'.$admin_id],
        ]);
        
        if ($validator->fails()) {    
            $message = $validator->errors()->getMessages();
            return ApiResponse::error($message, 400);
        }

        DB::beginTransaction();

        try {
            $user = User::find($admin_id);
            $user->email = $email;

            if ($password) {
                $validator = Validator::make($request->all(), [
                    'password' => ['required','string','min:8','regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'],
                    'password_confirmation' => 'required|same:password',
                ],
                [
                    'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character',
                    'password_confirmation.same' => 'Password confirmation must match password',
                ]);
                
                if ($validator->fails()) {    
                    $message = $validator->errors()->getMessages();
                    $data = array(
                        'message' => $message
                    );
                    return response()->json($data,400);
                }

                $user->password = Hash::make($password);
            }

            $user->save();

            $admin = Admin::where('user_id', '=', $admin_id)->first();
            $admin->first_name = $first_name;
            $admin->last_name = $last_name;
            $admin->save();

            DB::commit();

            $data =  array();
            $message = 'Admin data has been successfully updated';

            return ApiResponse::success($data, $message);
        } catch (\Exception $e) {
            DB::rollback();

            $message = 'Failed to update admin data.';

            return ApiResponse::error($message, 400);

        }
    }

    public function Delete(Request $request) {
        $admin_id = $request['admin_id'];

        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|integer',
        ]);
        
        if ($validator->fails()) {    
            $message = $validator->errors()->getMessages();
            return ApiResponse::error($message, 400);
        }

        DB::beginTransaction();

        try {
            $user = User::find($admin_id);
            if (!$user) {
                $message = 'Admin data not found';
                return ApiResponse::error($message, 404);
            }
            
            $user->delete();

            DB::commit();
        
            $data = array();
            $message = 'Admin data has been successfully deleted';
            return ApiResponse::success($data, $message);
        } catch (\Exception $e) {
            DB::rollback();
        
            $message = 'Failed to delete admin data.';
            return ApiResponse::error($message, 400);
        }
        
    }
}
