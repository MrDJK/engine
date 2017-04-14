<?php
namespace Notifications;

class Notifications {


	protected $db = null;

	public function __construct ( $db ) {
		$this->db = $db;
	}


	public function createNotification ( $player, $message ) {
		$playerClass = new \Player\Player ( $this->db );
		if ( $playerClass->validPlayer ( $player ) ) {
			$ins = $this->db->insert ( 'notifications', [
				'user' => $player,
				'message' => $message,
				'#date' => 'NOW()'
			]);

			if ( $ins ) {
				return true;
			}
		}
		return false;
	}

	public function deleteNotification ( $player, $noteID ) {
		$del = $this->db->delete ( 'notifications', [
			'user' => $player,
			'id' => $noteID
		]);

		if ( $del ) {
			return true;
		}
		return false;
	}
}
