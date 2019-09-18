<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\RegisterResource as Reg;

use App\User; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use Validator;

class AuthController extends Controller
{
    public $successStatus = 200;
  
    public function register(Request $request) {  
    $validator = Validator::make($request->all(), [ 
                 'login_id' => 'required',
                 'device_id'=>'required',
                 'email' => 'required|email',
                 'password' => 'required',  
                 'c_password' => 'required|same:password', 
       ]);   
    if ($validator->fails()) {          
          return response()->json(['error'=>$validator->errors()], 401);                        }    
    $input = $request->all(); 
    $x['login_id']=$input['login_id'];
    $x['email']=$input['email'];
    $a['device_id']=$input['device_id'];
    $x['password'] = bcrypt($input['password']);    
    $user = User::create($x);
    $a['user_id']=$user['id'];
    $success['token'] =  $user->createToken('AppName')->accessToken;
    $a['access_token']=$success['token'];
    DB::table('mob_auth')->insert($a);
    $plan=array();
    $p['user_id']=$user['id'];
    $sql = "select plan_id,plan_name,plan_description,time_period_months,actual_price,discount_price,is_hd_available,can_download,number_of_device from plan where plan_id=1";
    $comments = DB::select($sql);
    DB::table('subscription')->insert($p);
    return response()->json(['message' => 'New User Created','user' => $user, 'subscription' => $comments]);

   //  DB::table('subscription')
   //  $client=User();
   //  $response=array();
   //  $response['']

   //  return Reg::collection();
   }
     
      
   public function login(){ 
   if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){ 
      $user = Auth::user(); 
      $success['token'] =  $user->createToken('AppName')-> accessToken; 
       return response()->json(['success' => $success], $this-> successStatus); 
     } else{ 
      return response()->json(['error'=>'Unauthorised'], 401); 
      } 
   }
     
   public function getUser() {
    $user = Auth::user();
    return response()->json(['success' => $user], $this->successStatus); 
   
   }
   
   public function logout(){

      $user=Auth::user();
      $a=$user->token();
      $a->revoke();
      $result='User Logged out Successfully';
      return response()->json(['success' => $result],200);
      
   }

}