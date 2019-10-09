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
        $subs=DB::table('subscription')
        ->select('subscription.sub_start_date','subscription.sub_end_date','subscription.subs_status','subscription.user_id')
        ->where("subscription.user_id", "=",$id)
        ->get();
        $sub_plan=DB::table('subscription')
      ->select('subscription.plan_id','plan.plan_name','plan.plan_description','plan.time_period_months','plan.actual_price','plan.discount_price','plan.is_hd_available','plan.is_uhd_available','plan.can_download','plan.number_of_device')
      ->join('plan','plan.plan_id','=','subscription.plan_id')
      ->where("subscription.user_id", "=",$id)
      ->get();
      $sub=DB::table('subscription')
      ->select('subscription.sub_id')
      ->join('plan','plan.plan_id','=','subscription.plan_id')
      ->where("subscription.user_id", "=",$id)
      ->get();

      return response()->json(['subscription' =>$sub,'plan'=>$sub_plan,'status'=>$subs]);

    }
    public function getplanlist()
    {
        $user = Auth::user();
        $plan=DB::table('plan')
      ->select('plan.plan_id','plan.plan_name','plan.plan_description','plan.time_period_months','plan.actual_price','plan.discount_price','plan.is_hd_available','plan.is_uhd_available','plan.number_of_device','plan.can_download')
      ->get();
      return response()->json(['plan' =>$plan]);

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
    $update['user_id']=$input['user_id'];
    $update['plan_id']=$input['plan_id'];
    $new=$input['plan_id'];
    $update['sub_start_date']=$dt;
    $sql="select plan_id,plan_name,actual_price,discount_price from plan where plan_id=$new";
    $comments = DB::select($sql);
   
   foreach ($comments as $comments) {
                
      $plan_name= $comments->plan_name;
      $actual_price= $comments->actual_price;
      $plan_id= $comments->plan_id;
      $discount_price= $comments->discount_price;



   }
   DB::table('subscription')
   ->where('user_id',$id)
   ->update(array('plan_id'=>$plan_id,'sub_start_date'=>$dt,'actual_price'=>$actual_price,'discount_price'=>$discount_price));
    $code = array("code"=>200,'message'=>'Your Plan Has Been Upgraded to '.$plan_name);

      return response()->json(['success' =>$code]); 
    }
}
