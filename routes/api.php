<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::post('login','usersControl@login_user');
Route::post('register','usersControl@register_user');
Route::get('checkSession/{username}','usersControl@checkSession');
Route::get('categories','usersControl@categories');
Route::post('my-reports','usersControl@getMyPost');
Route::post('report','usersControl@postReport');
Route::post('validateByPost','usersControl@validateByPost');
Route::get('posts/{post_id}','usersControl@get_post');

Route::get('getAllPost','usersControl@getAllPost');

Route::get('specficPost','usersControl@specficPost');
Route::post('getChats','usersControl@getchatsPerUser');
Route::post('sendMessage','usersControl@message');

Route::get('splitString','usersControl@splitString');
Route::post('delete','usersControl@delete');
// Route::group(['middleware'=>['user_Login']],function (){
//     Route::resource('user','peopleControl');

// });
Route::post('admin','usersControl@admin');
Route::post('admin/post/delete','usersControl@adminDeletePost');
