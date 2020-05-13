<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes :: Authentication
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * Authentication Process Flow
 * 1 :: Login screen
 * 2 :: Multiple Attempts Failed = User Account Locked Screen (Activated = false)
 * 3 :: If Authenticated, check if user account is locked (request unlock/verification from IT support)
 * 4 :: On logged out, return to login screen with inital value of user set & focus at password field
**/
Route::post('check-username', 'AuthController@checkUsername');
Route::post('me', 'AuthController@me');
Route::post('logout', 'AuthController@logout');

/**
 * New User Account Process Flow
 * 1 :: Welcome screen, check email token validity
 * 2 :: Option to resend email token if token is valid, but has expired
 * 3 :: Update initial user profile (password, profile, bio, display photo etc...)
 * 4 :: Redirect user to login, with inital value of user set & focus at password field
**/
Route::post('check-email-token', 'AuthController@checkEmailToken');
Route::post('resend-email-token', 'AuthController@resendEmailToken');
Route::post('verify-email-login', 'AuthController@emailVerifyLogin');

/**
 * Reset Password Process Flow
 * 1 :: Requesting a reset-password email to be sent
 * 2 :: Check that reset token is valid from reset-password landing page
 * 3 :: Reset the password with valid username, token and password validations/combination
 * 4 :: Redirect user to login, with inital value of user set & focus at password field
**/
Route::post('forget-password', 'AuthController@sendResetEmail');
Route::post('check-reset-token', 'AuthController@checkResetToken');
Route::post('reset-password', 'AuthController@resetPassword');








