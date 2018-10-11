<?php

class Wp_EazyCV_Apply {

	public $job = null;
	private $api = null;

	function __construct( $api, $job = null ) {
		$this->api = $api;
		$this->job = $job;
	}

	public function render() {

		if(!empty($this->job)){
			$html = '<h2>' . $this->job['functiontitle'] . '</h2>';
		} else {
			$html = '<h2>' . __('Open Application') . '</h2>';
			$html .= '<p></p>';
		}
		$html .'<form method="post">';
		$html .= '<fieldset>';
		$html .= '<label>'.__('Upload je CV').'</label><Br/>
		<input type="file" name="resume">';
		$html .= '<Br/><input type="submit" value="'.__('Submit').'">';

		$html .= '</fieldset>';
		$html .'</form>';

		return $html;
	}
}