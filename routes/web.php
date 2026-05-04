<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AdminPagesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

/* ── Authentication ── */
Route::get('/', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/* ── Password Reset ── */
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendLink'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

/* ── Protected ── */
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/staff', [AdminPagesController::class, 'staffIndex'])->name('admin.staff.index');
    Route::get('/admin/admins', [AdminPagesController::class, 'adminIndex'])->name('admin.admins.index');
    Route::get('/admin/customers', [AdminPagesController::class, 'customerIndex'])->name('admin.customers.index');
    Route::get('/staff/customers', [AdminPagesController::class, 'staffCustomerIndex'])->name('staff.customers.index');
    Route::get('/customers/{customer}', [AdminPagesController::class, 'customerShow'])->name('customers.show');

    /* ── Inventory ── */
    Route::get('/admin/inventory', [InventoryController::class, 'index'])->name('admin.inventory.index');
    Route::post('/admin/inventory', [InventoryController::class, 'store'])->name('admin.inventory.store');
    Route::put('/admin/inventory/{product}', [InventoryController::class, 'update'])->name('admin.inventory.update');
    Route::post('/admin/inventory/{product}/adjust', [InventoryController::class, 'adjustStock'])->name('admin.inventory.adjust');
    Route::delete('/admin/inventory/{product}', [InventoryController::class, 'destroy'])->name('admin.inventory.destroy');

    /* ── Orders ── */
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::get('/orders/stock-check', [OrderController::class, 'stockCheck'])->name('orders.stockCheck');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/approve', [OrderController::class, 'approve'])->name('orders.approve');
    Route::post('/orders/{order}/reject', [OrderController::class, 'reject'])->name('orders.reject');
    Route::post('/orders/{order}/deliver', [OrderController::class, 'deliver'])->name('orders.deliver');

    /* ── Transactions ── */
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

    /* ── Notifications ── */
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    /* ── Deliveries ── */
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/create', [DeliveryController::class, 'create'])->name('deliveries.create');
    Route::post('/deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');
    Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show'])->name('deliveries.show');
    Route::post('/deliveries/{delivery}/approve', [DeliveryController::class, 'approve'])->name('deliveries.approve');
    Route::post('/deliveries/{delivery}/deliver', [DeliveryController::class, 'markDelivered'])->name('deliveries.deliver');

    /* ── Payments ── */
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
    Route::get('/users/create-staff', [UserManagementController::class, 'createStaff'])->name('users.create.staff');
    Route::post('/users/staff', [UserManagementController::class, 'storeStaff'])->name('users.store.staff');
    Route::get('/users/create-admin', [UserManagementController::class, 'createAdmin'])->name('users.create.admin');
    Route::post('/users/admin', [UserManagementController::class, 'storeAdmin'])->name('users.store.admin');
    Route::get('/users/create-customer', [UserManagementController::class, 'createCustomer'])->name('users.create.customer');
    Route::post('/users/customer', [UserManagementController::class, 'storeCustomer'])->name('users.store.customer');

    /* ── User Edit & Impersonation ── */
    Route::get('/users/{user}/edit', [UserManagementController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [UserManagementController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/impersonate', [UserManagementController::class, 'impersonate'])->name('users.impersonate');
    Route::post('/impersonate/stop', [UserManagementController::class, 'stopImpersonating'])->name('impersonate.stop');

    /* ── AJAX helpers ── */
    Route::get('/ajax/customers', [UserManagementController::class, 'ajaxCustomers'])->name('ajax.customers');
    Route::get('/ajax/customer-orders', [OrderController::class, 'ajaxCustomerOrders'])->name('ajax.customerOrders');
});
