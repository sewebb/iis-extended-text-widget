jQuery(function( jQuery ) {
	var custom_uploader,
	    postInput,
	    imgID,
	    imgContainer;

	jQuery('#upload_image_button').live( 'click', function( e ) {
		e.preventDefault();

		postInput  = jQuery(this).prev( '.custom-upload-image' );
		imgID      = jQuery(postInput).attr( 'id' );
		imgElement = jQuery('#img-' + imgID);

		if (custom_uploader) {
			custom_uploader.open();
			return;
		}
 
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
			multiple: false
		});

		custom_uploader.on( 'select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();

			postInput.val( attachment.url );
			imgElement.attr( 'src' , attachment.url );
		});

		custom_uploader.open();
	});

	jQuery('.custom_clear_image_button').live('click', function( e ) {
		postInput  = jQuery( this ).prev().prev();
		imgID      = jQuery(postInput).attr( 'id' );
		imgElement = jQuery('#img-' + imgID);

		jQuery(postInput).val( '' );
		jQuery(imgElement).attr( 'src', '' );

		return false;
	});
});
