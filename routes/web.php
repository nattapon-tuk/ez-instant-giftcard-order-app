<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});



//TODO: to temp test
//Route::get('/get-user', function (){
//    //$users = DB::select('select * from users where name = ?', [1]);
//    $users = DB::select('select * from users');
//    dump($users);
//    return "get-user";
//});
//
//Route::get('/add-user', function(){
//    $generatedPassword = bin2hex(random_bytes(8));
//    $tempName = substr($generatedPassword, 0, 8);
//    $tempEmail = $tempName.".doetest@gmail.com";
//
//    $result = DB::insert('insert into users (name, email, password) values (?, ?, ?)',
//        [$tempName, $tempEmail, $generatedPassword]);
//
//    dump($result);
//    return "add-user";
//});


