<?php

class Wp_EazyCV_Job
{

	public $job = null;
	private $api = null;

	function __construct($job, $api)
	{
		$this->api = $api;
		$this->job = $job;
	}

	public function render()
	{

		//ander inschrijfjformulier meegegeven?
		$mainForm = '';
		if (isset($_GET['applyform'])) {
			$mainForm = '?applyform=' . intval($_GET['applyform']);
		}


		if (empty($this->job['original_functiontitle'])) {
			$this->job['original_functiontitle'] = $this->job['functiontitle'];
		}

		if (!empty($this->job)) {
			$applyButton = '<a class="eazycv-apply-to-job eazycv-btn" href="' . get_site_url() . '/' . get_option('wp_eazycv_apply_page') . '/' . sanitize_title($this->job['functiontitle']) . '-' . $this->job['id'] . $mainForm . '">' . __('Solliciteren') . '</a>';
		} else {
			$applyButton = '<a class="eazycv-apply-to-job eazycv-btn" href="' . get_site_url() . '/' . get_option('wp_eazycv_apply_page') . '/open' . $mainForm . '">' . __('Solliciteren') . '</a>';
		}


		$urlBack = get_site_url() . '/' . get_option('wp_eazycv_jobsearch_page');
		$html = '<div class="eazycv-job-body eazycv-job-' . $this->job['type'] . '">';
		if (!empty($this->job['cover'])) {
			$html .= '<div class="eazycv-job-cover"><img src="https://eazycv.s3.eu-central-1.amazonaws.com/' . $this->job['cover'] . '?bust=' . rand(0, 292992) . '" alt="' . $this->job['functiontitle'] . '" /></div>';
		}
		$html .= '<div class="eazycv-job-breadcrumbs"><a href="' . get_site_url() . '">Home</a> &raquo; <a href="' . $urlBack . '">Alle vacatures</a> &raquo; <span> (' . $this->job['reference'] . ') ' . $this->job['functiontitle'] . ' </span> </div>';
		$html .= '<h2 class="eazycv-job-view-h2">' . $this->job['original_functiontitle'] . '</h2>';


		if (!empty($this->job['location_string'])) {
			$html .= '<div class="eazycv-job-body-location-string"><span class="eazycv-jobhead-labels">' . __('Locatie') . '</span> ' . $this->job['location_string'];
			if (!empty($this->job['default_distance'])) {
				$html .= ' <span class="eazycv-job-body-distance">(&#177; ' . $this->job['default_distance'] . ' km)</span>';
			}
			$html .= '</div>';
		}
		if (!empty($this->job['address']['city'])) {
			$html .= '<div class="eazycv-job-body-city"><span class="eazycv-jobhead-labels">' . __('Standplaats') . '</span> ' . $this->job['address']['city'] . '</div>';
		}

		if (!empty($this->job['discipline'])) {
			$html .= '<div class="eazycv-job-body-discipline"><span class="eazycv-jobhead-labels">' . __('Vakgebied') . '</span> ' . $this->job['discipline']['name'] . '</div>';
		}

		if (!empty($this->job['educations'])) {
			$educs = [];
			foreach ($this->job['educations'] as $e) {
				$educs[] = $e['name'];
			}
			$html .= '<div class="eazycv-job-body-education"><span class="eazycv-jobhead-labels">' . __('Opleidingsniveau') . '</span> ' . implode(', ', $educs) . '</div>';
		}


		$html .= '<div class="eazycv-apply-button-top">' . $applyButton . '</div>';
		foreach ($this->job['texts'] as $kk => $text) {

			if (isset($text['content'])) {
				if (!empty(trim(strip_tags($text['content'])))) {
					$html .= '<div class="eazycv-job-text-block eazycv-job-text-block-' . sanitize_title($kk) . '">';
					$html .= '<h3 class="eazycv-job-view-h3" id="eazycv-job-heading eazycv-job-heading-' . sanitize_title($kk) . '">' . $text['label'] . '</h3>';
					$html .= '<p class="eazycv-job-view-paragraph eazycv-job-paragraph-' . sanitize_title($kk) . '">' . $text['content'] . '</p>';
					$html .= '</div>';
				}
			}
		}
		$html .= '<div class="eazycv-job-view-apply-footer">';
		$html .= '<div class="eazycv-apply-button-bottom">' . $applyButton . '</div>';

		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}
}