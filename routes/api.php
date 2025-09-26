<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['namespace' => 'App\Http\Controllers'], function () {

    Route::post('/login', 'UserAuthController@login');
    Route::post('/forgot-password', 'UserAuthController@sendResetPasswordLink');
    Route::post('/recovery-password', 'UserAuthController@recoveryPassword');
    // Route::post('/register', ["UserAuthController@ 'register']);
    Route::post('/verify-otp', 'OtpController@verifyOTP');

    Route::post('/user-notify', 'UserAuthController@notify');
    Route::get('/users-notified', 'UserController@getNotified');
    Route::get('settings', 'SettingController@index');
    Route::post('settings', 'SettingController@update');

    Route::middleware('basic.auth')->group(function () {

        Route::apiResources([
            'users' => 'UserController',
            'municipalities' => 'MunicipalityController',
        ]);
        Route::get('municipalities-ghm', 'MunicipalityController@indexGhm');
    });

    Route::middleware('auth:api')->group(function () {
        Route::get('/user', 'UserAuthController@user');
        Route::get('/logout', 'UserAuthController@logout');
        Route::post('/change-password', 'UserAuthController@changePassword');
        Route::post('/change-first-password', 'UserAuthController@ChangeFirstPassword');
        Route::post('/reset-password', 'UserAuthController@resetPassword');
        Route::post('/user-update', 'UserAuthController@update');
        Route::post('/user-permissions', 'UserAuthController@userPermission');

        Route::post('/user-push-token', 'UserAuthController@addPushToken');
        Route::get('/user-push-token-delete/{id}', 'UserAuthController@deletePushToken');

        Route::post('/upload-file', 'UserAuthController@uploadFile');
        Route::get('/delete-file', 'UserAuthController@deleteFile');

        Route::apiResources([
            'countries' => 'CountryController',
            'departments' => 'DepartmentController',
            'districts' => 'DistrictController',
            'villages' => 'VillageController',
            'roles' => 'RoleController',
            'permissions' => 'PermissionController',
            'project-categories' => 'ProjectCategoryController',
            'projects' => 'ProjectController',
            'user-projects' => 'UserProjectController',
            'menus' => 'MenuController',
            'notifications' => 'NotificationController',
            'promoters' => 'PromoterController',
        ]);

        Route::get('/logs', 'LogController@index');

        Route::get('municipalities-format', 'MunicipalityController@downloadFormat');
        Route::post('municipalities-import', 'MunicipalityController@import');

        Route::get('departments-format', 'DepartmentController@downloadFormat');
        Route::post('departments-import', 'DepartmentController@import');

        Route::post('send-otp', 'OtpController@sendOTP');

        Route::post('generate-pdf', 'ExportController@generatePDF');
        Route::post('generate-excel', 'ExportController@generateExcel');

        Route::get('notifications/{id}/state/{state}', 'NotificationController@changeState');
        Route::post('notifications-search', 'NotificationController@search');


        Route::get('corps/{id}/state/{state}', 'CorpsController@changeState');
        Route::post('corps-search', 'CorpsController@search');

        Route::get('statut/{id}/state/{state}', 'StatutController@changeState');
        Route::post('statut-search', 'StatutController@search');

        Route::get('fonction/{id}/state/{state}', 'FonctionController@changeState');
        Route::post('corps-search', 'FonctionController@search');

        Route::get('grade/{id}/state/{state}', 'GradeController@changeState');
        Route::post('grade-search', 'GradeController@search');

        Route::get('periode/{id}/state/{state}', 'PeriodeController@changeState');
        Route::post('periode-search', 'PeriodeController@search');

        Route::get('typeacte/{id}/state/{state}', 'TypeacteController@changeState');
        Route::post('typeacte-search', 'TypeacteController@search');

        Route::get('typeprime/{id}/state/{state}', 'TypeprimeController@changeState');
        Route::post('typeprime-search', 'TypeprimeController@search');

        Route::get('primestatut/{id}/state/{state}', 'PrimeStatutController@changeState');
        Route::post('primestatut-search', 'PrimeStatutController@search');

        Route::get('retenue/{id}/state/{state}', 'RetenueController@changeState');
        Route::post('retenue-search', 'RetenueController@search');

        Route::get('agent/{id}/state/{state}', 'Agentontroller@changeState');
        Route::post('agent-search', 'AgentController@search');

        Route::get('joursferies/{id}/state/{state}', 'JoursFeriesontroller@changeState');
        Route::post('joursferies-search', 'JoursFeriesController@search');

        Route::get('notationagent/{id}/state/{state}', 'NotationAgent@changeState');
        Route::post('notationagent-search', 'NotationAgentController@search');

        Route::get('hsup/{id}/state/{state}', 'Hsup@changeState');
        Route::post('hsup-search', 'HsupController@search');

        Route::get('ua/{id}/state/{state}', 'UA@changeState');
        Route::post('ua-search', 'UAController@search');

        
        Route::get('countries/{id}/state/{state}', 'CountryController@changeState');
        Route::post('countries-search', 'CountryController@search');

        Route::get('countries/{id}/state/{state}', 'CountryController@changeState');
        Route::post('countries-search', 'CountryController@search');
        Route::post('/generate-pdf', 'ExportController@generatePDF');

        Route::get('project-categories/{id}/state/{state}', 'ProjectCategoryController@changeState');
        Route::post('project-categories-search', 'ProjectCategoryController@search');

        Route::get('municipalities/{id}/state/{state}', 'MunicipalityController@changeState');
        Route::post('municipalities-search', 'MunicipalityController@search');
        Route::post('municipalities-store-ghm', 'MunicipalityController@storeGhm');
        Route::put('municipalities-update-ghm/{id}', 'MunicipalityController@updateGhm');

        Route::get('districts/{id}/state/{state}', 'DistrictController@changeState');
        Route::post('districts-search', 'DistrictController@search');

        Route::get('villages/{id}/state/{state}', 'VillageController@changeState');
        Route::post('villages-search', 'VillageController@search');

        Route::post('roles-search', 'RoleController@search');
        Route::post('permissions-search', 'PermissionController@search');

        Route::get('user-settings', 'UserSettingController@index');
        Route::put('user-settings', 'UserSettingController@update');

        Route::get('user-projects/{id}/state/{state}', 'UserProjectController@changeState');

        Route::get('menus/{id}/state/{state}', 'MenuController@changeState');

        Route::post('projects-search', 'ProjectController@search');
        Route::get('projects/{id}/state/{state}', 'ProjectController@changeState');

        Route::get('users/{id}/state/{state}', 'UserController@changeState');
        Route::post('users-search', 'UserController@search');
        Route::post('users-candidate', 'UserController@createUser');
        Route::post('users-pr', 'UserController@createUserPR');

        Route::get('users-rh', 'UserController@indexRH');
        Route::get('users-rh/{id}', 'UserController@showRH');
    });
});
