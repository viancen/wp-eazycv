<?php

class Wp_EazyCV_Jobs
{
    private $eazycvTranslations = [];

    public $publishedFields = [
        'id' => 'ID',
        'refererence' => 'Referentiecode',
        'functiontitle' => 'Functietitel (publicatie)',
        'original_functiontitle' => 'Originele functietitel',
        'business_unit' => 'Afdeling/Business-unit',
        'location_string' => 'Locatie',
        'url' => 'Url',
        'default_distance' => 'Afstand tot locatie',
        'deadline_at' => 'Deadline',
        'logo' => 'Logo',
        'cover' => 'Cover',
        //'description' => 'Originele vacature text',
        'address' => 'Standplaats',
        'level_id' => 'Niveau',
        'education' => 'Opleidingsniveau(s)',
        'discipline_id' => 'Vakgebied',
        'job_category_id' => 'Categorie',

        'category_description' => 'Categorie omschrijving',

        'years_experience_min' => 'Jaren werkervaring (vanaf)',
        'years_experience_max' => 'Jaren werkervaring (tot)',
        'salary' => 'Salaris',
        'salary_from' => 'Salaris vanaf',
        'salary_to' => 'Salaris tot',
        'salary_text' => 'Salaris toelichting',

        'rate' => 'Tarief',
        'rate_from' => 'Tarief vanaf',
        'rate_to' => 'Tarief tot',
        'rate_text' => 'Tarief toelichting',

        'hours' => 'Uren',
        'hours_from' => 'Aantal uur van',
        'hours_to' => 'Aantal uur tot',

        'hours_text' => 'Uren toelichting',
        'contract_type' => 'Contractvorm',
        'contract_text' => 'Contract toelichting',
        'created_at' => 'Aangemaakt op',
        'updated_at' => 'Geupdate op',
        'start_publish_date' => 'Publicatie datum (van)',
        'end_publish_date' => 'Publicatie datum (tot)',
        'start_at' => 'Startdatum',
        'end_at' => 'Einddatum',
    ];

    //custom translations?
    public function __construct()
    {
        $custom_labels = get_option('wp_eazycv_custom_labels');
        if (!empty($custom_labels)) {
            $custom_labels = json_decode($custom_labels, true);
            if (!empty($custom_labels)) {
                $this->publishedFields = [];
                foreach ($custom_labels as $labelName => $labelValue) {
                    $this->publishedFields[$labelName] = $labelValue;
                }
            }
        }
        $translations = get_option('wp_eazycv_global_labels');
        if (!empty($translations)) {
            $translations = json_decode($translations, true);
            if (!empty($translations)) {
                $this->eazycvTranslations = $translations;

            }
        }
    }

    /**
     * @return array|mixed
     */
    public function get_published_fields()
    {
        $currentSelection = get_option('wp_eazycv_display_job_fields');

        $currentSelectionArray = [];
        if (!empty($currentSelection)) {
            $currentSelectionArray = json_decode($currentSelection, true);
        }
        if (empty($currentSelectionArray)) {
            $currentSelectionArray = [];
        }
        return $currentSelectionArray;
    }

    /**
     * @return array|mixed
     */
    public function get_all_fields()
    {
        return $this->publishedFields;
    }

    /**
     * @return array|mixed
     */
    public function get_published_filters()
    {
        $currentSelection = get_option('wp_eazycv_display_job_filters');

        $currentSelectionArray = [];
        if (!empty($currentSelection)) {
            $currentSelectionArray = json_decode($currentSelection, true);
        }
        if (empty($currentSelectionArray)) {
            $currentSelectionArray = [];
        }
        return $currentSelectionArray;
    }


    /**
     * @param $job
     */
    public function getFieldData($job)
    {


        if (isset($job['educations'])) {
            $job['education'] = $job['educations'];
        }
        if (isset($job['main_level'])) {
            $job['level'] = $job['main_level'];
        }

        $result = [];
        $enabledFields = $this->get_published_fields();


        $job['rate'] = '';
        $job['salary'] = '';
        $job['category_description'] = '';
        $job['hours'] = '';


        foreach ($job as $fieldName => $fieldValue) {

            if (in_array($fieldName, $enabledFields)) {


                if (strstr($fieldName, '_date') || substr($fieldName, -3) == '_at') {
                    if (!empty($job[$fieldName])) {
                        $result[$fieldName] = [
                            'label' => $this->publishedFields[$fieldName],
                            'value' => '<span class="eazycv-field-list-item eazycv-field-' . $fieldName . '">' . date('d-m-Y', strtotime($fieldValue)) . '</span>'
                        ];
                    }
                } elseif (in_array($fieldName, ['rate', 'hours', 'salary'])) {

                    $fv = '';
                    if (!empty($job[$fieldName . '_from']) && $job[$fieldName . '_from'] != 0) {
                        if ($fieldName == 'hours') {
                            $fv = number_format($job[$fieldName . '_from'], 0, '', '.');
                        } else {
                            $fv = '&euro; ' . number_format($job[$fieldName . '_from'], 0, '', '.');
                        }
                    }

                    if (!empty($job[$fieldName . '_to']) && $job[$fieldName . '_to'] != 0) {
                        if (!empty($fv)) {
                            $fv .= ' - ';
                        }
                        if ($fieldName == 'hours') {
                            $fv .= number_format($job[$fieldName . '_to'], 0, '', '.');
                        } else {
                            $fv .= '&euro; ' . number_format($job[$fieldName . '_to'], 0, '', '.');
                        }
                    }

                    if (!empty($fv)) {
                        $result[$fieldName] = [
                            'label' => $this->publishedFields[$fieldName],
                            'value' => '<span class="eazycv-field-list-item eazycv-field-' . $fieldName . '">' . $fv . '</span>'
                        ];
                    }
                } elseif (in_array($fieldName, ['salary_from', 'salary_to', 'rate_from', 'rate_to'])) {
                    if (!empty($job[$fieldName])) {
                        $result[$fieldName] = [
                            'label' => $this->publishedFields[$fieldName],
                            'value' => '<span class="eazycv-field-list-item eazycv-field-' . $fieldName . '">&euro; ' . number_format($fieldValue, 2, ',', '.') . '</span>'
                        ];
                    }
                } elseif ($fieldName == 'contract_type') {
                    if (!empty($job[$fieldName])) {

                        if (isset($this->eazycvTranslations['contract_type'][$fieldValue])) {
                            $fv = $this->eazycvTranslations['contract_type'][$fieldValue];
                        } else {
                            $fv = $job[$fieldName] == 'temporary' ? 'Bepaalde tijd' : 'Onbepaalde tijd';
                        }

                        $result[$fieldName] = [
                            'label' => $this->publishedFields[$fieldName],
                            'value' => '<span class="eazycv-field-list-item eazycv-field-' . $fieldName . '">' . $fv . '</span>'
                        ];
                    }
                } elseif ($fieldName == 'address') {
                    if (!empty($job[$fieldName])) {
                        $list = '';
                        foreach ($job['address'] as $ed => $fvvv) {
                            if (in_array($ed, ['city', 'street', 'zipcode'])) {
                                $list .= ' <span class="eazycv-field-list-item eazycv-field-address-' . $ed . ' ">' . $fvvv . '</span> ';
                            }
                        }
                        $result['address'] = [
                            'label' => $this->publishedFields['address'],
                            'value' => $list
                        ];
                    }
                } elseif ($fieldName == 'education') {
                    if (!empty($job[$fieldName])) {
                        $list = '';
                        foreach ($job['education'] as $ed) {
                            $list .= ' <span class="eazycv-field-list-item eazycv-field-education">' . $ed['name'] . '</span> ';
                        }
                        $result['education'] = [
                            'label' => $this->publishedFields['education'],
                            'value' => $list
                        ];
                    }
                } elseif ($fieldName == 'cover' || $fieldName == 'logo') {
                    //<a href=' . $ed['url'] . ' target="_blank">
                    $result[$fieldName] = [
                        'label' => $this->publishedFields[$fieldName],
                        'value' => $job[$fieldName]
                    ];
                } elseif ($fieldName == 'url') {
                    if (!empty($job[$fieldName])) {
                        //<a href=' . $ed['url'] . ' target="_blank">
                        $result['url'] = [
                            'label' => $this->publishedFields['url'],
                            'value' => '<span class="eazycv-field-list-item eazycv-field-' . $fieldName . '"><a href=' . $job['url'] . ' target="_blank">' . $job['url'] . '</a></span>'
                        ];
                    }
                } elseif ($fieldName == 'category_description') {

                    if(isset($job['job_category'])){
                        $job['category'] = $job['job_category'];
                    }
                    if (!empty($job['category']) && !empty($job['category']['description'])) {
                        $result['url'] = [
                            'label' => $this->publishedFields['category_description'],
                            'value' => '<span class="eazycv-field-list-item eazycv-field-' . $fieldName . '">'.$job['category']['description'].'</span>'
                        ];
                    }
                } elseif (substr($fieldName, -3) == '_id') {
                    if (!empty($job[$fieldName])) {
                        $fieldCheck = str_replace('_id', '', $fieldName);
                        $result[$fieldName] = [
                            'label' => $this->publishedFields[$fieldName],
                            'value' => '<span class="eazycv-field-list-item eazycv-field-' . $fieldName . '">' . $job[$fieldCheck]['name'] . '</span>'
                        ];
                    }
                } else {
                    if (!empty($job[$fieldName])) {
                        $result[$fieldName] = [
                            'label' => $this->publishedFields[$fieldName],
                            'value' => '<span class="eazycv-field-list-item eazycv-field-' . $fieldName . '">' . $fieldValue . '</span>'
                        ];
                    }
                }
            }
        }

        $defResult = $this->publishedFields;
        foreach ($this->publishedFields as $sorter => $lalb) {
            if (isset($result[$sorter])) {
                $defResult[$sorter] = $result[$sorter];
            } else {
                unset($defResult[$sorter]);
            }
        }

        return $defResult;

    }

}