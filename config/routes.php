<?php
/**
 * Routes Configuration File
 *
 * This file defines all the routes for the Slim application.
 */

use Slim\App;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;


return function (App $app) {
    $app->get('/', [AuthController::class, 'loginPage'])->setName('home');
    $app->get('/login', [AuthController::class, 'loginPage'])->setName('auth.loginPage');
    $app->post('/login', [AuthController::class, 'login'])->setName('auth.login');
    $app->get('/logout', [AuthController::class, 'logout'])->setName('auth.logout');
	$app->get('/dashboard', [DashboardController::class, 'index'])->setName('dashboard');
};
