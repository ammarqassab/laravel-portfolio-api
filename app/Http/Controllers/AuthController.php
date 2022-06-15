<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\http\Controllers\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


use Illuminate\Support\Facades\Validator;

//use Laravel\Passport\RefreshToken;
//use Laravel\Passport\Token;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                
                'username' => 'required|unique:users|max:30',
                'email' => 'required|email',
                'phone'=>'required',
                'country'=>'required',
                'city'=>'required',
                'password' => 'required',
                'c_password' => 'required|same:password'
            ]);
        if ($validator->fails()) {
            return $this->sendError('Validator Error', $validator->errors());
        }
        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
     return response()->json([
        'message'=>'register successfully',],200);
    }

    public function login(Request $request)
    {

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            if ($user->role_as==1)
            {
                $role='admin';
                $success['token']=  $user->createToken('token-admin',['server:admin'])->plainTextToken;
            }
            else
            {
                $role='user';
                $success['token'] = $user->createToken('token-user',['server:user'])->plainTextToken;
            }
            $success['id'] = $user->id;
            $success['username'] = $user->username;
            $success['phone'] = $user->phone;
            $success['country'] = $user->country;
            $success['city'] = $user->city;
            $success['email'] = $user->email;
            $success['role']=$role;
            return $this->sendResponse($success, 'login Successfully!');

        }
         else
        {
            return $this->sendError(' Error', ['error', 'Unauthorized']);
        }
    }

    public function logout(Request $request)
    {

        auth()->user()->tokens()->delete();
        return $this->sendResponse('Logout','USER logout Successfully!');
        
    }
    public function change_password(Request $request)
    {
            
        $validator=Validator::make($request->all(),
        [
            'old_password'=>'required',
            'password'=>'required',
            'confirm_password'=>'required|same:password'
        ]);
        
        if($validator->fails())
        {
         
          return response()->json([
              'message'=>'validations fails',
              'errors'=>$validator->errors() ],422);
          
        }
        $user=$request->user();
        if (Hash::check($request->old_password,$user->password))
        {
              $user->update([
                  'password'=>Hash::make($request->password)
              ]);

              return response()->json([
                'message'=>'updated password successfully',],200);
            
          
        }
        else
        {
            return $this->sendError('Validator Error', $validator->errors());
        }
    }
    public function get_profile(Request $request)
    {

    }

    public function update_profile(Request $request)

    {
                $validator=Validator::make($request->all(),
                [
                    
                    'username' => 'required|unique:users|max:30',
                    'email' => 'required|email',
                    'phone'=>'required',
                    'country'=>'required',
                    'city'=>'required',
                    
                ]);
                if ($validator->fails())
                {
                  
                    return response()->json([
                        'message'=>'validations fails',
                        'errors'=>$validator->errors() ],422);
                    
                }
                $user=$request->user();
               
               

                $user->update([
                    
                    'username'=>$request->username,
                    'email'=>$request->email,
                    'phone'=>$request->phone,
                    'country'=>$request->country,
                    'city'=>$request->city,
                    
                ]);
                return response()->json([
                    'message'=>'updated profile successfully',],200);  
    }
}
