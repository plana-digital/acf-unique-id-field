<?php

namespace PhilipNewcomer\ACF_Unique_ID_Field;

use acf_field;

class ACF_Field_Unique_ID extends acf_field {

	/**
	 * Whether the class has been initialized.
	 *
	 * @var bool
	 */
	protected static $initialized = false;

	/**
	 * Initialize the class.
	 */
	public static function init() {
		if ( self::$initialized || ! function_exists( 'add_action' ) ) {
			return;
		}

		self::$initialized = true;

		add_action(
			'acf/include_field_types',
			function() {
				if ( ! class_exists( 'acf_field' ) ) {
					return;
				}

				new static();
			}
		);
	}

	/**
	 * Initialize the field.
	 */
	public function __construct() {
		$this->name     = 'unique_id';
		$this->label    = 'Unique ID';
		$this->category = 'basic';

		parent::__construct();
	}

	/**
	 * Render the HTML field.
	 *
	 * @param array $field The field data.
	 */
	public function render_field( $field ) {
		$value = $field['value'];

		if ( empty( $value ) && false === strpos( $field['name'], 'acfcloneindex' ) ) {
			$value = self::generate_unique_id();
		}

		printf(
			'<input type="text" name="%s" value="%s" readonly>',
			esc_attr( $field['name'] ),
			esc_attr( $value )
		);
	}

	/**
	 * Define the unique ID if one does not already exist.
	 *
	 * @param string $value   The field value.
	 * @param int    $post_id The post ID.
	 * @param array  $field   The field data.
	 *
	 * @return string The filtered value.
	 */
	public function update_value( $value, $post_id, $field ) {

		if ( ! empty( $value ) ) {
			return $value;
		}

		return self::generate_unique_id();
	}

	/**
	 * Generate a unique ID.
	 *
	 * @return string
	 */
	public static function generate_unique_id() {
		if ( function_exists( 'wp_generate_uuid4' ) ) {
			return wp_generate_uuid4();
		}

		return uniqid();
	}
}

ACF_Field_Unique_ID::init();
