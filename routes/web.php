<?php
use app\Http\Kernel;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SlotController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\SupervisorProfileController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\StudentProjectController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\CoordinatorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatRoomController;
use App\Http\Controllers\FeedbackController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/google/auth', [GoogleCalendarController::class, 'redirectToGoogle'])->name('google.auth');
Route::get('/oauth2callback', [GoogleCalendarController::class, 'handleGoogleCallback']);

Route::group(['middleware' => ['auth', 'google.connected']], function () {
    Route::get('/dashboard', [HomeController::class, 'redirectToDashboard'])->name('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/projects/{projectId}/tasks', [ProjectController::class, 'getTasks'])->name('project.get_tasks');
    Route::post('/project/{projectId}/user/{userId}/add-task', [ProjectController::class, 'addTask'])->name('project.add_task');
    Route::put('/task/{taskId}', [ProjectController::class, 'updateTask']);
    Route::delete('/task/{taskId}', [ProjectController::class, 'deleteTask']);
    Route::get('/chatroom/messages/{roomId}', [ChatRoomController::class, 'fetchMessages']);
    Route::get('/appointments/check-google-auth', [AppointmentController::class, 'checkGoogleAuth'])->name('appointments.checkGoogleAuth');
    Route::get('/feedback/create', [FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
    Route::post('/session/update', function (Request $request) {
      if ($request->has('google_auth_shown')) {
          session()->put('google_auth_shown', true);
          \Log::info('Session updated: google_auth_shown set to true.');
      } else {
          \Log::warning('Session update failed: google_auth_shown not found in request.');
      }
      return response()->json(['success' => true]);
  })->name('session.update');

  Route::post('/chatroom/active', [ChatroomController::class, 'markActive'])->name('chatroom.active');

});

// Admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/index', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/verify-coordinator/{supervisor}', [AdminController::class, 'verifyCoordinator'])->name('admin.verify.coordinator');
    Route::post('/admin/demote-coordinator/{supervisor}', [AdminController::class, 'demoteCoordinator'])->name('admin.demote.coordinator');
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback/{id}/resolve', [FeedbackController::class, 'resolve'])->name('feedback.resolve');
});


Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');


Route::middleware(['auth', 'role:coordinator'])->group(function () {
    //Route::get('/coordinator/dashboard', [HomeController::class, 'coordinatorDashboard'])->name('coordinator.dashboard');
    Route::get('/supervisor/dashboard', [HomeController::class, 'supervisorDashboard'])->name('supervisor.dashboard');
    Route::get('/supervisor/profile', [SupervisorProfileController::class, 'edit'])->name('supervisor.profile.edit');
    
    Route::post('/supervisor/profile', [SupervisorProfileController::class, 'update'])->name('supervisor.profile.update');
    Route::get('/coordinator/dashboard', [CoordinatorController::class, 'dashboard'])->name('coordinator.dashboard');
                                                //SUPERVISOR APPOINTMENT
    Route::get('/supervisor/appointments/upcoming', [AppointmentController::class, 'upcomingSupervisor'])->name('supervisor.appointments.upcoming');
    Route::get('/supervisor/appointments/past', [AppointmentController::class, 'pastSupervisor'])->name('supervisor.appointments.past');
    Route::get('/slots', [SlotController::class, 'index'])->name('slots.index');
    Route::get('/slots/create', [SlotController::class, 'create'])->name('slots.create');
    Route::post('/slots', [SlotController::class, 'store'])->name('slots.store');
    Route::get('slots/{slot}/edit', [SlotController::class, 'edit'])->name('slots.edit');
    Route::put('slots/{slot}/edit', [SlotController::class, 'update'])->name('slots.update');
    Route::delete('/slots/{slot}', [SlotController::class, 'destroy'])->name('slots.destroy');
    Route::get('supervisor/appointments/manage', [AppointmentController::class, 'manage'])->name('supervisor.appointments.manage');
    Route::post('supervisor/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('supervisor.appointments.updateStatus');
    Route::post('supervisor/appointments/{appointment}/updateMeetingDetails', [AppointmentController::class, 'updateMeetingDetails'])->name('supervisor.appointments.updateMeetingDetails');
    Route::post('appointments/cancel/{appointment}', [AppointmentController::class, 'cancelWithReason'])->name('appointments.cancel');

                                                    //SUPERVISOR PROJECT
    Route::get('supervisor/projects', [ProjectController::class, 'index'])->name('supervisor.projects.index');
    Route::get('supervisor/projects/create', [ProjectController::class, 'create'])->name('supervisor.projects.create');
    Route::post('supervisor/projects', [ProjectController::class, 'store'])->name('supervisor.projects.store');
    Route::get('supervisor/projects/{project}', [ProjectController::class, 'show'])->name('supervisor.projects.show');
    Route::get('supervisor/projects/{project}/edit', [ProjectController::class, 'edit'])->name('supervisor.projects.edit');
    Route::put('supervisor/projects/{project}', [ProjectController::class, 'update'])->name('supervisor.projects.update');
    Route::delete('supervisor/projects/{project}', [ProjectController::class, 'destroy'])->name('supervisor.projects.destroy');
    Route::get('supervisor/profile/{id}', [SupervisorProfileController::class, 'show'])->name('supervisor.profile.show'); // View other supervisor's profile
    Route::get('/supervisor/students/index', [SupervisorProfileController::class, 'index'])->name('supervisor.students.index');
   // Route::get('/students/profile/{id}', [StudentProfileController::class, 'show'])->name('students.profile.show');
    Route::get('/supervisor/students/projects/index', [ProjectController::class, 'indexSupervisorProjects'])->name('supervisor.students.projects.index');
    Route::get('/supervisor/students/{student}/projects', [ProjectController::class, 'viewStudentProject'])->name('supervisor.students.projects.view_student_project');
    Route::get('/supervisor/students/projects/view', [ProjectController::class, 'viewSupervisorProject'])->name('supervisor.students.projects.view');
    Route::post('/supervisor/students/projects/comment/{fileId}', [ProjectController::class, 'addComment'])->name('supervisor.students.projects.comment');
     // Route::get('students/profile/{id}', [StudentProfileController::class, 'show'])->name('students.profile.show'); // View student's profile
    // Route::get('/supervisor/students/index', [StudentProfileController::class, 'index'])->name('supervisor.students.index');


                                                //SUPERVISOR'S LOGBOOK
     Route::get('/logbook/file/{logbookFile}', [LogbookController::class, 'showFile'])->name('students.logbook.showFile');
    Route::post('/supervisor/students/projects/comment/{file}', [ProjectController::class, 'addComment'])->name('supervisor.students.projects.comment');
    Route::get('supervisor/logbook/{project}', [LogbookController::class, 'index'])->name('supervisor.logbook.index');
    Route::post('supervisor/logbook/verify/{logbookEntry}', [LogbookController::class, 'verify'])->name('supervisor.logbook.verify');
    Route::get('supervisor/logbook/{student}', [LogbookController::class, 'viewStudentLogbooks'])->name('supervisor.logbook.view_student');
  //  Route::post('supervisor/logbook/{logbook}/verify', [LogbookController::class, 'verify'])->name('supervisor.logbook.verify');
  //Route::get('/chatroom/{id}', [ChatRoomController::class, 'show'])->name('chatroom.show');
  Route::get('/coordinator/logbook', [CoordinatorController::class, 'viewDepartmentLogbooks'])->name('coordinator.logbook');
    
});

Route::middleware(['auth', 'role:supervisor',])->group(function () {
   // Route::get('/supervisor/dashboard', [HomeController::class, 'supervisorDashboard'])->name('supervisor.dashboard');
    Route::get('/supervisor/profile', [SupervisorProfileController::class, 'edit'])->name('supervisor.profile.edit');
    Route::post('/supervisor/profile', [SupervisorProfileController::class, 'update'])->name('supervisor.profile.update');
    Route::get('/coordinator/dashboard', [CoordinatorController::class, 'dashboard'])->name('coordinator.dashboard');
    Route::match(['get', 'post'], '/supervisor/dashboard', [HomeController::class, 'supervisorDashboard'])->name('supervisor.dashboard');
   // Route::get('/students/profile/{id}', [StudentProfileController::class, 'show'])->name('students.profile.show');

    //Route::get('/supervisor/profile/edit', [SupervisorProfileController::class, 'edit'])->name('supervisor.profile.edit');
  //  Route::post('/supervisor/profile/update', [SupervisorProfileController::class, 'update'])->name('supervisor.profile.update');
  Route::get('/upcoming/{slotId?}', [AppointmentController::class, 'upcoming'])->name('appointments.upcoming');
                                                //SUPERVISOR APPOINTMENT
    Route::get('/supervisor/appointments/upcoming', [AppointmentController::class, 'upcomingSupervisor'])->name('supervisor.appointments.upcoming');
    Route::get('/supervisor/appointments/past', [AppointmentController::class, 'pastSupervisor'])->name('supervisor.appointments.past');
    Route::get('/slots', [SlotController::class, 'index'])->name('slots.index');
    Route::get('/slots/create', [SlotController::class, 'create'])->name('slots.create');
    Route::post('/slots', [SlotController::class, 'store'])->name('slots.store');
    //Route::get('slots/{slot}/edit', [SlotController::class, 'edit'])->name('slots.edit');
  
    Route::delete('/slots/{slot}', [SlotController::class, 'destroy'])->name('slots.destroy');
    Route::get('supervisor/appointments/manage', [AppointmentController::class, 'manage'])->name('supervisor.appointments.manage');
    Route::post('supervisor/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('supervisor.appointments.updateStatus');
    Route::post('supervisor/appointments/{appointment}/updateMeetingDetails', [AppointmentController::class, 'updateMeetingDetails'])->name('supervisor.appointments.updateMeetingDetails');
    Route::post('appointments/cancel/{appointment}', [AppointmentController::class, 'cancelWithReason'])->name('appointments.cancel');
    Route::get('/coordinator/logbook/{id}', [CoordinatorController::class, 'viewDepartmentLogbook'])->name('coordinator.logbook');
    Route::get('/slots/{slot}/edit-modal', [SlotController::class, 'editModal'])->name('slots.editModal');

    Route::put('/slots/{slot}', [SlotController::class, 'update'])->name('slots.update');
                                                    //SUPERVISOR PROJECT
    Route::get('supervisor/projects', [ProjectController::class, 'index'])->name('supervisor.projects.index');
    Route::get('supervisor/projects/create', [ProjectController::class, 'create'])->name('supervisor.projects.create');
    Route::post('supervisor/projects', [ProjectController::class, 'store'])->name('supervisor.projects.store');
    Route::get('supervisor/projects/{project}', [ProjectController::class, 'show'])->name('supervisor.projects.show');
    Route::get('supervisor/projects/{project}/edit', [ProjectController::class, 'edit'])->name('supervisor.projects.edit');
    Route::put('supervisor/projects/{project}', [ProjectController::class, 'update'])->name('supervisor.projects.update');
    Route::delete('supervisor/projects/{project}', [ProjectController::class, 'destroy'])->name('supervisor.projects.destroy');
    Route::get('supervisor/profile/{id}', [SupervisorProfileController::class, 'show'])->name('supervisor.profile.show'); // View other supervisor's profile
    Route::get('/supervisor/students/index', [SupervisorProfileController::class, 'index'])->name('supervisor.students.index');
   
    Route::get('/supervisor/students/projects/index', [ProjectController::class, 'indexSupervisorProjects'])->name('supervisor.students.projects.index');
    Route::get('/supervisor/students/{student}/projects', [ProjectController::class, 'viewStudentProject'])->name('supervisor.students.projects.view_student_project');
    Route::get('/supervisor/students/projects/view/{id}', [ProjectController::class, 'viewSupervisorProject'])->name('supervisor.students.projects.view');
    Route::post('/supervisor/students/projects/comment/{fileId}', [ProjectController::class, 'addComment'])->name('supervisor.students.projects.comment');
     // Route::get('students/profile/{id}', [StudentProfileController::class, 'show'])->name('students.profile.show'); // View student's profile
    // Route::get('/supervisor/students/index', [StudentProfileController::class, 'index'])->name('supervisor.students.index');


                                                //SUPERVISOR'S LOGBOOK
     Route::get('/logbook/file/{logbookFile}', [LogbookController::class, 'showFile'])->name('students.logbook.showFile');
    Route::post('/supervisor/students/projects/comment/{file}', [ProjectController::class, 'addComment'])->name('supervisor.students.projects.comment');
    Route::get('supervisor/logbook/{project}', [LogbookController::class, 'index'])->name('supervisor.logbook.index');
    Route::post('supervisor/logbook/verify/{logbookEntry}', [LogbookController::class, 'verify'])->name('supervisor.logbook.verify');
    Route::get('supervisor/logbook/{student}', [LogbookController::class, 'viewStudentLogbooks'])->name('supervisor.logbook.view_student');
  //  Route::post('supervisor/logbook/{logbook}/verify', [LogbookController::class, 'verify'])->name('supervisor.logbook.verify');

  Route::get('supervisor/students/projects/{projectFile}/view', [ProjectController::class, 'viewFile'])->name('supervisor.students.projects.viewFile');
  Route::get('/students/profiless/{id}', [StudentProfileController::class, 'show'])->name('students.profile.show');
  
  Route::get('/coordinator/assigned-projects', [CoordinatorController::class, 'showAssignedProjects'])->name('coordinator.assigned_projects');
  Route::get('/coordinator/total-projects', [CoordinatorController::class, 'projects'])->name('coordinator.total_project');
  Route::get('/chatroom/{id}', [ChatRoomController::class, 'show'])->name('chatroom.show');
  Route::delete('/chatroom/{id}/delete', [ChatRoomController::class, 'destroy'])->name('chatroom.delete');
 // Route::post('/chatroom/{id}/message', [ChatRoomController::class, 'sendMessage'])->name('chatroom.message.send');
 
 //Route::post('/chatroom/{id}/supervisor/message', [ChatRoomController::class, 'sendSupervisorMessage'])->name('chatroom.supervisor.message.send')->middleware('auth');

 //Route::post('/chatroom/send-message/{id}', [ChatRoomController::class, 'sendSupervisorMessage'])->name('chatroom.supervisor.message.send');
 Route::post('/chatroom/send-supervisor-message/{id}', [ChatRoomController::class, 'sendSupervisorMessage'])->name('chatroom.supervisor.message.send');
 //Route::get('/supervisor/dashboard', [HomeController::class, 'supervisorDashboard'])->name('supervisor.dashboard');
Route::post('/supervisor/announcement', [HomeController::class, 'postAnnouncement'])->name('supervisor.announcement.post');
Route::post('/announcement/edit/{id}', [HomeController::class, 'editAnnouncement'])->name('supervisor.announcement.edit');
Route::delete('/announcement/delete/{id}', [HomeController::class, 'deleteAnnouncement'])->name('supervisor.announcement.delete');

Route::patch('/chatroom/messagesEdit/{message}', [ChatRoomController::class, 'editMessage'])->name('messages.edit');
Route::delete('/chatroom/messages/{message}', [ChatRoomController::class, 'deleteMessage'])->name('messages.delete');


 Route::get('/projects/{project}/create-submission', [ProjectController::class, 'createSubmission'])->name('projects.createSubmission');
 Route::post('/projects/{project}/store-submission', [ProjectController::class, 'storeSubmission'])->name('projects.storeSubmission');
 Route::get('/projects/edit-submission/{projectFile}', [ProjectController::class, 'editSubmission'])->name('projects.editSubmission');
 Route::put('/projects/update-submission/{projectFile}', [ProjectController::class, 'updateSubmission'])->name('projects.updateSubmission');
 Route::delete('/projects/file/{fileId}', [ProjectController::class, 'deleteFile'])->name('projects.deleteFile');

 Route::get('/coordinator/students', [CoordinatorController::class, 'studentsList'])->name('coordinator.students');
 Route::get('/supervisor/leaderboard', [ProjectController::class, 'showLeaderboard'])->name('supervisor.leaderboard');

 
//  Route::get('/projects/{projectId}/tasks', [ProjectController::class, 'getTasks'])->name('project.get_tasks');
//  Route::post('/project/{projectId}/user/{userId}/add-task', [ProjectController::class, 'addTask'])->name('project.add_task');
//  // Fetch tasks for Gantt chart in this project view
//  //Route::get('/supervisor/students/{student}/projects/{project}/tasks', [ProjectController::class, 'getTasks'])->name('projects.getTasks');
//     Route::put('/task/{taskId}', [ProjectController::class, 'updateTask']);
//     Route::delete('/task/{taskId}', [ProjectController::class, 'deleteTask']);
    Route::delete('/projects/delete-submission-requirement/{projectFile}', [ProjectController::class, 'deleteSubmissionRequirement'])->name('projects.deleteSubmissionRequirement');
});



Route::middleware(['auth', 'role:student'])->group(function () {
   // Route::get('/student/dashboard', [HomeController::class, 'studentDashboard'])->name('students.dashboard');
   Route::get('/student/dashboard', [HomeController::class, 'studentDashboard'])->name('students.dashboard');
   //Route::get('/students/dashboard/{id}', [StudentProfileController::class, 'showD'])->name('students.dashboard'); // View student's profile
                                         //STUDENTS PROFILE
    Route::get('/supervisors', [SupervisorController::class, 'index'])->name('supervisors.index');
    Route::get('/supervisors/{supervisor}', [SupervisorController::class, 'show'])->name('supervisors.show');
    Route::get('/students/profiles/{id}', [StudentProfileController::class, 'show'])->name('students.profile.show'); // View student's profile
    Route::get('supervisor/profile/{id}', [SupervisorProfileController::class, 'show'])->name('supervisor.profile.show'); // View supervisor's profile
   // Route::get('/supervisor/profile', [SupervisorProfileController::class, 'edit'])->name('supervisor.profile.edit');
    Route::get('/students/profile/{id}', [StudentProfileController::class, 'edit'])->name('students.profile.edit'); // Form to edit student profile
    Route::post('/students/profile/{id}', [StudentProfileController::class, 'update'])->name('students.profile.update'); // Update student profile
        Route::get('/supervisors/{supervisor}/profile', [SupervisorProfileController::class, 'show'])->name('supervisors.profile.show');
                                        //STUDENT APPOINTMENTS ROUTE
    Route::get('students/appointments/index', [AppointmentController::class, 'indexNormal'])->name('students.appointments.index');
    Route::get('/appointments/upcoming', [AppointmentController::class, 'upcomingStudent'])->name('students.appointments.upcoming');//SHOW UPCOMING APPOINTMENTS
    Route::get('/appointments/past', [AppointmentController::class, 'pastStudent'])->name('students.appointments.past');//SHOW PAST APPOINTMENTS
    Route::get('appointments/slots/{supervisor}', [SlotController::class, 'show'])->name('appointments.slots');//DISPLAY SLOTS
    Route::post('/appointments/{slot}/book', [AppointmentController::class, 'bookSlot'])->name('appointments.bookSlot');//BOOK SLOT
   
    Route::get('/appointments/request', [AppointmentController::class, 'showRequestForm'])->name('appointments.requestForm');
    Route::post('/appointments/request', [AppointmentController::class, 'requestOwnTime'])->name('appointments.requestOwnTime');//REQ CUSTOM APPOINTMENT
      Route::post('students/appointments/{appointment}/updateMeetingDetails', [AppointmentController::class, 'updateMeetingDetails'])->name('supervisor.appointments.updateMeetingDetails');

      Route::patch('/chatroom/messages/{message}', [ChatRoomController::class, 'editMessage'])->name('messages.edit');


                                            //STUDENTS PROJECT ROUTE
    Route::get('/student/projects', [StudentProjectController::class, 'index'])->name('student.projects.index');
    Route::get('/students/projects', [ProjectController::class, 'indexStudent'])->name('students.projects.index');
    Route::get('/students/profile/edit', [StudentProfileController::class, 'edit'])->name('students.profile.edit');
    Route::get('students/projects/{project}', [ProjectController::class, 'showProjects'])->name('students.projects.details');
    Route::get('/students/projects', [ProjectController::class, 'indexStudent'])->name('students.projects.index');
    Route::get('/students/projects', [ProjectController::class, 'indexAllProjects'])->name('students.projects.index_all');//ALL PROJECTS
    Route::get('/projects/my_project', [ProjectController::class, 'myProject'])->name('students.projects.my_project');//STUDENT'S ASSIGNED PROJECT
    Route::post('/students/projects/{project}/submit_file', [ProjectController::class, 'submitFile'])->name('students.projects.submit_file');//SUBMIT DRAFT FILES
    Route::delete('/students/projects/delete_file/{file}', [ProjectController::class, 'deleteFile'])->name('students.projects.delete_file');//DELETE FILE
    // Route::get('/students/projects', [ProjectController::class, 'indexStudent'])->name('student.projects.index');
    //Route::get('/students/all-projects', [ProjectController::class, 'indexAllProjects'])->name('student.projects.index_all');
 
                                             //STUDENTS LOGBOOK ROUTE
    Route::get('/students/logbook', [LogbookController::class, 'indexStudent'])->name('students.logbook.index');
    Route::get('/students/logbook/create', [LogbookController::class, 'create'])->name('students.logbook.create');//ADD ACTIVITY
    Route::post('/students/logbook', [LogbookController::class, 'store'])->name('students.logbook.store');// STORE ACTIVITY IN DATABASE
    Route::get('/students/logbook/{logbookEntry}/edit', [LogbookController::class, 'edit'])->name('students.logbook.edit');//EDIT ACTIVITY
    Route::put('/students/logbook/{logbookEntry}', [LogbookController::class, 'update'])->name('students.logbook.update');//UPDATE ACTIVITY IN  DATABSE
    Route::delete('logbooks/{logbookEntry}', [LogbookController::class, 'destroy'])->name('students.logbook.destroy');//DELETE ACTIVITY
    Route::get('/logbook/file/{logbookFile}', [LogbookController::class, 'showFile'])->name('students.logbook.showFile');//DISPLAY FILE
    Route::get('/chatroom/{id}', [ChatRoomController::class, 'show'])->name('chatroom.show');
    
   

    //Route::post('/chatroom/{id}/message', [ChatRoomController::class, 'sendMessage'])->name('chatroom.message.send');
  //  Route::post('/chatroom/{id}/student/message', [ChatRoomController::class, 'sendStudentMessage'])->name('chatroom.student.message.send')->middleware('auth');
  Route::post('/chatroom/send-message/{id}', [ChatRoomController::class, 'sendStudentMessage'])->name('chatroom.student.message.send');

//  Route::get('/projects/{projectId}/tasks', [ProjectController::class, 'getTasks'])->name('project.get_tasks');
//  Route::post('/project/{projectId}/user/{userId}/add-task', [ProjectController::class, 'addTask'])->name('project.add_task');


});


   


// Route::middleware(['auth', 'role:student'])->group(function () {
//     Route::get('/supervisors', [SupervisorController::class, 'index'])->name('supervisors.index');
//     Route::get('/supervisors/{supervisor}', [SupervisorController::class, 'show'])->name('supervisors.show');
//     Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
//     Route::get('/appointments/upcoming', [AppointmentController::class, 'upcomingStudent'])->name('student.appointments.upcoming');
//     Route::get('/appointments/past', [AppointmentController::class, 'pastStudent'])->name('student.appointments.past');
//     Route::get('/supervisors/{supervisor}/slots', [SlotController::class, 'show'])->name('appointments.slots');
//     Route::post('/appointments/{slot}/book', [AppointmentController::class, 'bookSlot'])->name('appointments.bookSlot');
//     Route::post('/appointments/request', [AppointmentController::class, 'requestOwnTime'])->name('appointments.requestOwnTime');
//     Route::delete('/appointments/{appointment}/cancel', [AppointmentCo   Route::get('/logbook/{logbookEntry}/files', [LogbookController::class, 'viewFiles'])->name('students.logbook.viewFiles');//VIEW FILEntroller::class, 'cancel'])->name('appointments.cancel');
// });



require __DIR__.'/auth.php';

// Route::get('slots', [SlotController::class, 'index'])->name('slots.index');
// Route::get('/slots/create', [SlotController::class, 'create'])->name('slots.create');
// Route::post('/slots', [SlotController::class, 'store'])->name('slots.store');
// Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
// Route::delete('appointments/{appointment}', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
//  // Appointment booking routes for students
//  Route::post('appointments/book/{slot}', [AppointmentController::class, 'book'])->name('appointments.book');
//  Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
//  Route::get('appointments/upcoming', [AppointmentController::class, 'upcoming'])->name('appointments.upcoming');
//  Route::get('appointments/past', [AppointmentController::class, 'past'])->name('appointments.past');