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

        $html = '<div class="eazycv-job-body eazycv-job-' . $this->job['type'] . '">';
        $html .= '<h2 class="eazycv-job-view-h2">' . $this->job['original_functiontitle'] . '</h2>';


        if (!empty($this->job['location_string'])) {
            $html .= '<div class="eazycv-job-body-city">' . $this->job['location_string'];
            if (!empty($this->job['default_distance'])) {
                $html .= ' <span class="eazycv-job-body-distance">(&#177; ' . $this->job['default_distance'] . ' km)</span>';
            }
            $html .= '</div>';
        } else {
            if (!empty($this->job['address']['city'])) {
                $html .= '<div class="eazycv-job-body-city">' . $this->job['address']['city'] . '</div>';
            }
        }

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
        if (!empty($this->job)) {
            $html .= '<a class="eazycv-apply-to-job eazycv-btn" href="' . get_site_url() . '/' . get_option('wp_eazycv_apply_page') . '/' . sanitize_title($this->job['functiontitle']) . '-' . $this->job['id'] . $mainForm . '">' . __('Solliciteren') . '</a>';
        } else {
            $html .= '<a class="eazycv-apply-to-job eazycv-btn" href="' . get_site_url() . '/' . get_option('wp_eazycv_apply_page') . '/open' . $mainForm . '">' . __('Solliciteren') . '</a>';
        }
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}