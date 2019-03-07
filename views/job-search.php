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
			$portalId = get_option( 'wp_eazycv_apply_form' );
		} else {
			$portalId         = $this->atts['portal_id'];
			$not_default_form = true;
		}

		$filters = [
			'channels' => [
				$portalId
			]
		];

		if ( ! empty( $this->atts['job_type'] ) ) {
			$filters['job_type'] = $this->atts['job_type'] == 'project' ? 'project' : 'job';
		}

		if ( empty( $portalId ) ) {
			return '<div class="eazy-error">' . __( 'Er is (nog) geen inschrijfformulier ingesteld.' ) . '</div>';
		}


		try {
			$formSettings = $this->api->get( 'connectivity/public-forms/' . $portalId );

		} catch ( Exception $exception ) {
			return '<div class="eazy-error">' . __( 'Er is een fout inschrijfformulier ingesteld.' ) . '</div>';
		}

		if ( ! empty( $formSettings['layout_settings'] ) ) {
			$formSettings['layout_settings'] = json_decode( $formSettings['layout_settings'], true );
		}

		$jobs = $this->api->get( 'jobs/published', [
			'filter' => $filters
		] );

		$html = '';
		foreach ( $jobs['data'] as $job ) {

			if ( empty( $job['original_functiontitle'] ) ) {
				$job['original_functiontitle'] = $job['functiontitle'];
			}

			if ( $not_default_form ) {
				if ( $job['type'] == 'job' ) {
					$url = get_site_url() . '/' . get_option( 'wp_eazycv_jobpage' ) . '/' . sanitize_title( $job['original_functiontitle'] ) . '-' . $job['id'] . '?applyform=' . $this->atts['portal_id'];
				} else {
					$url = get_site_url() . '/' . get_option( 'wp_eazycv_projectpage' ) . '/' . sanitize_title( $job['original_functiontitle'] ) . '-' . $job['id'] . '?applyform=' . $this->atts['portal_id'];
				}

				$url_apply = get_site_url() . '/' . get_option( 'wp_eazycv_apply_page' ) . '/' . sanitize_title( $job['original_functiontitle'] ) . '-' . $job['id'] . '?applyform=' . $this->atts['portal_id'];
			} else {
				$url_apply = get_site_url() . '/' . get_option( 'wp_eazycv_apply_page' ) . '/' . sanitize_title( $job['original_functiontitle'] ) . '-' . $job['id'];

				if ( $job['type'] == 'job' ) {
					$url = get_site_url() . '/' . get_option( 'wp_eazycv_jobpage' ) . '/' . sanitize_title( $job['original_functiontitle'] ) . '-' . $job['id'];
				} else {
					$url = get_site_url() . '/' . get_option( 'wp_eazycv_projectpage' ) . '/' . sanitize_title( $job['original_functiontitle'] ) . '-' . $job['id'];
				}

			}

			$html .= '<div class="eazycv-job-row">';
			$html .= '<h4><a href="' . $url . '">' . $job['original_functiontitle'] . '</a></h4>';


			if ( isset( $job['texts']['meta']['content'] ) ) {
				if ( isset( $formSettings['layout_settings']['max-words-job-result-text'] ) ) {
					$html .= '<p>' . eazy_first_words( $job['texts']['meta']['content'], intval( $formSettings['layout_settings']['max-words-job-result-text'] ) ) . '</p>';
				} else {
					$html .= '<p>' . $job['texts']['meta']['content'] . '</p>';
				}


			} else if ( isset( $job['texts']['summary']['content'] ) ) {
				if ( isset( $formSettings['layout_settings']['max-words-job-result-text'] ) ) {
					$html .= '<p>' . eazy_first_words( $job['texts']['summary']['content'], intval( $formSettings['layout_settings']['max-words-job-result-text'] ) ) . '</p>';
				} else {
					$html .= '<p>' . $job['texts']['summary']['content'] . '</p>';
				}
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