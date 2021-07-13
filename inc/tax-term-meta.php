<?php 

/*
** Same method also goes for post_tag or cusotm taxonomy name
*/


/*
** Taxonomy Bootstraping with meta key
*/

function taxonomy_bootstrap(){
	$args = array(
		'type'=>'string',
		'sanitize_callback'=>'sanitize_text_field',
		'single'=>true,
		'description'=> 'Some help text',
		'rest_in_show'=>true
	);
	register_meta( 'term', 'tax_category_meta', $args );

}
add_action( 'init', 'taxonomy_bootstrap');

/*
** Addding Tax Meta field
*/

function category_add_form_fields_callback($term_id){

	$term_html = <<<EOD
	<div class="form-field form-required term-name-wrap">
	<label for="ex-name">Extra Field</label>
	<input name="ex-name" id="ex-name" type="text" value="" size="40" aria-required="true">
	<p>Some text for this meta.</p>
	</div>
	EOD;
	echo $term_html;

}
add_action( 'category_add_form_fields', 'category_add_form_fields_callback' );


function category_edit_form_fields_callback($term){

	$extra_name = get_term_meta( $term->term_id, 'tax_category_meta', true );

	$term_html = <<<EOD
	<tr class="form-field form-required term-name-wrap">
	<th scope="row"><label for="ex-name">Extra Field</label></th>
	<td><input name="ex-name" id="ex-name" type="text" value="{$extra_name}" size="40" aria-required="true">
	<p class="description">Extra field help text.</p></td>
	</tr>
	EOD;
	echo $term_html;

}
add_action( 'category_edit_form_fields', 'category_edit_form_fields_callback' );

/*
** Updating Tax Meta Value
*/

function taxm_save_create_category_meta($term_id){
	if(wp_verify_nonce( $_POST['_wpnonce_add-tag'], 'add-tag')){
		$extra_name = sanitize_text_field( $_POST['ex-name'] );
		update_term_meta( $term_id, 'tax_category_meta', $extra_name );
	}
}
add_action( 'create_category', 'taxm_save_create_category_meta' );

function taxm_edit_create_category_meta($term_id){
	if(wp_verify_nonce( $_POST['_wpnonce'], "update-tag_{$term_id}" )){
		$extra_name = sanitize_text_field( $_POST['ex-name'] );
		update_term_meta( $term_id, 'tax_category_meta', $extra_name );
	}
}
add_action( 'edit_category', 'taxm_edit_create_category_meta' );
