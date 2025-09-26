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


Route::group([
    'namespace' => 'App\Http\Controllers'
], function () {
    Route::post('/login', 'Auth\UserAuthController@login');
    Route::post('/register', 'Auth\UserAuthController@register');
    Route::post('/send-reset-password-link', 'Auth\ResetPasswordController@sendResetPasswordLink');
    Route::post('/recovery-password/{token}', 'Auth\ResetPasswordController@recoveryPassword');
    Route::post('eservice', 'EServiceController@store');
    Route::post('eservice-add-file', 'EServiceController@addFile');
    Route::post('eservice-purge-file', 'EServiceController@purgeFile');
    Route::get('eservice/{token}/{code}', 'EServiceController@getOne');
    Route::get('/departments/with/relations', 'DepartmentController@getDepartmentWithRelation');
    Route::get('/files', 'FileController@index');
   // Route::get('/files/{token}', 'FileController@check');

    Route::get('/targets-2', 'TargetController@index');
    Route::get('/type-data-2', 'TypeCapeController@index');
    Route::get('/type-garderies-2', 'TypeGarderieController@index');
    Route::get('/type-infos-2', 'TypeInfoController@index');
    Route::get('/type-billings-2', 'TypeBillingController@index');
    Route::get('/services-2', 'ServiceController@index');

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

    Route::post('/messages', 'MessageController@store');

    Route::get('/nature-promotors2', 'NaturePromotorController@index');

    
    Route::group([ 'middleware' => 'auth:api','permission'], function ($router) {
        
    Route::get('me', 'Auth\UserAuthController@user');
    Route::post('update-profile', 'Auth\UserAuthController@update');
    Route::get('/logout', 'Auth\UserAuthController@logout');
    Route::get('/logged-user-data', 'Auth\UserAuthController@loggedUserData');
    Route::post('/change-password', 'Auth\UserAuthController@changePassword');
    Route::post('/change-first-password', 'Auth\UserAuthController@changeFirstPassword');

    Route::get('dash', 'DashboardController@index');
    Route::get('referals/with-cape/all', 'ReferalController@getWithCape');
    Route::get('capes/authorized/all', 'CapeController@getAllAuthorized');
    
    Route::get('requetes/authorized/set-all', 'RequeteController@authorized');


      Route::resources([
         "users"=>"UserController",
          "profile"=>"ProfileController",
          "roles"=>"RolesController",
          "permissions"=>"PermissionsController",
          "departments"=>"DepartmentController",
          "municipalities"=>"MunicipalityController",
          "districts"=>"DistrictController",
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
          "messages"=>"MessageController",
          "type-infos"=>"TypeInfoController",
          "type-billings"=>"TypeBillingController",
          "infos"=>"InfoController",
          "services"=>"ServiceController",
          "billings"=>"BillingController",
          'actualities'=>'ActualityController',
          'cfes'=>'ControlFileElementController',
          "nature-promotors"=>"NaturePromotorController",
          "type-garderies"=>"TypeGarderieController",
          "type-sous-garderies"=>"TypeSousGarderieController"
        

      ]);

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

    });
    
}); 
