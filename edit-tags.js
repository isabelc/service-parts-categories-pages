jQuery( document ).ready( function( $ ) {

	$( 'body' ).on( 'click', '.taxonomy-image-control a', function () {
		taxonomyImagesPlugin.tt_id = parseInt( $( this ).parent().find( 'input.tt_id' ).val() );
		taxonomyImagesPlugin.term_name = $( this ).parent().find( 'input.term_name' ).val();
		taxonomyImagesPlugin.image_id = parseInt( $( this ).parent().find( 'input.image_id' ).val() );
	} );

	$( 'body' ).on( 'click', '.taxonomy-image-control .remove', function () {
		$.ajax( {
			url: ajaxurl,
			type: "POST",
			dataType: 'json',
			data: {
				'action'   : 'taxonomy_image_plugin_remove_association',
				'wp_nonce' : taxonomyImagesPlugin.nonce,
				'tt_id'    : taxonomyImagesPlugin.tt_id
				},
			cache: false,
			success: function ( response ) {
				if ( 'good' === response.status ) {
					$( '#remove-' + taxonomyImagesPlugin.tt_id ).addClass( 'hide' );
					var imgId = 'taxonomy_image_plugin_' + taxonomyImagesPlugin.tt_id;
					$( '#' + imgId ).replaceWith( $( taxonomyImagesPlugin.no_img ).attr( 'id', imgId ) ).remove();
				}
				else if ( 'bad' === response.status ) {
					alert( response.why );
				}
			}
		} );
		return false;
	} );
} );
