<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class HomeController {

	protected $controller = null;

	public function __construct ( \Slim\Container $controller ) {
		$this->controller = $controller;
	}

	public function home ( Request $request, Response $response ) {
		$getName = $this->controller->player->getInfo ( $_SESSION['id'], '*' );
		$notes = new \Notifications\Notifications ( $this->controller->database );
		$notes->createNotification ( $_SESSION['id'], 'Test Notifications' );
		$this->controller->view->render($response, '@home/index.html', $getName[0]);
	}




}