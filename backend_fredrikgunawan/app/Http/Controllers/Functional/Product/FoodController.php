<?php

namespace App\Http\Controllers\Functional\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;
use App\Models\Product\Food;

class FoodController extends Controller
{
    public function Create(Request $request) {
        $name = $request['name'];
        $sellingprice = $request['sellingprice'];
        $costprice = $request['costprice'];
        $description = $request['description'];
        $image = $request->file('image');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sellingprice' => 'required|numeric',
            'costprice' => 'required|numeric',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {    
            $message = $validator->errors()->getMessages();
            return ApiResponse::error($message, 400);
        }

        DB::beginTransaction();

        try {
            $image_name = time().'_'.$image->getClientOriginalName();
            $path = 'Assets/Files/Images/Product/Food';
            $destination_path = public_path($path);
            $image->move($destination_path, $image_name);
            $image = $path. '/' .$image_name;


            $food = new Food;
            $food->name = $name;
            $food->sellingprice = $sellingprice;
            $food->costprice = $costprice;
            $food->description = $description;
            $food->image = $image;
            $food->save();

            DB::commit();

            $data = array(
                'food' => $food
            );
            $message = 'Food created successfully';
            
            return ApiResponse::success($data, $message);
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Failed to create food';
            
            return ApiResponse::error($message, 400);
        }
    }

    public function Read(Request $request) {
        $food_id = $request['food_id'];
        $search = $request['search'];
        $perPage = $request['perPage'];

        $query = Food::query();

        if ($food_id) {
            $query->where('foods', $food_id);
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $datas = $query->paginate($perPage);

        $data = array();
        if ($datas->isEmpty()) {
            $message = 'Food data not found';
            return ApiResponse::error($message, 404);
        }else{
            foreach ($datas->items() as $key => $value) {
                $no = $key + 1;
                $food = array(
                    'no' => $no,
                    'food_id' => $value->id,
                    'name' => $value->name,
                    'sellingprice' => $value->sellingprice,
                    'description' => $value->description,
                    'image' => $value->image,
                );

            if (Auth::guard('api')->check()) {
                if (Auth::guard('api')->user()->role == 'admin') {
                    $food['costprice'] = $value->costprice;
                } else {
                    unset($food['costprice']);
                }
            }

                array_push($data, $food);
            }
        }

        $message = 'Food data found successfully';
        
        return ApiResponse::success($data, $message, $datas);
    }

    public function Update(Request $request) {
        $food_id = $request['food_id'];
        $name = $request['name'];
        $sellingprice = $request['sellingprice'];
        $costprice = $request['costprice'];
        $description = $request['description'];
        $image = $request->file('image');

        $validator = Validator::make($request->all(), [
            'food_id' => 'required|numeric',
            'name' => 'required|string|max:255',
            'sellingprice' => 'required|numeric',
            'costprice' => 'required|numeric',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {    
            $message = $validator->errors()->getMessages();
            return ApiResponse::error($message, 400);
        }

        DB::beginTransaction();

        try {

            $food = Food::find($food_id);
            $food->name = $name;
            $food->sellingprice = $sellingprice;
            $food->costprice = $costprice;
            $food->description = $description;

            if ($image) {
                $image_name = time().'_'.$image->getClientOriginalName();
                $path = 'Assets/Files/Images/Product/Food';
                $destination_path = public_path($path);
                $image->move($destination_path, $image_name);
                $image = $path. '/' .$image_name;

                $food->image = $image;
            } 

            $food->save();

            DB::commit();

            $data = array();
            $message = 'Food updated successfully';
            
            return ApiResponse::success($data, $message);
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Failed to update food';
            
            return ApiResponse::error($message, 400);
        }
    }

    public function Delete(Request $request) {
        $food_id = $request['food_id'];

        $validator = Validator::make($request->all(), [
            'food_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {    
            $message = $validator->errors()->getMessages();
            return ApiResponse::error($message, 400);
        }

        DB::beginTransaction();

        try {
            $food = Food::find($food_id);
        
            if (!$food) {
                $message = 'Food not found';
                return ApiResponse::error($message, 404);
            }
        
            $food->delete();

            DB::commit();
        
            $data = array();
            $message = 'Food deleted successfully';
            return ApiResponse::success($data, $message);
        } catch (\Exception $e) {
            $message = 'Failed to delete food';
            return ApiResponse::error($message, 400);
        }
        
    }
}
