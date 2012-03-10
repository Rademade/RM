<?php
interface RM_Interface_Hideable {

	const ACTION_STATUS = 1;

	const STATUS_SHOW = 1;
	const STATUS_HIDE = 2;

	public function isShow();
	public function show();
	public function hide();

}