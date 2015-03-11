<?php
add_action( 'init', 'ninja_forms_register_tab_general_settings', 9 );

function ninja_forms_register_tab_general_settings(){
	$args = array(
		'name' => __( 'General', 'ninja-forms' ),
		'page' => 'ninja-forms-settings',
		'display_function' => '',
		'save_function' => 'ninja_forms_save_general_settings',
	);
	ninja_forms_register_tab( 'general_settings', $args );
}

add_action('init', 'ninja_forms_register_general_settings_metabox');

function ninja_forms_register_general_settings_metabox(){

	$plugin_settings = nf_get_settings();
	if ( isset ( $plugin_settings['version'] ) ) {
		$current_version = $plugin_settings['version'];
	} else {
		$current_version = NF_PLUGIN_VERSION;
	}

	$args = array(
		'page' => 'ninja-forms-settings',
		'tab' => 'general_settings',
		'slug' => 'general_settings',
		'title' => __( 'General Settings', 'ninja-forms' ),
		'settings' => array(
			array(
				'name' 	=> 'version',
				'type' 	=> 'desc',
				'label' => __( 'Version', 'ninja-forms' ),
				'desc' 	=> $current_version,
			),
			array(
				'name' 	=> 'date_format',
				'type' 	=> 'text',
				'label' => __( 'Date Format', 'ninja-forms' ),
				'desc' 	=> 'e.g. m/d/Y, d/m/Y - ' . sprintf( __( 'Tries to follow the %sPHP date() function%s specifications, but not every format is supported.', 'ninja-forms' ), '<a href="http://www.php.net/manual/en/function.date.php" target="_blank">', '</a>' ),
			),
			array(
				'name' 	=> 'currency_symbol',
				'type' 	=> 'text',
				'label' => __( 'Currency Symbol', 'ninja-forms' ),
				'desc' 	=> 'e.g. $, &pound;, &euro;',
			),
		),
	);
	ninja_forms_register_tab_metabox( $args );	

	$args = array(
		'page' => 'ninja-forms-settings',
		'tab' => 'general_settings',
		'slug' => 'advanced_settings',
		'title' => __( 'Advanced Settings', 'ninja-forms' ),
		'settings' => array(
			array(
				'name'	=> 'delete_on_uninstall',
				'type'	=> 'checkbox',
				'label'	=> __( 'Remove ALL Ninja Forms data upon uninstall?', 'ninja-forms' ),
				'desc'	=> sprintf( __( 'If this box is checked, ALL Ninja Forms data will be removed from the database upon deletion. %sAll form and submission data will be unrecoverable.%s', 'ninja-forms' ), '<span class="nf-nuke-warning">', '</span>' ),
			)
		),
		'state' => 'closed',
	);
	ninja_forms_register_tab_metabox( $args );

}

function ninja_forms_save_general_settings( $data ){
	$plugin_settings = nf_get_settings();

	foreach( $data as $key => $val ){
		$plugin_settings[$key] = $val;
	}

	update_option( 'ninja_forms_settings', $plugin_settings );
	$update_msg = __( 'Settings Saved', 'ninja-forms' );
	return $update_msg;
}