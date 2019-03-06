<?php

class Wp_EazyCV_Job_Search {

	private $api = null;
	private $atts = null;

	function __construct( $api, $atts ) {
		$this->api  = $api;
		$this->atts = $atts;

	}

	public function render() {
		$not_default_form = false;


		if ( empty( $this->atts['portal_id'] ) ) {
			$this->atts['portal_id'] = get_option( 'wp_eazycv_apply_form' );
		} else {
			$not_default_form = true;
		}
		$filters = [
			'channels' => [
				$this->atts['portal_id']
			]
		];

		if (!empty( $this->atts['job_type'] ) ) {
			$filters['job_type'] = $this->atts['job_type'] == 'project' ? 'project' : 'job';
		}

		if ( $not_default_form == true && empty( $this->atts['portal_id'] ) ) {
			return '<div class="eazy-error">' . __( 'Er is (nog) geen inschrijfformulier ingesteld.' ) . '</div>';
		}

		$jobs = $this->api->get( 'jobs/published', [
			'filter' => $filters
		] );

		$html = '';
		foreach ( $jobs['data'] as $job ) {

			if ( $not_default_form ) {
				$url       = get_site_url().'/'.get_option( 'wp_eazycv_jobpage' ) . '/' . sanitize_title( $job['original_functiontitle'] ) . '-' . $job['id'] . '?applyform=' . $this->atts['portal_id'];
				$url_apply = get_site_url().'/'.get_option( 'wp_eazycv_apply_page' ) . '/' . sanitize_title( $job['original_functiontitle'] ) . '-' . $job['id'] . '?applyform=' . $this->atts['portal_id'];
			} else {
				$url_apply = get_site_url().'/'.get_option( 'wp_eazycv_apply_page' ) . '/' . sanitize_title( $job['original_functiontitle'] ) . '-' . $job['id'];
				$url       = get_site_url().'/'.get_option( 'wp_eazycv_jobpage' ) . '/' . sanitize_title( $job['original_functiontitle'] ) . '-' . $job['id'];
			}

			$html .= '<div class="eazycv-job-row">';
			$html .= '<h4><a href="' . $url . '">' . $job['original_functiontitle'] . '</a></h4>';
			if ( isset( $job['texts']['meta']['content'] ) ) {
				$html .= '<p>' . $job['texts']['meta']['content'] . '</p>';
			} else if ( isset( $job['texts']['summary']['content'] ) ) {
				$html .= '<p>' . $job['texts']['summary']['content'] . '</p>';
			}

			$html .= '<div class="eazycv-job-row-details">';
			$html .= '<div class="eazycv-job-row-reference">' . $job['reference'] . '</div>';
			$html .= '<div class="eazycv-job-row-created_at">' . date( 'd-m-Y', strtotime( $job['created_at'] ) ) . '</div>';
			if ( isset( $job['address']['city'] ) ) {
				$html .= '<div class="eazycv-job-row-city">' . $job['address']['city'] . '</div>';
			}
			$html .= '</div>';
			$html .= '<div class="eazycv-job-row-apply">';
			$html .= '<div class="eazycv-job-row-link-details"><a href="' . $url . '">' . __( 'Bekijk details' ) . '</a></div>';
			$html .= '<div class="eazycv-job-row-link-apply"><a href="' . $url_apply . '">' . __( 'Solliciteer' ) . '</a></div>';
			$html .= '</div>';
			$html .= '</div>';

		}

		return $html;
	}
}