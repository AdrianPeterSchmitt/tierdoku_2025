<?php

use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\KremationService;
use App\Services\AuthService;
use App\Services\QRCodeService;
use App\Services\PDFLabelService;
use App\Controllers\AuthController;
use Illuminate\Container\Container;

$container = new Container();

// Register services
$container->singleton(AuditService::class, function ($container) {
    return new AuditService();
});

$container->singleton(NotificationService::class, function ($container) {
    return new NotificationService();
});

$container->singleton(AuthService::class, function ($container) {
    return new AuthService();
});

$container->singleton(QRCodeService::class, function ($container) {
    return new QRCodeService();
});

$container->singleton(PDFLabelService::class, function ($container) {
    return new PDFLabelService();
});

$container->singleton(KremationService::class, function ($container) {
    return new KremationService(
        $container->get(AuditService::class),
        $container->get(NotificationService::class)
    );
});

// Register controllers
$container->singleton(AuthController::class, function ($container) {
    return new AuthController($container->get(AuthService::class));
});

$container->singleton(\App\Controllers\KremationController::class, function ($container) {
    return new \App\Controllers\KremationController(
        $container->get(KremationService::class),
        $container->get(NotificationService::class),
        $container->get(QRCodeService::class),
        $container->get(PDFLabelService::class)
    );
});

$container->singleton(\App\Controllers\HerkunftController::class, function ($container) {
    return new \App\Controllers\HerkunftController();
});

$container->singleton(\App\Controllers\UserController::class, function ($container) {
    return new \App\Controllers\UserController();
});

$container->singleton(\App\Controllers\NotificationController::class, function ($container) {
    return new \App\Controllers\NotificationController($container->get(NotificationService::class));
});

$container->singleton(\App\Controllers\StatisticsController::class, function ($container) {
    return new \App\Controllers\StatisticsController();
});

return $container;
