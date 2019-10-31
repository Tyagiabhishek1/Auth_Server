<?php

namespace App\Http\Controllers;

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\RegisterResource as Reg;
use App\User; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use Validator;
use Illuminate\Support\Facades\Input;

class AuthController extends Controller
{
    public $successStatus = 200;

    //------Register User API---------
  
    public function register(Request $request) {  
    $validator = Validator::make($request->all(), [ 
                 'login_id' => 'required|unique:users',
                 'device_id'=>'required',
                 'email' => 'required|email|unique:users',
                 'password' => 'required',  
                 'c_password' => 'required|same:password', 
       ]);   
    if ($validator->fails()) {          
          return response()->json(['error'=>$validator->errors()], 401);  
    }  
     
    $input = $request->all(); 
    $x['login_id']=$input['login_id'];
    $x['email']=$input['email'];
    $a['device_id']=$input['device_id'];
    $x['password'] = bcrypt($input['password']);   
    $user = User::create($x);
    $a['user_id']=$user['user_id'];
    $success['token'] =  $user->createToken('AppName')->accessToken;
    $a['access_token']=$success['token'];
    $mob=$a;
    DB::table('mob_auth')->insert($mob);
    $plan=array();
    $p['user_id']=$user['user_id'];
    $sql = "select plan_id,plan_name,plan_description,time_period_months,actual_price,discount_price,is_hd_available,is_uhd_available,can_download,number_of_device from plan where plan_id=1";
    $comments = DB::select($sql);
    foreach ($comments as $comments) {
                
      $plan_id= $comments->plan_id;
      $actual_price= $comments->actual_price;
      $discount_price= $comments->discount_price;
   }
   $usd=DB::table('users')
   ->select('users.user_id','users.login_id','users.email','users.delete_ind','users.update_user_id','users.enabled_ind','users.account_not_expired','users.account_not_locked','users.credentials_not_expired','users.user_details_ind','mob_auth.access_token','mob_auth.login_ind')
   ->join('mob_auth','mob_auth.user_id','=','users.user_id')
   ->where("users.user_id", "=",$p)
   ->get(); 
   
   foreach ($usd as $usd) {
                
      $user_id= $usd->user_id;
      $login_id= $usd->login_id;
   }    
    DB::table('subscription')->insert(array('user_id'=>$user_id,'plan_id'=>$plan_id,'actual_price'=>$actual_price,'discount_price'=>$discount_price));
    $usd=DB::table('users')
   ->select('users.user_id','users.login_id','users.email','users.delete_ind','users.update_user_id','users.enabled_ind','users.account_not_expired','users.account_not_locked','users.credentials_not_expired','users.user_details_ind','mob_auth.access_token','mob_auth.login_ind')
   ->join('mob_auth','mob_auth.user_id','=','users.user_id')
   ->where("users.user_id", "=",$p)
   ->get();    

   $subs=DB::table('subscription')
   ->select('subscription.sub_start_date','subscription.sub_end_date','subscription.subs_status','subscription.actual_price','subscription.discount_price','subscription.paid_price','subscription.user_id')
   ->where("subscription.user_id", "=",$p)
   ->get();
   DB::table('user_details')->insert($p);
   $code = array("code"=>200,'message'=>'User Successfully Created, Please Login!');   
   return response()->json(['userMessage' => $code]);
   }


   //------Login User API---------
     
      
   public function login(){ 
   if(Auth::attempt(['login_id' => request('login_id'), 'password' => request('password')])){ 
      $user = Auth::user();
      $id=$user->user_id;
      $dt =now();
      $success['access_token'] =  $user->createToken('AppName')-> accessToken; 
      DB::table('mob_auth')->update($success);
      $code = array("code"=>200,'message'=>'logged in successfully');
      $cc = array("servertime"=>$dt);  
      $subs=DB::table('subscription')
      ->select('subscription.sub_id','subscription.sub_start_date','subscription.sub_end_date','subscription.subs_status','subscription.actual_price','subscription.discount_price','subscription.paid_price')
      ->where("subscription.user_id", "=",$id)
      ->get(); 
      $sql = "select plan_id,plan_name,plan_description,time_period_months,actual_price,discount_price,is_hd_available,is_uhd_available,can_download,number_of_device from plan where plan_id=1";
      $comments = DB::select($sql);
      $usd=DB::table('users')
      ->select('users.user_id','users.login_id','users.email','users.delete_ind','users.update_user_id','users.enabled_ind','users.account_not_expired','users.account_not_locked','users.credentials_not_expired','users.user_details_ind','mob_auth.access_token','mob_auth.login_ind')
      ->join('mob_auth','mob_auth.user_id','=','users.user_id')
      ->where("users.user_id", "=",$id)
      ->get();
   

       return response()->json(['userMessage' => $code,'subscription'=>$subs,'plan'=>$comments,'user'=>$usd,'token'=>$success]); 
     } else{ 
      $codee = array("code"=>3004,'message'=>'Invalid LoginId or Password');
      return response()->json(['error'=>$codee], 401); 
      } 
   }

   //------Save User Details API---------
     
   public function savedetails(Request $request) {
    $user = Auth::user();
    $id=$user->user_id;
    $validator = Validator::make($request->all(), [ 
      'first_name' => 'required',
      'middle_name'=>'max:50',
      'last_name' => 'max:50',
      'prefix' => 'min:2',  
      'gender' => 'required',
      'dob' => 'required', 
      'phone_extension' => 'max:2',
      'phone' => 'max:10|min:10',
      'address_line1' => 'required', 
      'address_line2' => 'max:250', 
      'city' => 'required', 
      'state' => 'required', 
      'zipcode' => 'required', 
      'country' => 'required',
]);   
if ($validator->fails()) {          
      return response()->json(['error'=>$validator->errors()], 401);                        }    
      $input = $request->all(); 
      $id=$user->user_id;
      $d['first_name']=$input['first_name'];
      $d['middle_name']=$input['middle_name'];
      $d['last_name']=$input['last_name'];
      $d['prefix']=$input['prefix'];
      $d['gender']=$input['gender'];
      $d['dob']=$input['dob'];
      $d['user_id']=$user['user_id'];
      $d['phone_extension']=$input['phone_extension'];
      $d['phone']=$input['phone'];
      $d['address_line1']=$input['address_line1'];
      $d['address_line2']=$input['address_line2'];
      $d['city']=$input['city'];
      $d['state']=$input['state'];
      $d['zipcode']=$input['zipcode'];
      $d['country']=$input['country'];
          
      DB::table('user_details')
      ->where('user_id',$id)
      ->update($d);
      $code = array("code"=>200,'message'=>'details saved successfully');

      return response()->json(['success' =>$code], $this->successStatus); 
}
   
   
 //------Logout User API---------
 
 
   public function logout(){ 
      $user=Auth::user();
      $a=$user->token();
      $a->revoke();
      $code = array("code"=>200,'message'=>'logged out successfully');
      return response()->json(['success' => $code]);
     }
     
   //------Change Password API---------

   public function resetpassword(Request $request){
      $user=Auth::user();
      $a=$user->password;
      $validator = Validator::make($request->all(), [ 
    'new_password' => 'max:8',
    'c_password' => 'required|same:new_password',
            ]);   
   if ($validator->fails()) {          
         return response()->json(['error'=>$validator->errors()], 401);                        }    
         $input = $request->all();
         $x['password'] = bcrypt($input['new_password']);
         DB::table('users')->update($x);

         $code = array("code"=>200,'message'=>'Password Changed Successfully');
      return response()->json(['success' => $code]);

   }

   //------Forget Password API--------- 

   public function forgetPassword(){
      // $validator = Validator::make($request->all(), [ 
      //    'phone_num' => 'required|max:10|min:10',
      // ]);
      // if ($validator->fails()) {          
      //    return response()->json(['error'=>$validator->errors()], 401);                        }    
      //    $input = $request->all(); 
        // $number['phone_num']=$input['phone_num'];
         $user_phone = "select * from user_details where phone='".$_POST['phone_num']."'";
         $get_phone = DB::select($user_phone);
         if($get_phone==null)
         {
            $code = array("code"=>200,'message'=>'Phone Number Not Found In Our System. Please Try Again.');
            return response()->json(['success' => $code]);

         }else{
            $snsKey = env('AWS_ACCESS_KEY_ID');
            $snsSecret =env('AWS_SECRET_ACCESS_KEY');
            $snsRegion = env('AWS_DEFAULT_REGION');
            $snsVersion = env('AWS_VERSION');
            $len_of_otp = 6 ;
            $otp = $this->generateOTP($len_of_otp);
            DB::table('user_details')
            ->where('phone','=',$_POST['phone_num'])
            ->update(array('otp'=>$otp));
            $message = 'Your OTP for Verification is '.$otp;
            $phone = $_POST['phone_num']; 
            try{
               $sns = new SnsClient([
               'region' => $snsRegion, //Change according to you
               'version' => $snsVersion, //Change according to you
               'credentials' => [
               'key' => $snsKey,
               'secret' => $snsSecret,
               ],
               'scheme' => 'https', //disables SSL certification, there was an error on enabling it 
               ]);
               }catch(AwsException $ex){
               echo $ex;
               }
               //THIS TRY-CATCH  FUNCTION WILL SEND SMS TO PHONE NUMBER
            //    try {
            //       $result = $sns->publish([
            //           'Message' => $message,
            //           'PhoneNumber' => $phone,
            //       ]);
            //   } catch (AwsException $e) {
            //    echo $ex;
            //   } 
            return response()->json(["code"=>200, "otp"=>$otp]);
         }

   }
   //---------Send OTP API--------
    public function sendOTP(){
      $snsKey = env('AWS_ACCESS_KEY_ID');
      $snsSecret =env('AWS_SECRET_ACCESS_KEY');
      $snsRegion = env('AWS_DEFAULT_REGION');
      $snsVersion = env('AWS_VERSION');
      $len_of_otp = 6 ;
      $otp = $this->generateOTP($len_of_otp);
      $message = 'Your OTP for Verification is '.$otp;
      $phone = $_POST['phone_num']; 
      try{
         $sns = new SnsClient([
         'region' => $snsRegion, //Change according to you
         'version' => $snsVersion, //Change according to you
         'credentials' => [
         'key' => $snsKey,
         'secret' => $snsSecret,
         ],
         'scheme' => 'https', //disables SSL certification, there was an error on enabling it 
         ]);
         }catch(AwsException $ex){
         echo $ex;
         }
         //THIS TRY-CATCH  FUNCTION WILL SEND SMS TO PHONE NUMBER
      //    try {
      //       $result = $sns->publish([
      //           'Message' => $message,
      //           'PhoneNumber' => $phone,
      //       ]);
      //   } catch (AwsException $e) {
      //    echo $ex;
      //   } 
      print_r($message);die;
   }
    public function generateOTP($len_of_otp){ 
      $generator = "1357902468"; 
      $result = ""; 
      for ($i = 1; $i <= $len_of_otp; $i++) { 
          $result .= substr($generator, (rand()%(strlen($generator))), 1); 
      }  
      return $result; 
  }
  public function verifyOTP(){
   $user_otp = "select otp from user_details where phone='".$_POST['phone_num']."'";
   $get_user_otp = DB::select($user_otp);
   $final_otp = $get_user_otp[0];
   if($final_otp->otp==$_POST['otp'])
   {
      $code = array("code"=>200,'message'=>'Verified Successfully!!');
      return response()->json(['success' => $code]);

   }else{
      $code = array("code"=>200,'message'=>'Please Enter Valid OTP!!');
      return response()->json(['success' => $code]);

   }
      

  } 
}