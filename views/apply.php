<?php

class Wp_EazyCV_Apply {

	public $job = null;
	private $api = null;
	private $lists = null;
	private $licence = null;

	function __construct( $api, $job = null ) {
		$this->api = $api;
		$this->job = $job;
	}

	public function render() {

		//first get the form
		$mainForm = get_option( 'wp_eazycv_apply_form' );
		if ( empty( $mainForm ) ) {
			dump( 'Applyform EazyCV is not set' );
		}
		$formSettings = $this->api->get( 'connectivity/public-forms/' . $mainForm );
		if ( empty( $formSettings['settings'] ) ) {
			dd( 'Applyform EazyCV is not set' );
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

				return '<div class="eazy-error">' . __( 'Uw inschrijving is helaas niet verwerkt, neem contact op met ons.' ) . '</div>';
			} else {
				return '<div class="eazy-success">' . $form['success_message'] . '</div>';
			}
		}

		//otherwise render the applicaiton form
		$this->lists   = $this->api->get( 'lists' );
		$this->licence = $this->api->get( 'licence' );

		if ( ! empty( $this->job ) ) {
			$html = '<h2>' . $this->job['functiontitle'] . '</h2>';
		} else {
			$html = '<h2>' . __( 'Open Application' ) . '</h2>';

		}
		$html .= '<div class="eazycv-form">
		<form method="post" id="eazycv-apply-form" enctype="multipart/form-data">
  			<input type="hidden" name="job_id" value="'.$this->job['id'].'">';

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

		$html .= '<hr /><input class="eazy-submit eazy-btn" id="eazy-apply-submit-btn" type="button" value="' . __( 'Submit' ) . '">';

		$html .= '</form></div>';

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
		$html = '<div class="eazycv-form-group">' . PHP_EOL;
		$html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
		$html .= '<label class="eazycv-label" for="eazycv-field-' . sanitize_title( $field['name'] ) . '">' . $field['label'] . '</label>' . PHP_EOL;
		$html .= '<input type="text" id="eazycv-field-' . sanitize_title( $field['name'] ) . '" name="' . $field['name'] . '"';

		if ( $field['required'] ) {
			$html .= ' class="eazycv-field eazycv-text validate" required="" aria-required="true" />';
		} else {
			$html .= ' class="eazycv-field eazycv-text" />';
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
	public function fileUpload( $field ) {
		$html = '<div class="eazycv-form-group">' . PHP_EOL;
		$html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
		$html .= '<label class="eazycv-label" for="eazycv-field-' . sanitize_title( $field['name'] ) . '">' . $field['label'] . '</label>' . PHP_EOL;
		$html .= '<input type="file" id="eazycv-field-' . sanitize_title( $field['name'] ) . '" name="' . $field['name'] . '"';

		if ( $field['required'] ) {
			$html .= ' class="eazycv-field eazycv-file validate" required="" aria-required="true" />';
		} else {
			$html .= ' class="eazycv-field eazycv-file" />';
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
	public function dateField( $field ) {
		$html = '<div class="eazycv-form-group">' . PHP_EOL;
		$html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
		$html .= '<label class="eazycv-label" for="eazycv-field-' . sanitize_title( $field['name'] ) . '">' . $field['label'] . '</label>' . PHP_EOL;
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
		$html = '<div class="eazycv-form-group">' . PHP_EOL;
		$html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
		$html .= '<label class="eazycv-label" for="eazycv-field-' . sanitize_title( $field['name'] ) . '">' . $field['label'] . '</label>' . PHP_EOL;
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
		$html = '<div class="eazycv-form-group">' . PHP_EOL;
		$html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>' . PHP_EOL;
		$html .= '<label class="eazycv-label" for="eazycv-field-' . sanitize_title( $field['name'] ) . '">' . $field['label'] . '</label>' . PHP_EOL;
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
		$html = '<div class="eazycv-form-group">';
		$html .= '<i class="' . $field['icon'] . ' eazycv-icon"></i>';
		$html .= '<label class="eazycv-label" for="eazycv-field-' . sanitize_title( $field['name'] ) . '">' . $field['label'] . '</label>';
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
		$html = '<i class="' . $field['icon'] . ' prefix"></i>';
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
		$html .= '<label for="field-' . sanitize_title( $field['label'] ) . '">' . $field['label'] . '</label>';

		return $html;
	}


	private function performApply( $postData ) {


		if ( isset( $postData['files']['cv_document'] ) ) {

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
			$postData['run_textkernel'] = false;
		}

		if ( isset( $postData['files']['cv_document_tk'] ) ) {

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
			$postData['run_textkernel'] = true;
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

		$postData['subscription_form_id'] = get_option( 'wp_eazycv_apply_form' );;

		unset( $postData['files'] );

		try {
			$res = $this->api->post( 'candidates/signup', $postData );

		} catch ( Exception $exception ) {
			return 'Error';
		}

		return 'Oke';
	}
}