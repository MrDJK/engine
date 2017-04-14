<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


class LandingController {

	protected $controller = null;

	public function __construct ( \Slim\Container $controller ) {
		$this->controller = $controller;
		
	}


	public function signin ( $request, $response, $args) {


		if ( $request->getMethod() == 'POST' ) {

			$return = [
				'type' => 'error',
				'code' => null,
				'msg' => 'No Email specified.'
			];
			$args = $request->getParsedBody();

			if ( array_key_exists ( 'email', $args ) && filter_var ( $args['email'], FILTER_VALIDATE_EMAIL ) ) {

				if ( $data = $this->controller->database->select ( 'players', ['id'], ['email' => $args['email'] ] ) ) {
					$code = $this->controller->passwordless->generateUniqCode ( $args['email'] );
					$return = [ 'type' => 'success', 'code' => $code, 'msg' => 'Please check your email for your signin link' ];
					return $this->controller->view->render ( $response, '@landing/index.html', $return );
				} else {
					$return = [ 'type' => 'error', 'msg' => 'Unable to find email' ];
				}

			} else {
				$return['msg'] = 'Please specify an email address';
			}

			return $this->controller->view->render ( $response, '@landing/index.html', $return );

		}

		return $this->controller->view->render ( $response, '@landing/index.html' );
	}

	public function signup ( $request, $response, $args ) {

		if ( $request->getMethod() == 'POST' ) {

			$return = [
				'type' => 'error',
				'msg' => 'Please fill in all fields.'
			];


			$args = $request->getParsedBody();
			$name = null;
			$email = null;

			if ( array_key_exists ( 'email', $args ) && filter_var ( $args['email'], FILTER_VALIDATE_EMAIL ) ) {
				if ( $this->existCheck ( 'email', $args['email'] ) ) {
					$return['msg'] = 'Email address already in use.';
				} else {
					$email = $args['email'];
				}
			} else {
				$return['msg'] = 'Please specify a valid email address.';
			}

			if ( $email ) {
				if ( array_key_exists ( 'name', $args ) && is_string ( $args['name'] ) ) {

					if ( strlen ( trim ( $args['name'] ) ) >= 3 && strlen ( trim ( $args['name'] ) ) <= 30 ) {
						if ( $this->existCheck ( 'name', $args['name'] ) ) {
							$return['msg'] = 'Username already in use.';
						} else {
							$name = filter_var ( $args['name'], FILTER_SANITIZE_STRING );
						}
					} else { 
						$return['msg'] = 'Username doesn\'t match the character requirements ( 3 - 30 characters )';
					}
				}
			}

			if ( $email && $name ) {
				if ( $this->controller->database->insert ( 'players', [
					'name' => $name,
					'email' => $email,
				]) ) {

					$code = $this->controller->passwordless->generateUniqCode ( $email );
					$return = [ 'type' => 'success', 'code' => $code, 'msg' => 'Please check your email for your signin link' ];
				} else {
					$return['msg'] = 'Unable to add account to database, please contact an adminstrator';
				}
				
			}

			return $this->controller->view->render ( $response, '@landing/signup.html', $return ); 
		}

		return $this->controller->view->render ( $response, '@landing/signup.html' );

	}

	public function signout ( Request $request, Response $response ) {
		session_destroy();
		return $response->withStatus(502)->withHeader ( 'Location', '/signin' );	
	}

	private function existCheck ( $field, $value ) {

		if ( $field == 'email' || $field == 'name' ) {
			$data = $this->controller->database->select ( 'players', [
				'id'
			], [ $field => $value ] );

			if ( $data ) {
				return $data[0]['id'];
			}
		}

		return false;
	}

	public function authCode ( $request, $response ) {
		$code = $request->getAttribute ( 'code' );

		if ( $code ) {
			if ( $check = $this->controller->passwordless->validateCode ( $code ) ) {

				if ( $data = $this->existCheck ( 'email', $check['email'] ) ) {

					$_SESSION['id'] = $data;
					$_SESSION['auth'] = sha1 ( $_SERVER['HTTP_USER_AGENT'] );
					$_SESSION['activity'] = time();

					return $response->withStatus(302)->withHeader ( 'Location', '/' );
				} else {
					return $response->withStatus(502)->withHeader ( 'Location', '/' );
				}
			} else {
				return $response->withStatus(502)->withHeader ( 'Location', '/signin' );
			}
		}

		return $response->withStatus(502)->withHeader ( 'Location', '/signin' );

		
	}


}