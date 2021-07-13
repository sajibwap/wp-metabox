<?php 

	/*
	** Nonce Security Callback
	*/

	function is_secured($nonce_field,$action,$post_id){

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

	function add_meta_boxes_callback(){
		add_meta_box( 
			'post_dropdown_meta',
			__('Post Metabox','our-metabox'), 
			'post_dropdown_meta_callback', 
			'page' 
		);
	}
	add_action( 'add_meta_boxes', 'add_meta_boxes_callback');

	/*
	** Create Meta field and save meta value
	*/

	function post_dropdown_meta_callback($post){

		$dropdown_html = "";

		$_get_post_id = get_post_meta($post->ID,'post_list',true);

		$_posts = new WP_Query(array('post_type'=>'post','posts_per_page'=>-1));
		while ($_posts->have_posts()) {
			$_posts->the_post();
			$selected = (get_the_ID() == $_get_post_id ) ? 'selected' : '';
			$dropdown_html .= sprintf('<option class="level-0" value="%s" %s>%s</option>',get_the_ID(),$selected,get_the_title());
		};
		wp_reset_query();

		wp_nonce_field( 'post_list','post_list_wpnonce' );

		$meta_html = <<<EOD
		<p class="post-attributes-label-wrapper">
		<label class="post-attributes-label" for="post_list">Select a post</label></p>
		<select name="post_list" id="post_list">
		{$dropdown_html}
		</select>
		EOD;

		echo $meta_html;
	}

	function save_post_dropdown_meta_callback($post_id){
		if (!is_secured('post_list_wpnonce','post_list',$post_id)) {
			return $post_id;
		}

		$_post_id = $_POST['post_list'];
		update_post_meta( $post_id, 'post_list', $_post_id );

	}
	add_action( 'save_post', 'save_post_dropdown_meta_callback' );

