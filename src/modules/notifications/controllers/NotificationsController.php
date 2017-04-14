<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class NotificationsController {

	protected $controller = null;

	public function __construct ( \Slim\Container $controller ) {
		$this->controller = $controller;
	}

	public function notifications ( Request $request, Response $response ) {
		if ( $info = $this->getNotifications() ) {
			$this->controller->view->render ( $response, '@notifications/index.html', ['notifications' => $info] );
		} else {
			$this->controller->view->render ( $response, '@notifications/index.html');
		}
	}

	public function deleteNotification ( Request $request, Response $response ) {

		$route = $request->getAttribute('route');
    	$noteID = $route->getArgument('id');

    	if ( filter_var ( $noteID, FILTER_VALIDATE_INT ) ) {
    		$noteClass = new \Notifications\Notifications ( $this->controller->database );
    		if ( $noteClass->deleteNotification ( $_SESSION['id'], $noteID ) ) {
    			return $this->controller->view->render ( $response, '@notifications/deleted.html', [ 'type' => 'success']  );
    		}
    	}

    	return $this->controller->view->render ( $response, '@notifications/deleted.html', [ 'type' => 'error']  );
	}
	

	private function getNotifications() {
		if ( $info = $this->controller->database->select ( 'notifications', ['id', 'message', 'date', 'read'], [ 'user' => $_SESSION['id'] ], ['ORDER BY' => 'read,date DESC'] ) ) {

			$this->controller->database->update ( 'notifications', ['read' => 1], ['user' => $_SESSION['id']] );

			return $info;
		}

		return false;
	}


}