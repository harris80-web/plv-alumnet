<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlumniDashboardController;
use App\Http\Controllers\AlumniIdController;
use App\Http\Controllers\AlumniYearbookController;
use App\Http\Controllers\AlumnusController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ChatTicketController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\EmployerReviewController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobBookmarkController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\PasswordResetTokenController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SalaryMaxController;
use App\Http\Controllers\SalaryMinController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SeminarController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\UserController;
use App\Models\Testimonial;
use App\Http\Controllers\ResumeBuilderController;
use App\Http\Controllers\SkillSearchController;



//outside of session routes

//general
Route::get('/', function () {
    $testimonials = Testimonial::with(['alumnus.user', 'alumnus.program'])
        ->where('testimonial_post', true)
        ->latest()
        ->paginate(4)
        ->withQueryString()
        // Full page reload is unavoidable with plain pagination links, but
        // this at least lands back on the testimonials section instead of
        // the very top of the page.
        ->fragment('alumni-testimonials');
    return view('general.home', compact('testimonials'));
})->name('general.home');

Route::get('/about', function () {
    return view('general.about');
})->name('general.about');

Route::get('/register', function () {
    return view('/auth.register');
})->name('auth.register');

Route::get('/login', function () {
    return view('/auth.login');
})->name('auth.login');

Route::get('/waitForApproval', [UserController::class, 'goToWaitForApproval'])->name('general.waitForApproval');
Route::get('/dashboardRedirect', [UserController::class, 'redirectToDashboard'])->name('users.dashboardRedirect');


Route::get('/general/privacy-policy', function () {
    return view('general.privacy-policy');
})->name('general.privacy-policy');

Route::get('/general/tou', function () {
    return view('general.tou');
})->name('general.tou');

Route::get('/general/faqs', [FaqController::class, 'generalFaqs'])->name('general.faqs');

//alumni
Route::get('/alumni/about', function () {
    return view('alumni.about');
})->name('alumni.about');

Route::get('/alumni/privacy-policy', function () {
    return view('alumni.privacy-policy');
})->name('alumni.privacy-policy');

Route::get('/alumni/tou', function () {
    return view('alumni.tou');
})->name('alumni.tou');

Route::get('/alumni/faqs', [FaqController::class, 'alumniFaqs'])->name('alumni.faqs');

//employer
Route::get('/employer/about', function () {
    return view('employer.about');
})->name('employer.about');

Route::get('/employer/privacy-policy', function () {
    return view('employer.privacy-policy');
})->name('employer.privacy-policy');

Route::get('/employer/tou', function () {
    return view('employer.tou');
})->name('employer.tou');

Route::get('/employer/faqs', [FaqController::class, 'employerFaqs'])->name('employer.faqs');

//in session routes
Route::get('/profile', [UserController::class, 'showProfile'])->name('user.profile');
Route::post('/user/logout', [UserController::class, 'logout'])->name('user.logout');

Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('users.editProfile');

Route::get('/alumni/change-password', function () {
    return view('alumni_change_password');
})->name('alumni.changePassword');

Route::get('/superAdmin/dashboard', [UserController::class, 'showDashboard'])->name('superAdmin.dashboard');
Route::get('/superAdmin/dashboard/export-csv', [UserController::class, 'exportDashboardReport'])->name('superAdmin.dashboard.exportCsv')->middleware('auth');
Route::get('/superAdmin/dashboard/export-pdf', [UserController::class, 'exportDashboardReportPdf'])->name('superAdmin.dashboard.exportPdf')->middleware('auth');
Route::get('/superAdmin/profile', [UserController::class, 'showSuperAdminProfile'])->name('superAdmin.profile');


Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth'])->name('admin.dashboard');

Route::get('/registrar/dashboard', function () {
    return view('registrar.dashboard');
})->middleware(['auth'])->name('registrar.dashboard');

Route::get('/employer/dashboard', [EmployerController::class, 'dashboard'])->middleware(['auth'])->name('employer.dashboard');

Route::get('/alumni/dashboard', [AlumniDashboardController::class, 'index'])->middleware(['auth'])->name('alumnus.dashboard');

Route::get('/superAdmin/userManagement', [UserController::class, 'showUsers'])->name('superAdmin.userManagement');

Route::get('/alumniIdManagement', [AlumniIdController::class, 'index'])->name('alumniId.management')->middleware('auth');
Route::post('/alumniId/{id}/mark', [AlumniIdController::class, 'mark'])->name('alumniId.mark')->middleware('auth');
Route::post('/alumniId/{id}/updateStatus', [AlumniIdController::class, 'updateStatus'])->name('alumniId.updateStatus')->middleware('auth');
Route::post('/alumniId/bulkUpdateStatus', [AlumniIdController::class, 'bulkUpdateStatus'])->name('alumniId.bulkUpdateStatus')->middleware('auth');

Route::post('/alumniYearbook/{id}/update', [AlumniYearbookController::class, 'updateYearbook'])->name('alumniYearbook.update')->middleware('auth');
Route::post('/alumniYearbook/bulkUpdate', [AlumniYearbookController::class, 'bulkUpdateYearbook'])->name('alumniYearbook.bulkUpdate')->middleware('auth');

Route::get('/notices', [NoticeController::class, 'index'])->name('notices.management')->middleware('auth');
Route::post('/notices', [NoticeController::class, 'store'])->name('notices.store')->middleware('auth');
Route::put('/notices/{notice}', [NoticeController::class, 'update'])->name('notices.update')->middleware('auth');
Route::delete('/notices/{notice}', [NoticeController::class, 'destroy'])->name('notices.destroy')->middleware('auth');

Route::get('/alumni/events-seminars', [NoticeController::class, 'alumniEventsAndSeminars'])->name('notices.eventsSeminars')->middleware('auth');
Route::get('/alumni/announcements', [NoticeController::class, 'alumniAnnouncements'])->name('notices.announcements')->middleware('auth');
Route::get('/employer/events-seminars', [NoticeController::class, 'employerEventsAndSeminars'])->name('notices.employerEventsSeminars')->middleware('auth');
Route::get('/employer/announcements', [NoticeController::class, 'employerAnnouncements'])->name('notices.employerAnnouncements')->middleware('auth');
Route::post('/notices/{notice}/toggleInterest', [NoticeController::class, 'toggleInterest'])->name('notices.toggleInterest')->middleware('auth');

Route::get('/faqManagement', [FaqController::class, 'index'])->name('faqs.management')->middleware('auth');
Route::post('/faqs', [FaqController::class, 'store'])->name('faqs.store')->middleware('auth');
Route::post('/faqs/bulkDelete', [FaqController::class, 'bulkDestroy'])->name('faqs.bulkDestroy')->middleware('auth');
Route::post('/faqs/bulkUpdateRecipient', [FaqController::class, 'bulkUpdateRecipient'])->name('faqs.bulkUpdateRecipient')->middleware('auth');
Route::put('/faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update')->middleware('auth');
Route::delete('/faqs/{faq}', [FaqController::class, 'destroy'])->name('faqs.destroy')->middleware('auth');

// ── Bell icon notifications (alumni + employer) ──
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware('auth');
Route::post('/notifications/markAllRead', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead')->middleware('auth');

// ── Floating chatbot widget (alumni + employer) ──
Route::post('/chatbot/start', [ChatbotController::class, 'start'])->name('widget.start')->middleware('auth');
Route::post('/chatbot/{ticket}/send', [ChatbotController::class, 'send'])->name('widget.send')->middleware('auth');
Route::get('/chatbot/{ticket}/poll', [ChatbotController::class, 'poll'])->name('widget.poll')->middleware('auth');

// ── Admin: Chatbot & Messaging management ──
Route::get('/chatbotMessaging', [ChatTicketController::class, 'index'])->name('chatbot.management')->middleware('auth');
Route::post('/chatbotMessaging/settings', [ChatTicketController::class, 'updateSettings'])->name('chatbot.settings.update')->middleware('auth');
Route::post('/chatbotMessaging/flags/{messageFlag}/action', [ChatTicketController::class, 'flagAction'])->name('chatbot.flagAction')->middleware('auth');
Route::get('/chatbotMessaging/queue', [ChatTicketController::class, 'queueJson'])->name('chatbot.queue')->middleware('auth');
Route::get('/chatbotMessaging/{ticket}/thread', [ChatTicketController::class, 'threadJson'])->name('chatbot.thread')->middleware('auth');
Route::post('/chatbotMessaging/{ticket}/claim', [ChatTicketController::class, 'claim'])->name('chatbot.claim')->middleware('auth');
Route::post('/chatbotMessaging/{ticket}/reply', [ChatTicketController::class, 'reply'])->name('chatbot.reply')->middleware('auth');
Route::post('/chatbotMessaging/{ticket}/resolve', [ChatTicketController::class, 'resolve'])->name('chatbot.resolve')->middleware('auth');

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
Route::get('/alumni/profile', [AlumnusController::class, 'showAlumniProfile'])->name('alumni.profile');
Route::put('/alumni/updateProfile/{alumnus}', [AlumnusController::class, 'updateAlumniProfile'])->name('alumni.updateProfile');
Route::put('/alumni/deactivate/{id}', [AlumnusController::class, 'deactivateAlumnus'])->name('alumni.deactivateAlumnus');
Route::put('/alumni/activate/{id}', [AlumnusController::class, 'activateAlumnus'])->name('alumni.activateAlumnus');
Route::resource('alumni', AlumnusController::class)->middleware('auth');


Route::resource('announcements', AnnouncementController::class);

Route::get('/messages', [ConversationController::class, 'index'])->name('messages.index')->middleware('auth');
Route::get('/messages/contacts/search', [ConversationController::class, 'searchContacts'])->name('messages.searchContacts')->middleware('auth');
Route::get('/messages/sidebar-poll', [ConversationController::class, 'sidebarPoll'])->name('messages.sidebarPoll')->middleware('auth');
Route::post('/messages/start', [ConversationController::class, 'start'])->name('messages.start')->middleware('auth');
Route::get('/messages/{conversation}', [ConversationController::class, 'show'])->name('messages.show')->middleware('auth');
Route::post('/messages/{conversation}/send', [MessageController::class, 'store'])->name('messages.send')->middleware('auth');
Route::get('/messages/{conversation}/poll', [MessageController::class, 'poll'])->name('messages.poll')->middleware('auth');

Route::resource('employers', EmployerController::class);
Route::put('/employers/updateProfile/{employer}', [EmployerController::class, 'updateEmployerProfile'])->name('employers.updateProfile');
Route::put('/employer/deactivate/{id}', [EmployerController::class, 'deactivateEmployer'])->name('employers.deactivateEmployer');

Route::resource('events', EventController::class);

Route::resource('industries', IndustryController::class);

Route::resource('job-applications', JobApplicationController::class);
Route::post('/jobBoard/applyJob/{jobPostingId}', [JobApplicationController::class, 'applyJob'])->name('jobApplication.apply');
Route::get('/jobBoard/applications/{jobPostingId}', [JobApplicationController::class, 'showApplications'])->name('jobApplication.showApplications');
Route::post('/jobBoard/hireApplicant/{applicationId}', [JobApplicationController::class, 'hireApplicant'])->name('jobApplication.hireApplicant');
Route::post('/jobBoard/declineApplicant/{applicationId}', [JobApplicationController::class, 'declineApplicant'])->name('jobApplication.declineApplicant');
Route::post('/jobBoard/shortlistApplicant/{applicationId}', [JobApplicationController::class, 'shortlistApplicant'])->name('jobApplication.shortlistApplicant');
Route::post('/jobBoard/bulkHireApplicants/{jobPostingId}', [JobApplicationController::class, 'bulkHireApplicants'])->name('jobApplication.bulkHireApplicants');
Route::post('/jobBoard/bulkDeclineApplicants/{jobPostingId}', [JobApplicationController::class, 'bulkDeclineApplicants'])->name('jobApplication.bulkDeclineApplicants');
Route::post('/jobBoard/bulkShortlistApplicants/{jobPostingId}', [JobApplicationController::class, 'bulkShortlistApplicants'])->name('jobApplication.bulkShortlistApplicants');


Route::resource('job-postings', JobPostingController::class);
Route::get('/jobBoard', [JobPostingController::class, 'showJobBoard'])->name('jobPosting.jobBoard');
Route::get('/jobBoard/bookmarks', [JobPostingController::class, 'showBookmarks'])->name('jobPosting.bookmarks');
Route::get('/jobBoard/myApplications', [JobPostingController::class, 'showMyApplications'])->name('jobPosting.myApplications')->middleware('auth');
Route::post('/jobBoard/addJobPost/{id}', [JobPostingController::class, 'addJobPost'])->name('jobPosting.addJobPost');
Route::post('/jobBoard/toggleBookmark/{jobPostingId}', [JobBookmarkController::class, 'toggle'])->name('jobBookmark.toggle');
Route::post('/companies/{employer}/vote', [EmployerReviewController::class, 'vote'])->name('employerReviews.vote')->middleware('auth');
Route::get('/companies/{employer}/reviews', [EmployerReviewController::class, 'reviews'])->name('employerReviews.index');
Route::post('/job-postings/upload-image', [JobPostingController::class, 'uploadDescriptionImage'])->name('jobPosting.uploadDescriptionImage');
Route::get('/myJobPosts/{id}', [JobPostingController::class, 'showMyJobPosts'])->name('jobPosting.myJobPosts');
Route::post('/editJobPost/{id}', [JobPostingController::class, 'editJobPost'])->name('jobPosting.editJobPost');
Route::get('/jobManagement', [JobPostingController::class, 'showJobManagement'])->name('jobPosting.jobManagement');
Route::post('/approveJobPost/{id}', [JobPostingController::class, 'approveJobPost'])->name('jobPosting.approve');
Route::post('/declineJobPost/{id}', [JobPostingController::class, 'declineJobPost'])->name('jobPosting.decline');
Route::delete('/deleteJobPost/{id}', [JobPostingController::class, 'deleteJobPost'])->name('jobPosting.delete');

Route::resource('offices', OfficeController::class);
Route::put('/offices/updateProfile/{office}', [OfficeController::class, 'updateOfficeProfile'])->name('offices.updateProfile');
Route::delete('/admin/delete/{id}', [OfficeController::class, 'deleteAdmin'])->name('offices.deleteAdmin');

Route::resource('password-reset-tokens', PasswordResetTokenController::class);
Route::get('/forgotPasswordForm', [PasswordResetTokenController::class, 'index'])->name('passReset.forgotPassword');
Route::post('/forgotPassword', [PasswordResetTokenController::class, 'forgetPassword'])->name('passReset.forgetPasswordPost');
Route::get('/resetPassword/{token}', [PasswordResetTokenController::class, 'resetPassword'])->name('passReset.resetPassword');
Route::post('/resetPassword', [PasswordResetTokenController::class, 'updatePassword'])->name('passReset.updatePassword');

Route::resource('programs', ProgramController::class);

Route::resource('salary-maxes', SalaryMaxController::class);

Route::resource('salary-mins', SalaryMinController::class);

Route::resource('sections', SectionController::class);

Route::resource('seminars', SeminarController::class);

Route::post('/submitTestimonial/{id}', [TestimonialController::class, 'submitTestimonial'])->name('testimonials.submit');
Route::put('/testimonials/bulk-post', [TestimonialController::class, 'bulkPost'])->name('testimonials.bulkPost');
Route::put('/testimonials/bulk-hide', [TestimonialController::class, 'bulkHide'])->name('testimonials.bulkHide');
Route::put('/testimonials/bulk-delete', [TestimonialController::class, 'bulkDelete'])->name('testimonials.bulkDelete');
Route::put('/postTestimonial/{id}', [TestimonialController::class, 'postTestimonial'])->name('testimonials.post');
Route::delete('/deleteTestimonial/{id}', [TestimonialController::class, 'deleteTestimonial'])->name('testimonials.delete');
// Must come before Route::resource('testimonials', ...) below — that
// resource registers GET /testimonials/{testimonial}, which otherwise
// greedily matches "cards" as an id and 404s before this route is ever
// reached (same reason the bulk-* routes above also precede it).
Route::get('/testimonials/cards', [TestimonialController::class, 'cardsFragment'])->name('testimonials.cardsFragment');
Route::resource('testimonials', TestimonialController::class);
Route::get('/testimonialManagement', [TestimonialController::class, 'showTestimonials'])->name('testimonials.manage');


Route::post('/users/storeEmployer', [UserController::class, 'storeEmployer'])->name('users.storeEmployer');
Route::post('/users/login', [UserController::class, 'login'])->name('users.login');
Route::post('/users/approve/{id}', [UserController::class, 'approveEmployer'])->name('users.approveEmployer');
Route::put('/users/reject/{id}', [UserController::class, 'rejectEmployer'])->name('users.rejectEmployer');
Route::post('/users/addAlumnus', [UserController::class, 'addAlumnus'])->name('users.addAlumnus');
Route::get('/users/alumni/csv-template', [UserController::class, 'downloadAlumniCsvTemplate'])->name('users.downloadAlumniCsvTemplate')->middleware('auth');
Route::post('/users/alumni/import-csv', [UserController::class, 'importAlumniCsv'])->name('users.importAlumniCsv')->middleware('auth');
Route::get('/users/alumni/export-csv', [UserController::class, 'exportAlumniCsv'])->name('users.exportAlumniCsv')->middleware('auth');
Route::put('/users/alumni/bulk-deactivate', [UserController::class, 'bulkDeactivateAlumni'])->name('users.bulkDeactivateAlumni')->middleware('auth');
Route::post('/users/addAdmin', [UserController::class, 'addAdmin'])->name('users.addAdmin');
Route::get('/showChangePassword', [UserController::class, 'showChangePassword'])->name('users.showChangePassword');
Route::put('/changePassword', [UserController::class, 'changePassword'])->name('users.changePassword');
Route::resource('users', UserController::class);

Route::get('/alumnus/resume', [ResumeBuilderController::class, 'view'])->name('resume.view');
Route::get('/resume/build', [ResumeBuilderController::class, 'edit'])->name('resume.build');
Route::post('/resume/save', [ResumeBuilderController::class, 'save'])->name('resume.save');
Route::get('/resume/pdf', [ResumeBuilderController::class, 'downloadPdf'])->name('resume.pdf');
Route::post('/resume/import', [ResumeBuilderController::class, 'import'])->name('resume.import');
Route::get('/resume/view/{alumnusId}', [ResumeBuilderController::class, 'viewApplicantResume'])->name('resume.viewApplicant');

Route::get('/skills/search', [SkillSearchController::class, 'search'])->name('skills.search');