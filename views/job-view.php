<?php

class Wp_EazyCV_Job
{

    public $job = null;
    private $api = null;
    private $jobDetails = null;

    function __construct($job, $api, $atts)
    {
        $this->atts = $atts;
        $this->api = $api;
        $this->jobDetails = new Wp_EazyCV_Jobs();
        $this->job = $job;
    }

    public function render()
    {

        if (empty($this->job['id'])) {
            if (empty($_GET['jobid'])) {
                return 'No job.';
            } else {
                $this->job = $this->api->get('jobs/published/' . intval($_GET['jobid']));
            }
        }
        if (empty($this->job['id'])) {
            return 'error fetching job';
        }

        $disable_apply = false;
        if (!empty($this->atts['disable_apply_button'])) {
            $disable_apply = true;
        }

        //ander inschrijfjformulier meegegeven?
        $customApplyUrl = '';
        $mainForm = '';
        if (isset($_GET['applyform'])) {
            $mainForm = '?applyform=' . intval($_GET['applyform']);
            try {
                $formSettings = $this->api->get('connectivity/public-forms/' . intval($_GET['applyform']));

            } catch (Exception $exception) {
                return '<div class="eazy-error">' . __('Er is een fout opgetreden.') . '</div>';
            }

            if (!empty($formSettings['custom_apply_url'])) {
                $customApplyUrl = $formSettings['custom_apply_url'];
            }
        } else {
            $portalId = get_option('wp_eazycv_apply_form');
            try {
                $formSettings = $this->api->get('connectivity/public-forms/' . $portalId);

            } catch (Exception $exception) {
                return '<div class="eazy-error">' . __('Er is een fout opgetreden.') . '</div>';
            }

            if (!empty($formSettings['custom_apply_url'])) {
                $customApplyUrl = $formSettings['custom_apply_url'];
            }
        }


        if (empty($this->job['original_functiontitle'])) {
            $this->job['original_functiontitle'] = $this->job['functiontitle'];
        }

        if (!$disable_apply) {
            if (!empty($customApplyUrl)) {
                $applyButton = '<a class="eazycv-apply-to-job eazycv-btn" href="' . $customApplyUrl . '" target="_blank">' . __('Solliciteren') . '</a>';
            } else {
                if (!empty($this->job)) {
                    $applyButton = '<a class="eazycv-apply-to-job eazycv-btn" href="' . get_site_url() . '/' . get_option('wp_eazycv_apply_page') . '/' . sanitize_title($this->job['functiontitle']) . '-' . $this->job['id'] . $mainForm . '">' . __('Solliciteren') . '</a>';
                } else {
                    $applyButton = '<a class="eazycv-apply-to-job eazycv-btn" href="' . get_site_url() . '/' . get_option('wp_eazycv_apply_page') . '/open' . $mainForm . '">' . __('Solliciteren') . '</a>';
                }
            }
        } else {
            $applyButton = '';
        }


        $urlBack = get_site_url() . '/' . get_option('wp_eazycv_jobsearch_page');
        $html = '<div class="eazycv-job-body eazycv-job-' . $this->job['type'] . '">';

        $publishedFields = $this->jobDetails->getFieldData($this->job);

        if (isset($publishedFields['cover']) && !empty($publishedFields['cover']['value'])) {
            $html .= '<div class="eazycv-job-cover"><img src="https://eazycv.s3.eu-central-1.amazonaws.com/' . $publishedFields['cover']['value'] . '?bust=' . rand(0, 292992) . '" alt="' . $this->job['functiontitle'] . '" /></div>';
            unset($publishedFields['cover']);
        } else {
            unset($publishedFields['cover']);
        }

        $html .= '<div class="eazycv-job-breadcrumbs"><a href="' . get_site_url() . '">Home</a> &raquo; <a href="' . $urlBack . '">Alle vacatures</a> &raquo; <span>' . $this->job['functiontitle'] . ' </span> </div>';
        $html .= '<h2 class="eazycv-job-view-h2">' . $this->job['original_functiontitle'] . '</h2>';
        if (isset($publishedFields['logo']) && !empty($publishedFields['logo'])) {
            $html .= '<div class="eazycv-job-logo"><img src="https://eazycv.s3.eu-central-1.amazonaws.com/' . $publishedFields['logo']['value'] . '?bust=' . rand(0, 292992) . '" alt="' . $this->job['functiontitle'] . '" /></div>';
            unset($publishedFields['logo']);
        } else {
            unset($publishedFields['cover']);
        }

        foreach ($publishedFields as $fieldId => $field) {
            $html .= '<div class="eazycv-view-job-item-row eazycv-published-item eazycv-job-row-item-' . $fieldId . '">
                <span class="eazycv-jobhead-labels eazycv-job-row-item-' . $fieldId . '-label">' . $field['label'] . '</span> ' .
                $field['value'];
            $html .= '</div>';
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

        $social = get_option('wp_eazycv_jobpage_social');
        if ($social == 1) {
            $html .= '<div class="sharethis-inline-share-buttons"></div>
<script type="text/javascript" src="https://platform-api.sharethis.com/js/sharethis.js#property=6005a8eb2bc64600181b1338&product=inline-share-buttons" async="async"></script>';
        }

        return $html;
    }
}