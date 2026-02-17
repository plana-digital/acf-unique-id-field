<?php
/**
 * Plugin Name: ACF Unique ID Field
 * Description: Adds a Unique ID field type to Advanced Custom Fields.
 * Version: 1.0.0
 * Author: Philip Newcomer
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! class_exists( '\PhilipNewcomer\ACF_Unique_ID_Field\ACF_Field_Unique_ID' ) ) {
	require_once __DIR__ . '/src/ACF_Field_Unique_ID.php';
}

register_activation_hook(
	__FILE__,
	function() {
		add_option( 'acf_unique_id_field_needs_migration', 1, '', false );
	}
);

add_action(
	'admin_init',
	function() {
		if ( ! get_option( 'acf_unique_id_field_needs_migration' ) ) {
			return;
		}

		if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
			return;
		}

		$field_names = array();
		$field_groups = acf_get_field_groups();

		foreach ( $field_groups as $field_group ) {
			acf_unique_id_field_collect_names_from_fields( acf_get_fields( $field_group ), $field_names );
		}

		$field_names = array_unique( $field_names );

		if ( empty( $field_names ) ) {
			delete_option( 'acf_unique_id_field_needs_migration' );
			return;
		}

		global $wpdb;

		foreach ( $field_names as $field_name ) {
			$meta_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT meta_id FROM {$wpdb->postmeta}
					WHERE (meta_key = %s OR meta_key LIKE %s)
					AND (meta_value = '' OR meta_value IS NULL)",
					$field_name,
					'%' . $wpdb->esc_like( '_' . $field_name )
				)
			);

			foreach ( $meta_ids as $meta_id ) {
				$wpdb->update(
					$wpdb->postmeta,
					array(
						'meta_value' => \PhilipNewcomer\ACF_Unique_ID_Field\ACF_Field_Unique_ID::generate_unique_id(),
					),
					array(
						'meta_id' => (int) $meta_id,
					),
					array( '%s' ),
					array( '%d' )
				);
			}
		}

		delete_option( 'acf_unique_id_field_needs_migration' );
	}
);

/**
 * Collect unique ID field names from a field array.
 *
 * @param array $fields      ACF field definitions.
 * @param array $field_names Found field names.
 *
 * @return void
 */
function acf_unique_id_field_collect_names_from_fields( $fields, &$field_names ) {
	if ( empty( $fields ) || ! is_array( $fields ) ) {
		return;
	}

	foreach ( $fields as $field ) {
		if ( ! empty( $field['name'] ) && 'unique_id' === $field['type'] ) {
			$field_names[] = $field['name'];
		}

		if ( ! empty( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ) {
			acf_unique_id_field_collect_names_from_fields( $field['sub_fields'], $field_names );
		}
	}
}
