<?php

class Wp_EazyCV_Apply
{

    public $job = null;

    private $api = null;
    private $jobDetails = null;
    private $lists = null;
    private $licence = null;
    private $apply_form = null;

    function __construct($api, $atts, $job = null)
    {
        $this->atts = $atts;
        $this->api = $api;
        $this->jobDetails = new Wp_EazyCV_Jobs();
        $this->job = $job;
    }

    public function render()
    {

        $mainForm = null;

        ///wp force
        wp_enqueue_script('eazy_recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . get_option('wp_eazycv_google_api_key'), [], '19801203');

        $mainForm = null;
        if (isset($_GET['applyform'])) {
            $mainForm = intval($_GET['applyform']);
        }


        if (empty($mainForm) && !empty($this->atts['portal_id'])) {
            $mainForm = intval($this->atts['portal_id']);
        }

        if (empty($mainForm)) {
            $mainForm = get_option('wp_eazycv_apply_form');
        }
        //first get the form
        if (empty($mainForm)) {
            return '<div class="eazy-error">' . __('Er is geen inschrijfformulier ingesteld.') . '</div>';
        }

        $this->apply_form = $mainForm;

        $googleKey = get_option('wp_eazycv_google_api_key');
        $googleSecret = get_option('wp_eazycv_google_api_secret');

        if (empty($googleKey) || empty($googleSecret)) {
            return '<div class="eazy-error">' . __('Er is geen CAPTCHA ingesteld.') . '</div>';
        }

        try {
            $formSettings = $this->api->get('connectivity/public-forms/' . $mainForm);
        } catch (Exception $exception) {
            return '<div class="eazy-error">' . __('Er is een fout inschrijfformulier ingesteld.') . '</div>';
        }
        if (empty($formSettings['settings'])) {
            return '<div class="eazy-error">' . __('Er is geen inschrijfformulier ingesteld.') . '</div>';
        }
        $form = $formSettings;
        $formSettings = $formSettings['settings'];

        //otherwise render the applicaiton form
        $this->lists = $this->api->get('lists');
        $this->licence = $this->api->get('licence');
        $legal_stuff = $this->api->get('legal-info');

        $html = '';

        //stop1

        $html .= '<div class="eazycv-form">';
        if (!empty($this->job)) {
            $label = !empty($this->atts['title_apply']) ? $this->atts['title_apply'] : '';
            if (!empty($label)) {
                $html = '<h2 class="eazycv-job-view-h2">' . $label . ' ' . $this->job['original_functiontitle'] . '</h2>';
            }


            $urlBack = get_site_url() . '/' . get_option('wp_eazycv_jobsearch_page');

            $html .= '<div class="eazycv-job-breadcrumbs"><a href="' . get_site_url() . '">Home</a> &raquo; <a href="' . $urlBack . '">Alle vacatures</a> &raquo; <span> Solliciteren: ' . $this->job['functiontitle'] . ' </span> </div>';
            $html .= '<h2 class="eazycv-job-view-h2">' . $this->job['original_functiontitle'] . '</h2>';

            $publishedFields = $this->jobDetails->getFieldData($this->job);
            unset($publishedFields['cover']);
            unset($publishedFields['logo']);
            foreach ($publishedFields as $fieldId => $field) {
                $html .= '<div class="eazycv-apply-job-item-row eazycv-published-item eazycv-job-row-item-' . $fieldId . '">
                <span class="eazycv-jobhead-labels eazycv-job-row-item-' . $fieldId . '-label">' . $field['label'] . '</span> ' .
                    $field['value'];
                $html .= '</div>';
            }


        } else {
            $label = !empty($this->atts['title_open']) ? $this->atts['title_open'] : '';
            if (!empty($label)) {
                $html = '<h2 class="eazycv-job-view-h2">' . $label . '</h2>';
            }
            $urlBack = get_site_url() . '/' . get_option('wp_eazycv_jobsearch_page');

            $html .= '<div class="eazycv-job-breadcrumbs"><a href="' . get_site_url() . '">Home</a> &raquo; <a href="' . $urlBack . '">Alle vacatures</a> &raquo; <span> Inschrijven </span> </div>';

        }

        $html .= '<div class="eazy-success eazycv-hidden" id="eazy-successful-application">' . $form['success_message'] . '</div><div id="eazycv-success-apply"></div>';
        $html .= '<div class="eazy-error eazycv-hidden"  id="eazy-error-application-captcha">Oeps, de anti-robot validatie is niet gelukt... Probeer het formulier nog eens te versturen.</div>';
        $html .= '<div class="eazy-error eazycv-hidden" id="eazy-error-application">';
        $html .= 'Oeps, er is iets mis gegaan... Excuses voor het ongemak. Probeer het nog een keer.';
        $html .= '</div>';

        $html .= '
<div class="eazy-error" id="eazy-from-apply-error" style="display:none;"></div>
<input type="hidden" value="' . $googleKey . '" id="eazycv-grekey">
		<form method="post" id="eazycv-apply-form" class="validate" enctype="multipart/form-data">
  			<input type="hidden" name="eazy-url" value="' . strtok(current_location(), '?') . '">
  			<input type="hidden" class="eazymatch-active" name="grepact" value="" id="eazycv-greval">
  			<input type="hidden" name="subscription_form_id" value="' . $this->apply_form . '">
  			<input type="hidden" id="eazycv-apply-job_id"  name="job_id" value="' . @$this->job['id'] . '">
  			';

        foreach ($formSettings['fields'] as $field) {

            if ($field['name'] == 'type') {
                $html .= $this->formType($field);
            } elseif ($field['name'] == 'gender') {
                $html .= $this->gender($field);
            } elseif (in_array($field['name'], ['cv_document', 'cv_document_tk', 'picture', 'attachment1', 'attachment2', 'attachment3'])) {
                $html .= $this->fileUpload($field);
            } elseif (in_array($field['name'], ['birth_date', 'available_from', 'available_to'])) {
                $html .= $this->dateField($field);
            } elseif (in_array($field['name'], ['motivation', 'description'])) {
                $html .= $this->textarea($field);
            } elseif ($field['name'] == 'discipline_id') {
                $html .= $this->discipline($field);
            } elseif ($field['name'] == 'education_id') {
                $html .= $this->education($field);
            } elseif ($field['name'] == 'connect_through') {
                $html .= $this->connectThrough($field);
            } else {
                $html .= $this->textField($field);
            }
        }

        $html .= $this->gdpr($legal_stuff);

        $html .= '<hr /><input class="eazy-submit eazy-btn" id="eazy-apply-submit-btn" type="button" value="' . __('Submit') . '">';

        $html .= '</form></div>

<div id="eazycv-wait-modal" class="eazycv-modal"><div class="eazy-centerd"><div class="lds-ring"><div></div><div></div><div></div><div></div></div> <br /><br />Een moment geduld, uw sollicitatie wordt verwerkt.</div></div> 
			';

        return $html;
    }


    /**
     * @param $legal_stuff
     *
     * @return string
     */
    public function gdpr($legal_stuff)
    {

        $html = '';
        if (!empty($legal_stuff['gdpr_candidate']['content']) && $legal_stuff['gdpr_candidate']['enabled'] == 1) {
            $html .= '<div class="eazycv-form-group eazycv-wrapper-gdpr">';
            $html .= '<div class="eazycv-gdpr">
                    <label for="field-gdpr">
                        <input type="checkbox" id="eazycv-field-gdpr" data-eazycv-required="accept_gdpr_version"
                               name="accept_gdpr_version"
                               value="' . $legal_stuff['gdpr_candidate']['version_nr'] . '"/>
                             
                        <a href="javascript:void(0);" data-featherlight-variant="eazycv-lightbox" data-featherlight="#eazycv-gdpr-modal"> ' . $legal_stuff['gdpr_candidate']['link_text'] . '</a>
                    </label>
                </div>';

            $html .= '<div class="eazycv-apply-error eazycv-hidden eazy-error" id="eazycv-error-accept_gdpr_version">U moet akkoord gaan met onze voorwaarden.</div>';

            $html .= '
			<div id="eazycv-gdpr-modal" class="eazycv-modal">
				<h2  class="eazycv-h2-gdpr-heading">' . __('Privacystatement') . ': ' . $legal_stuff['gdpr_candidate']['version_nr'] . '</h2>
				    
				    <div class="eazycv-h4-privacy-p">' . $legal_stuff['gdpr_candidate']['content'] . '</div>
		
				  	<div class="eazycv-modal-footer"><hr />
				   	 <button id="accept-gdpr-modal-btn" type="button" class="eazycv-btn">' . __('Ik ga akkoord') . '</button>
					</div> 
			</div>';
            $html .= '</div>';
        }
        if (!empty($legal_stuff['terms_candidate']['content']) && $legal_stuff['terms_candidate']['enabled'] == 1) {
            $html .= '<div class="eazycv-form-group terms">';
            $html .= '<div class="eazycv-terms">
                    <label for="field-terms">
                        <input type="checkbox" id="eazycv-field-terms" data-eazycv-required="accept_terms_version"
                               name="accept_terms_version"
                               value="' . $legal_stuff['terms_candidate']['version_nr'] . '"/>
                             
                        <a href="javascript:void(0);" data-featherlight-variant="eazycv-lightbox" data-featherlight="#eazycv-terms-modal"> ' . $legal_stuff['terms_candidate']['link_text'] . '</a>
                    </label>
                </div>';

            $html .= '<div class="eazycv-apply-error eazycv-hidden eazy-error" id="eazycv-error-accept_terms_version">U moet akkoord gaan met onze voorwaarden.</div>';

            $html .= '
			<div id="eazycv-terms-modal" class="eazycv-modal">
				<h2  class="eazycv-h2-terms-heading">' . __('Algemene voorwaarden') . ': ' . $legal_stuff['terms_candidate']['version_nr'] . '</h2>
				   
				    <div class="eazycv-h4-terms-p">' . $legal_stuff['terms_candidate']['content'] . '</div>
				  	<div class="eazycv-modal-footer"><hr />
				   	 <button id="accept-terms-modal-btn" type="button" class="eazycv-btn">' . __('Ik ga akkoord') . '</button>
					</div> 
			</div>';
            $html .= '</div>';
        }


        return $html;
    }

    /**
     * Connected via source
     *
     * @param $field
     *
     * @return string
     */
    public function textField($field)
    {
        $html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title($field['name']) . '">';
        $html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
        $html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title($field['name']) . '" for="eazycv-field-' . sanitize_title($field['name']) . '">' . $field['label'] . '</label>' . PHP_EOL;
        $html .= '<input type="text" id="eazycv-field-' . sanitize_title($field['name']) . '" name="' . $field['name'] . '"';

        if ($field['required']) {
            $html .= ' class="eazycv-field eazycv-text" data-eazycv-required="' . sanitize_title($field['name']) . '" />';
        } else {
            $html .= ' class="eazycv-field eazycv-text" />';
        }
        $html .= '<div class="eazycv-apply-error eazycv-hidden eazy-error" id="eazycv-error-' . sanitize_title($field['name']) . '">' . $field['label'] . ' is verplicht</div>';
        $html .= '</div>' . PHP_EOL;


        if ($field['name'] == 'email') {
            $html .= '<div id="eazycv-ajax-email-error"></div>';
        }


        return $html;
    }

    /**
     * Connected via source
     *
     * @param $field
     *
     * @return string
     */
    public function fileUpload($field)
    {
        $html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title($field['name']) . '">';
        $html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
        $html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title($field['name']) . '" for="eazycv-field-' . sanitize_title($field['name']) . '">' . $field['label'] . '</label>' . PHP_EOL;
        $html .= '<div class="eazy-file-upload-wrapper" data-text="Selecteer ' . $field['label'] . '">';
        $html .= '<input type="file" id="eazycv-field-' . sanitize_title($field['name']) . '" name="' . $field['name'] . '"';


        if ($field['required']) {
            $html .= ' class="eazycv-field eazycv-file" data-eazycv-required="' . sanitize_title($field['name']) . '"  />';

        } else {
            $html .= ' class="eazycv-field eazycv-file" />';
        }
        $html .= '<div class="eazycv-apply-error eazycv-hidden eazy-error" id="eazycv-error-' . sanitize_title($field['name']) . '">' . $field['label'] . ' is verplicht</div>';
        $html .= '</div>
		</div>' . PHP_EOL;

        return $html;
    }

    /**
     * Connected via source
     *
     * @param $field
     *
     * @return string
     */
    public function dateField($field)
    {
        $html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title($field['name']) . '">';
        $html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
        $html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title($field['name']) . '" for="eazycv-field-' . sanitize_title($field['name']) . '">' . $field['label'] . '</label>' . PHP_EOL;
        $html .= '<input type="date" id="eazycv-field-' . sanitize_title($field['name']) . '" name="' . $field['name'] . '"';

        if ($field['required']) {
            $html .= ' class="eazycv-field eazycv-date" data-eazycv-required="' . sanitize_title($field['name']) . '" />';
        } else {
            $html .= ' class="eazycv-field eazycv-date" />';
        }
        $html .= '<div class="eazycv-apply-error eazycv-hidden eazy-error" id="eazycv-error-' . sanitize_title($field['name']) . '">' . $field['label'] . ' is verplicht</div>';
        $html .= '</div>' . PHP_EOL;

        return $html;
    }

    /**
     * Connected via source
     *
     * @param $field
     *
     * @return string
     */
    public function textarea($field)
    {

        $html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title($field['name']) . '">';
        $html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
        $html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title($field['name']) . '" for="eazycv-field-' . sanitize_title($field['name']) . '">' . $field['label'] . '</label>' . PHP_EOL;
        $html .= '<textarea id="eazycv-field-' . sanitize_title($field['name']) . '" name="' . $field['name'] . '"';

        if ($field['required']) {
            $html .= ' class="eazycv-field eazycv-textarea" data-eazycv-required="' . sanitize_title($field['name']) . '">';
        } else {
            $html .= ' class="eazycv-field eazycv-textarea">';
        }

        $html .= '</textarea>' . PHP_EOL;
        $html .= '<div class="eazycv-apply-error eazycv-hidden eazy-error" id="eazycv-error-' . sanitize_title($field['name']) . '">' . $field['label'] . ' is verplicht</div>';
        $html .= '</div>' . PHP_EOL;

        return $html;

    }

    /**
     * Connected via source
     *
     * @param $field
     *
     * @return string
     */
    public function gender($field)
    {
        $html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title($field['name']) . '">';
        $html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
        $html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title($field['name']) . '" for="eazycv-field-' . sanitize_title($field['name']) . '">' . $field['label'] . '</label>' . PHP_EOL;
        $html .= '<select id="eazycv-field-' . sanitize_title($field['name']) . '" name="gender"';

        if ($field['required']) {
            $html .= ' class="eazycv-field eazycv-select"  data-eazycv-required="' . sanitize_title($field['name']) . '" >' . PHP_EOL;
        } else {
            $html .= ' class="eazycv-field eazycv-select">' . PHP_EOL;
        }
        $html .= ' <option value="m">' . __('Male') . '</option>' . PHP_EOL;
        $html .= ' <option value="f">' . __('Female') . '</option>' . PHP_EOL;

        $html .= '</select>' . PHP_EOL;
        $html .= '<div class="eazycv-apply-error eazycv-hidden eazy-error" id="eazycv-error-' . sanitize_title($field['name']) . '">' . $field['label'] . ' is verplicht</div>';
        $html .= '</div>' . PHP_EOL;


        return $html;
    }

    /**
     * Connected via source
     *
     * @param $field
     *
     * @return string
     */
    public function connectThrough($field)
    {
        $html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title($field['name']) . '">';
        $html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>';
        $html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title($field['name']) . '" for="eazycv-field-' . sanitize_title($field['name']) . '">' . $field['label'] . '</label>';
        $html .= '<select id="eazycv-field-' . sanitize_title($field['name']) . '" name="connect_through_id"';

        if ($field['required']) {
            $html .= ' class="eazycv-field eazycv-select" data-eazycv-required="' . sanitize_title($field['name']) . '">';
        } else {
            $html .= ' class="eazycv-field eazycv-select">';
        }

        foreach ($this->lists['ConnectThrough'] as $key => $listItem) {
            $html .= ' <option value="' . $listItem['id'] . '">' . $listItem['name'] . '</option>';
        }

        $html .= '</select>';
        $html .= '<div class="eazycv-apply-error eazycv-hidden eazy-error" id="eazycv-error-' . sanitize_title($field['name']) . '">' . $field['label'] . ' is verplicht</div>';
        $html .= '</div>';


        return $html;
    }

    /**
     * Connected via source
     *
     * @param $field
     *
     * @return string
     */
    public function discipline($field)
    {
        $html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title($field['name']) . '">';
        $html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>';
        $html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title($field['name']) . '" for="eazycv-field-' . sanitize_title($field['name']) . '">' . $field['label'] . '</label>';
        $html .= '<select id="eazycv-field-' . sanitize_title($field['name']) . '" name="connect_through_id"';

        if ($field['required']) {
            $html .= ' class="eazycv-field eazycv-select" data-eazycv-required="' . sanitize_title($field['name']) . '">';
        } else {
            $html .= ' class="eazycv-field eazycv-select">';
        }

        foreach ($this->lists['Disciplines'] as $key => $listItem) {
            $html .= ' <option value="' . $listItem['id'] . '">' . $listItem['name'] . '</option>';
        }

        $html .= '</select>';
        $html .= '<div class="eazycv-apply-error eazycv-hidden eazy-error" id="eazycv-error-' . sanitize_title($field['name']) . '">' . $field['label'] . ' is verplicht</div>';
        $html .= '</div>';


        return $html;
    }

    /**
     * Connected via source
     *
     * @param $field
     *
     * @return string
     */
    public function education($field)
    {
        $html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title($field['name']) . '">';
        $html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>';
        $html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title($field['name']) . '" for="eazycv-field-' . sanitize_title($field['name']) . '">' . $field['label'] . '</label>';
        $html .= '<select id="eazycv-field-' . sanitize_title($field['name']) . '" name="connect_through_id"';

        if ($field['required']) {
            $html .= ' class="eazycv-field eazycv-select" data-eazycv-required="' . sanitize_title($field['name']) . '">';
        } else {
            $html .= ' class="eazycv-field eazycv-select">';
        }

        foreach ($this->lists['Education'] as $key => $listItem) {
            $html .= ' <option value="' . $listItem['id'] . '">' . $listItem['name'] . '</option>';
        }

        $html .= '</select>';
        $html .= '<div class="eazycv-apply-error eazycv-hidden eazy-error" id="eazycv-error-' . sanitize_title($field['name']) . '">' . $field['label'] . ' is verplicht</div>';
        $html .= '</div>';


        return $html;
    }

    /**
     * type of candidate
     *
     * @param $field
     *
     * @return string
     */
    public function formType($field)
    {
        $html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title($field['name']) . '">';
        $html .= '<label id="eazycv-label-for-' . sanitize_title($field['name']) . '" for="field-' . sanitize_title($field['label']) . '">' . $field['label'] . '</label>';

        $html .= '<i class="' . $field['icon'] . ' prefix"></i>';
        $html .= '<select id="field-' . sanitize_title($field['label']) . '" name="type"  data-eazycv-required="' . sanitize_title($field['name']) . '" class="validate">';
        foreach ($this->lists['LicenceTypes'] as $group => $items) {
            foreach ($items as $onLic) {
                if ($this->licence['customer']['licence_types'][$onLic] == true) {
                    $html .= '<option value="' . $onLic . '"';
                    if ($form['source']['auto_candidate_type'] == $onLic) {
                        $html .= 'selected';
                    }
                }
                $html .= $onLic;
                $html . '</option>';
            }
        }
        $html .= '</select>';
        $html .= '<div class="eazycv-apply-error eazycv-hidden eazy-error" id="eazycv-error-' . sanitize_title($field['name']) . '">' . $field['label'] . ' is verplicht</div>';
        $html .= '</div>';

        return $html;
    }


}