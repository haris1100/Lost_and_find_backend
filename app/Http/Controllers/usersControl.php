<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use App\peoplem;
use Illuminate\Database\QueryException;



class usersControl extends Controller
{

    public function login_user(Request $request){
            $email=$request->email;
          //  $username=$request->contact;
           // $cnic=$request->cnic;
            $password=$request->password;
       // if (isset($email)&&isset($username)) {
       ///     $this->json_error(['error' => 'try email or username not both!'], 403);
       // }
        if(isset($email)){
            $email_or_user=$email;
        }
//        else if(isset($username)){
//            $email_or_user=$username;
//        }
//        else if(isset($cnic)){
//            $email_or_user=$cnic;
//        }
        else
            $this->json_error(['error' => 'fill out email field'], 403);
        if(isset($email_or_user,$password)){
            if(peoplem::where('data',$email_or_user)->exists()){
               $get_data= peoplem::where('data',$email_or_user)->first();
            }
            else  $this->json_error(['error' => 'something is incorrect!'], 404);

            if(isset($get_data)) {
                if (password_verify($password, $get_data->password)) {

                  // ini_set('session.save_path', resource_path('views/sessions'));
                  session_start();
                  $_SESSION['private']=$get_data->data;
                    // Session::save();

                   return response(['success' =>'login successfully','token'=>$get_data->token,'name'=>$get_data->name,'address'=>$get_data->address], 200);
                }
                else $this->json_error(['error' => 'something is incorrect'], 404);
            } else  $this->json_error(['error' => 'data not found'], 404);
        }
        else
            $this->json_error(['error' => 'fill out all fields'], 403);
    }

    function register_user(Request $request){

        $password=$request->password;
        if(isset($request->type)) {
            if ($request->type == 'email') {
                $value = $request->email;
            } else if ($request->type == 'cnic') {
                $value = $request->cnic;
            } else {
                $value = $request->contact;
            }
            $type=$request->type;
        }
        else {
            $get_data=usersControl::configureDataType($request->data);
            $value=$get_data[0];
            $type=$get_data[1];
        }
        

        if (isset($value,$password)) {
            if($type!='null') {
                if (usersControl::validate_Password($password) == 1) {
                    if (!peoplem::where('data', $value)->exists()) {
                        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

                        try {
                            peoplem::insert([
                                'type' => $type,
                                'password' => password_hash($password, PASSWORD_ARGON2I, ['memory_cost' => 2048, 'time_cost' => 4, 'threads' => 3]),
                                'data' => $value,
                                'creation_time' => date('Y-m-d H:i:s'),
                                'token'=> substr(str_shuffle($permitted_chars), 0, 60),
                                'name'=>$request->name,
                                'address'=>$request->address
                            ]);

                            $this->json_error(['success' => 'user created successfully'], 200);
                        } catch (QueryException $error) {
                            $this->json_error(['error' => $error->getMessage()], 403);
                        }
//                        catch (ErrorException $errorException) {
//                            $this->json_error(['error' => $errorException->getMessage()],403);
//                        }

                    } else
                        $this->json_error(['error' => $type . ' already exists'], 403);
                } else
                    $this->json_error(['error' => 'Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.'], 400);
            }
            else
                $this->json_error(['error' => 'Data is in incorrect format'], 400);


        }
        else {
            $this->json_error(['error'=>'Please fill all fields'],400);
        }

    }

    static  function validate_Password($password){
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
            return 0;
        }else{
            return 1;
        }
    }

    public function json_error($array,$status) {
        header('HTTP/1.1 '.$status);
        die(json_encode($array));
    }

    function get_user_info($username){
    }
    public function checkSession($username){
        // session()->put('tt',1);
        session_start();
        if(isset($_SESSION['private'])){
            if($_SESSION['private']==$username)
                return $_SESSION['private'];
            else return 2;
        }
        else return $_SESSION['private'];
        //     return $_SESSION['id'];

    }

    static function configureDataType($data){
        if (filter_var($data, FILTER_VALIDATE_EMAIL)) {
            return array($data,'email');
        } else if (usersControl::validate_phone_number($data) == true) {
            $filtered_phone_number = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            // Remove "-" from number
            $phone_to_check = str_replace("-", "", $filtered_phone_number);
            $phone_to_check = str_replace("+", "", $phone_to_check);
            return array($phone_to_check,'contact');
        }
        else if(usersControl::validate_cnic_number($data) == true){
            $filtered_phone_number = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            // Remove "-" from number
            $phone_to_check = str_replace("-", "", $filtered_phone_number);
            $phone_to_check = str_replace("+", "", $phone_to_check);
            return array($phone_to_check,'cnic');
        }
        else
            return array($data,'null');
    }
    static function validate_phone_number($phone)
    {
        // Allow +, - and . in phone number
        $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
        // Remove "-" from number
        $phone_to_check = str_replace("-", "", $filtered_phone_number);
        $phone_to_check = str_replace("+", "", $phone_to_check);
        // Check the lenght of number
        // This can be customized if you want phone number from a specific country
        if (strlen($phone_to_check) > 10 && strlen($phone_to_check) < 14) {
            if(substr($phone,0,2)=='03' || substr($phone,0,2)=='92' ){
                return true;
            }
            else
                return false;

        } else {
            return false;
        }
    }
    static function validate_cnic_number($phone)
    {
        // Allow +, - and . in phone number
        $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
        // Remove "-" from number
        $phone_to_check = str_replace("-", "", $filtered_phone_number);
        $phone_to_check = str_replace("+", "", $phone_to_check);
        // Check the lenght of number
        // This can be customized if you want phone number from a specific country
        if (strlen($phone_to_check) == 13) {
//
            return true;

        } else {
            return false;
        }
    }

    function categories(){
            $data=DB::table('catagories')->get();
          //  $array=[];
        foreach ($data as $datum) {
            $array[]=$datum->name;
            }
        return response()->json($array);
    }
    static function getUserID($token){
            if(DB::table('people')->where('token',$token)->exists()){
               $id= DB::table('people')->where('token',$token)->pluck('uid')->first();
            }
            else
                $id='';
            return $id;
    }

    function postReport(Request $request){
            if(usersControl::getUserID($request->token)!= ''){
                if($request->category!='') {
                    DB::table('posts')->insert([
                        'posted_by' => usersControl::getUserID($request->token),
                        'category' => $request->category,
                        'type' => $request->type=='lost'?'lost':'found',
                        'color' => $request->color,
                        'location' => $request->location,
                        'description' => $request->description,
                        'name' => $request->name,
                        'date' => $request->date,
                        'model' => $request->model,
                        'imei' => $request->imei,
                        'serial' => $request->serial,
                        'sex' => $request->sex,
                        'age' => $request->age,
                        'registration' => $request->registration,
                        'company' => $request->company,
                        'height' => $request->height,
                        'brand' => $request->brand,
                        'images' => $request->images,
                        'date_posted' => date('Y-m-d')
                    ]);
                    $this->json_error(['success' => $request->type.' report posted!','date posted'=>date('Y-m-d')], 200);

                }
                else {
                    $this->json_error(['error' => 'category is not selected'], 404);

                }
            }
            else
                $this->json_error(['error' => 'token incorrect or missing!'], 404);
    }

    function getMyPost(Request $request){
        if(usersControl::getUserID($request->token)!= ''){
           $id= usersControl::getUserID($request->token);
           $data=DB::table('posts')->where('posted_by',$id)->get();

           return response()->json($data);

        }
        else
            $this->json_error(['error' => 'token incorrect or missing!'], 404);
    }

    function get_post($post_id){
            $data= DB::table('posts')->where('post_id',$post_id)->first();
            return response()->json($data);
    }
    function validateByPost(Request $i){
        if(usersControl::getUserID($i->token)!= ''){
            $id= usersControl::getUserID($i->token);
            if(DB::table('posts')->where('post_id',$i->post_id)->where('posted_by',$id)->exists()){
                  $this->json_error(['user' => 'true'], 200);
            }
            else 
           $this->json_error(['user' => 'false'], 200);
        }
        else
        $this->json_error(['error' => 'token incorrect or missing!'], 404);
    }

    function getchatsPerUser(Request $request){
        if(usersControl::getUserID($request->token)!= ''){
            $id= usersControl::getUserID($request->token);
            $getChats=DB::table('chats')->where('chatTo',$id)->get(['message','date']);
        
            $this->json_error(['chats' => $getChats], 200);
        }
        else
        $this->json_error(['error' => 'token incorrect or missing!'], 404);
    }

    function message(Request $request){
        if(usersControl::getUserID($request->token)!= ''){
            $from= usersControl::getUserID($request->token);
            if(DB::table('posts')->where('post_id',$request->post_id)->exists()){
            $to=DB::table('posts')->where('post_id',$request->post_id)->pluck('posted_by')->first();
            DB::table('chats')->insert([
                'chatFrom'=>$from,
                'chatTo'=>$to,
                'date'=>date('d-m-Y H:i:s'),
                'message'=>$request->message
            ]);
            return  $this->json_error(['success' => 'message sent'], 200);
            }
            else
            $this->json_error(['error' => 'Post not found!'], 404);

        }
        else
        $this->json_error(['error' => 'token incorrect or missing!'], 404);
    }
    function getAllPost(){
        $data=DB::table('posts')->orderBy('post_id', 'desc')->get();
        return response()->json($data);
    }
    function specficPost(){
        if(isset($_GET['keyword'])&&isset($_GET['city'])&&isset($_GET['category'])&&isset($_GET['type'])){
            if($_GET['keyword']!=null&&$_GET['city']!=null&&$_GET['category']!=null&&$_GET['type']){
                $keyword=$_GET['keyword'];
                $city=$_GET['city'];
                $category=$_GET['category'];
                $type=$_GET['type'];
                $getD=DB::table('posts')->Where(['category'=>$category,'type'=>$type])->Where('location', 'like', '%' . $city . '%')->get();
                $dataToSend=[];
                foreach($getD as $key=>$data){
                        $string=$data->name.' '.$data->description.' '.$data->date.' '.$data->model.' '.$data->imei.' '.$data->sex.' '.$data->age.' '.$data->registration.' '.$data->company.' '.$data->height.' '.$data->brand;
                        if(strpos($string, $keyword) !== false){
                            $dataToSend[]=$data;
                        }
                        
                }
            
               return response()->json($dataToSend);
            }  
            $this->json_error(['error' => 'some arguments are missing'], 404);
        }
        else
        $this->json_error(['error' => 'some arguments are missing'], 404);
    }

    function splitString(){
            if(isset($_GET['images'])){
            $data=explode(' ',$_GET['images']);
            foreach($data as $pic){
                $splited[]=$pic;
            }
            return response()->json($splited);
        }
    }
    function delete(Request $request){
        if(usersControl::getUserID($request->token)!= ''){
            $id= usersControl::getUserID($request->token);
            if(DB::table('posts')->where('post_id',$request->post_id)->exists()){
                DB::table('posts')->where('post_id',$request->post_id)->delete();
                return  $this->json_error(['error' => 'post deleted'], 200);
            }
            else {
                $this->json_error(['error' => 'post does not exists'], 404);
            }

        }
        else 
        $this->json_error(['error' => 'token incorrect or missing!'], 404);

    }

    function admin(Request $request){
        if($request->password=='hamza1'){
                $userCount=DB::table('people')->count();
                $numberOfPosts=DB::table('posts')->count();
                $users=DB::table('people')->get();
                $array=array();
                foreach($users as $user){
                    $array[]=array('name'=>$user->name,'data-type'=>$user->type,'data'=>$user->data,'user created'=>$user->creation_time);
                }
                $posts=DB::table('posts')->get();
                $array2=array();
                foreach($posts as $post){
                    $array2[]=$post;
                }
                $this->json_error(['admin' => 'true',
                'Total number of registered users on website'=>$userCount,
                'Total numbers of posts on website' =>$numberOfPosts,
                'users data'=>$array,
                'posts data'=>$array2
            
            ], 200);
        }
        else 
        $this->json_error(['error' => 'password incorrect or missing!'], 404);
    }
    function adminDeletePost(Request $request){
        if($request->password=='hamza1'){
            DB::table('posts')->where('post_id',$request->post_id)->delete();
            $this->json_error(['success' => 'true','message'=>'if post ever existed, it is deleted !'], 200);
        }
        else 
        $this->json_error(['error' => 'password incorrect or missing!'], 404);
    }

}
