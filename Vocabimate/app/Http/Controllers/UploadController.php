<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    //
    public function upload(Request $request) {

        $target_path = "C:/xampp\htdocs\Vocabimate\Upload";  
        $target_path=$target_path."/";
        $target_path = $target_path.basename( $_FILES['file']['name']);   
          
        if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {  
            print_r(json_encode("File uploaded successfully!") );  
        } else{  
            print_r(json_encode("Sorry, file not uploaded, please try again!") ); 
             
        }  
        //return response.json("Successful");
}
}