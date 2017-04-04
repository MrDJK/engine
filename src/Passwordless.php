<?php

namespace Passwordless;

class Passwordless {

	private $db = null;
	private $mailer = null;
	private $dateFormat = 'Y-m-d H:i:s';

	public function __construct ( $db, $mailer ) {
		$this->db = $db;
		$this->mailer = $mailer;

		return $this;
	}


	public function requestUniqCode ( $email ) {

		$data = $db->select ( 'passwordlessCodes', [
			'code',
			'expiry',
			'requested',
		], [
			'email' => $email
		]);


	}


	public function generateUniqCode ( $email ) {
		
		//check email exists
		$timestamp = time();
		$expiry = $timestamp + 900;

		$this->removeCode ( $email );
		$code = $this->createUniqCode ( $email, date($this->dateFormat, $timestamp) );
		if ( $this->saveCode ( $email, $code, $timestamp, $expiry ) ) {
			return $code;
		}

		return false;
	}

	private function createUniqCode ( $email, $time ) {
		return hash ( 'ripemd160', $email.$time );
	}


	public function validateCode ( $code ) {
		$data = $this->db->select ( 'passwordlessCodes', [
			'email',
			'requested',
			'expiry'
		], [
			'code' => $code
		]);

		if ( is_array ( $data ) && array_key_exists ( 0, $data ) ) {
			$this->removeCode ( null, $code );
			
			if ( strtotime ( $data[0]['expiry'] ) >= time() ) {
				return [ 'code' => $code, 'email' => $data[0]['email']];
			} else {
				return false;
			}
		}

		return false;
	}

	private function saveCode ( $email, $code, $requested, $expiry ) {
		$ins = $this->db->insert ( 'passwordlessCodes', [
			'email' => $email,
			'code' => $code,
			'expiry' => date ( $this->dateFormat, $expiry ),
			'requested' => date ( $this->dateFormat, $requested )
		]);

		if ( $ins ) {
			return $this->emailCode ( $email, $code );
		}

		return false;
	}

	private function emailCode ( $email, $code ) {

		$this->mailer->isSMTP(); // Set mailer to use SMTP
		$this->mailer->setFrom ( 'localhost', 'Engine Emailer' );
		$this->mailer->addAddress ( $email );
		$this->mailer->isHTML ( true );
		$this->mailer->Subject = 'Your authentication code';
		$this->mailer->Body = '<a href="localhost/auth/'.$code.'">Sign In</a>';

		if ( $this->mailer->send() ) {
			return true;
		}

		echo ( $this->mailer->ErrorInfo );

		return false;
	}

	private function removeCode ( $email = null, $code = null ) {

		if  ( $email !== null || $code !== null ) {
			$del = $this->db->delete ( 'passwordlessCodes', [ 
				'OR' => [
					'code' => $code,
					'email' => $email
				]
			]);

			if ( $del > 0 ) {
				return true;
			}
		}

		return false;
	}
}
