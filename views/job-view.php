<?php

class Wp_EazyCV_Job {

	public $job = null;
	private $api = null;

	function __construct( $job, $api ) {
		$this->api = $api;
		$this->job = $job;
	}

	public function render() {

		//ander inschrijfjformulier meegegeven?
		$mainForm = '';
		if ( isset( $_GET['applyform'] ) ) {
			$mainForm = '?applyform=' . intval( $_GET['applyform'] );
		}

		$html = '<h2>' . $this->job['functiontitle'] . '</h2>';
		foreach ( $this->job['texts'] as $text ) {

			$html .= '<h3>' . $text['heading_label'] . '</h3>';
			$html .= '<p>' . $text['content'] . '</p>';

		}

		if ( ! empty( $this->job ) ) {
			$html .= '<a class="eazycv-apply-to-job eazycv-btn" href="' . get_site_url().'/'.get_option( 'wp_eazycv_apply_page' ) . '/' . sanitize_title( $this->job['functiontitle'] ) . '-' . $this->job['id'] . $mainForm . '">' . __( 'Solliciteren' ) . '</a>';
		} else {
			$html .= '<a class="eazycv-apply-to-job eazycv-btn" href="' . get_site_url().'/'.get_option( 'wp_eazycv_apply_page' ) . '/open' . $mainForm . '">' . __( 'Solliciteren' ) . '</a>';
		}

		return $html;
	}
}