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
    public $successStatus = "200";

    //------Register User API---------
  
    public function register(Request $request) {  

    $validator = Validator::make($request->all(),[
       [
        'user'=>[
                   'login_id' => 'required|unique:users',
                     'device_id'=>'required',
                     'email' => 'required|email|unique:users',
                     'password' => 'required',  
                     'c_password' => 'required|same:password',
        ]     
    ]]);  
   //     $content = $request->getContent(); 
   //     $post_json = json_decode($content,true);
   //     $arra = $post_json['user'];
    if ($validator->fails()) {          
          return response()->json(['error'=>$validator->errors()], 401);  
    }  
     
    $input = $request->all(); 
   //  $content = $request->getContent(); 
   //  $post_json = json_decode($content,true);
     $arra = $input['user'];
    $user_input['login_id']=$arra['login_id'];
    $user_input['email']=$arra['email'];
    $user_input_mob['device_id']=$arra['device_id'];
    $user_input['password'] = bcrypt($arra['password']);  
    $user = User::create($user_input);
    $user_input_mob['user_id']=$user['user_id'];
    $success['token'] =  $user->createToken('AppName')->accessToken;
    $user_input_mob['access_token']=$success['token'];
    $mob=$user_input_mob;
    DB::table('mob_auth')->insert($mob);
    $plan=array();
    $user_id['user_id']=$user['user_id'];
    $sql = "select plan_id,plan_name,plan_description,time_period_months,actual_price,discount_price,is_hd_available,is_uhd_available,can_download,number_of_device from plan where plan_id=1";
    $plan = DB::select($sql);
    foreach ($plan as $plan) {
                
      $plan_id= $plan->plan_id;
      $actual_price= $plan->actual_price;
      $discount_price= $plan->discount_price;
   }
   $user_data=DB::table('users')
   ->select('users.user_id','users.login_id','users.email','users.delete_ind','users.update_user_id','users.enabled_ind','users.account_not_expired','users.account_not_locked','users.credentials_not_expired','users.user_details_ind','mob_auth.access_token','mob_auth.login_ind')
   ->join('mob_auth','mob_auth.user_id','=','users.user_id')
   ->where("users.user_id", "=",$user_id)
   ->get(); 
   
   foreach ($user_data as $user_data) {
                
      $user_id= $user_data->user_id;
      $login_id= $user_data->login_id;
   }    
    DB::table('subscription')->insert(array('user_id'=>$user_id,'plan_id'=>$plan_id,'actual_price'=>$actual_price,'discount_price'=>$discount_price));
    $user_data=DB::table('users')
   ->select('users.user_id','users.login_id','users.email','users.delete_ind','users.update_user_id','users.enabled_ind','users.account_not_expired','users.account_not_locked','users.credentials_not_expired','users.user_details_ind','mob_auth.access_token','mob_auth.login_ind')
   ->join('mob_auth','mob_auth.user_id','=','users.user_id')
   ->where("users.user_id", "=",$user_id)
   ->get();    

   $subs=DB::table('subscription')
   ->select('subscription.sub_start_date','subscription.sub_end_date','subscription.subs_status','subscription.actual_price','subscription.discount_price','subscription.paid_price','subscription.user_id')
   ->where("subscription.user_id", "=",$user_id)
   ->get();
   // DB::table('user_details')->insert($user_id);
   DB::table('user_details')
    ->insert(array('user_id'=>$user_id));
   $response = array("code"=>"200",'message'=>'User Successfully Created, Please Login!');   
   return response()->json(['userMessages' => array($response)]);
   }


   //------Login User API---------
     
      
   public function login(){ 
   if(Auth::attempt(['login_id' => request('login_id'), 'password' => request('password')])){ 
      $user = Auth::user();
      $id=$user->user_id;
      $dt =now();
      $servertime=strtotime($dt);
      $success['access_token'] =  $user->createToken('AppName')-> accessToken; 
      DB::table('mob_auth')->update($success);
      $response = array("code"=>200,'message'=>'logged in successfully');
      $current_time = array("servertime"=>$dt);  
      $user_subscription_details=DB::table('subscription')
      ->select('subscription.sub_id','subscription.sub_start_date','subscription.sub_end_date','subscription.subs_status','subscription.actual_price','subscription.discount_price','subscription.paid_price')
      ->where("subscription.user_id", "=",$id)
      ->get(); 

      //user subscription details fetching into array
      foreach ($user_subscription_details as $user_subscription_details) {   
         $user_sub_array['sub_id']= $user_subscription_details->sub_id;
         $user_sub_array['sub_start_date']= $user_subscription_details->sub_start_date;
         $user_sub_array['sub_end_date']= $user_subscription_details->sub_end_date;
         $user_sub_array['subs_status']= $user_subscription_details->subs_status;
         $user_sub_array['actual_price']= $user_subscription_details->actual_price;
         $user_sub_array['discount_price']= $user_subscription_details->discount_price;
         $user_sub_array['paid_price']= $user_subscription_details->paid_price;
      }  


      $sql = "select plan_id,plan_name,plan_description,time_period_months,actual_price,discount_price,is_hd_available,is_uhd_available,can_download,number_of_device from plan where plan_id=1";
      $user_plan_details = DB::select($sql);

      //user plan details fetching into array
      foreach ($user_plan_details as $user_plan_details) {   
         $user_plan_array['plan_id']= $user_plan_details->plan_id;
         $user_plan_array['plan_name']= $user_plan_details->plan_name;
         $user_plan_array['plan_description']= $user_plan_details->plan_description;
         $user_plan_array['time_period_months']= $user_plan_details->time_period_months;
         $user_plan_array['actual_price']= $user_plan_details->actual_price;
         $user_plan_array['discount_price']= $user_plan_details->discount_price;
         $user_plan_array['is_hd_available']= $user_plan_details->is_hd_available;
         $user_plan_array['is_uhd_available']= $user_plan_details->is_uhd_available;
         $user_plan_array['can_download']= $user_plan_details->can_download;
         $user_plan_array['number_of_device']= $user_plan_details->number_of_device;
      }  


      $user_data=DB::table('users')
      ->select('users.user_id','users.login_id','users.email','users.delete_ind','users.update_user_id','users.enabled_ind','users.account_not_expired','users.account_not_locked','users.credentials_not_expired','users.user_details_ind','mob_auth.access_token','mob_auth.login_ind')
      ->join('mob_auth','mob_auth.user_id','=','users.user_id')
      ->where("users.user_id", "=",$id)
      ->get();

      //user details fetching into array
      foreach ($user_data as $user_data) {   
         $user_return_array['user_id']= $user_data->user_id;
         $user_return_array['login_id']= $user_data->login_id;
         $user_return_array['email']= $user_data->email;
         $user_return_array['delete_ind']= $user_data->delete_ind;
         $user_return_array['update_user_id']= $user_data->update_user_id;
         $user_return_array['enabled_ind']= $user_data->enabled_ind;
         $user_return_array['account_not_expired']= $user_data->account_not_expired;
         $user_return_array['account_not_locked']= $user_data->account_not_locked;
         $user_return_array['credentials_not_expired']= $user_data->credentials_not_expired;
         $user_return_array['user_details_ind']= $user_data->user_details_ind;
         $user_return_array['access_token']= $user_data->access_token;
         $user_return_array['login_ind']= $user_data->login_ind;
      }  
       return response()->json(['userMessages' => array($response),'servertime'=>$servertime,'user'=>$user_return_array,'subscription'=>$user_sub_array,'plan'=>$user_plan_array]); 
     } else{ 
      $invalid_response = array("code"=>3004,'message'=>'Invalid LoginId or Password');
      return response()->json(['error'=>$invalid_response], 401); 
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
      $user_details['first_name']=$input['first_name'];
      $user_details['middle_name']=$input['middle_name'];
      $user_details['last_name']=$input['last_name'];
      $user_details['prefix']=$input['prefix'];
      $user_details['gender']=$input['gender'];
      $user_details['dob']=$input['dob'];
      $user_details['user_id']=$user['user_id'];
      $user_details['phone_extension']=$input['phone_extension'];
      $user_details['phone']=$input['phone'];
      $user_details['address_line1']=$input['address_line1'];
      $user_details['address_line2']=$input['address_line2'];
      $user_details['city']=$input['city'];
      $user_details['state']=$input['state'];
      $user_details['zipcode']=$input['zipcode'];
      $user_details['country']=$input['country'];
          
      DB::table('user_details')
      ->where('user_id',$id)
      ->update($user_details);
      $response = array("code"=>200,'message'=>'User Details Saved Successfully');

      return response()->json(['success' =>$response], $this->successStatus); 
}
   
   
 //------Logout User API---------
 
 
   public function logout(){ 
      $user=Auth::user();
      $user_token=$user->token();
      $user_token->revoke();
      $response = array("code"=>200,'message'=>'logged out successfully');
      return response()->json(['userMessages' => array($response)]);
     }
     
   //------Change Password API---------

   public function resetpassword(Request $request){
      $user=Auth::user();
      $user_password=$user->password;
      $validator = Validator::make($request->all(), [ 
    'new_password' => 'max:8',
    'c_password' => 'required|same:new_password',
            ]);   
   if ($validator->fails()) {          
         return response()->json(['error'=>$validator->errors()], 401);                        }    
         $input = $request->all();
         $password['password'] = bcrypt($input['new_password']);
         DB::table('users')->update($password);

         $response = array("code"=>200,'message'=>'Password Changed Successfully');
      return response()->json(['success' => array($response)]);

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
            $response = array("code"=>200,'message'=>'Phone Number Not Found In Our System. Please Try Again.');
            return response()->json(['success' => array($response)]);

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
            $response = array("code"=>200,'otp'=>$otp);
            return response()->json(['userMessages'=>array($response)]);
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
      $response = array("code"=>200,'message'=>'Verified Successfully!!');
      return response()->json(['success' => array($response)]);

   }else{
      $response = array("code"=>200,'message'=>'Please Enter Valid OTP!!');
      return response()->json(['success' => array($response)]);

   }
      

  } 
}