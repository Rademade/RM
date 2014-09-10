<?php
class RM_Content_Field_Process_Text
	extends
        RM_Content_Field_Process {

    use RM_Content_Field_Process_Singleton;

	private function br2nl($string) {
		return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
	}
	
	private function removeNewLine($string) {
		$string = str_replace("\n", "", $string);
		$string = str_replace("\r", "", $string);
		return $string;
	}
	
	public function getInitialContent($html) {
		return $this->br2nl(
			htmlspecialchars_decode($html)
		);
	}
	
	public function getParsedContent($string) {
		$string = nl2br(htmlspecialchars($string));
		$string = $this->removeNewLine($string);
		return trim($string);
	}
	
}