<?php

class Wp_EazyCV_Job_Search
{

    private $api = null;
    private $jobDetails = null;
    private $atts = null;

    function __construct($api, $atts)
    {
        $this->api = $api;
        $this->jobDetails = new Wp_EazyCV_Jobs();
        $this->atts = $atts;

    }

    public function render()
    {


        $not_default_form = false;
        if (empty($this->atts['portal_id'])) {
            $portalId = get_option('wp_eazycv_apply_form');
        } else {
            $portalId = $this->atts['portal_id'];
            $not_default_form = true;
        }

        $filters = [
            'channels' => [
                $portalId
            ]
        ];

        if (!empty($this->atts['job_type'])) {
            $filters['job_type'] = $this->atts['job_type'] == 'project' ? 'project' : 'job';
        }


        if (empty($portalId)) {
            return '<div class="eazy-error">' . __('Er is (nog) geen inschrijfformulier ingesteld.') . '</div>';
        }

        //$formSettings = $this->api->get( 'connectivity/public-forms/' . $portalId );
        try {
            $formSettings = $this->api->get('connectivity/public-forms/' . $portalId);

        } catch (Exception $exception) {
            return '<div class="eazy-error">' . __('Er is een fout opgetreden.') . '</div>';
        }

        if (!empty($formSettings['layout_settings'])) {
            $formSettings['layout_settings'] = json_decode($formSettings['layout_settings'], true);
        }

        $jobs = $this->api->get('jobs/published', [
            'filter' => $filters
        ]);

        if (empty($jobs['data'])) {
            return '<div class="eazy-no-job-results">' . $formSettings['no_results_message'] . '</div>';
        }
        $html = '';


        foreach ($jobs['data'] as $job) {


            if (empty($job['original_functiontitle'])) {
                $job['original_functiontitle'] = $job['functiontitle'];
            }

            if ($not_default_form) {
                if ($job['type'] == 'job') {
                    $url = get_site_url() . '/' . get_option('wp_eazycv_jobpage') . '/' . sanitize_title($job['original_functiontitle']) . '-' . $job['id'] . '?applyform=' . $this->atts['portal_id'];
                } else {
                    $url = get_site_url() . '/' . get_option('wp_eazycv_projectpage') . '/' . sanitize_title($job['original_functiontitle']) . '-' . $job['id'] . '?applyform=' . $this->atts['portal_id'];
                }

                $url_apply = get_site_url() . '/' . get_option('wp_eazycv_apply_page') . '/' . sanitize_title($job['original_functiontitle']) . '-' . $job['id'] . '?applyform=' . $this->atts['portal_id'];
            } else {
                $url_apply = get_site_url() . '/' . get_option('wp_eazycv_apply_page') . '/' . sanitize_title($job['original_functiontitle']) . '-' . $job['id'];

                if ($job['type'] == 'job') {
                    $url = get_site_url() . '/' . get_option('wp_eazycv_jobpage') . '/' . sanitize_title($job['original_functiontitle']) . '-' . $job['id'];
                } else {
                    $url = get_site_url() . '/' . get_option('wp_eazycv_projectpage') . '/' . sanitize_title($job['original_functiontitle']) . '-' . $job['id'];
                }
            }

            $html .= '<div class="eazycv-job-row">';
            $html .= '<h4><a href="' . $url . '">' . $job['original_functiontitle'] . '</a></h4>';

            $publishedFields = $this->jobDetails->getFieldData($job);

            if (!empty($publishedFields['logo'])) {
                $html .= '<div class="eazycv-search-job-item-row eazycv-published-item eazycv-job-row-item-logo">';
                $html .= '<div class="eazycv-job-logo"><img src="https://eazycv.s3.eu-central-1.amazonaws.com/' . $publishedFields['logo']['value'] . '?bust=' . rand(0, 292992) . '" alt="' . $job['functiontitle'] . '" /></div>';
                $html .= '</div>';
                unset($publishedFields['logo']);
            }

            if (isset($job['meta']['content']) && !empty($job['meta']['content'])) {
                if (isset($formSettings['layout_settings']['max-words-job-result-text'])) {
                    $html .= '<p class="eazycv-job-search-meta">' . eazy_first_words($job['meta']['content'], intval($formSettings['layout_settings']['max-words-job-result-text'])) . '</p>';
                } else {
                    $html .= '<p class="eazycv-job-search-meta">' . $job['meta']['content'] . '</p>';
                }
            }

            $html .= '<div class="eazycv-job-row-details">';

            foreach ($publishedFields as $fieldId => $field) {
                $html .= '<div class="eazycv-search-job-item-row eazycv-published-item eazycv-job-row-item-' . $fieldId . '">
                <span class="eazycv-jobhead-labels eazycv-job-row-item-' . $fieldId . '-label">' . $field['label'] . '</span> ' .
                    $field['value'];
                $html .= '</div>';
            }

            $html .= '<div class="eazycv-job-row-reference"><span class="eazycv-jobhead-labels">' . __('Referentie') . '</span> ' . $job['reference'] . '</div>';
            $html .= '<div class="eazycv-job-row-created_at"><span class="eazycv-jobhead-labels">' . __('Geplaatst') . '</span> ' . date('d-m-Y', strtotime($job['created_at'])) . '</div>';


            $html .= '</div>';
            $html .= '<div class="eazycv-job-row-apply">';
            $html .= '<div class="eazycv-job-row-link-details"><a class="eazycv-link"  href="' . $url . '">' . __('Bekijk details') . '</a></div>';
            $html .= '<div class="eazycv-job-row-link-apply"><a class="eazycv-link" href="' . $url_apply . '">' . __('Solliciteer') . '</a></div>';
            $html .= '</div>';
            $html .= '</div>';

        }

        return $html;
    }
}