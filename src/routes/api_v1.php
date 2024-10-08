<?php

use App\Http\Controllers\DashboardDrawerItemController;
use App\Http\Controllers\SidebarMenuItemController;
use App\Http\Controllers\TableDrawerItemListController;
use App\Http\Controllers\UserAuthEmailVerifyController;
use App\Http\Controllers\UserAuthenticationController;
use App\Http\Controllers\TenantAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/status', function (Request $request): string {
    return "API is live";
});

Route::controller(UserAuthenticationController::class)->group(function () {
    Route::post('user_register', 'registerNewUser');
    Route::post('user_login', 'loginUser');
    Route::post('refresh', 'refreshToken');
    Route::post('drawer_item', 'drawerItems');
    Route::post('dashboard_item', 'dashboardItem');
})->middleware(['cors']);

Route::controller(TenantAuthController::class)->group(function () {
    Route::post('tenant_user_login', 'tenantLoginUser');
})->middleware(['auth:api']);

Route::controller(TenantAuthController::class)->group(function () {
    Route::post('tenant_user_register', 'tenantUserRegister');
})->middleware('cors');

Route::controller(DashboardDrawerItemController::class)->group(function () {
    Route::post('drawer_item', 'drawerItems');
    Route::post('dashboard_item', 'dashboardItem');
    Route::post('menu_structure', 'storeSidebarMenuItems');
})->middleware(['cors']);

Route::controller(TableDrawerItemListController::class)->group(function () {
    Route::post('add_new_drawer_item', 'storeTableDrawerItemList');
    Route::get('get_drawer_item_list', 'retrieveDrawerItemList');
})->middleware(['cors']);

Route::controller(SidebarMenuItemController::class)->group(function () {
    Route::post('menu_structure', 'storeSidebarMenuItems');
    Route::get('get_menu_structure', 'getSidebarMenuItems');
})->middleware(['cors']);

Route::controller(UserAuthenticationController::class)->group(function () {
    Route::get('get_user_details', 'getUserDetails');
    Route::post('logout_user', 'userLogout');
})->middleware('auth:api');

Route::post('/email/verify', [UserAuthEmailVerifyController::class, 'verifyUserAuthEmail'])->name('verification.verify');