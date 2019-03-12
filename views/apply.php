<?php

class Wp_EazyCV_Apply {

	public $job = null;

	private $api = null;
	private $lists = null;
	private $licence = null;
	private $apply_form = null;

	function __construct( $api, $atts, $job = null ) {
		$this->atts = $atts;
		$this->api  = $api;
		$this->job  = $job;
	}

	public function render() {

		$mainForm = null;

		///wp force
		wp_enqueue_script( 'eazy_recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . get_option( 'wp_eazycv_google_api_key' ), [], '19801203' );

		$mainForm = null;
		if ( isset( $_GET['applyform'] ) ) {
			$mainForm = intval( $_GET['applyform'] );
		}

		if ( empty( $mainForm ) && ! empty( $this->atts['portal_id'] ) ) {
			$mainForm = intval( $this->atts['portal_id'] );
		}

		if ( empty( $mainForm ) ) {
			$mainForm = get_option( 'wp_eazycv_apply_form' );
		}
		//first get the form
		if ( empty( $mainForm ) ) {
			return '<div class="eazy-error">' . __( 'Er is geen inschrijfformulier ingesteld.' ) . '</div>';
		}

		$this->apply_form = $mainForm;

		$googleKey    = get_option( 'wp_eazycv_google_api_key' );
		$googleSecret = get_option( 'wp_eazycv_google_api_secret' );

		if ( empty( $googleKey ) || empty( $googleSecret ) ) {
			return '<div class="eazy-error">' . __( 'Er is geen CAPTCHA ingesteld.' ) . '</div>';
		}

		try {
			$formSettings = $this->api->get( 'connectivity/public-forms/' . $mainForm );
		} catch ( Exception $exception ) {
			return '<div class="eazy-error">' . __( 'Er is een fout inschrijfformulier ingesteld.' ) . '</div>';
		}
		if ( empty( $formSettings['settings'] ) ) {
			return '<div class="eazy-error">' . __( 'Er is geen inschrijfformulier ingesteld.' ) . '</div>';
		}
		$form         = $formSettings;
		$formSettings = $formSettings['settings'];

		//check if the form is submitted
		$newApplication = [];
		if ( ! empty( $_POST ) ) {
			$newApplication = $_POST;
			if ( ! empty( $_FILES ) ) {
				$newApplication['files'] = $_FILES;
			}

			$success = $this->performApply( $newApplication );
			if ( $success == 'Error' ) {
				$url = current_location() . '?success=false';

				return '<div class="eazy-error">' . __( 'Uw inschrijving is helaas niet verwerkt, neem contact met ons op.' ) . '</div>';
			} elseif ( $success == 'Error-Captcha' ) {
				return '<div class="eazy-error">' . __( 'Uw inschrijving is helaas niet verwerkt, ben je een robot?' ) . '</div>';
			} else {
				return '<div class="eazy-success">' . $form['success_message'] . '</div><div id="eazycv-success-apply"></div>';
			}
		}

		//otherwise render the applicaiton form
		$this->lists   = $this->api->get( 'lists' );
		$this->licence = $this->api->get( 'licence' );
		$legal_stuff   = $this->api->get( 'legal-info' );

		if ( ! empty( $this->job ) ) {
			$html = '<h2 class="eazycv-job-view-h2">' . $this->job['original_functiontitle'] . '</h2>';
		} else {
			$html = '<h2 class="eazycv-job-view-h2">' . __( 'Open Application' ) . '</h2>';

		}
		$html .= '

<div class="eazycv-form">
<div class="eazy-error" id="eazy-from-apply-error" style="display:none;"></div>
<input type="hidden" value="' . $googleKey . '" id="eazycv-grekey">
		<form method="post" id="eazycv-apply-form" class="validate" enctype="multipart/form-data">
  			<input type="hidden" class="eazymatch-active" name="grepact" value="" id="eazycv-greval">
  			<input type="hidden" id="eazycv-apply-job_id"  name="job_id" value="' . $this->job['id'] . '">
  			';

		foreach ( $formSettings['fields'] as $field ) {

			if ( $field['name'] == 'type' ) {
				$html .= $this->formType( $field );
			} elseif ( $field['name'] == 'gender' ) {
				$html .= $this->gender( $field );
			} elseif ( in_array( $field['name'], [ 'cv_document', 'cv_document_tk', 'picture' ] ) ) {
				$html .= $this->fileUpload( $field );
			} elseif ( in_array( $field['name'], [ 'birth_date', 'available_from', 'available_to' ] ) ) {
				$html .= $this->dateField( $field );
			} elseif ( in_array( $field['name'], [ 'motivation', 'description' ] ) ) {
				$html .= $this->textarea( $field );
			} elseif ( $field['name'] == 'connect_through' ) {
				$html .= $this->connectThrough( $field );
			} else {
				$html .= $this->textField( $field );
			}
		}

		$html .= $this->gdpr( $legal_stuff );

		$html .= '<hr /><input class="eazy-submit eazy-btn" id="eazy-apply-submit-btn" type="button" value="' . __( 'Submit' ) . '">';

		$html .= '</form></div>';

		return $html;
	}


	/**
	 * @param $legal_stuff
	 *
	 * @return string
	 */
	public function gdpr( $legal_stuff ) {

		$html = '';
		if ( ! empty( $legal_stuff['gdpr_candidate']['content'] ) ) {

			$html .= '<p class="eazycv-gdpr">
                    <label for="field-gdpr">
                        <input type="checkbox" id="eazycv-field-gdpr" aria-required="true" required class="required validate"
                               name="accept_gdpr_version"
                               value="' . $legal_stuff['gdpr_candidate']['version_nr'] . '"/>
                             
                        <a href="javascript:;" id="eazycv-gdpr-link"> ' . __( 'Ik ga akkoord met de privacy voowaarden & algemene voorwaarden' ) . '</a>
                    </label>
                </p>';

			$html .= '<!-- The Modal -->

				<div id="eazycv-gdpr-modal" class="eazycv-modal">
					<div class="eazycv-modal-content">
					  <div class="eazycv-modal-header">
					    <span class="eazycv-close">&times;</span>
					    <h2  class="eazycv-h2-gdpr-heading">' . __( 'Privacy & algemene voorwaarden' ) . ': ' . $legal_stuff['gdpr_candidate']['version_nr'] . '</h2>
					  </div>
					  <div class="eazycv-modal-body">
					     <h4 class="eazycv-h4-privacy-heading">' . __( 'Privacy voorwaarden' ) . '</h4>
					     <p class="eazycv-h4-privacy-p">' . $legal_stuff['gdpr_candidate']['content'] . '</p>
					     <hr />
					     <h4 class="eazycv-h4-terms-heading">' . __( 'Algemene voorwaarden' ) . '</h4>
					      <p class="eazycv-h4-terms-p">' . $legal_stuff['terms_candidate']['content'] . '</p>
					  </div>
					  <div class="eazycv-modal-footer">
					    <h3><button id="accept-gdpr-modal-btn" type="button" class="eazycv-btn">' . __( 'Ik ga akkoord' ) . '</button> </h3>
					  </div>
					</div> 
				</div>';
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
	public function textField( $field ) {
		$html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title( $field['name'] ) . '">';
		$html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
		$html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title( $field['name'] ) . '" for="eazycv-field-' . sanitize_title( $field['name'] ) . '">' . $field['label'] . '</label>' . PHP_EOL;
		$html .= '<input type="text" id="eazycv-field-' . sanitize_title( $field['name'] ) . '" name="' . $field['name'] . '"';

		if ( $field['required'] ) {
			$html .= ' class="eazycv-field eazycv-text validate" required="" aria-required="true" />';
		} else {
			$html .= ' class="eazycv-field eazycv-text" />';
		}
		$html .= '</div>' . PHP_EOL;

		if ( $field['name'] == 'email' ) {
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
	public function fileUpload( $field ) {
		$html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title( $field['name'] ) . '">';
		$html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
		$html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title( $field['name'] ) . '" for="eazycv-field-' . sanitize_title( $field['name'] ) . '">' . $field['label'] . '</label>' . PHP_EOL;
		$html .= '<div class="eazy-file-upload-wrapper" data-text="Selecteer ' . $field['label'] . '">';
		$html .= '<input type="file" id="eazycv-field-' . sanitize_title( $field['name'] ) . '" name="' . $field['name'] . '"';


		if ( $field['required'] ) {
			$html .= ' class="eazycv-field eazycv-file validate" required="" aria-required="true" />';
		} else {
			$html .= ' class="eazycv-field eazycv-file" />';
		}
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
	public function dateField( $field ) {
		$html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title( $field['name'] ) . '">';
		$html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
		$html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title( $field['name'] ) . '" for="eazycv-field-' . sanitize_title( $field['name'] ) . '">' . $field['label'] . '</label>' . PHP_EOL;
		$html .= '<input type="text" id="eazycv-field-' . sanitize_title( $field['name'] ) . '" name="' . $field['name'] . '"';

		if ( $field['required'] ) {
			$html .= ' class="eazycv-field eazycv-date validate" required="" aria-required="true" />';
		} else {
			$html .= ' class="eazycv-field eazycv-date" />';
		}
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
	public function textarea( $field ) {
		$html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title( $field['name'] ) . '">';
		$html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
		$html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title( $field['name'] ) . '" for="eazycv-field-' . sanitize_title( $field['name'] ) . '">' . $field['label'] . '</label>' . PHP_EOL;
		$html .= '<textarea id="eazycv-field-' . sanitize_title( $field['name'] ) . '" name="' . $field['name'] . '"';

		if ( $field['required'] ) {
			$html .= ' class="eazycv-field eazycv-textarea validate" required="" aria-required="true">';
		} else {
			$html .= ' class="eazycv-field eazycv-textarea">';
		}
		$html .= '</textarea>' . PHP_EOL;
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
	public function gender( $field ) {
		$html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title( $field['name'] ) . '">';
		$html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
		$html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title( $field['name'] ) . '" for="eazycv-field-' . sanitize_title( $field['name'] ) . '">' . $field['label'] . '</label>' . PHP_EOL;
		$html .= '<select id="eazycv-field-' . sanitize_title( $field['name'] ) . '" name="gender"';

		if ( $field['required'] ) {
			$html .= ' class="eazycv-field eazycv-select validate" required="" aria-required="true">' . PHP_EOL;
		} else {
			$html .= ' class="eazycv-field eazycv-select">' . PHP_EOL;
		}
		$html .= ' <option value="m">' . __( 'Male' ) . '</option>' . PHP_EOL;
		$html .= ' <option value="f">' . __( 'Female' ) . '</option>' . PHP_EOL;

		$html .= '</select>' . PHP_EOL;
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
	public function connectThrough( $field ) {
		$html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title( $field['name'] ) . '">';
		$html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>';
		$html .= '<label class="eazycv-label" id="eazycv-label-for-' . sanitize_title( $field['name'] ) . '" for="eazycv-field-' . sanitize_title( $field['name'] ) . '">' . $field['label'] . '</label>';
		$html .= '<select id="eazycv-field-' . sanitize_title( $field['name'] ) . '" name="connect_through_id"';

		if ( $field['required'] ) {
			$html .= ' class="eazycv-field eazycv-select validate" required="" aria-required="true">';
		} else {
			$html .= ' class="eazycv-field eazycv-select">';
		}

		foreach ( $this->lists['ConnectThrough'] as $key => $listItem ) {
			$html .= ' <option value="' . $listItem['id'] . '">' . $listItem['name'] . '</option>';
		}

		$html .= '</select>';
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
	public function formType( $field ) {
		$html = '<div class="eazycv-form-group eazycv-wrapper-' . sanitize_title( $field['name'] ) . '">';
		$html .= '<label id="eazycv-label-for-' . sanitize_title( $field['name'] ) . '" for="field-' . sanitize_title( $field['label'] ) . '">' . $field['label'] . '</label>';

		$html .= '<i class="' . $field['icon'] . ' prefix"></i>';
		$html .= '<select id="field-' . sanitize_title( $field['label'] ) . '" name="type" required="" aria-required="true" class="validate">';
		foreach ( $this->lists['LicenceTypes'] as $group => $items ) {
			foreach ( $items as $onLic ) {
				if ( $this->licence['customer']['licence_types'][ $onLic ] == true ) {
					$html .= '<option value="' . $onLic . '"';
					if ( $form['source']['auto_candidate_type'] == $onLic ) {
						$html .= 'selected';
					}
				}
				$html .= $onLic;
				$html . '</option>';
			}
		}
		$html .= '</select>';
		$html .= '</div>';

		return $html;
	}


	/*
	 * ggreat magic
	 */
	private function performApply( $postData ) {


		if ( ! isset( $postData['grepact'] ) ) {
			return 'Error';
		}

		$response = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify?secret=' . get_option( 'wp_eazycv_google_api_secret' ) . '&response=' . $postData['grepact'] . '&remoteip=' . $_SERVER['REMOTE_ADDR'] );
		$resp     = json_decode( $response );

		if ( ! $resp->success ) {
			return 'Error-Captcha';
		}

		if ( isset( $postData['files']['cv_document'] ) && ! empty( $postData['files']['cv_document']['tmp_name'] ) ) {

			$file = $postData['files']['cv_document'];

			$source = file_get_contents( $postData['files']['cv_document']['tmp_name'] );

			$imageFileType = strtolower( pathinfo( $postData['files']['cv_document']['name'], PATHINFO_EXTENSION ) );

			if ( in_array( $imageFileType, [
				'txt',
				'pdf',
				'doc',
				'docx'
			] )
			) {
				$mimeType = [
					'txt'  => 'application/text',
					'pdf'  => 'application/pdf',
					'doc'  => 'application/msword',
					'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				];
				//resume addon
				$postData['resume']   = [
					'filename'  => $postData['files']['cv_document']['name'],
					'mime_type' => $mimeType[ $imageFileType ],
					'content'   => base64_encode( $source )
				];
				$postData['document'] = [
					'content' => $postData['resume']['content'],
					'name'    => $postData['resume']['filename'],
					'type'    => $postData['resume']['mime_type'],
				];

			}

			unset( $postData['files']['cv_document'] );
			$postData['useTextkernel'] = false;
		}

		if ( isset( $postData['files']['cv_document_tk'] ) && ! empty( $postData['files']['cv_document_tk']['tmp_name'] ) ) {

			$file = $postData['files']['cv_document_tk'];

			$source = file_get_contents( $postData['files']['cv_document_tk']['tmp_name'] );

			$imageFileType = strtolower( pathinfo( $postData['files']['cv_document_tk']['name'], PATHINFO_EXTENSION ) );

			if ( in_array( $imageFileType, [
				'txt',
				'pdf',
				'doc',
				'docx'
			] )
			) {
				$mimeType = [
					'txt'  => 'application/text',
					'pdf'  => 'application/pdf',
					'doc'  => 'application/msword',
					'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				];

				//resume addon
				$postData['resume']   = [
					'filename'  => $postData['files']['cv_document_tk']['name'],
					'mime_type' => $mimeType[ $imageFileType ],
					'content'   => base64_encode( $source )
				];
				$postData['document'] = [
					'content' => $postData['resume']['content'],
					'name'    => $postData['resume']['filename'],
					'type'    => $postData['resume']['mime_type'],
				];
			}

			unset( $postData['files']['cv_document_tk'] );
			$postData['useTextkernel'] = true;
		}

		if ( isset( $postData['files']['avatar'] ) ) {

			$file = $postData['files']['avatar'];

			$source = file_get_contents( $postData['files']['avatar']['tmp_name'] );

			$imageFileType = strtolower( pathinfo( $postData['files']['avatar']['name'], PATHINFO_EXTENSION ) );

			if ( in_array( $imageFileType, [
				'png',
				'jpg',
				'jpeg'
			] )
			) {
				$mimeType = [
					'png'  => 'image/png',
					'jpg'  => 'image/jpg',
					'jpeg' => 'image/jpeg',
				];
				//resume addon
				$postData['avatar'] = [
					'filename'  => $postData['files']['cv_document_tk']['name'],
					'mime_type' => $mimeType[ $imageFileType ],
					'content'   => base64_encode( $source )
				];
			}

			unset( $postData['files']['avatar'] );
		}

		$userFields = [
			'first_name',
			'last_name',
			'prefix',
			'birth_date',
			'gender',
			'avatar',
			'country'
		];

		foreach ( $userFields as $fieldName ) {
			if ( isset( $postData[ $fieldName ] ) ) {
				$postData['user'][ $fieldName ] = $postData[ $fieldName ];
				unset( $postData[ $fieldName ] );
			}
		}

		if ( ! isset( $postData['user']['gender'] ) ) {
			$postData['user']['gender'] = 'm';
		}

		$postData['subscription_form_id'] = $this->apply_form;

		unset( $postData['files'] );

		try {

			$res = $this->api->post( 'candidates/signup', $postData );

		} catch ( Exception $exception ) {
			return 'Error';
		}

		return 'Oke';
	}
}