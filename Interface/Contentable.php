<?php
interface RM_Interface_Contentable {
	
	public function getIdContent();
	
	public function setContentManager(RM_Content $contentManager);
	
	public function getContentManager();
	
	public function getDefaultContent();
	
	public function getContent();
	
}