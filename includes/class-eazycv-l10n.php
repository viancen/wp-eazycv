<?php

class wpeazycv_l10n {

	private $languages = array( 'nl', 'en' );
	private $language;
	private $library;

	function __construct( $language ) {

		if ( ! in_array( $language, $this->languages ) ) {
			die( 'Language not available right now.' );
		}

		$this->library              = new stdClass();
		$this->library->{$language} = new stdClass();

		$this->language = $language;
		$this->load();
	}

	public function get_array( $keys ) {
		$arr = [];
		foreach ( $keys as $key ) {
			$arr[ $key ] = $this->get( $key );
		}

		return $arr;
	}

	public function get( $key, $default = false ) {
		$get  = explode( '.', $key );
		$node = $this->library->{$this->language};

		foreach ( $get as $key ) {
			if ( ! $node->{$key} ) {
				if ( $default ) {
					return $default;
				}

				return 'undefined';
			}
			$node = $node->{$key};
		}

		return $node;
	}

	private function load() {
		$l10n_file = WPEAZYCV_PLUGIN_DIR . '/l10n/' . $this->language . '.local.php';
		if ( ! file_exists( $l10n_file ) ) {
			die( 'Could not load l10n file: ' . $l10n_file );
		}
		require_once( $l10n_file );
		if ( ! isset( $local ) ) {
			die( 'Loaded l10n file is corrupted or empty.' );
		}
		$this->library->{$this->language} = $this->l10n_add( $local );
	}

	private function l10n_add( $local ) {
		$node = new stdClass();
		foreach ( $local as $key => $val ) {
			if ( ! is_array( $val ) ) {
				$node->{$key} = $val;
			} else {
				$node->{$key} = $this->l10n_add( $val );
			}
		}

		return $node;
	}

	public function add_globals( $local ) {
		$globals = $this->get( 'global' );
		foreach ( $globals as $key => $val ) {
			$local[ $key ] = $val;
		}

		return $local;
	}
}

