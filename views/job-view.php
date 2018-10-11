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
		if(!empty($this->job )){
			$html .= '<a href="/'.get_option('wp_eazycv_apply_page').'/'.sanitize_title($this->job['functiontitle']).'-'.$this->job['id'].'">' .__('Apply To Job') . '</a>';
		} else {
			$html .= '<a href="/'.get_option('wp_eazycv_apply_page').'/open">'  .__('Apply To Job') .'</a>';
		}
		return $html;
	}
}