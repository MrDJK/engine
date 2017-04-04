<?php

namespace Player;

class Player {


	protected $db = null;
	private $allColumnsHelper = [
		'id', 'name', 'email', 'level', 'cash'
	];

	public function __construct ( $db ) {
		$this->db = $db;
	}


	public function getInfo ( $id, $columns ) {

		$info = null;

		if ( filter_var ( $id, FILTER_VALIDATE_INT ) ) {
			if ( is_array ( $columns ) ) {
				$info = $this->db->select ( 'players', $columns, [ 'id' => $id ] );
			} else {
				if ( strtolower ( $columns ) == 'all' || $columns == '*' ) {
					$info = $this->db->select ( 'players', $this->allColumnsHelper, [ 'id' => $id ] );
				} else {
					$info = $this->db->select ( 'players', [ $columns ], [ 'id' => $id ] );
				}
			}
		}

		return $info;
	}
	
}