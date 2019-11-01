<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; 
use Validator;
use App\User; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class LicenseController extends Controller
{
    public function getCurrentSubsPlan()
    {
        $user = Auth::user();
        $id=$user->user_id;
        $user_subscription_plan=DB::table('subscription')
        ->select('subscription.sub_start_date','subscription.sub_end_date','subscription.subs_status','subscription.user_id')
        ->where("subscription.user_id", "=",$id)
        ->get();
        $user_plan_details=DB::table('subscription')
      ->select('subscription.plan_id','plan.plan_name','plan.plan_description','plan.time_period_months','plan.actual_price','plan.discount_price','plan.is_hd_available','plan.is_uhd_available','plan.can_download','plan.number_of_device')
      ->join('plan','plan.plan_id','=','subscription.plan_id')
      ->where("subscription.user_id", "=",$id)
      ->get();
      $user_plan_status=DB::table('subscription')
      ->select('subscription.sub_id')
      ->join('plan','plan.plan_id','=','subscription.plan_id')
      ->where("subscription.user_id", "=",$id)
      ->get();

      return response()->json(['subscription' =>$user_subscription_plan,'plan'=>$user_plan_details,'status'=>$user_plan_status]);

    }
    public function getplanlist()
    {
        $user = Auth::user();
        $get_plan_list=DB::table('plan')
      ->select('plan.plan_id','plan.plan_name','plan.plan_description','plan.time_period_months','plan.actual_price','plan.discount_price','plan.is_hd_available','plan.is_uhd_available','plan.number_of_device','plan.can_download')
      ->get();
      return response()->json(['plan' =>$get_plan_list]);

    }
    public function upgradeplan(Request $request)
    {   $user = Auth::user();
        $dt =now();
        $validator = Validator::make($request->all(), [ 
            'user_id' => 'required|numeric',
            'plan_id'=>'required|numeric', 
  ]);   
if ($validator->fails()) {          
     return response()->json(['error'=>$validator->errors()], 401);  
}  
    $input = $request->all(); 
    $id=$user->user_id;
    $upgrade_plan['user_id']=$input['user_id'];
    $upgrade_plan['plan_id']=$input['plan_id'];
    $new_plan_id=$input['plan_id'];
    $upgrade_plan['sub_start_date']=$dt;
    $sql="select plan_id,plan_name,actual_price,discount_price from plan where plan_id=$new_plan_id";
    $user_new_plan = DB::select($sql);
   
   foreach ($user_new_plan as $user_new_plan) {
                
      $plan_name= $user_new_plan->plan_name;
      $actual_price= $user_new_plan->actual_price;
      $plan_id= $user_new_plan->plan_id;
      $discount_price= $user_new_plan->discount_price;



   }
   DB::table('subscription')
   ->where('user_id',$id)
   ->update(array('plan_id'=>$plan_id,'sub_start_date'=>$dt,'actual_price'=>$actual_price,'discount_price'=>$discount_price));
    $response = array("code"=>200,'message'=>'Your Plan Has Been Upgraded to '.$plan_name);

      return response()->json(['success' =>$response]); 
    }
}
