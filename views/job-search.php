<?php

class Wp_EazyCV_Job_Search {

	private $api = null;

	function __construct( $api ) {
		$this->api = $api;
	}

	public function render() {
		$jobs = $this->api->get( 'jobs/published' );
		$html = '';
		foreach ( $jobs['data'] as $job ) {
			$html .= '<div class="eazycv-job-row">';
			$html .= '<h4><a href="'.get_option('wp_eazycv_jobpage').'/'.sanitize_title($job['functiontitle']).'-'.$job['id'].'">' . $job['functiontitle'] . '</a></h4>';
			if ( isset( $job['texts']['meta']['content'] ) ) {
				$html .= '<p>' . $job['texts']['meta']['content'] . '</p>';
			} else if( isset( $job['texts']['summary']['content'] ) ) {
				$html .= '<p>' . $job['texts']['summary']['content'] . '</p>';
			}
			$html .= '</div>';
		}

		return $html;
	}
}