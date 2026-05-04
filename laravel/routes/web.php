<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ActivitiesController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectAssignmentByRoleController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LicensesAgreementsController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BillingListController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BudgetCriterionController;
use App\Http\Controllers\AssignSettlementController;
use App\Http\Controllers\InvoiceConsecutiveController;
use App\Http\Controllers\ReceiptConsecutiveController;
use Illuminate\Support\Facades\Artisan;


Route::get('/optimize-cache', function() {
    Artisan::call('optimize:clear');    
    return 'Application cache has been cleared';
});
//optimize cache:

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return 'Application cache has been cleared';
});
//Clear config cache:

Route::get('/config-cache', function() {
  Artisan::call('config:cache');
  return 'Config cache has been cleared';
}); 
// Clear view cache:

Route::get('/view-clear', function() {
    Artisan::call('view:clear');
    return 'View cache has been cleared';
});

Route::get('/config-clear', function() {
  Artisan::call('config:clear');
  return 'Config cache has been cleared';
}); 
//Clear config cache:


Route::get('/', function () {
    return view('auth.login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');
// Route::post('/set-language', [LanguageController::class, 'setLanguage'])->name('set-language');

Route::group(['middleware' => ['auth', 'web']], function() {
    Route::post('/language/switch', [LanguageController::class, 'switchLang'])->name('language.switch');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard-get-activities', [DashboardController::class, 'getActivitiesData'])->name('dashboard.get-activities.data');

    Route::get('/user-profile', [DashboardController::class, 'editProfile'])->name('user-profile.edit');
    Route::post('/user-profile', [DashboardController::class, 'updateProfile'])->name('user-profile.update');
    Route::get('/change-password', [DashboardController::class, 'changePasswordForm'])->name('change.password');
    Route::post('/change-password', [DashboardController::class, 'changePassword'])->name('changePassword.update');
    Route::get('/help', [DashboardController::class, 'helpContent'])->name('help-page');

    Route::get('/roles', [RoleController::class, 'index'])->name('roles');
    Route::get('/get-roles', [RoleController::class, 'getAjaxData'])->name('get-roles.data');
    Route::get('/role/add', [RoleController::class, 'create'])->name('role.create');
    Route::post('/role/store', [RoleController::class, 'store'])->name('role.store');
    Route::get('/role/edit/{id}', [RoleController::class, 'edit'])->name('role.edit');
    Route::post('/role/update/{id}', [RoleController::class, 'update'])->name('role.update');
    Route::post('/role/delete/{id}', [RoleController::class, 'destroy'])->name('role.delete');

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions');
    Route::get('/get-permissions', [PermissionController::class, 'getAjaxData'])->name('get-permissions.data');
    Route::get('/permission/add', [PermissionController::class, 'create'])->name('permission.create');
    Route::post('/permission/store', [PermissionController::class, 'store'])->name('permission.store');
    Route::get('/permission/edit/{id}', [PermissionController::class, 'edit'])->name('permission.edit');
    Route::post('/permission/update/{id}', [PermissionController::class, 'update'])->name('permission.update');
    Route::post('/permission/delete/{id}', [PermissionController::class, 'destroy'])->name('permission.delete');

    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/get-users', [UserController::class, 'getAjaxData'])->name('get-users.data');
    Route::get('/user/add', [UserController::class, 'create'])->name('user.create');
    Route::post('/user/store', [UserController::class, 'store'])->name('user.store');
    Route::get('/user/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
    Route::post('/user/update/{id}', [UserController::class, 'update'])->name('user.update');
    Route::post('/user/delete/{id}', [UserController::class, 'destroy'])->name('user.delete');
    Route::post('/user/sendResetLink', [UserController::class, 'sendResetLink'])->name('user.sendResetLink');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects');
    Route::get('/get-projects', [ProjectController::class, 'getAjaxData'])->name('get-projects.data');
    Route::get('/project/add', [ProjectController::class, 'create'])->name('project.create');
    Route::post('/project/store', [ProjectController::class, 'store'])->name('project.store');
    Route::get('/project/edit/{id}', [ProjectController::class, 'edit'])->name('project.edit');
    Route::get('/project/view/{id}', [ProjectController::class, 'view'])->name('project.view');
    Route::post('/project/update/{id}', [ProjectController::class, 'update'])->name('project.update');
    Route::post('/project/delete/{id}', [ProjectController::class, 'destroy'])->name('project.delete');
    Route::get('/project/history/{id}', [ProjectController::class, 'projectHistory'])->name('project.history');
    Route::get('/project/comment/{id}', [ProjectController::class, 'projectComment'])->name('project.comment');
    Route::get('/project/get-details/{id}', [ProjectController::class, 'getDetails'])->name('project.get-details');
    Route::put('/project/updateViaAjax/{id}', [ProjectController::class, 'updateViaAjax'])->name('project.updateViaAjax');

    Route::post('/project/upload', [ProjectController::class, 'upload'])->name('project.upload');
    Route::post('/project/upload-remove/{id}', [ProjectController::class, 'uploadRemove'])->name('project.upload-remove');
    Route::post('/project/comment/store', [ProjectController::class, 'projectCommentStore'])->name('project.comment.store');
    Route::post('/project/comment/upload', [ProjectController::class, 'projectCommentUpload'])->name('project.comment.upload');
    Route::post('/project/comment/delete/{id}', [ProjectController::class, 'projectCommentDelete'])->name('project.comment.delete');
    Route::get('/project/add-activity/{id}', [ProjectController::class, 'addActivity'])->name('project.add-activity');

    Route::get('/activities', [ActivitiesController::class, 'index'])->name('activities');
    Route::get('/get-activities', [ActivitiesController::class, 'getAjaxData'])->name('get-activities.data');
    Route::post('/get-personal-activities', [ActivitiesController::class, 'getAjaxDataPersonal'])->name('get-personal-activities.data');
    Route::get('/activity/add', [ActivitiesController::class, 'create'])->name('activity.create');
    Route::post('/activity/store', [ActivitiesController::class, 'store'])->name('activity.store');
    Route::get('/activity/edit/{id}', [ActivitiesController::class, 'edit'])->name('activity.edit');
    Route::get('/activity/view/{id}', [ActivitiesController::class, 'view'])->name('activity.view');
    Route::post('/activity/update/{id}', [ActivitiesController::class, 'update'])->name('activity.update');
    Route::post('/activity/delete/{id}', [ActivitiesController::class, 'destroy'])->name('activity.delete');
    Route::get('/activity/history/{id}', [ActivitiesController::class, 'activityHistory'])->name('activity.history');
    Route::get('/activity/comment/{id}', [ActivitiesController::class, 'activityComment'])->name('activity.comment');
    Route::get('/project/{id}/client', [ActivitiesController::class, 'getProjectClient'])->name('project.client');

    Route::post('/activity/upload', [ActivitiesController::class, 'upload'])->name('activity.upload');
    Route::post('/activity/upload-remove/{id}', [ActivitiesController::class, 'uploadRemove'])->name('activity.upload-remove');
    Route::post('/activity/comment/store', [ActivitiesController::class, 'activityCommentStore'])->name('activity.comment.store');
    Route::post('/activity/comment/upload', [ActivitiesController::class, 'activityCommentUpload'])->name('activity.comment.upload');
    Route::post('/activity/comment/delete/{id}', [ActivitiesController::class, 'activityCommentDelete'])->name('activity.comment.delete');

    Route::get('/clients', [ClientController::class, 'index'])->name('clients');
    Route::get('/get-clients', [ClientController::class, 'getAjaxData'])->name('get-clients.data');
    Route::get('/client/add', [ClientController::class, 'create'])->name('client.create');
    Route::post('/client/store', [ClientController::class, 'store'])->name('client.store');
    Route::get('/client/edit/{id}', [ClientController::class, 'edit'])->name('client.edit');
    Route::post('/client/update/{id}', [ClientController::class, 'update'])->name('client.update');
    Route::get('/client/view/{id}', [ClientController::class, 'view'])->name('client.view');
    Route::post('/client/delete/{id}', [ClientController::class, 'destroy'])->name('client.delete');
    Route::post('/clients/{id}/upgrade-comment', [ClientController::class, 'addUpgradeComment'])->name('client.upgrade-comment');
    Route::get('/clients/{id}/upgrade-comments', [ClientController::class, 'getUpgradeComments'])->name('client.upgrade-comments');
    Route::post('/client/check-duplicate', [ClientController::class, 'checkDuplicate'])->name('client.checkDuplicate');

    Route::get('/basic-info', [ClientController::class, 'indexBasicInfo'])->name('basic-info');
    Route::get('/get-basic-info', [ClientController::class, 'getAjaxDataBasicInfo'])->name('get-basic-info.data');
    Route::post('/basic-info/store', [ClientController::class, 'storeBasicInfo'])->name('basic-info.store');
    Route::get('/basic-info/edit/{id}', [ClientController::class, 'editBasicInfo'])->name('basic-info.edit');
    Route::post('/basic-info/update/{id}', [ClientController::class, 'updateBasicInfo'])->name('basic-info.update');
    Route::post('/basic-info/delete/{id}', [ClientController::class, 'destroyBasicInfo'])->name('basic-info.delete');

    Route::get('/assign-by-role-project', [ProjectAssignmentByRoleController::class, 'index'])->name('assign-by-role-project');
    Route::get('/get-assign-by-role-project', [ProjectAssignmentByRoleController::class, 'getAjaxData'])->name('get-assign-by-role-project.data');
    Route::post('/assign-by-role-project/store', [ProjectAssignmentByRoleController::class, 'store'])->name('assign-by-role-project.store');
    Route::post('/assign-by-role-project/delete/{id}', [ProjectAssignmentByRoleController::class, 'destroy'])->name('assign-by-role-project.delete');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::post('/calendar/store', [CalendarController::class, 'store'])->name('calendar.store');
    Route::post('/calendar/{schedule}/update', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{schedule}/delete', [CalendarController::class, 'destroy'])->name('calendar.delete');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::get('/calendar/list', [CalendarController::class, 'calendarList'])->name('calendar.list');
    Route::get('/calendar/get-calendars', [CalendarController::class, 'getAjaxData'])->name('get-calendars.data');
    Route::get('/calendar/view/{id}', [CalendarController::class, 'calendarView'])->name('calendar.view');
    Route::post('/calendar/upload', [CalendarController::class, 'upload'])->name('calendar.upload');
    Route::post('/calendar/upload/store', [CalendarController::class, 'uploadStore'])->name('calendar.upload.store');
    Route::post('/calendar/upload-remove/{id}', [CalendarController::class, 'uploadRemove'])->name('calendar.upload-remove');

    Route::get('/calendar/comment/{id}', [CalendarController::class, 'calendarComment'])->name('calendar.comment');
    Route::post('/calendar/comment/store', [CalendarController::class, 'calendarCommentStore'])->name('calendar.comment.store');
    Route::post('/calendar/comment/upload', [CalendarController::class, 'calendarCommentUpload'])->name('calendar.comment.upload');
    Route::post('/calendar/comment/delete/{id}', [CalendarController::class, 'calendarCommentDelete'])->name('calendar.comment.delete');
    Route::get('/get-subcategories/{categoryId}', [ClientController::class, 'getSubcategories'])->name('get.subcategories');

    Route::get('/category', [CategoryController::class, 'index'])->name('category');
    Route::get('/get-category', [CategoryController::class, 'getAjaxData'])->name('get-category.data');
    Route::post('/category/store', [CategoryController::class, 'store'])->name('category.store');
    Route::post('/category/delete/{id}', [CategoryController::class, 'destroy'])->name('category.delete');
    Route::get('/category/edit/{id}', [CategoryController::class, 'edit'])->name('category.edit');
    Route::post('/category/update/{id}', [CategoryController::class, 'update'])->name('category.update');

    Route::get('/sub-category', [CategoryController::class, 'indexSubCategory'])->name('sub-category');
    Route::get('/get-sub-category', [CategoryController::class, 'getAjaxDataSubCategory'])->name('get-sub-category.data');
    Route::post('/sub-category/store', [CategoryController::class, 'storeSubCategory'])->name('sub-category.store');
    Route::post('/sub-category/delete/{id}', [CategoryController::class, 'destroySubCategory'])->name('sub-category.delete');
    Route::get('/sub-category/edit/{id}', [CategoryController::class, 'editSubCategory'])->name('sub-category.edit');
    Route::post('/sub-category/update/{id}', [CategoryController::class, 'updateSubCategory'])->name('sub-category.update');

    Route::get('/use-types', [CategoryController::class, 'indexUseTypes'])->name('use-types');
    Route::get('/get-use-types', [CategoryController::class, 'getAjaxDataUseTypes'])->name('get-use-types.data');
    Route::post('/use-types/store', [CategoryController::class, 'storeUseTypes'])->name('use-types.store');
    Route::post('/use-types/delete/{id}', [CategoryController::class, 'destroyUseTypes'])->name('use-types.delete');

    Route::get('/licenses-agreements', [LicensesAgreementsController::class, 'index'])->name('licenses-agreements');
    Route::get('/get-licenses-agreements', [LicensesAgreementsController::class, 'getAjaxData'])->name('get-licenses-agreements.data');
    Route::get('/licenses-agreements/add', [LicensesAgreementsController::class, 'create'])->name('licenses-agreements.create');
    Route::post('/licenses-agreements/store', [LicensesAgreementsController::class, 'store'])->name('licenses-agreements.store');
    Route::get('/licenses-agreements/edit/{id}', [LicensesAgreementsController::class, 'edit'])->name('licenses-agreements.edit');
    Route::get('/licenses-agreements/view/{id}', [LicensesAgreementsController::class, 'view'])->name('licenses-agreements.view');
    Route::post('/licenses-agreements/update/{id}', [LicensesAgreementsController::class, 'update'])->name('licenses-agreements.update');
    Route::post('/licenses-agreements/delete/{id}', [LicensesAgreementsController::class, 'destroy'])->name('licenses-agreements.delete');
    Route::get('/licenses-agreements/get-user-type/{client}', [LicensesAgreementsController::class, 'getUserType'])->name('licenses-agreements.clients.getUserType');

    Route::post('/licenses-agreements/attachment/delete/{id}', [LicensesAgreementsController::class, 'deleteAttachment'])->name('licenses-agreements.attachment.delete');
    Route::get('/licenses-agreements/attachment/download/{id}', [LicensesAgreementsController::class, 'downloadAttachment'])->name('licenses-agreements.attachment.download');
    Route::post('/licenses-agreements/comment/store', [LicensesAgreementsController::class, 'licensesCommentStore'])->name('licenses.comment.store');
    Route::post('/licenses-agreements/comment/delete/{id}', [LicensesAgreementsController::class, 'licensesCommentDelete'])->name('licenses.comment.delete');

    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets');
    Route::get('/get-budgets', [BudgetController::class, 'getAjaxData'])->name('get-budgets.data');
    Route::get('/budget/add', [BudgetController::class, 'create'])->name('budget.create');
    Route::post('/budget/store', [BudgetController::class, 'store'])->name('budget.store');
    Route::get('/budget/edit/{id}', [BudgetController::class, 'edit'])->name('budget.edit');
    Route::get('/budget/view/{id}', [BudgetController::class, 'view'])->name('budget.view');
    Route::post('/budget/update/{id}', [BudgetController::class, 'update'])->name('budget.update');
    Route::post('/budget/delete/{id}', [BudgetController::class, 'destroy'])->name('budget.delete');
    Route::get('/budget/get-user-type/{client}', [BudgetController::class, 'getUserType'])->name('budget.clients.getUserType');
    Route::get('/get-budget-record/{id}', [BudgetController::class, 'getBudgetRecord'])->name('get-budget-record');
    Route::post('/generate-invoice', [BudgetController::class, 'generateInvoice'])->name('generate-invoice');
    Route::get('/get-invoice-data', [BudgetController::class, 'getInvoiceData'])->name('get-invoice.data');
    Route::get('/get-invoice/view/{id}', [BudgetController::class, 'viewGetInvoice'])->name('get-invoice.view');
    Route::get('/budget/check-deadline', [BudgetController::class, 'checkDeadlineAlert'])->name('budget.check-deadline');
    Route::get('/invoice/concepts/total', [BudgetController::class, 'invoiceConceptTotals'])->name('invoice.concepts.total');
    Route::get('/invoice/download', [BudgetController::class, 'invoiceDownload'])->name('invoice.download');

    Route::get('/billing-list', [BillingListController::class, 'index'])->name('billing-list');
    Route::get('/get-billing-list', [BillingListController::class, 'getAjaxData'])->name('get-billing-list.data');
    Route::get('/billing-list/add', [BillingListController::class, 'create'])->name('billing-list.create');
    Route::post('/billing-list/store', [BillingListController::class, 'store'])->name('billing-list.store');
    Route::get('/billing-list/edit/{id}', [BillingListController::class, 'edit'])->name('billing-list.edit');
    Route::get('/billing-list/view/{id}', [BillingListController::class, 'view'])->name('billing-list.view');
    Route::post('/billing-list/update/{id}', [BillingListController::class, 'update'])->name('billing-list.update');
    Route::post('/billing-list/delete/{id}', [BillingListController::class, 'destroy'])->name('billing-list.delete');
    Route::get('/billing-list/get-user-type/{client}', [BillingListController::class, 'getUserType'])->name('billing-list.clients.getUserType');
    Route::get('/billing-list/get-invoices/{invoice}', [BillingListController::class, 'getInvoices'])->name('billing-list.invoices.getUserType');


    Route::get('/billing/list', [BudgetController::class,'billingList'])->name('billing.list');
    Route::get('/billing/credit-notes', [BudgetController::class,'creditNotesList'])->name('billing.creditnotes');
    Route::post('/billing/credit-note', [BudgetController::class,'storeCreditNote'])->name('billing.cn.store');
    Route::delete('/billing/credit-note/{id}', [BudgetController::class,'deleteCreditNote'])->name('billing.cn.delete');
    Route::post('/billing/register-invoice', [BudgetController::class,'quickRegisterInvoice'])->name('billing.quick.invoice');
    Route::post('/billing/upload', [BudgetController::class,'billingUpload'])->name('billing.upload');
    Route::get('/billing/download', [BudgetController::class,'billingDownload'])->name('billing.download');
    Route::get('/billing/report', [BudgetController::class,'billingReport'])->name('billing.report');


    Route::get('/income/data', [BudgetController::class, 'getIncomeData'])->name('income.data');
    Route::post('/income/store', [BudgetController::class, 'storeIncome'])->name('income.store');
    Route::get('/income/edit/{id}', [BudgetController::class, 'getIncome'])->name('income.get');
    Route::post('/income/update/{id}', [BudgetController::class, 'updateIncome'])->name('income.update');
    Route::delete('/income/delete/{id}', [BudgetController::class, 'deleteIncome'])->name('income.delete');
    Route::get('/income/company-invoices/{companyId}', [BudgetController::class, 'getCompanyInvoices'])->name('income.company.invoices');
    Route::get('/income/download/report', [BudgetController::class, 'downloadIncomeReport'])->name('income.download');
    Route::get('/income/totals-by-concept', [BudgetController::class, 'getIncomeTotalsByConcept'])->name('income.totals.by.concept');


    Route::get('/banks', [BankController::class, 'index'])->name('banks');
    Route::get('/get-banks', [BankController::class, 'getAjaxData'])->name('get-banks.data');
    Route::get('/bank/add', [BankController::class, 'create'])->name('bank.create');
    Route::post('/bank/store', [BankController::class, 'store'])->name('bank.store');
    Route::get('/bank/edit/{id}', [BankController::class, 'edit'])->name('bank.edit');
    Route::post('/bank/update/{id}', [BankController::class, 'update'])->name('bank.update');
    Route::post('/bank/delete/{id}', [BankController::class, 'destroy'])->name('bank.delete');

    Route::get('/criterions', [BudgetCriterionController::class, 'index'])->name('criterions');
    Route::get('/get-criterions', [BudgetCriterionController::class, 'getAjaxData'])->name('get-criterions.data');
    Route::post('/criterion/store', [BudgetCriterionController::class, 'store'])->name('criterion.store');
    Route::get('/criterion/edit/{id}', [BudgetCriterionController::class, 'edit'])->name('criterion.edit');
    Route::post('/criterion/update/{id}', [BudgetCriterionController::class, 'update'])->name('criterion.update');
    Route::post('/criterion/delete/{id}', [BudgetCriterionController::class, 'destroy'])->name('criterion.delete');

    // Validation CRUD operations
    Route::get('validations/data', [BudgetController::class, 'getValidationData'])->name('validations.data');
    Route::get('validations/preview', [BudgetController::class, 'previewValidation'])->name('validations.preview');
    Route::post('validations/create', [BudgetController::class, 'createValidation'])->name('validations.create');
    Route::get('validations/{id}', [BudgetController::class, 'getValidation'])->name('validations.get');
    Route::get('validations/view/{id}', [BudgetController::class, 'viewValidation'])->name('validations.view');
    Route::put('validations/{id}', [BudgetController::class, 'updateValidation'])->name('validations.update');
    Route::delete('validations/{id}', [BudgetController::class, 'deleteValidation'])->name('validations.delete');
    
    // Validation submission and management
    Route::post('validations/{id}/submit', [BudgetController::class, 'submitValidation'])->name('validations.submit');
    Route::post('validations/{id}/resend', [BudgetController::class, 'resendValidation'])->name('validations.resend');
    

    // Distributions Routes
    Route::get('/distributions/data', [BudgetController::class, 'getDistributionsData'])->name('distributions.data');
    Route::get('/validated-incomes', [BudgetController::class, 'getValidatedIncomes'])->name('validated.incomes');
    Route::post('/distributions/create', [BudgetController::class, 'createDistributions'])->name('distributions.create');
    Route::post('/settlements/create', [BudgetController::class, 'createSettlement'])->name('settlements.create');
    Route::get('/settlements/data', [BudgetController::class, 'getSettlementsData'])->name('settlements.data');
    Route::get('/settlements/{id}', [BudgetController::class, 'viewSettlement'])->name('settlements.view');
    Route::post('/settlements/{id}/mark-paid', [BudgetController::class, 'markSettlementPaid'])->name('settlements.mark.paid');
    Route::get('/settlements/{id}/download', [BudgetController::class, 'downloadSettlementReport'])->name('settlements.download');
    Route::get('/associates/list', [BudgetController::class, 'getAssociatesForSettlement'])->name('associates.list');
    Route::get('/settlements/report/download', [BudgetController::class, 'downloadSettlementsReport'])->name('settlements.report.download');

    // Missing routes that are needed:
    Route::get('/distributions/{id}', [BudgetController::class, 'getDistribution'])->name('distributions.get');
    Route::put('/distributions/{id}', [BudgetController::class, 'updateDistribution'])->name('distributions.update');
    Route::delete('/distributions/{id}', [BudgetController::class, 'deleteDistribution'])->name('distributions.delete');

    Route::get('/portfolio/data', [BudgetController::class, 'getPortfolioData'])->name('portfolio.data');
    Route::get('/portfolio/export', [BudgetController::class, 'exportPortfolioReport'])->name('portfolio.export');
    Route::post('/portfolio/comments/store', [BudgetController::class, 'storePortfolioComment'])->name('portfolio.comments.store');
    Route::post('/portfolio/comments/{id}/approve', [BudgetController::class, 'approvePortfolioComment'])->name('portfolio.comments.approve');
    Route::delete('/portfolio/comments/{id}', [BudgetController::class, 'deletePortfolioComment'])->name('portfolio.comments.delete');

    Route::get('/assign-settlement', [AssignSettlementController::class, 'index'])->name('assign-settlement');
    Route::get('/get-assign-settlement', [AssignSettlementController::class, 'getAjaxData'])->name('get-assign-settlement.data');
    Route::post('/assign-settlement/store', [AssignSettlementController::class, 'store'])->name('assign-settlement.store');
    Route::post('/assign-settlement/delete/{id}', [AssignSettlementController::class, 'destroy'])->name('assign-settlement.delete');

    Route::get('/invoice-consecutive', [InvoiceConsecutiveController::class, 'index'])->name('invoice-consecutive');
    Route::get('/get-invoice-consecutive', [InvoiceConsecutiveController::class, 'getAjaxData'])->name('get-invoice-consecutive.data');
    Route::post('/invoice-consecutive/store', [InvoiceConsecutiveController::class, 'store'])->name('invoice-consecutive.store');
    Route::get('/invoice-consecutive/edit/{id}', [InvoiceConsecutiveController::class, 'edit'])->name('invoice-consecutive.edit');
    Route::post('/invoice-consecutive/update/{id}', [InvoiceConsecutiveController::class, 'update'])->name('invoice-consecutive.update');
    Route::post('/invoice-consecutive/delete/{id}', [InvoiceConsecutiveController::class, 'destroy'])->name('invoice-consecutive.delete');

    Route::get('/receipt-consecutive', [ReceiptConsecutiveController::class, 'index'])->name('receipt-consecutive');
    Route::get('/get-receipt-consecutive', [ReceiptConsecutiveController::class, 'getAjaxData'])->name('get-receipt-consecutive.data');
    Route::post('/receipt-consecutive/store', [ReceiptConsecutiveController::class, 'store'])->name('receipt-consecutive.store');
    Route::get('/receipt-consecutive/edit/{id}', [ReceiptConsecutiveController::class, 'edit'])->name('receipt-consecutive.edit');
    Route::post('/receipt-consecutive/update/{id}', [ReceiptConsecutiveController::class, 'update'])->name('receipt-consecutive.update');
    Route::post('/receipt-consecutive/delete/{id}', [ReceiptConsecutiveController::class, 'destroy'])->name('receipt-consecutive.delete');

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/run-seeder', function () {
    try {
        Artisan::call('db:seed');
        return 'Database seeders executed successfully';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});
