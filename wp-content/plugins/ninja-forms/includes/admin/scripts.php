<?php
//Load up our WP Ninja Custom Form JS files.
function ninja_forms_admin_css(){
	$plugin_settings = nf_get_settings();

	wp_enqueue_style( 'jquery-smoothness', NINJA_FORMS_URL .'css/smoothness/jquery-smoothness.css' );
	wp_enqueue_style( 'ninja-forms-admin', NINJA_FORMS_URL .'css/ninja-forms-admin.css', 'editor' );

	add_filter('admin_body_class', 'ninja_forms_add_class');

}

function ninja_forms_add_class($classes) {
	// add 'class-name' to the $classes array
	$classes .= ' nav-menus-php';
	// return the $classes array
	return $classes;
}

function ninja_forms_admin_js(){
	global $version_compare;

	$form_id = isset ( $_REQUEST['form_id'] ) ? $_REQUEST['form_id'] : '';

	if ( defined( 'NINJA_FORMS_JS_DEBUG' ) && NINJA_FORMS_JS_DEBUG ) {
		$suffix = '';
		$src = 'dev';
	} else {
		$suffix = '.min';
		$src = 'min';
	}

	$plugin_settings = nf_get_settings();
	if(isset($plugin_settings['date_format'])){
		$date_format = $plugin_settings['date_format'];
	}else{
		$date_format = 'm/d/Y';
	}

	$date_format = ninja_forms_date_to_datepicker($date_format);

	$datepicker_args = array();
	if ( !empty( $date_format ) ) {
		$datepicker_args['dateFormat'] = $date_format;
	}

	wp_enqueue_script('ninja-forms-admin',
	NINJA_FORMS_URL . 'js/' . $src .'/ninja-forms-admin' . $suffix . '.js',
	array('jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'jquery-ui-draggable', 'jquery-ui-droppable'));

	wp_localize_script( 'ninja-forms-admin', 'ninja_forms_settings', array( 'nf_ajax_nonce' => wp_create_nonce( 'nf_ajax'), 'form_id' => $form_id, 'datepicker_args' => apply_filters( 'ninja_forms_admin_forms_datepicker_args', $datepicker_args ), 'add_fav_prompt' => __( 'What would you like to name this favorite?', 'ninja-forms' ), 'add_fav_error' => __( 'You must supply a name for this favorite.', 'ninja-forms' ), 'deactivate_all_licenses_confirm' => __( 'Really deactivate all licenses?', 'ninja-forms' ), 'nuke_warning' => 'This setting will COMPLETELY remove anything Ninja Forms related. This includes SUBMISSIONS and FORMS. It cannot be undone.', 'ninja-forms' ) );

	if ( isset ( $_REQUEST['page'] ) && $_REQUEST['page'] == 'ninja-forms' && isset ( $_REQUEST['tab'] ) ) {
		wp_enqueue_script( 'nf-builder',
			NINJA_FORMS_URL . 'assets/js/' . $src .'/builder' . $suffix . '.js', array( 'backbone' ) );

		if ( '' != $form_id ) {
			$fields = Ninja_Forms()->form( $form_id )->fields;

			$current_tab = ninja_forms_get_current_tab();
			$current_page = isset ( $_REQUEST['page'] ) ? esc_html( $_REQUEST['page'] ) : '';

			foreach ( $fields as $field_id => $field ) {
				$fields[ $field_id ]['metabox_state'] = 0;
			}

			$form_status = Ninja_Forms()->form( $form_id )->get_setting( 'status' );
			$form_title = Ninja_Forms()->form( $form_id )->get_setting( 'form_title' );

			wp_localize_script( 'nf-builder', 'nf_admin', array( 'edit_form_text' => __( 'Edit Form', 'ninja-forms' ), 'form_title' => $form_title, 'form_status' => $form_status, 'fields' => $fields, 'saved_text' => __( 'Saved', 'ninja-forms' ), 'save_text' => __( 'Save', 'ninja-forms' ), 'saving_text' => __( 'Saving...', 'ninja-forms' ), 'remove_field' => __( 'Remove this field? It will be removed even if you do not save.', 'ninja-forms' ) ) );
		}
	}
}
