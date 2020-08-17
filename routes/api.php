<?php

use Illuminate\Support\Facades\Route;


// Public routes
Route::get('me', 'User\MeController@getMe');

// Get designs
Route::get('designs', 'Designs\DesignController@index');
Route::get('designs/{id}', 'Designs\DesignController@findDesign');
Route::get('designs/slug/{slug}', 'Designs\DesignController@findBySlug');

// Get users
Route::get('users', 'User\UserController@search');
Route::get('user/{username}', 'User\UserController@findByUsername');
Route::get('users/{id}/designs', 'Designs\DesignController@getForUser');

// Get Team
Route::get('teams/slug/{slug}', 'Teams\TeamsController@findBySlug');
Route::get('teams/{id}/designs', 'Designs\DesignController@getForTeam');

// Search Designs
Route::get('search/designs', 'Designs\DesignController@search');
Route::get('search/designers', 'User\UserController@search');
Route::get('search/designs/{tag}/tag', 'Designs\DesignController@searchByTagName');
Route::get('search/designs/tags/{tags}', 'Designs\DesignController@searchByTags');
Route::get('search/designs/{user}/user', 'Designs\DesignController@getForUserFront');
Route::get('search/designs/{user}/usermore/{id}', 'Designs\DesignController@getForUserWhereNotIn');
Route::get('/designs/{id}/totalLikes', 'Designs\DesignController@totalLikes');

// Get Likes 
Route::get('search/designs/last', 'Designs\DesignController@lastDesigns');

// Route group for authenticated users only
Route::group(['middleware'=>['auth:api']], function(){
    Route::post('logout', 'Auth\LoginController@logout');

    Route::put('settings/profile', 'User\SettingsController@updateProfile');
    Route::put('settings/password', 'User\SettingsController@updatePassword');

    // Upload designs
    Route::post('designs', 'Designs\UploadController@upload');
    Route::put('designs/{id}', 'Designs\DesignController@update');
    Route::put('designs/{id}/updateIsLive', 'Designs\DesignController@updateIsLive');
    Route::get('designs/{id}/byUser', 'Designs\DesignController@userOwnsDesign');
    Route::delete('designs/{id}', 'Designs\DesignController@destroy');

    
    // Likes and Unlikes
    Route::post('designs/{id}/like', 'Designs\DesignController@like');
    Route::get('designs/{id}/liked', 'Designs\DesignController@checkIfUserHasLiked');

    // Comments
    Route::post('designs/{id}/comments', 'Designs\CommentController@store');
    Route::put('comments/{id}', 'Designs\CommentController@update');
    Route::delete('comments/{id}', 'Designs\CommentController@destroy');

    // Teams
    Route::post('teams', 'Teams\TeamsController@store');
    Route::get('teams/{id}', 'Teams\TeamsController@findById');
    Route::get('teams', 'Teams\TeamsController@index');
    Route::get('users/teams/', 'Teams\TeamsController@fetchUserTeams');
    Route::put('teams/{id}', 'Teams\TeamsController@update');
    Route::delete('teams/{id}', 'Teams\TeamsController@destroy');
    Route::delete('teams/{team_id}/users/{user_id}', 'Teams\TeamsController@removeFromTeam');

    // Invitations
    Route::post('invitations/{teamId}', 'Teams\InvitationsController@invite');
    Route::post('invitations/{id}/resend', 'Teams\InvitationsController@resend');
    Route::post('invitations/{id}/respond', 'Teams\InvitationsController@respond');
    Route::delete('invitations/{id}/', 'Teams\InvitationsController@destroy');

    // Chats
    Route::post('chats', 'Chats\ChatController@sendMessage');
    Route::get('chats', 'Chats\ChatController@getUserChats');
    Route::get('chats/{id}/messages', 'Chats\ChatController@getChatMessages');
    Route::put('chats/{id}/markAsRead', 'Chats\ChatController@markAsRead');
    Route::delete('messages/{id}', 'Chats\ChatController@destroyMessage');
    
});

// Route group for guest users only
Route::group(['middleware' => ['guest:api']], function(){
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('verification/verify/{user}', 'Auth\VerificationController@verify')->name('verification.verify');
    Route::post('verification/resend', 'Auth\VerificationController@resend');
    Route::post('login', 'Auth\LoginController@login');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');    
});