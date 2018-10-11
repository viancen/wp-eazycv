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
			foreach ( $this->job['texts'] as $text ) {
				$html .= '<h3>' . $text['heading_label'] . '</h3>';
			}
		} else {
			$html = '<h2>' . __('Open Application') . '</h2>';
			$html .= '<p>Solliciteer erop </p>';
		}
		
		return $html;
	}
}