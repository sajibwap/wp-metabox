<?php 
/*
Plugin Name: Our Metabox
Plugin URI: http://msajib.com
Description: Our metabox description
Version: 1.00
Author: Sajib
Author URI: http://msajib.com
Text Domain: our-metabox
Domain Path: /languages/
*/

Class OurMetabox{
	public function __construct(){
		add_action( 'plugins_loaded', array($this,'omb_load_textdomain'));
		add_action( 'add_meta_boxes', array($this,'omb_post_meta_box'));
		add_action( 'save_post', array($this,'omb_save_meta_values'));
		add_action( 'save_post', array($this,'omb_save_img_meta_values'));
		add_action( 'save_post', array($this,'omb_save_gallery_meta_values'));

		add_filter( 'user_contactmethods', array($this,'user_contact_meta') );

		add_action( 'admin_enqueue_scripts', array($this,'omb_admin_assets'));
	}

	public function omb_admin_assets(){
		wp_enqueue_style( 'omb-admin-css', plugin_dir_url( __FILE__ )."/assets/admin/css/style.css", null, time() );
		wp_enqueue_style( 'omb-admin-css', plugin_dir_url( __FILE__ )."/assets/admin/css/style.css", null, time() );
		wp_enqueue_style( 'select2-css', plugin_dir_url( __FILE__ )."/assets/admin/css/select2.min.css", null, time() );

		wp_enqueue_script( 'omb-admin-js', plugin_dir_url( __FILE__ )."/assets/admin/js/scripts.js", array('jquery','jquery-ui-datepicker'), time(), true );
		wp_enqueue_script( 'select2-js', plugin_dir_url( __FILE__ )."/assets/admin/js/select2.min.js", array('jquery'), time(), true );
	}

	/*
	** Nonce Security Callback
	*/

	public function is_secured($nonce_field,$action,$post_id){

		$nonce = isset($_POST[$nonce_field]) ? $_POST[$nonce_field] : "";

		if ( $nonce == "" ) {
			return false;
		}
		if (!wp_verify_nonce( $nonce, $action )) {
			return false;
		}
		if (!current_user_can( 'edit_post', $post_id )) {
			return false;
		}
		if (wp_is_post_autosave( $post_id )) {
			return false;
		}
		if (wp_is_post_revision( $post_id )) {
			return false;
		}

		return true;
	}
	/*
	** User Contact Meta field
	** Ourput : get_the_author_meta('facebook');
	*/

	public function user_contact_meta($fields){

		$fields['facebook']	= __( 'Facebook', 'our-metabox' );
		$fields['twitter']  = __( 'Twitter', 'our-metabox' );
		$fields['youtube']  = __( 'Youtube', 'our-metabox' );

		return $fields;
	}

	/*
	** Saving Meta Values
	*/

	function omb_save_img_meta_values($post_id){

		// if ($this->is_secured('omb_img_wpnonce','omb_img_action',$post_id)) {
		// 	return $post_id;
		// }

		$img_id  = isset($_POST['omb_img_id']) ? $_POST['omb_img_id'] : '';
		$img_url = isset($_POST['omb_img_url']) ? $_POST['omb_img_url'] : '';

		update_post_meta( $post_id, 'omb_img_id', $img_id );
		update_post_meta( $post_id, 'omb_img_url', $img_url );
	}	

	function omb_save_gallery_meta_values($post_id){

		// if ($this->is_secured('omb_img_wpnonce','omb_img_action',$post_id)) {
		// 	return $post_id;
		// }

		$gallery_id  = isset($_POST['omb_gallery_id']) ? $_POST['omb_gallery_id'] : '';
		$gallery_url = isset($_POST['omb_gallery_url']) ? $_POST['omb_gallery_url'] : '';

		update_post_meta( $post_id, 'omb_gallery_id', $gallery_id );
		update_post_meta( $post_id, 'omb_gallery_url', $gallery_url );
	}

	public function omb_save_meta_values($post_id){
		/*
		** Adding Nonce Security
		*/

		if ($this->is_secured('omb_location_nonce_field','omb_location_action',$post_id)) {
			return $post_id;
		}

		$location 	 	= isset($_POST['omb_location']) ? $_POST['omb_location'] : "";
		$country 		= isset($_POST['omb_country']) ? $_POST['omb_country'] : "";
		$is_favorite 	= isset($_POST['omb_is_favorite']) ? $_POST['omb_is_favorite'] : 0;
		$save_colors 	= isset($_POST['omb_color']) ? $_POST['omb_color'] : array();
		$radio_colors 	= isset($_POST['omb_radio']) ? $_POST['omb_radio'] : "";
		$select_date	= isset($_POST['omb_date']) ? $_POST['omb_date'] : "";
		$select_colors 	= isset($_POST['omb_select']) ? $_POST['omb_select'] : "";

		update_post_meta( $post_id, 'omb_location', sanitize_text_field( $location ));
		update_post_meta( $post_id, 'omb_country', sanitize_text_field( $country ));
		update_post_meta( $post_id, 'omb_is_favorite', $is_favorite );
		update_post_meta( $post_id, 'omb_color', $save_colors );
		update_post_meta( $post_id, 'omb_date', $select_date );
		update_post_meta( $post_id, 'omb_select', $select_colors );
	}


	/*
	** Adding Meta Box
	*/

	public function omb_post_meta_box(){
		add_meta_box( 
			'omb_post_metabox',
			__('Custom Metabox','our-metabox'),
			array($this,'omb_post_meta_box_callback'),
			'post')
		;

		add_meta_box( 
			'omb_img_metabox', 
			__('Media Box', 'our-metabox'),
			array($this,'omb_img_meta_box_callback'),
			'post')
		;

		add_meta_box( 
			'omb_gallery_metabox', 
			__('Gallery Box', 'our-metabox'),
			array($this,'omb_gallery_meta_box_callback'),
			'post')
		;
	}

	/*
	** Adding Meta Box : Callback function
	*/

	public function omb_post_meta_box_callback($post){
		$colors = array('red','green','blue','grey','black','orange');

		$location 		= get_post_meta( $post->ID, 'omb_location', true );
		$country 		= get_post_meta( $post->ID, 'omb_country', true );
		$is_favorite 	= get_post_meta( $post->ID, 'omb_is_favorite', true );
		$checked 		= ($is_favorite==1) ? 'checked' : "";
		$get_colors 	= get_post_meta( $post->ID, 'omb_color', true );
		$active_color 	= get_post_meta( $post->ID, 'omb_radio', true );
		$get_date 		= get_post_meta( $post->ID, 'omb_date', true );
		$select_color 	= get_post_meta( $post->ID, 'omb_select', true );

		// echo $get_date;

		wp_nonce_field('omb_location_action','omb_location_field_nonce');
		$metabox_html = <<<EOD
		<div class="fields_container">
		<label for="omb_location">Location</label>
		<input type="text" id="omb_location" name="omb_location" value="{$location}" />
		</div>
		<div class="fields_container">
		<label for="omb_country">Country</label>
		<input type="text" id="omb_country" name="omb_country" value="{$country}" />
		</div>
		<div class="fields_container">
		<label for="omb_is_favorite">Is Favorite ? </label>
		<input type="checkbox" id="omb_is_favorite" name="omb_is_favorite" value="1" {$checked} />
		</div>
		<div class="fields_container">
		<label> Colors : </label>
		<div>
		EOD;
		foreach ($colors as $color) {
			$get_colors = (is_array($get_colors)) ? $get_colors : array(); // to avoid error
			$_color = ucwords($color);
			$checked_color = (in_array($color, $get_colors)) ? 'checked' : '';
			$metabox_html .= <<<EOD
			<div class="color_container">
			<input type="checkbox" id="omb_color" name="omb_color[]" value="{$color}" {$checked_color} />
			<label for="omb_color_{$color}">{$_color}</label>
			</div>
			EOD;
		}
		$metabox_html .= <<<EOD
		</div>
		</div>
		<div class="fields_container">
		<label for="omb_radio"> Colors : </label>
		<div>
		EOD;

		foreach ($colors as $color) {
			$_color = ucwords($color);
			$active_radio = ( $color == $active_color ) ? 'checked' : '';
			$metabox_html .= <<<EOD
			<div class="color_container">
			<input type="radio" name="omb_radio" id="omb_radio_{$color}" value="{$color}" $active_radio />
			<label for="omb_radio_{$color}">{$_color}</label>
			</div>
			EOD;
		}

		$metabox_html .= "</div></div>";
		$metabox_html .= <<<EOD
		<div class="fields_container">
		<label for="omb_location">Date</label>
		<input type="text" id="omb_date" name="omb_date" value="{$get_date}" />
		</div>
		EOD;

		$dropdown_html = <<<EOD
		<option value="">Select a color</option>
		EOD;
		foreach ($colors as $color) {
			$selected = ( $color == $select_color ) ? 'selected' : '';
			$_color = ucwords($color);
			$dropdown_html .= <<<EOD
			<option {$selected} value="{$color}">{$_color}</option>
			EOD;
		}

		$metabox_html .= <<<EOD
		<div class="fields_container">
		<label for="omb_select"> Colors : </label>
		<select name="omb_select" id="omb_select">
		{$dropdown_html}
		</select>
		<div>
		</div>
		</div>
		EOD;

		echo $metabox_html;
	}


	/*
	** Media Meta box
	*/

	public function omb_img_meta_box_callback($post){

		$get_img_id  = get_post_meta( $post->ID, 'omb_img_id', true );
		$get_img_url = get_post_meta( $post->ID, 'omb_img_url', true );

		wp_nonce_field( 'omb_img_action', 'omb_img_wpnonce' );

		$img_metabox_html = <<<EOD
		<div class="fields_container">
		<label>Upload Image</label>
		<div class="img_container">
		<input type="button" id="omb_image_btn" name="omb_image_btn" value="Upload" />
		<input type="hidden" id="omb_img_id" name="omb_img_id" value="{$get_img_id}" />
		<input type="hidden" id="omb_img_url" name="omb_img_url" value="{$get_img_url}" />
		<div class="omb_img_container"></div>
		</div>
		</div>
		EOD;
		echo $img_metabox_html;
	}	

	/*
	** Gallery Meta box
	*/

	public function omb_gallery_meta_box_callback($post){

		$get_gallery_id  = get_post_meta( $post->ID, 'omb_gallery_id', true );
		$get_gallery_url = get_post_meta( $post->ID, 'omb_gallery_url', true );

		wp_nonce_field( 'omb_gallery_action', 'omb_gallery_wpnonce' );

		$gallery_metabox_html = <<<EOD
		<div class="fields_container">
		<label>Select Images</label>
		<div class="gallery_container">
		<input type="button" id="omb_gallery_btn" name="omb_gallery_btn" value="Upload" />
		<input type="hidden" id="omb_gallery_id" name="omb_gallery_id" value="{$get_gallery_id}" />
		<input type="hidden" id="omb_gallery_url" name="omb_gallery_url" value="{$get_gallery_url}" />
		<div class="omb_gallery_container"></div>
		</div>
		</div>
		EOD;
		echo $gallery_metabox_html;
	}


	
	/*
	** Loading TextDomain
	*/
	public function omb_load_textdomain(){
		load_plugin_textdomain( 'our-metabox', false, dirname(__FILE__) . '/languages' );
	}


}

new OurMetabox;

include 'inc/tax-term-meta.php';
include 'inc/meta-field-post-dropdown.php';
include 'inc/meta-field-post-dropdown-multiple.php';