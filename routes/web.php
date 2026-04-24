<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlumnusController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ChatTicketController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\PasswordResetTokenController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SalaryMaxController;
use App\Http\Controllers\SalaryMinController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SeminarController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\UserController;


//outside of session routes
Route::get('/', function () {
    return view('/general/home');
})->name('general.home');

Route::get('/register', function () {
    return view('/auth.register');
})->name('auth.register');

Route::get('/login', function () {
    return view('/auth.login');
})->name('auth.login');

Route::get('/waitForApproval', [UserController::class, 'goToWaitForApproval'])->name('general.waitForApproval');



//in session routes
Route::get('/profile', [UserController::class, 'showProfile'])->name('user.profile');
Route::post('/user/logout', [UserController::class, 'logout'])->name('user.logout');

Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('users.editProfile');

Route::get('/alumni/change-password', function () {
    return view('alumni_change_password');
})->name('alumni.changePassword');

Route::get('/superAdmin/dashboard', function () {
    return view('superAdmin.dashboard');
})->middleware(['auth'])->name('superAdmin.dashboard');


Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth']);

Route::get('/registrar/dashboard', function () {
    return view('registrar.dashboard');
})->middleware(['auth']);

Route::get('/employer/dashboard', function () {
    return view('employer.dashboard');
})->middleware(['auth']);

Route::get('/alumni/dashboard', function () {
    return view('alumni.dashboard');
})->middleware(['auth']);

Route::get('/superAdmin/userManagement', [UserController::class, 'showUsers'])->name('superAdmin.userManagement');

Route::group(['middleware' => 'super_admin'], function () {
    //super admin routes



});
Route::group(['middleware' => 'admin'], function () {
    //admin routes

});
Route::group(['middleware' => 'registrar'], function () {
    //registrar routes

});
Route::group(['middleware' => 'employer'], function () {
    //employer routes

});
Route::group(['middleware' => 'alumni'], function () {
    //alumni routes

});



//resource routes
Route::resource('alumni', AlumnusController::class);
Route::get('/alumni/profile', [AlumnusController::class, 'showAlumniProfile'])->name('alumni.profile');
Route::put('/alumni/updateProfile/{alumnus}', [AlumnusController::class, 'updateAlumniProfile'])->name('alumni.updateProfile');


Route::resource('announcements', AnnouncementController::class);

Route::resource('job-postings', JobPostingController::class);

Route::resource('chat-tickets', ChatTicketController::class);

Route::resource('conversations', ConversationController::class);

Route::resource('employers', EmployerController::class);

Route::resource('events', EventController::class);

Route::resource('faqs', FaqController::class);

Route::resource('industries', IndustryController::class);

Route::resource('job-applications', JobApplicationController::class);

Route::resource('job-postings', JobPostingController::class);
Route::get('/jobBoard', [JobPostingController::class, 'showJobBoard'])->name('jobPosting.jobBoard');
Route::post('/jobBoard/addJobPost/{id}', [JobPostingController::class, 'addJobPost'])->name('jobPosting.addJobPost');
Route::get('/myJobPosts/{id}', [JobPostingController::class, 'showMyJobPosts'])->name('jobPosting.myJobPosts');
Route::post('/editJobPost/{id}', [JobPostingController::class, 'editJobPost'])->name('jobPosting.editJobPost');

Route::resource('messages', MessageController::class);

Route::resource('offices', OfficeController::class);

Route::resource('password-reset-tokens', PasswordResetTokenController::class);
Route::get('/forgotPassword', [PasswordResetTokenController::class, 'index'])->name('passReset.forgotPassword');
Route::post('/forgotPassword', [PasswordResetTokenController::class, 'forgetPassword'])->name('passReset.forgetPasswordPost');
Route::get('/resetPassword/{token}', [PasswordResetTokenController::class, 'resetPassword'])->name('passReset.resetPassword');
Route::post('/resetPassword', [PasswordResetTokenController::class, 'updatePassword'])->name('passReset.updatePassword');

Route::resource('programs', ProgramController::class);

Route::resource('salary-maxes', SalaryMaxController::class);

Route::resource('salary-mins', SalaryMinController::class);

Route::resource('sections', SectionController::class);

Route::resource('seminars', SeminarController::class);

Route::resource('testimonials', TestimonialController::class);

Route::resource('users', UserController::class);
Route::post('/users/storeEmployer', [UserController::class, 'storeEmployer'])->name('users.storeEmployer');
Route::post('/users/login', [UserController::class, 'login'])->name('users.login');
Route::post('/users/approve/{id}', [UserController::class, 'approveEmployer'])->name('users.approveEmployer');
Route::post('/users/addAlumnus', [UserController::class, 'addAlumnus'])->name('users.addAlumnus');
Route::post('/users/addAdmin', [UserController::class, 'addAdmin'])->name('users.addAdmin');
