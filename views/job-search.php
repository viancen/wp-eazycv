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
        if (empty($this->atts['detail_url'])) {
            $jobUrl = get_option('wp_eazycv_jobpage');
            $projectUrl = get_option('wp_eazycv_projectpage');
        } else {
            $jobUrlCustom = $this->atts['detail_url'];
        }

        if (empty($this->atts['portal_id'])) {
            $portalId = get_option('wp_eazycv_apply_form');
            $filters = [
                'channels' => [
                    $portalId
                ]
            ];
        } else {
            $portalId = $this->atts['portal_id'];
            $not_default_form = true;
            $filters = [
                'channel' => [
                    $portalId
                ]
            ];
        }
        $getFilters = [
            'organisations',
            'categories',
            'education',
            'disciplines',
            'query',
            'location',
            'distance',
        ];
        foreach ($getFilters as $getFilter) {
            if (!empty($_GET[$getFilter])) {
                $filters[$getFilter] = strip_tags($_GET[$getFilter]);
            }
        }

        $disable_apply = false;
        if (!empty($this->atts['disable_apply_button'])) {
            $disable_apply = true;
        }
        if (!empty($this->atts['job_type'])) {
            $filters['job_type'] = $this->atts['job_type'] == 'project' ? 'project' : 'job';
        }

        $filters['order_by'] = get_option('wp_eazycv_job_order_by') ? get_option('wp_eazycv_job_order_by') : 'jobs.updated_at';
        $filters['order_direction'] = 'desc';

        if (empty($portalId)) {
            return '<div class="eazy-error">' . __('Er is (nog) geen inschrijfformulier ingesteld.') . '</div>';
        }

        $filterSettings = $this->jobDetails->get_published_filters();

        //$formSettings = $this->api->get( 'connectivity/public-forms/' . $portalId );
        try {
            $formSettings = $this->api->get('connectivity/public-forms/' . $portalId);
            $ListsFilters = [
                'query' => [
                    'data' => in_array('query', $filterSettings) ? 'query' : null,
                    'label' => 'Functie/Inhoud'
                ],
                'location' => [
                    'data' => in_array('location', $filterSettings) ? 'location' : null,
                    'label' => 'Postcode/plaats + straal'
                ],
                'organisations' => [
                    'data' => in_array('organisations', $filterSettings) ? $this->api->get('lists/organisations') : null,
                    'label' => 'Business Unit'
                ],
                'categories' => [
                    'data' => in_array('categories', $filterSettings) ? $this->api->get('lists/JobCategories') : null,
                    'label' => 'Categorie'
                ],
                'education' => [
                    'data' => in_array('education', $filterSettings) ? $this->api->get('lists/education') : null,
                    'label' => 'Opleidingsniveau'
                ],
                'disciplines' => [
                    'data' => in_array('disciplines', $filterSettings) ? $this->api->get('lists/disciplines') : null,
                    'label' => 'Vakgebied'
                ]
            ];

        } catch (Exception $exception) {
            return '<div class="eazy-error">' . __('Er is een fout opgetreden.') . '</div>';
        }

        if (!empty($formSettings['custom_apply_url'])) {
            $disable_apply = false;
        }
        if (!empty($formSettings['layout_settings'])) {
            $formSettings['layout_settings'] = json_decode($formSettings['layout_settings'], true);
        }


        $pageInfo = '';
        foreach ($ListsFilters as $filter => $values) {
            if (!empty($values['data'])) {
                if (empty($filterHtml)) {
                    $filterHtml = '<div class="eazycv-filters">' .
                        '<div class="eazycv-filters-title"><h3>Filters</h3></div>';
                }
                $filterHtml .= '<div class="eazycv-filter-group">';
                $filterHtml .= '<div class="eazycv-filter-group-label">' . $values['label'] . '</div>';
                if ($values['data'] == 'query') {
                    $value = (isset($_GET[$filter])) ? strip_tags($_GET[$filter]) : '';
                    $filterHtml .= '<input name="' . $filter . '"  id="eazycv-filter-' . $filter . '" value="' . $value . '" placeholder="Zoek ' . $values['label'] . '" class="eazycv-job-search-filters">';
                } elseif ($values['data'] == 'location') {
                    $value = (isset($_GET['location'])) ? strip_tags($_GET['location']) : '';
                    $filterHtml .= '<div class="eazycv-location-filter">';
                    $filterHtml .= '<div class="eazycv-location-filter-where"><input name="' . $filter . '"  id="eazycv-filter-' . $filter . '" value="' . $value . '" placeholder="' . $values['label'] . '" class="eazycv-job-search-filters"></div>';

                    $filterHtml .= '<div class="eazycv-location-filter-distance"><select name="distance"  id="eazycv-filter-distance" placeholder="Selecteer straal" class="eazycv-job-search-filters">';
                    $filterHtml .= '<option value="100">100km</option>';

                    $distances = [
                        '75',
                        '60',
                        '50',
                        '40',
                        '30',
                        '20',
                        '10',
                    ];
                    foreach ($distances as $value) {
                        $selected = (isset($_GET['distance']) && $_GET['distance'] == $value) ? 'selected="selected"' : '';
                        $filterHtml .= '<option value="' . $value . '" ' . $selected . '>' . $value . 'km</option>';
                    }
                    $filterHtml .= '</select>';
                    $filterHtml .= '</div>';
                    $filterHtml .= '</div>';
                } else {
                    $filterHtml .= '<select name="' . $filter . '"  id="eazycv-filter-' . $filter . '" placeholder="Selecteer ' . $values['label'] . '" class="eazycv-job-search-filters">';
                    $filterHtml .= '<option value=""></option>';

                    foreach ($values['data'] as $value) {
                        $selected = (isset($_GET[$filter]) && $_GET[$filter] == $value['id']) ? 'selected="selected"' : '';
                        if($filter == 'categories'){
                            $pageInfo = $value['description'];
                        }
                        $filterHtml .= '<option value="' . $value['id'] . '" ' . $selected . '>' . $value['name'] . '</option>';
                    }
                    $filterHtml .= '</select>';
                }
                $filterHtml .= '</div>';
            }
        }

        if (!empty($filterHtml)) {
            $filterHtml .= '<div class="eazycv-filters-buttons">
 <button type="reset" class="eazycv-job-search-filters-reset">Reset</button>
 <button type="button" class="eazycv-job-search-filters-submit">Filteren</button>
 </div>';
            $filterHtml .= '</div>';
        }
        $html = '<div id="eazycv-top-of-jobs"></div>' . $filterHtml;

        //-- pagination
        if (!isset ($_GET['eazy-page'])) {
            $page = 1;
        } else {
            $page = $_GET['eazy-page'];
        }


        $results_per_page = get_option('wp_eazycv_job_pagination') ? get_option('wp_eazycv_job_pagination') : 10;
        $current_page = $page;

        $jobs = $this->api->get('jobs/published', [
            'filter' => $filters,
            'limit' => $results_per_page,
            'page' => $page,
        ]);


        $number_of_result = $jobs['total'];

        //determine the total number of pages available
        $number_of_page = ceil($number_of_result / $results_per_page);


        if (empty($jobs['data'])) {
            if (!isset($formSettings['no_results_message'])) $formSettings['no_results_message'] = '';
            return $filterHtml . '<div class="eazy-no-job-results">' . $formSettings['no_results_message'] . '</div>';
        }

        $html .= '<div class="eazycv-job-search-totals"><h4>'.$number_of_result.' vacatures gevonden</h4></div>';

        if(!empty($pageInfo)){
            $html .= '<div class="eazycv-job-search-intro">'.$pageInfo.'</div>';
        }
        foreach ($jobs['data'] as $job) {

            if (empty($job['original_functiontitle'])) {
                $job['original_functiontitle'] = $job['functiontitle'];
            }

            if (isset($jobUrl)) {
                if ($job['type'] == 'job') {
                    $url = get_home_url() . '/' . $jobUrl . '/' . sanitize_title($job['original_functiontitle']) . '-' . $job['id'] . '?applyform=' . $portalId;
                } else {
                    $url = get_home_url() . '/' . $projectUrl . '/' . sanitize_title($job['original_functiontitle']) . '-' . $job['id'] . '?applyform=' . $portalId;
                }
            } else {
                $url = get_home_url() . '/' . $jobUrlCustom . '/?jobid=' . $job['id'] . '&applyform=' . $portalId;
            }

            if (empty($formSettings['custom_apply_url'])) {
                $url_apply = get_home_url() . '/' . get_option('wp_eazycv_apply_page') . '/' . sanitize_title($job['original_functiontitle']) . '-' . $job['id'] . '?applyform=' . $portalId;
            } else {
                $url_apply = $formSettings['custom_apply_url'];
            }

            $html .= '<div class="eazycv-job-row">';
            $html .= '<h4><a href="' . $url . '">' . $job['original_functiontitle'] . '</a></h4>';

            $publishedFields = $this->jobDetails->getFieldData($job);

            if (!empty($publishedFields['cover'])) {
                unset($publishedFields['cover']);
            }
            if (isset($publishedFields['logo']) && !empty($publishedFields['logo']['value'])) {

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

            $html .= '</div>';
            $html .= '<div class="eazycv-job-row-apply">';
            $html .= '<div class="eazycv-job-row-link-details"><a class="eazycv-link"  href="' . $url . '">' . __('Bekijk details') . '</a></div>';
            if (!$disable_apply) {
                if (!empty($formSettings['custom_apply_url'])) {
                    $html .= '<div class="eazycv-job-row-link-apply"><a class="eazycv-link" href="' . $formSettings['custom_apply_url'] . '">' . __('Solliciteer') . '</a></div>';
                } else {
                    $html .= '<div class="eazycv-job-row-link-apply"><a class="eazycv-link" href="' . $url_apply . '">' . __('Solliciteer') . '</a></div>';
                }

            }
            $html .= '</div>';
            $html .= '</div>';

        }

        $html .= '<div class="eazycv-pagination">';
        $current_rel_uri = add_query_arg(NULL, NULL);
        for ($page = 1; $page <= $number_of_page; $page++) {
            if (empty($_GET)) {
                $link = $current_rel_uri . '?eazy-page=' . $page;
            } else {
                $link = $current_rel_uri . '&eazy-page=' . $page;
            }
            $class = $current_page == $page ? 'current' : '';
            $html .= '<a href="' . $link . '" class="' . $class . '">' . $page . '</a>';
        }
        $html .= '</div>';

        $html .= '<div class="eazycv-back-top">';
        $html .= '<a href="#eazycv-top-of-jobs">terug naar boven</a>';
        $html .= '</div>';
        return $html;
    }
}