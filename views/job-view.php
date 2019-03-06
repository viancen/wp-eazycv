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

		$html = '<div class="eazycv-job-body eazycv-job-' . $this->job['type'] . '">';
		$html .= '<h2 class="eazycv-job-view-h2">' . $this->job['original_functiontitle'] . '</h2>';

		foreach ( $this->job['texts'] as $kk => $text ) {
			if ( ! empty( trim( strip_tags( $text['content'] ) ) ) ) {
				$html .= '<h3 class="eazycv-job-view-h3" id="eazycv-job-heading eazycv-job-heading-' . sanitize_title( $kk ) . '">' . $text['heading_label'] . '</h3>';
				$html .= '<p class="eazycv-job-view-paragraph eazycv-job-paragraph-' . sanitize_title( $kk ) . '">' . $text['content'] . '</p>';
			}
		}

		if ( ! empty( $this->job ) ) {
			$html .= '<a class="eazycv-apply-to-job eazycv-btn" href="' . get_site_url() . '/' . get_option( 'wp_eazycv_apply_page' ) . '/' . sanitize_title( $this->job['functiontitle'] ) . '-' . $this->job['id'] . $mainForm . '">' . __( 'Solliciteren' ) . '</a>';
		} else {
			$html .= '<a class="eazycv-apply-to-job eazycv-btn" href="' . get_site_url() . '/' . get_option( 'wp_eazycv_apply_page' ) . '/open' . $mainForm . '">' . __( 'Solliciteren' ) . '</a>';
		}
		$html .= '</div>';

		return $html;
	}
}