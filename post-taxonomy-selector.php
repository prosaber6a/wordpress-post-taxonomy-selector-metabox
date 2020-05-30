<?php

/**
 * Plugin Name: Post & Taxonomy Selector
 * Plugin URI: http://saberhr.com
 * Author: SaberHR
 * Author URI: http://saberhr.com
 * Description: WordPress Post & Taxonomy Selector Dropdown Meta field
 * Licence: GPLv2 or Later
 * Text Domain: post-taxonomy-selector
 * Domain Path: /languages/
 */

function pts_init() {
	add_action( 'admin_enqueue_scripts', 'pts_admin_assets' );
}

add_action( 'init', 'pts_init' );

function pts_admin_assets() {
	wp_enqueue_style( 'pts-admin-style', plugin_dir_url( __FILE__ ) . 'assets/admin/css/style.css', null, time() );
}

function pts_load_textdomain() {
	load_plugin_textdomain( 'post-taxonomy-selector', false, plugin_dir_url( __FILE__ ) . '/languages' );
}

add_action( 'plugins_loaded', 'pts_load_textdomain' );

function pts_add_metabox() {
	add_meta_box(
		'pts_select_post',
		__( 'Select Posts', 'post-taxonomy-selector' ),
		'pts_display_metabox',
		array( 'page' )
	);
}

add_action( 'load-post.php', 'pts_add_metabox' );


function pts_display_metabox( $post ) {
	$selected_post_id = get_post_meta( $post->ID, 'pts_select_post', true );
	wp_nonce_field( 'pts_posts', 'pts_posts_nonce' );
	$label         = __( 'Select Post', 'post-taxonomy-selector' );
	$args          = array(
		'post_type'     => 'post',
		'post_per_page' => - 1
	);
	$_post         = new wp_query( $args );
	$dropdown_list = "";
	while ( $_post->have_posts() ) {
		$extra = "";
		$_post->the_post();
		if ( get_the_ID() == $selected_post_id ) {
			$extra = "selected";
		}
		$dropdown_list .= sprintf( '<option %s value="%s">%s</option>', esc_attr( $extra ), esc_attr( get_the_ID() ), esc_html( get_the_title() ) );
	}
	wp_reset_query();
	$metabox_html = <<<EOD
<div class="fields">
	<div class="field_c">
		<div class="label_c">
			<label for="pts_post">{$label}</label>
		</div>
		<div class="input_c">
		    <select name="pts_post" id="pts_post">
		        <option value="0">{$label}</option>
		        {$dropdown_list}
            </select>
		</div>
		<div class="float-clear"></div>
	</div>
</div>
EOD;
	echo $metabox_html;
}


function pts_save_select_post_metavalue( $post_id ) {
	if ( ! pts_is_sercured( 'pts_posts_nonce', 'pts_posts', $post_id ) ) {
		return $post_id;
	}

	$selected_post_id = $_POST['pts_post'];
	if ( $selected_post_id > 0 ) {
		update_post_meta( $post_id, 'pts_select_post', $selected_post_id );
	}

	return $post_id;


}

add_action( 'save_post', 'pts_save_select_post_metavalue' );


if ( ! function_exists( 'pts_is_sercured' ) ) {
	function pts_is_sercured( $nonce_field, $nonce_action, $post_id ) {
		$nonce = isset( $_POST[ $nonce_field ] ) ? $_POST[ $nonce_field ] : '';

		if ( '' == $nonce ) {
			return $post_id;
		}

		if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		if ( wp_is_post_autosave( $post_id ) ) {
			return false;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return false;
		}

		return true;
	}
}
