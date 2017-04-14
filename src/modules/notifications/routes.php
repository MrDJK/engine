<?php

$app->get ( '/notifications', \NotificationsController::class . ':notifications' )->setName('notifications');
$app->get ( '/notifications/delete/{id}', \NotificationsController::class . ':deleteNotification');
