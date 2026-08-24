<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

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
    Route::post('/register', 'UserAuthController@register');
    Route::post('/verify-otp', 'OtpController@verifyOTP');

    Route::post('/user-notify', 'UserAuthController@notify');
    Route::get('/users-notified', 'UserController@getNotified');
    Route::get('settings', 'SettingController@index');
    Route::post('settings', 'SettingController@update');


    Route::post('/send-contact', 'PublicController@sendContact');




    Route::get('/test-mail', function () {
    Mail::raw('Bonjour — test rapide depuis Laravel!', function ($message) {
        $message->to('ornihouss1@gmail.com')
                ->subject('Test rapide Laravel');
    });

    return 'Mail envoyé (ou loggé selon configuration)';
});


    Route::get('/actualities-index-2', 'ActualityController@index2');
    Route::get('/cps/exports/pdf', 'CpsController@exportPDF');

    Route::post('/billings-store-2', 'BillingController@store');
    Route::get('/billings-get-2/{id}', 'BillingController@show');
    Route::post('/billings-response-store', 'BillingController@storeResponse');
    Route::get('/billings/set-status/{id}/{state}', 'BillingController@setStatus');

    Route::get('/sessions/results/all', 'SessionController@results');
    Route::get('/requetes/list-cape/all/{serviceId}', 'RequeteController@getListForPublic');
    Route::get('/requetes/result-session/{code}', 'RequeteController@showResult');

    Route::get('/download-cape-import-file', 'CapeController@downloadImportFile');

    Route::get('/nature-promotors2', 'NaturePromotorController@index');

    

    Route::middleware('basic.auth')->group(function () {

        Route::apiResources([
            'users' => 'UserController',
            'municipalities' => 'MunicipalityController',
        ]);
        Route::get('municipalities-ghm', 'MunicipalityController@indexGhm');
    });

        Route::get('/targets-2', 'TargetController@index');
    Route::get('/type-data-2', 'TypeCapeController@index');
    Route::get('/type-garderies-2', 'TypeGarderieController@index');
    Route::get('/type-infos-2', 'TypeInfoController@index');
    Route::get('/type-billings-2', 'TypeBillingController@index');
    Route::get('/services-2', 'ServiceController@index');


      Route::apiResources([
            'actualities' => 'ActualityController',
            'municipalities' => 'MunicipalityController',
            "messages"=>"MessageController",

        ]);

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', 'UserAuthController@user');
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


        Route::get('dash', 'DashboardController@index');
        Route::get('referals/with-cape/all', 'ReferalController@getWithCape');
        Route::get('capes/authorized/all', 'CapeController@getAllAuthorized');
        Route::get('requetes/authorized/set-all', 'RequeteController@authorized');




        Route::post('eservice', 'EServiceController@store');
    Route::post('eservice-add-file', 'EServiceController@addFile');
    Route::post('eservice-purge-file', 'EServiceController@purgeFile');
    Route::get('eservice/{token}/{code}', 'EServiceController@getOne');
    Route::get('/departments/with/relations', 'DepartmentController@getDepartmentWithRelation');
    Route::get('/files', 'FileController@index');
   // Route::get('/files/{token}', 'FileController@check');




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


              "cps"=>"CpsController",
          "targets"=>"TargetController",
          "type-capes"=>"TypeCapeController",
          "requetes"=>"RequeteController",
          "sessions"=>"SessionController",
          "members"=>"MemberController",
          "avis"=>"AvisController",
          "agendas"=>"AgendaController",
          "staffs"=>"StaffController",
          "backups"=>"BackupController",
          "activity-logs"=>"JournalController",
          "referals"=>"ReferalController",
          "residents"=>"ResidentController",
          "referal-controls"=>"ReferalControlController",
          "capes"=>"CapeController",
          "type-avis"=>"TypeAvisController",
          "type-controls"=>"TypeControlController",
          "controls"=>"ControlControlController",
          "type-files"=>"TypeFileController",
          "type-sanctions"=>"TypeSanctionController",
          "activity-reports"=>"ActivityReportController",
          "activity-report-responses"=>"ActivityReportResponseController",
          "unite-admins"=>"UniteAdminController",
          "sanctions"=>"SanctionController",
          "controls"=>"ControlController",
          "type-infos"=>"TypeInfoController",
          "type-billings"=>"TypeBillingController",
          "infos"=>"InfoController",
          "services"=>"ServiceController",
          "billings"=>"BillingController",
         // 'actualities'=>'ActualityController',
          'cfes'=>'ControlFileElementController',
          "nature-promotors"=>"NaturePromotorController",
          "type-garderies"=>"TypeGarderieController",
          "type-sous-garderies"=>"TypeSousGarderieController"
        
        ]);

        Route::get('/logs', 'LogController@index');


        Route::get('/residents-abandons', 'ResidentController@getAbandons');
        Route::post('/residents-set-abandon/{id}', 'ResidentController@setAbandon');
        Route::get('/residents-exports', 'ResidentController@getExports');



        Route::get('municipalities-format', 'MunicipalityController@downloadFormat');
        Route::post('municipalities-import', 'MunicipalityController@import');

        Route::get('departments-format', 'DepartmentController@downloadFormat');
        Route::post('departments-import', 'DepartmentController@import');

        Route::post('send-otp', 'OtpController@sendOTP');

        Route::post('generate-pdf', 'ExportController@generatePDF');
        Route::post('generate-excel', 'ExportController@generateExcel');

        Route::get('notifications/{id}/state/{state}', 'NotificationController@changeState');
        Route::post('notifications-search', 'NotificationController@search');


        
        Route::get('countries/{id}/state/{state}', 'CountryController@changeState');
        Route::post('countries-search', 'CountryController@search');

        Route::get('countries/{id}/state/{state}', 'CountryController@changeState');
        Route::post('countries-search', 'CountryController@search');
        Route::post('/generate-pdf', 'ExportController@generatePDF');

        Route::get('project-categories/{id}/state/{state}', 'ProjectCategoryController@changeState');
        Route::post('project-categories-search', 'ProjectCategoryController@search');

        Route::get('municipalities/{id}/state/{state}', 'MunicipalityController@changeState');
        Route::post('municipalities-search', 'MunicipalityController@search');

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


        Route::get('users/{id}/state/{state}', 'UserController@changeState');
        Route::post('users-search', 'UserController@search');
        Route::post('users/{id}/reset-password', 'UserController@resetPasswordByAdmin');







           Route::resource('/files', 'FileController')->except(['index']);

      Route::get('/sanctions/set-status/{id}/{state}', 'SanctionController@setStatus');

      
      Route::get('/infos/set-status/{id}/{state}', 'InfoController@setStatus');

      Route::post('/users/store/sign-code', 'UserController@signCode');


      Route::post('/cps/store-districts/all', 'CpsController@storeDistricts');

      Route::get('/controls/trans-up/{id}', 'ControlController@transUp');
      Route::get('/referal-controls/trans-up/{id}', 'ReferalControlController@transUp');


      Route::get('/statistics/{type}/{agg?}', 'StatistiqueController@index');
      Route::get('/searches/{type}', 'SearchController@index');

      Route::get('/activity-reports/cape/all', 'ActivityReportController@getForCape');
      Route::get('/activity-reports/set-status/{id}/{state}', 'ActivityReportController@setStatus');
      Route::get('/activity-reports/set-transmission/{id}', 'ActivityReportController@send');

      Route::post('/sessions/store-members/all', 'SessionController@storeMembers');
      Route::delete('/sessions/remove-member/{id}', 'SessionController@removeMember');
      Route::delete('/sessions/remove-requete/{id}', 'SessionController@removeRequete');
      Route::post('/sessions/store-requetes/all', 'SessionController@storeRequetes');
      Route::post('/sessions/store-requetes-notes/all', 'SessionController@enregistrerNotes');
      Route::get('/sessions/requests/all', 'SessionController@getSessionRequests');
      Route::get('/sessions/set-status/{id}/{state}', 'SessionController@setStatus');
      Route::get('/sessions/set-status-member/{id}/{state}', 'SessionController@setStatutMember');



      Route::get('/requetes/get-by-instance/new', 'RequeteController@getNewRequete');
      Route::get('/requetes/get-by-instance/pending', 'RequeteController@getPendingRequete');
      Route::get('/requetes/get-by-instance/corrected', 'RequeteController@getCorrectedRequete');
      Route::get('/requetes/get-by-instance/rejected', 'RequeteController@getRejectedRequete');
      Route::get('/requetes/get-by-instance/validated', 'RequeteController@getValidatedRequete');
      Route::get('/requetes/get-by-instance/finished', 'RequeteController@getFinishedRequete');
      Route::get('/requetes/get-by-instance/admissible', 'RequeteController@getAdmissibleRequete');
      Route::get('/requetes/get-by-instance/transmitted', 'RequeteController@getTransmittedRequete');
      Route::post('/requetes/state/finished-store-1', 'RequeteController@finishStore1');
      Route::post('/requetes/state/finished-store-2', 'RequeteController@finishStore2');
      Route::post('/requetes/trans-up', 'RequeteController@transUp');
      Route::post('/requetes/trans-down', 'RequeteController@transDown');
      Route::post('/requetes/invite', 'RequeteController@inviteStore');
      Route::get('/requetes/for-session/{code}', 'RequeteController@getForSession');
      Route::post('/requetes/session/decision', 'RequeteController@setDecision');
      Route::get('/requetes/session/decision', 'RequeteController@getDecision');
      Route::get('/requetes/get-pending-validation/all', 'RequeteController@getPendingValidation');
      Route::get('/requetes/search/all', 'RequeteController@search');
      Route::get('/requetes/search/export', 'RequeteController@export');
      Route::post('/requetes/transfer-district', 'RequeteController@transferDistrict');


      Route::post('/responses/step/need-correction', 'ReponseController@needCorrection');
      Route::post('/responses/step/declined', 'ReponseController@decline');
      Route::post('/responses/step/validated', 'ReponseController@validation');

   
      Route::post('/requetes/set-status', 'RequeteController@setStatus');
      Route::post('/requetes/set-status2', 'RequeteController@setStatus2');
      Route::post('/requetes/set-file-traitment', 'RequeteController@setFileTreatment');


      Route::get('/users/set-status/{id}/{state}', 'UserController@setStatus');
      Route::get('/cfes/set-status/{id}/{state}', 'ControlFileElementController@setStatus');
      Route::get('/cfes-actives/{serviceId}', 'ControlFileElementController@getAllActives');
      Route::get('/departments/set-status/{id}/{state}', 'DepartmentController@setStatus');
      Route::get('/municipalities/set-status/{id}/{state}', 'MunicipalityController@setStatus');
      Route::get('/districts/set-status/{id}/{state}', 'DistrictController@setStatus');
      Route::get('/targets/set-status/{id}/{state}', 'TargetController@setStatus');
      Route::get('/type-avis/set-status/{id}/{state}', 'TypeAvisController@setStatus');
      Route::get('/type-files/set-status/{id}/{state}', 'TypeFileController@setStatus');
      Route::get('/type-controls/set-status/{id}/{state}', 'TypeControlController@setStatus');
      Route::get('/type-sanctions/set-status/{id}/{state}', 'TypeSanctionController@setStatus');
      Route::get('/type-capes/set-status/{id}/{state}', 'TypeCapeController@setStatus');
    
      Route::get('/send-recepisse/{code}', 'RequeteController@sendRecepisse');

      /*
       * Reconnaissance des agréments délivrés hors plateforme.
       * Espace promoteur : revendication d'un centre importé puis dépôt des
       * pièces. Espace DFEA : file d'attente et décision.
       */
      Route::get('/agrement-claims/available', 'AgrementClaimController@available');
      Route::post('/agrement-claims/request-otp', 'AgrementClaimController@requestOtp');
      Route::post('/agrement-claims/verify-otp', 'AgrementClaimController@verifyOtp');
      Route::get('/agrement-claims/required-files', 'AgrementClaimController@requiredFiles');
      Route::post('/agrement-claims/add-file', 'AgrementClaimController@addFile');
      Route::post('/agrement-claims/submit', 'AgrementClaimController@submit');
      Route::get('/agrement-claims/mine', 'AgrementClaimController@mine');
      Route::get('/agrement-claims/pending', 'AgrementClaimController@pending');
      Route::post('/agrement-claims/decide', 'AgrementClaimController@decide');


    });
});
