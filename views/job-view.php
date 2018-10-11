<?php

class Wp_EazyCV_Job {

	public $job = null;
	private $api = null;

	function __construct( $job, $api ) {
		$this->api = $api;
		$this->job = $job;
	}

	public function render() {
		$html = '<h2>' . $this->job['functiontitle'] . '</h2>';
		foreach ( $this->job['texts'] as $text ) {

			$html .= '<h3>' . $text['heading_label'] . '</h3>';
			$html .= '<p>' . $text['content'] . '</p>';
		}

		return $html;
	}
}