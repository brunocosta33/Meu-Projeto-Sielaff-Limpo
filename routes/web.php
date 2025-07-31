<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginActivityController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ConfigurationsController;
use App\Http\Controllers\Frontoffice\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Frontoffice\FlyerController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ImagesController;

use App\Http\Controllers\StateController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadHistoryController;
use App\Http\Controllers\ReturnTextController;

use App\Http\Controllers\FlyersController;
use App\Http\Controllers\ProductsTextController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\FlyerBannerController;
use App\Http\Controllers\NewLinkController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskScheduleController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\TechnicalRequestController;
use App\Http\Controllers\TechnicalScheduleController;
use App\Http\Controllers\InstallationController;



Route::get('/clear', function () {
    Artisan::call('optimize:clear');
    return 'Routes cache has clear successfully !';
});

Auth::routes();
Route::post('/language-switch',[LanguageController::Class,'languageSwitch'])->name('language.switch');
//Route::get('/', [HomeController::class, 'index'])->name('frontoffice.index');
Route::get('/', function () {
    return redirect()->route('backoffice.index'); // ou 'login' se preferires ir para a página de login
});


//Route::get('/flyer/{slug}', [FlyerController::class, 'show'])->name('frontoffice.flyer.show');


//Route::get('/', [DashboardController::class, 'index'])->name('backoffice.index')->middleware('auth');



Route::group(['middleware' => ['auth']], function() {


Route::get('/backoffice', [DashboardController::class, 'index'])->name('backoffice.index')->middleware('auth');


//task schedules
Route::prefix('backoffice/task-schedules')->group(function () {
    Route::get('/', [TaskScheduleController::class, 'index'])->name('backoffice.task_schedules.index');
    Route::get('/create', [TaskScheduleController::class, 'create'])->name('backoffice.task_schedules.create');
    Route::post('/', [TaskScheduleController::class, 'store'])->name('backoffice.task_schedules.store');
    Route::get('/{id}/edit', [TaskScheduleController::class, 'edit'])->name('backoffice.task_schedules.edit');
    Route::put('/{id}', [TaskScheduleController::class, 'update'])->name('backoffice.task_schedules.update');
    Route::delete('/{id}', [TaskScheduleController::class, 'destroy'])->name('backoffice.task_schedules.destroy');
    Route::get('/minhas-tarefas', [TaskScheduleController::class, 'minhasTarefas'])->name('backoffice.task_schedules.minhas');
});

Route::prefix('backoffice/tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('backoffice.tasks.index');
    Route::get('/create', [TaskController::class, 'create'])->name('backoffice.tasks.create');
    Route::post('/', [TaskController::class, 'store'])->name('backoffice.tasks.store');
    Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('backoffice.tasks.edit');
    Route::put('/{task}', [TaskController::class, 'update'])->name('backoffice.tasks.update');
    Route::delete('/{task}', [TaskController::class, 'destroy'])->name('backoffice.tasks.destroy');
});
//Installations
Route::prefix('backoffice/installations')->group(function () {
    Route::get('/', [InstallationController::class, 'index'])->name('backoffice.installations.index');
    Route::get('/create', [InstallationController::class, 'create'])->name('backoffice.installations.create');
    Route::post('/', [InstallationController::class, 'store'])->name('backoffice.installations.store');
});


//My Tasks
Route::prefix('backoffice/mytasks')->group(function () {
    Route::get('/', [TaskController::class, 'myTasks'])->name('backoffice.mytasks.index');
    Route::get('/{task}/show', [TaskController::class, 'show'])->name('backoffice.mytasks.show');
    Route::put('/{task}/update-status', [TaskController::class, 'updateStatus'])->name('backoffice.mytasks.updateStatus');
});


//Appointments
Route::prefix('appointments')->group(function () {
    Route::get('/', [AppointmentController::class, 'index'])->name('backoffice.appointments.index');
    Route::get('/create', [AppointmentController::class, 'create'])->name('backoffice.appointments.create');
    Route::post('/store', [AppointmentController::class, 'store'])->name('backoffice.appointments.store');
    Route::get('/{id}/edit', [AppointmentController::class, 'edit'])->name('backoffice.appointments.edit');
    Route::get('/appointments/{id}/show', [AppointmentController::class, 'show'])->name('backoffice.appointments.show');
    Route::post('/{id}/update', [AppointmentController::class, 'update'])->name('backoffice.appointments.update');
    Route::get('/{id}/delete', [AppointmentController::class, 'delete'])->name('backoffice.appointments.delete');
});

//suppliers
Route::prefix('suppliers')->group(function () {
    Route::get('/', [SupplierController::class, 'index'])->name('backoffice.suppliers.index');
    Route::get('/create', [SupplierController::class, 'create'])->name('backoffice.suppliers.create');
    Route::post('/store', [SupplierController::class, 'store'])->name('backoffice.suppliers.store');
    Route::get('/{id}', [SupplierController::class, 'show'])->name('backoffice.suppliers.show');
    Route::get('/{id}/edit', [SupplierController::class, 'edit'])->name('backoffice.suppliers.edit');
    Route::get('/{id}/delete', [SupplierController::class, 'delete'])->name('backoffice.suppliers.delete');
    Route::post('{id}/update', [SupplierController::class, 'update'])->name('backoffice.suppliers.update')->middleware(['auth']);
});
//teams
Route::prefix('teams')->group(function () {
    Route::get('/', [TeamController::class, 'index'])->name('backoffice.teams.index');
    Route::get('/create', [TeamController::class, 'create'])->name('backoffice.teams.create');
    Route::post('/store', [TeamController::class, 'store'])->name('backoffice.teams.store');
    Route::get('/{id}/edit', [TeamController::class, 'edit'])->name('backoffice.teams.edit');
    Route::post('/{id}/update', [TeamController::class, 'update'])->name('backoffice.teams.update');
    Route::get('/{id}/delete', [TeamController::class, 'delete'])->name('backoffice.teams.delete');
});


//Stores
Route::prefix('stores')->group(function () {
    Route::get('/', [StoreController::class, 'index'])->name('backoffice.stores.index');
    Route::get('/create', [StoreController::class, 'create'])->name('backoffice.stores.create');
    Route::post('/store', [StoreController::class, 'store'])->name('backoffice.stores.store');
    Route::get('/{id}/edit', [StoreController::class, 'edit'])->name('backoffice.stores.edit');
    Route::post('/{id}/update', [StoreController::class, 'update'])->name('backoffice.stores.update');
    Route::get('/{id}/delete', [StoreController::class, 'delete'])->name('backoffice.stores.delete');
});

//Parts
Route::prefix('backoffice/parts')->group(function () {
    Route::get('/', [PartController::class, 'index'])->name('backoffice.parts.index');
    Route::get('/create', [PartController::class, 'create'])->name('backoffice.parts.create');
    Route::post('/store', [PartController::class, 'store'])->name('backoffice.parts.store');
    Route::get('/{id}/edit', [PartController::class, 'edit'])->name('backoffice.parts.edit');
    Route::get('/{id}/show', [PartController::class, 'show'])->name('backoffice.parts.show');
    Route::post('/{id}/update', [PartController::class, 'update'])->name('backoffice.parts.update');
    Route::get('/{id}/delete', [PartController::class, 'delete'])->name('backoffice.parts.delete');
});
//Machines
Route::prefix('backoffice/machines')->group(function () {
    Route::get('/', [MachineController::class, 'index'])->name('backoffice.machines.index');
    Route::get('/create', [MachineController::class, 'create'])->name('backoffice.machines.create');
    Route::post('/store', [MachineController::class, 'store'])->name('backoffice.machines.store');
    Route::get('/{id}/edit', [MachineController::class, 'edit'])->name('backoffice.machines.edit');
    Route::get('/{id}/show', [MachineController::class, 'show'])->name('backoffice.machines.show');
    Route::post('/{id}/update', [MachineController::class, 'update'])->name('backoffice.machines.update');
    Route::get('/{id}/delete', [MachineController::class, 'delete'])->name('backoffice.machines.delete');
});
//Technical Requests
Route::prefix('backoffice/technical-requests')->group(function () {
    Route::get('/', [TechnicalRequestController::class, 'index'])->name('backoffice.technical_requests.index');
    Route::get('/create', [TechnicalRequestController::class, 'create'])->name('backoffice.technical_requests.create');
    Route::post('/store', [TechnicalRequestController::class, 'store'])->name('backoffice.technical_requests.store');
    Route::get('/{id}/show', [TechnicalRequestController::class, 'show'])->name('backoffice.technical_requests.show');
    Route::get('/{id}/edit', [TechnicalRequestController::class, 'edit'])->name('backoffice.technical_requests.edit');
    Route::put('backoffice/technical-requests/{id}', [TechnicalRequestController::class, 'update'])->name('backoffice.technical_requests.update');
    Route::get('/{id}/delete', [TechnicalRequestController::class, 'delete'])->name('backoffice.technical_requests.delete');
});

//Technical Schedules
Route::prefix('backoffice/technical-schedules')->group(function () {
    Route::get('/', [TechnicalScheduleController::class, 'index'])->name('backoffice.technical_schedules.index');
    Route::get('/create', [TechnicalScheduleController::class, 'create'])->name('backoffice.technical_schedules.create');
    Route::post('/store', [TechnicalScheduleController::class, 'store'])->name('backoffice.technical_schedules.store');
    Route::get('/{id}/show', [TechnicalScheduleController::class, 'show'])->name('backoffice.technical_schedules.show');
    Route::get('/{id}/edit', [TechnicalScheduleController::class, 'edit'])->name('backoffice.technical_schedules.edit');
    Route::post('/{id}/update', [TechnicalScheduleController::class, 'update'])->name('backoffice.technical_schedules.update');
    Route::get('/{id}/delete', [TechnicalScheduleController::class, 'delete'])->name('backoffice.technical_schedules.delete');
});

//States
Route::prefix('state')->group(function () {
    Route::get('/', [StateController::class, 'index'])->name('backoffice.state.index');
    Route::get('/create', [StateController::class, 'create'])->name('backoffice.state.create');
    Route::post('/store', [StateController::class, 'store'])->name('backoffice.state.store');
    Route::get('/{id}', [StateController::class, 'show'])->name('backoffice.state.show');
    Route::get('/{id}/edit', [StateController::class, 'edit'])->name('backoffice.state.edit');
    Route::get('/{id}/delete', [StateController::class, 'delete'])->name('backoffice.state.delete');
    Route::post('{id}/update', [StateController::class, 'update'])->name('backoffice.state.update')->middleware(['auth']);
});

//Contacts
Route::prefix('contacts')->group(function () {
    Route::get('/', [ContactController::class, 'index'])->name('backoffice.contacts.index');
    Route::get('/create', [ContactController::class, 'create'])->name('backoffice.contacts.create');
    Route::post('/store', [ContactController::class, 'store'])->name('backoffice.contacts.store');
    Route::get('/{id}', [ContactController::class, 'show'])->name('backoffice.contacts.show');
    Route::get('/{id}/edit', [ContactController::class, 'edit'])->name('backoffice.contacts.edit');
    Route::get('/{id}/delete', [ContactController::class, 'delete'])->name('backoffice.contacts.delete');
    Route::post('{id}/update', [ContactController::class, 'update'])->name('backoffice.contacts.update')->middleware(['auth']);
   
});

//Clients
Route::prefix('clients')->group(function () {
    Route::get('/', [ClientController::class, 'index'])->name('backoffice.clients.index');
    Route::get('/create', [ClientController::class, 'create'])->name('backoffice.clients.create');
    Route::post('/store', [ClientController::class, 'store'])->name('backoffice.clients.store');
    Route::get('/{id}', [ClientController::class, 'show'])->name('backoffice.clients.show');
    Route::get('/{id}/edit', [ClientController::class, 'edit'])->name('backoffice.clients.edit');
    Route::get('/{id}/delete', [ClientController::class, 'delete'])->name('backoffice.clients.delete');
    Route::post('{id}/update', [ClientController::class, 'update'])->name('backoffice.clients.update')->middleware(['auth']);
});

//Leads
Route::prefix('leads')->group(function () {
    Route::get('/', [LeadController::class, 'index'])->name('backoffice.leads.index');
    Route::get('/create', [LeadController::class, 'create'])->name('backoffice.leads.create');
    Route::post('/store', [LeadController::class, 'store'])->name('backoffice.leads.store');
    Route::get('/{id}', [LeadController::class, 'show'])->name('backoffice.leads.show');
    Route::get('/{id}/edit', [LeadController::class, 'edit'])->name('backoffice.leads.edit');
    Route::get('/{id}/delete', [LeadController::class, 'delete'])->name('backoffice.leads.delete');
    Route::post('{id}/update', [LeadController::class, 'update'])->name('backoffice.leads.update')->middleware(['auth']);
});

//Response Text for email
Route::prefix('returnText')->group(function () {
    Route::get('/', [ReturnTextController::class, 'index'])->name('backoffice.returnText.index');
    Route::post('/store', [ReturnTextController::class, 'store'])->name('backoffice.returnText.store');
});




Route::get('leadhistory/{id}', [LeadHistoryController::class, 'show'])->name('backoffice.leadHistory.show');



//Admin
Route::get('/backoffice/loginactivity', [LoginActivityController::class, 'index'])->name('backoffice.loginactivity.index')->middleware(['hasPermission:view_loginactivity']);

Route::get('/backoffice/users', [UsersController::class, 'index'])->name('backoffice.users.index')->middleware(['hasPermission:view_users']);
Route::get('/dashboard/users/create', [UsersController::class, 'create'])->name('dashboard.users.create')->middleware(['hasPermission:manage_users']);
Route::get('/dashboard/users/create/store', [UsersController::class, 'store'])->name('dashboard.users.store')->middleware(['hasPermission:manage_users']);
Route::get('/dashboard/users/show/{user}', [UsersController::class, 'show'])->name('dashboard.users.show')->middleware(['hasPermission:manage_users']);
Route::get('/backoffice/dashboard/users/edit/{user}', [UsersController::class, 'edit'])->name('dashboard.users.edit')->middleware(['hasPermission:manage_users']);
Route::put('/dashboard/users/edit/{user}/update', [UsersController::class, 'update'])->name('dashboard.users.update')->middleware(['hasPermission:manage_users']);
Route::get('/dashboard/users/delete/{user}', [UsersController::class, 'delete'])->name('dashboard.users.delete')->middleware(['hasPermission:manage_users']);
Route::get('/backoffice/dashboard/users/destroy/{user}', [UsersController::class, 'destroy'])->name('dashboard.users.destroy')->middleware(['hasPermission:manage_users']);
Route::put('/backoffice/dashboard/users/{user}/locale', [UsersController::class, 'locale'])->name('user.locale')->middleware(['auth']);
Route::put('/backoffice/dashboard/users/{user}/password', [UsersController::class, 'password'])->name('user.password');
Route::get('/dashboard/users/pass/{user}', [UsersController::class, 'pass'])->name('dashboard.users.pass')->middleware(['hasPermission:manage_users']);
Route::post('/dashboard/users/pass/store', [UsersController::class, 'passstore'])->name('dashboard.users.passstore')->middleware(['hasPermission:manage_users']);

Route::get('/backoffice/roles', [RolesController::class, 'index'])->name('backoffice.roles.index')->middleware(['hasPermission:manage_roles']);
Route::get('/backoffice/roles/create', [RolesController::class, 'create'])->name('backoffice.roles.create')->middleware(['hasPermission:manage_roles']);
Route::post('/backoffice/roles/create/store', [RolesController::class, 'store'])->name('backoffice.roles.store')->middleware(['hasPermission:manage_roles']);
Route::get('/backoffice/roles/edit/{role}', [RolesController::class, 'edit'])->name('backoffice.roles.edit')->middleware(['hasPermission:manage_roles']);
Route::post('/backoffice/roles/edit/{role}/update', [RolesController::class, 'update'])->name('backoffice.roles.update')->middleware(['hasPermission:manage_roles']);
Route::get('/backoffice/roles/delete/{role}', [RolesController::class, 'delete'])->name('backoffice.roles.delete')->middleware(['hasPermission:manage_roles']);

Route::get('/backoffice/permissions', [PermissionsController::class, 'index'])->name('backoffice.permissions.index')->middleware(['hasPermission:view_permissions']);
Route::get('/backoffice/permissions/create', [PermissionsController::class, 'create'])->name('backoffice.permissions.create')->middleware(['hasPermission:view_permissions']);
Route::post('/backoffice/permissions/create/store', [PermissionsController::class, 'store'])->name('backoffice.permissions.store')->middleware(['hasPermission:view_permissions']);
Route::get('/backoffice/permissions/edit/{role}', [PermissionsController::class, 'edit'])->name('backoffice.permissions.edit')->middleware(['hasPermission:view_permissions']);
Route::post('/backoffice/permissions/edit/{role}/update', [PermissionsController::class, 'update'])->name('backoffice.permissions.update')->middleware(['hasPermission:view_permissions']);
Route::get('/backoffice/permissions/delete/{role}', [PermissionsController::class, 'delete'])->name('backoffice.permissions.delete')->middleware(['hasPermission:view_permissions']);

Route::get('/backoffice/profile', [ProfileController::class, 'index'])->name('backoffice.profile.index');
Route::get('/backoffice/myprofile', [ProfileController::class, 'myprofile'])->name('backoffice.myprofile.index');
Route::get('/backoffice/change-password', [UsersController::class, 'changePasswordForm'])->name('backoffice.profile.change-password');
Route::post('/backoffice/profile/change-password/save', [UsersController::class, 'changePassword'])->name('backoffice.profile.change-password.save');

Route::get('/backoffice/configurations', [ConfigurationsController::class, 'index'])->name('backoffice.configurations.index')->middleware(['hasPermission:view_configurations']);
Route::post('/backoffice/configurations/store', [ConfigurationsController::class, 'store'])->name('backoffice.configurations.store')->middleware(['hasPermission:manage_configurations']);

Route::put('/backoffice/dashboard/users/{user}/locale', [UsersController::class, 'locale'])->middleware('auth')->name('user.locale');


});



