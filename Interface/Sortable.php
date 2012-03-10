<?php
interface RM_Interface_Sortable {
	
	const ACTION_SORT = 4;

	public function setPosition( $position );
	public function getPosition();
	
}