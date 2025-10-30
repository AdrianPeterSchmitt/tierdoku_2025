<?php

use App\Controllers\HomeController;
use App\Controllers\AboutController;
use App\Controllers\AuthController;
use App\Controllers\KremationController;
use App\Controllers\HerkunftController;
use App\Controllers\UserController;
use App\Controllers\NotificationController;
use App\Controllers\StatisticsController;

return [
    // GET Routes
    'GET' => [
        '/' => [HomeController::class, 'index'],
        '/about' => [AboutController::class, 'index'],
        '/login' => [AuthController::class, 'loginForm'],
        '/logout' => [AuthController::class, 'logout'],
        '/kremation' => [KremationController::class, 'index'],
        '/kremation/export' => [KremationController::class, 'export'],
        '/kremation/{id}/qr' => [KremationController::class, 'showQRCode'],
        '/kremation/{id}/label' => [KremationController::class, 'downloadLabel'],
        '/kremation/scan' => [KremationController::class, 'scanQRCode'],
        '/herkunft' => [HerkunftController::class, 'index'],
        '/users' => [UserController::class, 'index'],
        '/statistics' => [StatisticsController::class, 'index'],
        '/notifications/unread-count' => [NotificationController::class, 'unreadCount'],
    ],

    // POST Routes
    'POST' => [
        '/login' => [AuthController::class, 'login'],
        '/extend-session' => [AuthController::class, 'extendSession'],
        '/users' => [UserController::class, 'store'],
        '/users/{id}' => [UserController::class, 'update'],
        '/users/{id}/delete' => [UserController::class, 'delete'],
        '/kremation' => [KremationController::class, 'store'],
        '/kremation/update' => [KremationController::class, 'update'],
        '/kremation/complete' => [KremationController::class, 'complete'],
        '/kremation/delete' => [KremationController::class, 'delete'],
        '/kremation/scan/process' => [KremationController::class, 'processScannedQR'],
        '/herkunft' => [HerkunftController::class, 'store'],
        '/herkunft/{id}' => [HerkunftController::class, 'update'],
        '/herkunft/{id}/delete' => [HerkunftController::class, 'delete'],
    ],
];
