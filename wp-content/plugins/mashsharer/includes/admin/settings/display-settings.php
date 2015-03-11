<?php
/**
 * Admin Options Page
 *
 * @package     MASHSB
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* Returns list elements for jQuery tab navigation 
 * based on header callback
 * 
 * @since 2.1.2
 * @todo Use sprintf to sanitize  $field['id'] instead using str_replace() Should be much faster? 
 * @return string
 */

function getTabHeader($page, $section){
    global $mashsb_options;
    global $wp_settings_fields;
    
    if (!isset($wp_settings_fields[$page][$section]))
        return;
    
    echo '<ul>';
    foreach ((array) $wp_settings_fields[$page][$section] as $field) {  
    $sanitizedID = str_replace('[', '', $field['id'] );
    $sanitizedID = str_replace(']', '', $sanitizedID );     
     if (strpos($field['callback'],'header') !== false) { 
         echo '<li class="mashsb-tabs"><a href="#' . $sanitizedID . '">' . $field['title'] .'</a></li>';
     }      
    }
    echo '</ul>';
}


/**
 * Print out the settings fields for a particular settings section
 *
 * Part of the Settings API. Use this in a settings page to output
 * a specific section. Should normally be called by do_settings_sections()
 * rather than directly.
 *
 * @global $wp_settings_fields Storage array of settings fields and their pages/sections
 * @return string
 *
 * @since 2.1.2
 *
 * @param string $page Slug title of the admin page who's settings fields you want to show.
 * @param section $section Slug title of the settings section who's fields you want to show.
 * 
 * Copied from WP Core 4.0 /wp-admin/includes/template.php do_settings_fields()
 * We use our own function to be able to create jQuery tabs with easytabs()
 * 
*  We dont use tables here any longer. Are we stuck in the nineties?
 * @todo Use sprintf to sanitize  $field['id'] instead using str_replace() Should be faster?
 * @todo Push this code into EasyDigitalDownload EDD@github
 * @todo some media queries for better responisbility
 */
function mashsb_do_settings_fields($page, $section) {
    global $wp_settings_fields;
    $header = false;
    $firstHeader = false;
    
    if (!isset($wp_settings_fields[$page][$section]))
        return;
    
    // Check first if any callback header registered
    foreach ((array) $wp_settings_fields[$page][$section] as $field) {
       strpos($field['callback'],'header') !== false ? $header = true : $header = false; 
       if ($header === true)
               break;
    }
    
    foreach ((array) $wp_settings_fields[$page][$section] as $field) {
        
       $sanitizedID = str_replace('[', '', $field['id'] );
       $sanitizedID = str_replace(']', '', $sanitizedID );
       
       // Check if header has been created previously
       if (strpos($field['callback'],'header') !== false && $firstHeader === false) { 
           echo '<div id="' . $sanitizedID . '">'; 
           $firstHeader = true;
       } elseif (strpos($field['callback'],'header') !== false && $firstHeader === true) { 
       // Header has been created previously so we have to close the first opened div
           echo '</div><div id="' . $sanitizedID . '">'; 
       } 
        echo '<div class="row">';
        if (!empty($field['args']['label_for']))
            echo '<label for="' . esc_attr($field['args']['label_for']) . '">' . $field['title'] . '</label>';
        else
            echo '<div class="col-title">' . $field['title'] . '</div>';
        echo '<div style="overflow:auto;">';
        call_user_func($field['callback'], $field['args']);
        echo '</div>';
        echo '</div>';
        
    }
    if ($header === true){
    echo '</div>';
    }
}

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_options_page() {
	global $mashsb_options;

	$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], mashsb_get_settings_tabs() ) ? $_GET[ 'tab' ] : 'general';

	ob_start();
	?>
	<div class="wrap">
             <h1> <?php echo __('Welcome to Mashshare ', 'mashsb') . MASHSB_VERSION; ?></h1>
            <div class="about-text" style="font-weight: 400;line-height: 1.6em;font-size: 19px;">
                <?php echo __('Thank you for updating to the latest version!', 'mashsb');?>
                <br>
                <?php echo __('Mashshare is ready to increase your Shares!', 'mashsb'); ?>
                <?php if (!function_exists('curl_init')){ echo '<br><span style="color:red;">' . __('php_curl is not working on your server. </span><a href="http://us.informatiweb.net/programmation/32--enable-curl-extension-of-php-on-windows.html" target="_blank">Please enable it.</a>'); } ?>
                <br>
                <iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fmashshare.net&amp;width=100&amp;layout=standard&amp;action=like&amp;show_faces=false&amp;share=true&amp;height=35&amp;appId=449277011881884" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:400px; height:25px;" allowTransparency="true"></iframe>
            </div>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( mashsb_get_settings_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $tab_id
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
		<div id="tab_container" class="tab_container">
                        <?php getTabHeader( 'mashsb_settings_' . $active_tab, 'mashsb_settings_' . $active_tab ); ?>   
                    <div class="panel-container"> <!-- new //-->
			<form method="post" action="options.php">
				<?php
				settings_fields( 'mashsb_settings' );
				mashsb_do_settings_fields( 'mashsb_settings_' . $active_tab, 'mashsb_settings_' . $active_tab );
				?>
				<!--</table>-->
				<?php submit_button(); ?>
			</form>
                    </div> <!-- new //-->
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}
