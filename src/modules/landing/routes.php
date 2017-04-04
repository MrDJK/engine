<?php

$app->get ( '/signin[/{error}]', \LandingController::class . ':signin' )->setName('signin');
$app->post ( '/signin', \LandingController::class . ':signin' );
$app->get ( '/auth/{code}', \LandingController::class . ':authCode' )->setName('auth');
$app->get ( '/signup[/{error}]', \LandingController::class . ':signup' )->setName ( 'signup' );
$app->post ( '/signup', \LandingController::class . ':signup' );
