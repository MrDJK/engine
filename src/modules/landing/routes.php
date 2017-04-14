<?php

$app->get ( '/signin', \LandingController::class . ':signin' )->setName('signin');
$app->post ( '/signin', \LandingController::class . ':signin' );
$app->get ( '/auth/{code}', \LandingController::class . ':authCode' )->setName('auth');
$app->get ( '/signup', \LandingController::class . ':signup' )->setName ( 'signup' );
$app->post ( '/signup', \LandingController::class . ':signup' );
$app->get ( '/signout', \LandingController::class . ':signout' );
