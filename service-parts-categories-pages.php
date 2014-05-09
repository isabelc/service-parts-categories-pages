<?php
/*
Plugin Name: Service Parts Categories Pages
Plugin URI: http://smartestthemes.com/docs/service-categories-page-and-parts-categories-page-plugin/
Description: Adds a shortcode to show only categories of services or parts, with images.
Author: Smartest Themes
Version: 0.9.8
Author URI: http://smartestthemes.com
License: GPL2
Text Domain: service-parts-categories-pages
Domain Path: languages

Copyright 2014 Smartest Themes

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


/* @test remove
require_once plugin_dir_path( __FILE__ ) . 'updater.php';
if ( is_admin() ) {
    new ST_GitHubPluginUpdater( __FILE__, 'isabelc', "service-parts-categories-pages" );
}

*/

/* @test */
require_once plugin_dir_path( __FILE__ ) . 'updater.php';
if (is_admin()) {
    $config = array(
        'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
        'proper_folder_name' => 'service-parts-categories-pages', // this is the name of the folder your plugin lives in
        'api_url' => 'https://api.github.com/repos/isabelc/service-parts-categories-pages', // the github API url of your github repo
        'raw_url' => 'https://raw.github.com/isabelc/service-parts-categories-pages/master', // the github raw url of your github repo
        'github_url' => 'https://github.com/isabelc/service-parts-categories-pages', // the github url of your github repo
        'zip_url' => 'https://github.com/isabelc/service-parts-categories-pages/zipball/master', // the zip url of the github repo
        'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
        'requires' => '3.6', // which version of WordPress does your plugin require?
        'tested' => '3.9.1', // which version of WordPress is your plugin tested up to?
        'readme' => 'README.md', // which file to use as the readme for the version number
        'access_token' => '', // Access private repositories by authorizing under Appearance > Github Updates when this example plugin is installed
    );
    new ST_SPCP_GitHub_Updater($config);
}


/* shortcode for listing CPT custom taxonomy (categories) */
function smartestthemes_custom_tax_list($atts) {
	$a = shortcode_atts(array(
		'cpt'    => 'services',// services or parts
		'thumbnail'     => 'true',
		'order'			=> 'ASC',
		'orderby'       => 'name',
		'exclude'		=> array(),
		'exclude_tree'	=> array(),
		'child_of'	=> 0),
		$atts, 'auto_categories');

    // get tax name
	if ($a['cpt'] == 'parts') {
		$tax = 'smartest_auto_parts_category';
	} else {
		$tax = 'smartest_service_category';
	}

	$thumbs_array = array();
	$assoc = taxonomy_image_plugin_get_associations();
	if ( empty( $assoc ) ) {
		$thumbs_array = array();
	}
	$terms = get_terms( $tax, array(
								'orderby'       => $a['orderby'], 
								'order'         => $a['order'],
								'exclude'		=> $a['exclude'],
								'exclude_tree'	=> $a['exclude_tree'],
								'child_of'	=> $a['child_of'])
						);
	if ( is_wp_error( $terms ) ) {
		$thumbs_array = array();
	}
	
	$image_ids = array();
	$terms_with_images = array();
	foreach ( (array) $terms as $key => $term ) {
		$terms[$key]->image_id = 0;
		if ( array_key_exists( $term->term_taxonomy_id, $assoc ) ) {
			$terms[$key]->image_id = $assoc[$term->term_taxonomy_id];
			$image_ids[] = $assoc[$term->term_taxonomy_id];
			if ( ! empty( $args['having_images'] ) ) {
				$terms_with_images[] = $terms[$key];
			}
		}
	}
	
	$image_ids = array_unique( $image_ids );

	if ( ! empty( $terms_with_images ) ) {
		$thumbs_array = $terms_with_images;
	}
	$thumbs_array = $terms;

	$out = '';
	if ( !empty( $thumbs_array ) && !is_wp_error( $thumbs_array ) ) {
		foreach ($thumbs_array as $thumb) {
			$out .= '<div class="col-1">';
			if ( $a['thumbnail'] == 'true' ) {
				$thumbnail_image = wp_get_attachment_image( $thumb->image_id, 'auto-cats' );// @test
				if ( $thumbnail_image ) {
					$out .= '<figure class="indent-bot"><a href="' . get_term_link( $thumb) . '" title="' . esc_attr( sprintf( __( '%s', 'service-parts-categories-pages' ), $thumb->name ) ) . '">' . $thumbnail_image . '</a></figure>';
				}
			}
			$out .= '<div class="equheight"><h6><a href="' . get_term_link( $thumb ) . '" title="' . esc_attr( sprintf( __( '%s', 'service-parts-categories-pages' ), $thumb->name ) ) . '">' . $thumb->name . '</a></h6>';
			$out .= '</div></div>';
		}
	}
	return $out;
}
add_shortcode('auto_categories', 'smartestthemes_custom_tax_list');

/**
 * Version Number.
 *
 * @return    string    The plugin's version number.
 * @access    private
 * @since     0.7
 * @alter     0.7.4
 */
function taxonomy_image_plugin_version() {
	return '0.8.0';
}
/**
 * Get a url to a file in this plugin.
 *
 * @return    string
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_url( $file = '' ) {
	static $path = '';
	if ( empty( $path ) ) {
		$path = plugin_dir_url( __FILE__ );
	}
	return $path . $file;
}
/**
 * Detail Image Size.
 *
 * @return    array     Configuration for the "detail" image size.
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_detail_image_size() {
	return array(
		'name' => 'detail',
		'size' => array( 150, 150, true )
	);
}
/**
 * Register custom image size with WordPress.
 *
 * @access    private
 * @since     2010-10-28
 */
function taxonomy_image_plugin_add_image_size() {
	$detail = taxonomy_image_plugin_detail_image_size();
	add_image_size(
		$detail['name'],
		$detail['size'][0],
		$detail['size'][1],
		$detail['size'][2]
	);
	add_image_size( 'auto-cats', 159, 110, true );
	
}
add_action( 'init', 'taxonomy_image_plugin_add_image_size' );


/**
 * Load Plugin Text Domain.
 *
 * @access    private
 * @since     0.7.3
 */
function taxonomy_image_plugin_text_domain() {
	load_plugin_textdomain( 'service-parts-categories-pages', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'taxonomy_image_plugin_text_domain' );


/**
 * Modal Button.
 *
 * Create a button in the modal media window to associate the current image to the term.
 *
 * @param     array     Multidimensional array representing the images form.
 * @param     stdClass  WordPress post object.
 * @return    array     The image's form array with added button if modal window was accessed by this script.
 *
 * @access    private
 * @since     2010-10-28
 * @alter     0.7
 */
function taxonomy_image_plugin_modal_button( $fields, $post ) {
	if ( isset( $fields['image-size'] ) && isset( $post->ID ) ) {
		$image_id = (int) $post->ID;

		$o = '<div class="taxonomy-image-modal-control" id="' . esc_attr( 'taxonomy-image-modal-control-' . $image_id ) . '">';

		$o.= '<span class="button create-association">' . sprintf( esc_html__( 'Associate with %1$s', 'service-parts-categories-pages' ), '<span class="term-name">' . esc_html__( 'this term', 'service-parts-categories-pages' ) . '</span>' ) . '</span>';

		$o.= '<span class="remove-association">' . sprintf( esc_html__( 'Remove association with %1$s', 'service-parts-categories-pages' ), '<span class="term-name">' . esc_html__( 'this term', 'service-parts-categories-pages' ) . '</span>' ) . '</span>';

		$o.= '<input class="taxonomy-image-button-image-id" name="' . esc_attr( 'taxonomy-image-button-image-id-' . $image_id ) . '" type="hidden" value="' . esc_attr( $image_id ) . '" />';

		$o.= '<input class="taxonomy-image-button-nonce-create" name="' . esc_attr( 'taxonomy-image-button-nonce-create-' . $image_id ) . '" type="hidden" value="' . esc_attr( wp_create_nonce( 'taxonomy-image-plugin-create-association' ) ) . '" />';

		$o.= '<input class="taxonomy-image-button-nonce-remove" name="' . esc_attr( 'taxonomy-image-button-nonce-remove-' . $image_id ) . '" type="hidden" value="' . esc_attr( wp_create_nonce( 'taxonomy-image-plugin-remove-association' ) ) . '" />';

		$o.= '</div>';

		$fields['image-size']['extra_rows']['taxonomy-image-plugin-button']['html'] = $o;
	}
	return $fields;
}
add_filter( 'attachment_fields_to_edit', 'taxonomy_image_plugin_modal_button', 20, 2 );


/**
 * Get Image Source.
 *
 * Return a uri to a custom image size.
 *
 * If size doesn't exist, attempt to create a resized version.
 * The output of this function should be escaped before printing to the browser.
 *
 * @param     int       Image ID.
 * @return    string    URI of custom image on success; emtpy string otherwise.
 *
 * @access    private.
 * @since     2010-10-28
 */
function taxonomy_image_plugin_get_image_src( $id ) {
	$detail = taxonomy_image_plugin_detail_image_size();

	/* Return url to custom intermediate size if it exists. */
	$img = image_get_intermediate_size( $id, $detail['name'] );
	if ( isset( $img['url'] ) )
		return $img['url'];

	/* Detail image does not exist, attempt to create it. */
	$wp_upload_dir = wp_upload_dir();
	if ( isset( $wp_upload_dir['basedir'] ) ) {

		/* Create path to original uploaded image. */
		$path = trailingslashit( $wp_upload_dir['basedir'] ) . get_post_meta( $id, '_wp_attached_file', true );
		if ( is_file( $path ) ) {

			/* Attempt to create a new downsized version of the original image. */
			$new = wp_get_image_editor( $path, $detail['size'] );

			/* Image creation successful. Generate and cache image metadata. Return url. */
			if ( ! is_wp_error( $new ) ) {
				$meta = wp_generate_attachment_metadata( $id, $path );
				wp_update_attachment_metadata( $id, $meta );
				$img = image_get_intermediate_size( $id, $detail['name'] );
				if ( isset( $img['url'] ) ) {
					return $img['url'];
				}
			}
		}
	}

	/* Custom intermediate size cannot be created, try for thumbnail. */
	$img = image_get_intermediate_size( $id, 'thumbnail' );
	if ( isset( $img['url'] ) )
		return $img['url'];

	/* Thumbnail cannot be found, try fullsize. */
	$url = wp_get_attachment_url( $id );
	if ( ! empty( $url ) )
		return $url;

	/*
	 * No image can be found.
	 * If we are in the administration panels:
	 * - Delete the association.
	 * - Return false.
	 */
	if ( is_admin() ) {
		$assoc = taxonomy_image_plugin_get_associations();
		foreach ( $assoc as $term => $img ) {
			if ( $img === $id ) {
				unset( $assoc[$term] );
			}
		}
		update_option( 'taxonomy_image_plugin', $assoc );
		return false;
	}

	/*
	 * No image can be found.
	 * This is most likely caused by a user deleting an attachment before deleting it's association with a taxonomy.
	 * Return path to blank-image.png.
	 */
	return taxonomy_image_plugin_url( 'blank.png' );
}

/**
 * Sanitize Associations.
 *
 * Ensures that all key/value pairs are positive integers.
 * This filter will discard all zero and negative values.
 *
 * @param     array     An array of term_taxonomy_id/attachment_id pairs.
 * @return    array     Sanitized version of parameter.
 *
 * @access    private
 */
function taxonomy_image_plugin_sanitize_associations( $associations ) {
	$o = array();
	foreach ( (array) $associations as $tt_id => $im_id ) {
		$tt_id = absint( $tt_id );
		$im_id = absint( $im_id );
		if ( 0 < $tt_id && 0 < $im_id )
			$o[$tt_id] = $im_id;
	}
	return $o;
}

/**
 * JSON Respose.
 * Terminates script execution.
 *
 * @param     array     Associative array of values to be encoded in JSON.
 *
 * @access    private
 */
function taxonomy_image_plugin_json_response( $args ) {
	/* translators: An ajax request has failed for an unknown reason. */
	$response = wp_parse_args( $args, array(
		'status' => 'bad',
		'why'    => esc_html__( 'Unknown error encountered', 'service-parts-categories-pages' )
	) );
	header( 'Content-type: application/jsonrequest' );
	print json_encode( $response );
	exit;
}

/**
 * Get Term Info.
 *
 * Returns term info by term_taxonomy_id.
 *
 * @param     int       term_taxonomy_id
 * @return    array     Keys: term_id (int) and taxonomy (string).
 *
 * @access    private
 */
function taxonomy_image_plugin_get_term_info( $tt_id ) {
	static $cache = array();
	if ( isset( $cache[$tt_id] ) ) {
		return $cache[$tt_id];
	}

	global $wpdb;

	$data = $wpdb->get_results( $wpdb->prepare( "SELECT term_id, taxonomy FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = %d LIMIT 1", $tt_id ) );
	if ( isset( $data[0]->term_id ) )
		$cache[$tt_id]['term_id'] = absint( $data[0]->term_id );

	if ( isset( $data[0]->taxonomy ) )
		$cache[$tt_id]['taxonomy'] = sanitize_title_with_dashes( $data[0]->taxonomy );

	if ( isset( $cache[$tt_id] ) )
		return $cache[$tt_id];

	return array();
}


/**
 * Check Taxonomy Permissions.
 *
 * Allows a permission check to be performed on a term
 * when all you know is the term_taxonomy_id.
 *
 * @param     int       term_taxonomy_id
 * @return    bool      True if user can edit terms, False if not.
 *
 * @access    private
 */
function taxonomy_image_plugin_check_permissions( $tt_id ) {
	$data = taxonomy_image_plugin_get_term_info( $tt_id );
	if ( ! isset( $data['taxonomy'] ) )
		return false;

	$taxonomy = get_taxonomy( $data['taxonomy'] );
	if ( ! isset( $taxonomy->cap->edit_terms ) )
		return false;

	return current_user_can( $taxonomy->cap->edit_terms );
}

/**
 * Create an association.
 *
 * Callback for the wp_ajax_{$_GET['action']} hook.
 *
 * @access    private
 */
function taxonomy_image_plugin_create_association() {
	if ( ! isset( $_POST['tt_id'] ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'tt_id not sent', 'service-parts-categories-pages' ),
		) );
	}

	$tt_id = absint( $_POST['tt_id'] );
	if ( empty( $tt_id ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'tt_id is empty', 'service-parts-categories-pages' ),
		) );
	}

	if ( ! taxonomy_image_plugin_check_permissions( $tt_id ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'You do not have the correct capability to manage this term', 'service-parts-categories-pages' ),
		) );
	}

	if ( ! isset( $_POST['wp_nonce'] ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'No nonce included.', 'service-parts-categories-pages' ),
		) );
	}

	if ( ! wp_verify_nonce( $_POST['wp_nonce'], 'taxonomy-image-plugin-create-association' ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'Nonce did not match', 'service-parts-categories-pages' ),
		) );
	}

	if ( ! isset( $_POST['attachment_id'] ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'Image id not sent', 'service-parts-categories-pages' )
		) );
	}

	$image_id = absint( $_POST['attachment_id'] );
	if ( empty( $image_id ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'Image id is not a positive integer', 'service-parts-categories-pages' )
		) );
	}

	$assoc = taxonomy_image_plugin_get_associations();
	$assoc[$tt_id] = $image_id;
	if ( update_option( 'taxonomy_image_plugin', taxonomy_image_plugin_sanitize_associations( $assoc ) ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'good',
			'why'    => esc_html__( 'Image successfully associated', 'service-parts-categories-pages' ),
			'attachment_thumb_src' => taxonomy_image_plugin_get_image_src( $image_id ),
			'image_id' => $image_id,
		) );
	}
	else {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'Association could not be created', 'service-parts-categories-pages' )
		) );
	}

	/* Don't know why, but something didn't work. */
	taxonomy_image_plugin_json_response();
}
add_action( 'wp_ajax_taxonomy_image_create_association', 'taxonomy_image_plugin_create_association' );
/**
 * Remove an association.
 *
 * Removes an association from the setting stored in the database.
 * Print json encoded message and terminates script execution.
 *
 * @access    private
 */
function taxonomy_image_plugin_remove_association() {
	if ( ! isset( $_POST['tt_id'] ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'tt_id not sent', 'service-parts-categories-pages' ),
		) );
	}

	$tt_id = absint( $_POST['tt_id'] );
	if ( empty( $tt_id ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'tt_id is empty', 'service-parts-categories-pages' ),
		) );
	}

	if ( ! taxonomy_image_plugin_check_permissions( $tt_id ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'You do not have the correct capability to manage this term', 'service-parts-categories-pages' ),
		) );
	}

	if ( ! isset( $_POST['wp_nonce'] ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'No nonce included', 'service-parts-categories-pages' ),
		) );
	}

	if ( ! wp_verify_nonce( $_POST['wp_nonce'], 'taxonomy-image-plugin-remove-association') ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'Nonce did not match', 'service-parts-categories-pages' ),
		) );
	}

	$assoc = taxonomy_image_plugin_get_associations();
	if ( ! isset( $assoc[$tt_id] ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'good',
			'why'    => esc_html__( 'Nothing to remove', 'service-parts-categories-pages' )
		) );
	}

	unset( $assoc[$tt_id] );

	if ( update_option( 'taxonomy_image_plugin', $assoc ) ) {
		taxonomy_image_plugin_json_response( array(
			'status' => 'good',
			'why'    => esc_html__( 'Association successfully removed', 'service-parts-categories-pages' )
		) );
	}
	else {
		taxonomy_image_plugin_json_response( array(
			'status' => 'bad',
			'why'    => esc_html__( 'Association could not be removed', 'service-parts-categories-pages' )
		) );
	}

	/* Don't know why, but something didn't work. */
	taxonomy_image_plugin_json_response();
}
add_action( 'wp_ajax_taxonomy_image_plugin_remove_association', 'taxonomy_image_plugin_remove_association' );


/**
 * Get a list of user-defined associations.
 * Associations are stored in the WordPress options table.
 *
 * @param     bool      Should WordPress query the database for the results
 * @return    array     List of associations. Key => taxonomy_term_id; Value => image_id
 *
 * @access    private
 */
function taxonomy_image_plugin_get_associations( $refresh = false ) {
	static $associations = array();
	if ( empty( $associations ) || $refresh )
		$associations = taxonomy_image_plugin_sanitize_associations( get_option( 'taxonomy_image_plugin' ) );

	return $associations;
}
add_action( 'init', 'taxonomy_image_plugin_get_associations' );


/**
 * Dynamically create hooks for each taxonomy.
 *
 * Adds hooks for each taxonomy that the user has given
 * an image interface to via settings page. These hooks
 * enable the image interface on wp-admin/edit-tags.php.
 *
 * @access    private
 * @since     0.4.3
 * @alter     0.7
 */
function taxonomy_image_plugin_add_dynamic_hooks() {
	$settings = get_option( 'taxonomy_image_plugin_settings' );
	if ( ! isset( $settings['taxonomies'] ) )
		return;

	foreach ( $settings['taxonomies'] as $taxonomy ) {
		add_filter( 'manage_' . $taxonomy . '_custom_column', 'taxonomy_image_plugin_taxonomy_rows', 15, 3 );
		add_filter( 'manage_edit-' . $taxonomy . '_columns',  'taxonomy_image_plugin_taxonomy_columns' );
		add_action( $taxonomy . '_edit_form_fields',          'taxonomy_image_plugin_edit_tag_form', 10, 2 );
	}
}
add_action( 'admin_init', 'taxonomy_image_plugin_add_dynamic_hooks' );


/**
 * Edit Term Columns.
 *
 * Insert a new column on wp-admin/edit-tags.php.
 *
 * @see taxonomy_image_plugin_add_dynamic_hooks()
 *
 * @param     array     A list of columns.
 * @return    array     List of columns with "Images" inserted after the checkbox.
 *
 * @access    private
 * @since     0.4.3
 */
function taxonomy_image_plugin_taxonomy_columns( $original_columns ) {
	$new_columns = $original_columns;
	array_splice( $new_columns, 1 );
	$new_columns['taxonomy_image_plugin'] = esc_html__( 'Image', 'service-parts-categories-pages' );
	return array_merge( $new_columns, $original_columns );
}


/**
 * Edit Term Rows.
 *
 * Create image control for each term row of wp-admin/edit-tags.php.
 *
 * @see taxonomy_image_plugin_add_dynamic_hooks()
 *
 * @param     string    Row.
 * @param     string    Name of the current column.
 * @param     int       Term ID.
 * @return    string    @see taxonomy_image_plugin_control_image()
 *
 * @access    private
 * @since     2010-11-08
 */
function taxonomy_image_plugin_taxonomy_rows( $row, $column_name, $term_id ) {
	if ( 'taxonomy_image_plugin' === $column_name ) {
		global $taxonomy;
		return $row . taxonomy_image_plugin_control_image( $term_id, $taxonomy );
	}
	return $row;
}


/**
 * Edit Term Control.
 *
 * Create image control for wp-admin/edit-tag-form.php.
 * Hooked into the '{$taxonomy}_edit_form_fields' action.
 *
 * @param     stdClass  Term object.
 * @param     string    Taxonomy slug.
 *
 * @access    private
 * @since     2010-11-08
 */
function taxonomy_image_plugin_edit_tag_form( $term, $taxonomy ) {
	$taxonomy = get_taxonomy( $taxonomy );
	$name = __( 'term', 'service-parts-categories-pages' );
	if ( isset( $taxonomy->labels->singular_name ) )
		$name = strtolower( $taxonomy->labels->singular_name );
	?>
	<tr class="form-field hide-if-no-js">
		<th scope="row" valign="top"><label for="description"><?php print esc_html__( 'Image', 'service-parts-categories-pages' ) ?></label></th>
		<td>
			<?php print taxonomy_image_plugin_control_image( $term->term_id, $taxonomy->name ); ?>
			<div class="clear"></div>
			<span class="description"><?php printf( esc_html__( 'Associate an image from your media library to this %1$s.', 'service-parts-categories-pages' ), esc_html( $name ) ); ?></span>
		</td>
	</tr>
	<?php
}

/**
 * Image Control.
 *
 * Creates all image controls on edit-tags.php.
 *
 * @todo      Remove rel tag from link... will need to adjust js to accommodate.
 */
function taxonomy_image_plugin_control_image( $term_id, $taxonomy ) {

	$term = get_term( $term_id, $taxonomy );

	$tt_id = 0;
	if ( isset( $term->term_taxonomy_id ) )
		$tt_id = (int) $term->term_taxonomy_id;

	$taxonomy = get_taxonomy( $taxonomy );

	$name = esc_html__( 'term', 'service-parts-categories-pages' );
	if ( isset( $taxonomy->labels->singular_name ) )
		$name = strtolower( $taxonomy->labels->singular_name );

	$hide = ' hide';
	$attachment_id = 0;
	$associations = taxonomy_image_plugin_get_associations();
	if ( isset( $associations[ $tt_id ] ) ) {
		$attachment_id = (int) $associations[ $tt_id ];
		$hide = '';
	}

	$img = taxonomy_image_plugin_get_image_src( $attachment_id );
	if ( $img ) {
		$img = '<img id="' . esc_attr( 'taxonomy_image_plugin_' . $tt_id ) . '" src="' . esc_url( $img ) . '" alt="" style="width: 75px; height: auto;" />';
	} else {
		$img = '<span id="' . esc_attr( 'taxonomy_image_plugin_' . $tt_id ) . '" class="taxonomy-images-set-featured">Set Featured Image</span>';
	}

	$term = get_term( $term_id, $taxonomy->name );

	$o = "\n" . '<div id="' . esc_attr( 'taxonomy-image-control-' . $tt_id ) . '" class="taxonomy-image-control hide-if-no-js">';
	$o.= "\n" . '<a class="thickbox taxonomy-image-thumbnail" href="' . esc_url( admin_url( 'media-upload.php' ) . '?type=image&width=800&tab=library&post_id=0&TB_iframe=true' ) . '" title="' . esc_attr( sprintf( __( 'Associate an image with the %1$s named &#8220;%2$s&#8221;.', 'service-parts-categories-pages' ), $name, $term->name ) ) . '">'.$img.'</a>';
	$o.= "\n" . '<a class="remove ' . $hide . '" href="#" id="' . esc_attr( 'remove-' . $tt_id ) . '" rel="' . esc_attr( $tt_id ) . '" title="' . esc_attr( sprintf( __( 'Remove image from this %s.', 'service-parts-categories-pages' ), $name ) ) . '">' . esc_html__( 'Remove Featured Image' ) . '</a>';
	$o.= "\n" . '<input type="hidden" class="tt_id" name="' . esc_attr( 'tt_id-' . $tt_id ) . '" value="' . esc_attr( $tt_id ) . '" />';

	$o.= "\n" . '<input type="hidden" class="image_id" name="' . esc_attr( 'image_id-' . $tt_id ) . '" value="' . esc_attr( $attachment_id ) . '" />';

	if ( isset( $term->name ) && isset( $term->slug ) )
		$o.= "\n" . '<input type="hidden" class="term_name" name="' . esc_attr( 'term_name-' . $term->slug ) . '" value="' . esc_attr( $term->name ) . '" />';

	$o.= "\n" . '</div>';
	return $o;
}


/**
 * Custom javascript for modal media box.
 *
 * This script need to be added to all instance of the media upload box.
 *
 * @access    private
 */
function taxonomy_image_plugin_media_upload_popup_js() {
	wp_enqueue_script(
		'taxonomy-images-media-upload-popup',
		taxonomy_image_plugin_url( 'media-upload-popup.js' ),
		array( 'jquery' ),
		taxonomy_image_plugin_version()
	);
	wp_localize_script( 'taxonomy-images-media-upload-popup', 'TaxonomyImagesModal', array (
		'termBefore'  => esc_html__( '&#8220;', 'service-parts-categories-pages' ),
		'termAfter'   => esc_html__( '&#8221;', 'service-parts-categories-pages' ),
		'associating' => esc_html__( 'Associating &#8230;', 'service-parts-categories-pages' ),
		'success'     => esc_html__( 'Successfully Associated', 'service-parts-categories-pages' ),
		'removing'    => esc_html__( 'Removing &#8230;', 'service-parts-categories-pages' ),
		'removed'     => esc_html__( 'Successfully Removed', 'service-parts-categories-pages' )
	) );
}
add_action( 'admin_print_scripts-media-upload-popup', 'taxonomy_image_plugin_media_upload_popup_js' );


/**
 * Custom javascript for wp-admin/edit-tags.php.
 *
 * @access    private
 */
function taxonomy_image_plugin_edit_tags_js() {
	if ( false == taxonomy_image_plugin_is_screen_active() )
		return;

	wp_enqueue_script(
		'taxonomy-image-plugin-edit-tags',
		taxonomy_image_plugin_url( 'edit-tags.js' ),
		array( 'jquery', 'thickbox' ),
		taxonomy_image_plugin_version()
	);
	wp_localize_script( 'taxonomy-image-plugin-edit-tags', 'taxonomyImagesPlugin', array (
		'nonce'    => wp_create_nonce( 'taxonomy-image-plugin-remove-association' ),
		'no_img'  => '<span class="taxonomy-images-set-featured">Set Featured Image</span>',
		'tt_id'    => 0,
		'image_id' => 0,
	) );
}
add_action( 'admin_print_scripts-edit-tags.php', 'taxonomy_image_plugin_edit_tags_js' );


/**
 * Custom styles.
 *
 * @since     0.7
 * @access    private
 */
function taxonomy_image_plugin_css_admin() {
	if ( false == taxonomy_image_plugin_is_screen_active() && 'admin_print_styles-media-upload-popup' != current_filter() )
		return;

	wp_enqueue_style(
		'taxonomy-image-plugin-edit-tags',
		taxonomy_image_plugin_url( 'admin.css' ),
		array(),
		taxonomy_image_plugin_version(),
		'screen'
	);
}
add_action( 'admin_print_styles-edit-tags.php', 'taxonomy_image_plugin_css_admin' );
add_action( 'admin_print_styles-media-upload-popup', 'taxonomy_image_plugin_css_admin' );


/**
 * Thickbox styles.
 *
 * @since     0.7
 * @access    private
 */
function taxonomy_image_plugin_css_thickbox() {
	if ( false == taxonomy_image_plugin_is_screen_active() )
		return;

	wp_enqueue_style( 'thickbox' );
}
add_action( 'admin_print_styles-edit-tags.php', 'taxonomy_image_plugin_css_thickbox' );


/**
 * Public Styles.
 *
 * Prints custom css to all public pages. If you do not
 * wish to have these styles included for you, please
 * insert the following code into your theme's functions.php
 * file:
 *
 * add_filter( 'taxonomy-images-disable-public-css', '__return_true' );
 *
 * @since     0.7
 * @access    private
 */
function taxonomy_image_plugin_css_public() {
	if ( apply_filters( 'taxonomy-images-disable-public-css', false ) )
		return;

	wp_enqueue_style(
		'taxonomy-image-plugin-public',
		taxonomy_image_plugin_url( 'style.css' ),
		array(),
		taxonomy_image_plugin_version(),
		'screen'
	);
}
add_action( 'wp_print_styles', 'taxonomy_image_plugin_css_public' );


/**
 * Activation.
 *
 * Two entries in the options table will created when this
 * plugin is activated in the event that they do not exist.
 *
 * 'taxonomy_image_plugin' (array) A flat list of all associations
 * made by this plugin. Keys are integers representing the
 * term_taxonomy_id of terms. Values are integers representing the
 * ID property of an image attachment.
 *
 * 'taxonomy_image_plugin_settings' (array) A multi-dimensional array
 * of user-defined settings. As of version 0.7, only one key is used:
 * 'taxonomies' which is a whitelist of registered taxonomies having ui
 * that support the custom image ui provided by this plugin.
 *
 */
function taxonomy_image_plugin_activate() { // @test
	$associations = get_option( 'taxonomy_image_plugin' );
	if ( false === $associations )
		add_option( 'taxonomy_image_plugin', array() );

	$settings = get_option( 'taxonomy_image_plugin_settings' );
	if ( false === $settings ) {
		add_option( 'taxonomy_image_plugin_settings', array(
			'taxonomies' => array('smartest_service_category','smartest_auto_parts_category')// @test
		) );
	}
}
register_activation_hook( __FILE__, 'taxonomy_image_plugin_activate' );


/**
 * Is Screen Active?
 *
 * @return    bool
 *
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_is_screen_active() {
	$screen = get_current_screen();
	if ( ! isset( $screen->taxonomy ) )
		return false;

	$settings = get_option( 'taxonomy_image_plugin_settings' );
	if ( ! isset( $settings['taxonomies'] ) )
		return false;

	if ( in_array( $screen->taxonomy, $settings['taxonomies'] ) )
		return true;

	return false;
}


/**
 * Cache Images
 *
 * Sets the WordPress object cache for all term images
 * associated to the posts in the provided array. This
 * function has been created to minimize queries when
 * using this plugins get_the_terms() style function.
 *
 * @param     array          Post objects.
 *
 * @access    private
 * @since     1.1
 */
function taxonomy_image_plugin_cache_images( $posts ) {
	$assoc = taxonomy_image_plugin_get_associations();
	if ( empty( $assoc ) )
		return;

	$tt_ids = array();
	foreach ( (array) $posts as $post ) {
		if ( ! isset( $post->ID ) || ! isset( $post->post_type ) )
			continue;

		$taxonomies = get_object_taxonomies( $post->post_type );
		if ( empty( $taxonomies ) )
			continue;

		foreach ( $taxonomies as $taxonomy ) {
			$the_terms = get_the_terms( $post->ID, $taxonomy );
			foreach ( (array) $the_terms as $term ) {
				if ( ! isset( $term->term_taxonomy_id ) ) {
					continue;
				}
				$tt_ids[] = $term->term_taxonomy_id;
			}
		}
	}
	$tt_ids = array_filter( array_unique( $tt_ids ) );

	$image_ids = array();
	foreach ( $tt_ids as $tt_id ) {
		if ( ! isset( $assoc[$tt_id] ) )
			continue;

		if ( in_array( $assoc[$tt_id], $image_ids ) )
			continue;

		$image_ids[] = $assoc[$tt_id];
	}

	if ( empty( $image_ids ) )
		return;

	$images = get_posts( array(
		'include'   => $image_ids,
		'post_type' => 'attachment'
	) );
}


/**
 * Cache Images
 *
 * Cache all term images associated with posts in
 * the main WordPress query.
 *
 * @param     array          Post objects.
 *
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_cache_queried_images() {
	global $posts;
	taxonomy_image_plugin_cache_images( $posts );
}
add_action( 'template_redirect', 'taxonomy_image_plugin_cache_queried_images' );


/**
 * Check Taxonomy
 *
 * Wrapper for WordPress core functions taxonomy_exists().
 * In the event that an unregistered taxonomy is passed a
 * E_USER_NOTICE will be generated.
 *
 * @param     string         Taxonomy name as registered with WordPress.
 * @param     string         Name of the current function or filter.
 * @return    bool           True if taxonomy exists, False if not.
 *
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_check_taxonomy( $taxonomy, $filter ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		trigger_error( sprintf( esc_html__( 'The %1$s argument for %2$s is set to %3$s which is not a registered taxonomy. Please check the spelling and update the argument.', 'service-parts-categories-pages' ),
		'<var>' . esc_html__( 'taxonomy', 'service-parts-categories-pages' ) . '</var>',
		'<code>' . esc_html( $filter ) . '</code>',
		'<strong>' . esc_html( $taxonomy ) . '</strong>'
		) );
		return false;
	}

	$settings = get_option( 'taxonomy_image_plugin_settings' );

	if ( ! isset( $settings['taxonomies'] ) ) {
		trigger_error( __( 'No taxonomies have image support.', 'service-parts-categories-pages' ) );
		return false;
	}

	if ( ! in_array( $taxonomy, (array) $settings['taxonomies'] ) ) {
		trigger_error( sprintf( esc_html__( 'The %1$s taxonomy does not have image support.', 'service-parts-categories-pages' ),
			'<strong>' . esc_html( $taxonomy ) . '</strong>'
		) );
		return false;
	}
	return true;
}

/**
 * Please Use Filter.
 *
 * Report to user that they are directly calling a function
 * instead of using supported filters. A E_USER_NOTICE will
 * be generated.
 *
 * @param     string         Name of function called.
 * @param     string         Name of filter to use instead.
 *
 * @access    private
 * @since     0.7
 */
function taxonomy_image_plugin_please_use_filter( $function, $filter ) {
	trigger_error( sprintf( esc_html__( 'The %1$s has been called directly. Please use the %2$s filter instead.', 'service-parts-categories-pages' ),
		'<code>' . esc_html( $function . '()' ) . '</code>',
		'<code>' . esc_html( $filter ) . '</code>'
	) );
}

function service_parts_cats_pages_scripts() {
	global $post;
	if( is_page() && has_shortcode( $post->post_content, 'auto_categories') ) {
		wp_enqueue_script( 'equalheights',get_template_directory_uri(). '/js/equalheights.js', array( 'jquery' ), false, true);
	}
}
add_action( 'wp_enqueue_scripts', 'service_parts_cats_pages_scripts');