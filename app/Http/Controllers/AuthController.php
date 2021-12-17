<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function signin(StoreUserRequest $req)
    {
        //
        $user = User::where('email',$req->email)->first();
        // return $user->createToken($req->email)->plainTextToken;
        if($user){
            $haspassword=$user->password;
            $ismatch=Hash::check($req->password,$haspassword);
            if($ismatch){
                $token=$user->createToken($req->email)->plainTextToken;
                return response()->json([
                    'user'=>$user->email,
                    'token'=>$token
                ],200);
            }else{
                return response()->json(['message'=>'Password does not match']);
            }
        }else{
            return response()->json(['message'=>'User Not Exist'],200);
        }
            
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function signup(StoreUserRequest $req)
    {
        //
        $user=new User;
        $haspassword=Hash::make($req->password);
        $isexist=User::where('email',$req->email)->get();
        if(count($isexist) > 0){
            return response()->json(['message'=>'User Exist'],200);
        }else{
            $user->email=$req->email;
            $user->password=$haspassword;
            $user->save();
            $token=$user->createToken('mytoken')->plainTextToken;
            return response()->json([
                'message'=>'Signup Completed',
                'token'=>$token
            ],201) ;
        }

        

        
    }

    public function logout(Request $req){
        $req->user()->tokens()->delete();
        return response('Logout',200);
    }

    public function deleteAccount(User $user)
    {
        //
    }

    public function isvalidemail(Request $req){
        $email=$req->email;
        $data=User::where('email',$email)->first()?? 0;
        if($data){
            return response()->json(['resettoken'=>Hash::make($data['email'])]);
        }else{
            return response()->json(['message'=>'Not Found']);
        }
    }

    public function resetpassword(Request $req){
        $email=$req->email;
        $password=$req->password;
        $token=$req->resettoken;
        
        $ischecked=Hash::check($email,$token);

        if(!$ischecked){
            return response()->json(['messsage'=>'Email Does not matched']);
        }else{
            $newpass=Hash::make($password);
            User::where('email',$email)->update(['password'=>$newpass]);
            return response()->json(['message'=>'Password Updated']);
        }


    }
}
