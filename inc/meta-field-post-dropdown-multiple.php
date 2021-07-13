<?php 

	/*
	** Nonce Security Callback
	*/

	function _is_secured($nonce_field,$action,$post_id){

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
	** Create Meta box
	*/

	function _add_meta_boxes_callback(){
		add_meta_box( 
			'multi_post_dropdown_meta',
			__('Multiple Selected Post Metabox','our-metabox'), 
			'multi_post_dropdown_meta_callback', 
			'page' 
		);
	}
	add_action( 'add_meta_boxes', '_add_meta_boxes_callback');

	/*
	** Create Meta field and save meta value
	*/

	function multi_post_dropdown_meta_callback($post){

		$dropdown_html = "";

		$_get_post_id = get_post_meta($post->ID,'_post_list',true);
		$_get_post_id = is_array($_get_post_id) ? $_get_post_id : array();

		$_posts = new WP_Query(array('post_type'=>'post','posts_per_page'=>-1));
		while ($_posts->have_posts()) {
			$_posts->the_post();
			$selected = (in_array(get_the_ID(), $_get_post_id)) ? 'selected' : '';
			$dropdown_html .= sprintf('<option class="" style="width:500px;" value="%s" %s>%s</option>',get_the_ID(),$selected,get_the_title());
		};
		wp_reset_query();

		wp_nonce_field( '_post_list','_post_list_wpnonce' );

		$meta_html = <<<EOD
		<p class="post-attributes-label-wrapper">
		<label class="" for="_post_list">Select a post</label></p>
		<select class="js-example-basic-multiple js-states form-control" id="id_label_multiple" name="_post_list[]" multiple="multiple" id="_post_list">
		{$dropdown_html}
		</select>
		EOD;

		echo $meta_html;
	}

	function _save_post_dropdown_meta_callback($post_id){
		if (!_is_secured('_post_list_wpnonce','_post_list',$post_id)) {
			return $post_id;
		}

		$_post_id = $_POST['_post_list'];
		update_post_meta( $post_id, '_post_list', $_post_id );

	}
	add_action( 'save_post', '_save_post_dropdown_meta_callback' );

