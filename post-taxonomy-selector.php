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
	wp_enqueue_style( 'pts-select2-css', '//cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css' );
	wp_enqueue_script( 'pts-select2-js', '//cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js', array(), '1.0.0', true );
	wp_enqueue_script( 'pts-admin-main-js', plugin_dir_url( __FILE__ ) . 'assets/admin/js/main.js', array(
		'jquery',
		'pts-select2-js'
	), time(), true );
}

function pts_load_textdomain() {
	load_plugin_textdomain( 'post-taxonomy-selector', false, plugin_dir_url( __FILE__ ) . '/languages' );
}

add_action( 'plugins_loaded', 'pts_load_textdomain' );

function pts_add_metabox() {
	add_meta_box(
		'pts_select_post',
		__( 'Post & Taxonomy Selector Metabox', 'post-taxonomy-selector' ),
		'pts_display_metabox',
		array( 'page' )
	);
}

add_action( 'load-post.php', 'pts_add_metabox' );


function pts_display_metabox( $post ) {
	$selected_post_id          = get_post_meta( $post->ID, 'pts_select_post', true );
	$selected_term_id          = get_post_meta( $post->ID, 'pts_select_term', true );
	$selected_multiple_post_id = get_post_meta( $post->ID, 'pts_select_multiple_posts', true );
	$selected_multiple_post_id = $selected_multiple_post_id ? $selected_multiple_post_id : array();
//	print_r($selected_multiple_post_id);
//	wp_die();
	wp_nonce_field( 'pts_posts', 'pts_posts_nonce' );
	$label               = __( 'Select Post', 'post-taxonomy-selector' );
	$label2              = __( 'Select Multiple Posts', 'post-taxonomy-selector' );
	$label3              = __( 'Select Term', 'post-taxonomy-selector' );
	$args                = array(
		'post_type'     => 'post',
		'post_per_page' => - 1
	);
	$_post               = new wp_query( $args );
	$post_dropdown_list  = "";
	$post_dropdown_list2 = "";
	while ( $_post->have_posts() ) {
		$selected = "";
		$_post->the_post();
		if ( get_the_ID() == $selected_post_id ) {
			$selected = "selected";
		}
		$post_dropdown_list .= sprintf( '<option %s value="%s">%s</option>', esc_attr( $selected ), esc_attr( get_the_ID() ), esc_html( get_the_title() ) );


		if ( in_array( get_the_ID(), $selected_multiple_post_id ) ) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		$post_dropdown_list2 .= sprintf( '<option %s value="%s">%s</option>', esc_attr( $selected ), esc_attr( get_the_ID() ), esc_html( get_the_title() ) );

	}
	wp_reset_query();

	$term_dropdown_list = "";
	$_terms             = get_terms( array(
		'taxonomy'   => 'category',
		'hide_empty' => false
	) );

	foreach ( $_terms as $_term ) {
		$selected = "";
		if ( $selected_term_id == $_term->term_id ) {
			$selected = "selected";
		}
		$term_dropdown_list .= sprintf( '<option %s value="%s">%s</option>', $selected, $_term->term_id, $_term->name );
	}


	$metabox_html = <<<EOD
<div class="fields">
	<div class="field_c">
		<div class="label_c">
			<label for="pts_post">{$label}</label>
		</div>
		<div class="input_c">
		    <select name="pts_post" id="pts_post">
		        <option value="0">{$label}</option>
		        {$post_dropdown_list}
            </select>
		</div>
		<div class="float-clear"></div>
	</div>
	<div class="field_c">
		<div class="label_c">
			<label for="pts_posts">{$label2}</label>
		</div>
		<div class="input_c">
		    <select multiple="multiple" name="pts_posts[]" id="pts_posts">
		        <option value="0">{$label2}</option>
		        {$post_dropdown_list2}
            </select>
		</div>
		<div class="float-clear"></div>
	</div>
	
	<div class="field_c">
		<div class="label_c">
			<label for="pts_term">{$label3}</label>
		</div>
		<div class="input_c">
		    <select name="pts_term" id="pts_term">
		        <option value="0">{$label3}</option>
		        {$term_dropdown_list}
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

	$selected_multiple_posts_id = $_POST['pts_posts'];
	if ( $selected_multiple_posts_id != null ) {
		update_post_meta( $post_id, 'pts_select_multiple_posts', $selected_multiple_posts_id );
	}

	$selected_term_id = $_POST['pts_term'];
	if ( $selected_term_id > 0 ) {
		update_post_meta( $post_id, 'pts_select_term', $selected_term_id );
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
