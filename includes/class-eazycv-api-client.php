<?php

class wpeazycv_api_client {
	private $base_uri;
	private $path_uri;
	private $querystr;
	private $headers = [];
	private $content_type;
	private $content;
	private $instance;
	private $authorization;
	private $method;

	public function __construct() {
		$this->set_method( 'GET' );
		$this->set_base_uri( 'https://api.eazycv.net/' );
		$this->set_content_type( 'application/json' );
		$this->set_instance_id( get_option( 'wpeazycv-settings.instance-id' ) );
		$this->set_instance_pass( get_option( 'wpeazycv-settings.instance-pass' ) );
	}

	public function debug( $dbg ) {
		echo "<pre>";
		print_r( $dbg );
		echo "</pre>";
	}

	private function request() {
		try {
			$ch = curl_init();

			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_URL, $this->get_request_uri() );
			curl_setopt( $ch, CURLOPT_HTTPGET, true );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $this->get_headers() );
			curl_setopt( $ch, CURLOPT_VERBOSE, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );

			if ( ! curl_exec( $ch ) ) {
				die( 'Error: "' . curl_error( $ch ) . '" - Code: ' . curl_errno( $ch ) );
			} else {
				$response = array( 'status' => curl_getinfo( $ch, CURLINFO_HTTP_CODE ), 'body' => $this->parse_response_body( curl_exec( $ch ) ) );
			}
			curl_close( $ch );

			return $response;
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
	}

	public function GET() {
		$this->method  = 'GET';
		$this->content = false;

		return $this->request();
	}

	public function test() {
		$this->set_path_uri( 'licence' );
		$body = $this->GET();

		if ( $body['body'] && $body['body']['type'] && $body['body']['type'] === 'json' ) {
			if ( $body['body']['content'] && $body['body']['content']['status'] === 'error' ) {
				return array( 'test' => 'failed' );
			}

			$body_data = $body['body']['content']['data'];

			$data = array(
				'customer_id'            => $body_data['customer']['id'],
				'status'                 => $body_data['customer']['active'],
				'name'                   => $body_data['customer']['name'],
				'email'                  => $body_data['customer']['email'],
				'address'                => $body_data['customer']['addres'],
				'subdomain'              => $body_data['customer']['subdomain'],
				'billing_name'           => $body_data['customer']['billing_name'],
				'billing_email'          => $body_data['customer']['billing_email'],
				'billing_country'        => $body_data['customer']['billing_country'],
				'billing_vat'            => $body_data['customer']['billing_vat'],
				'billing_reference'      => $body_data['customer']['billing_reference'],
				'billing_salutation'     => $body_data['customer']['billing_salutation'],
				'billing_address'        => $body_data['customer']['billing_address'],
				'qty_organisations'      => $body_data['customer']['qty_organisations'],
				'qty_users'              => $body_data['customer']['qty_users'],
				'qty_candidates'         => $body_data['customer']['qty_candidates'],
				'qty_contacts'           => $body_data['customer']['qty_contacts'],
				'qty_jobs'               => $body_data['customer']['qty_jobs'],
				'qty_textkernel'         => $body_data['customer']['qty_textkernel'],
				'licence_type_candidate' => $body_data['customer']['licence_types']['candidate'],
				'licence_type_employee'  => $body_data['customer']['licence_types']['employee']
			);

			return array( 'test' => 'succes', $data );
		}

		return array( 'test' => 'failed' );
	}

	public function set_instance_id( $instance ) {
		$this->headers['X-Customer'] = $instance;
	}

	public function set_instance_pass( $authorization ) {
		$this->headers['X-Authorization'] = $authorization;
	}

	private function parse_response_body( $response_body ) {
		$json_body = @json_decode( $response_body, true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			return array( 'type' => 'json', 'content' => $json_body );
		}

		return array( 'type' => 'text', 'content' => $response_body );
	}

	private function set_base_uri( $base_uri ) {
		$this->base_uri = $base_uri;
	}

	private function set_path_uri( $path_uri ) {
		$this->path_uri = $path_uri;
	}

	private function set_querystr( $querystr ) {
		$qs             = http_build_query( $querystr );
		$this->querystr = strlen( $qs ) > 0 ? '?' . $qs : '';
	}

	private function set_content( $content = false ) {
		$this->content = json_encode( strlen( $content ) > 0 ? $content : $this->content );
		$this->set_content_length();
	}

	private function set_content_type( $content_type = false ) {
		$this->content_type = strlen( $content_type ) > 0 ? $content_type : $this->content_type;
	}

	private function set_content_length() {
		$this->content_length = strlen( $this->content );
	}

	private function set_method( $method = false ) {
		$this->method = strlen( $method ) > 0 ? strtoupper( $method ) : strtoupper( $this->method );
	}

	private function get_request_uri() {
		return $this->base_uri . ( strlen( $this->path_uri ) > 0 ? $this->path_uri : '' ) . ( strlen( $this->querystr ) > 0 ? $this->querystr : '' );
	}

	private function set_headers( $headers ) {
		$this->headers['Host']       = $this->base_uri;
		$this->headers['Connection'] = 'close';
		$this->headers['User-Agent'] = 'wpeazycv_api_client';
		if ( in_array( $this->method, [ 'POST', 'PUT' ] ) ) {
			$this->headers['Content-Type']   = $this->content_type;
			$this->headers['Content-Length'] = strlen( $this->content );
		}
		if ( is_array( $headers ) ) {
			foreach ( $headers as $key => $val ) {
				$this->headers[ $key ] = $val;
			}
		}
	}

	private function get_headers() {
		$headers = [];
		if ( is_array( $this->headers ) && count( $this->headers ) > 0 ) {
			foreach ( $this->headers as $key => $val ) {
				$headers[] = $key . ': ' . $val;
			}
		}

		return $headers;
	}
}
