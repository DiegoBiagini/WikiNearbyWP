jQuery(document).ready(function($){
	
	//Add media uploader
	var mediaUploader;
	$('#upload_image_button').click(function(e) {
		e.preventDefault();
		if (mediaUploader) {
		mediaUploader.open();
		return;
		}
		mediaUploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
			text: 'Choose Image'
		}, multiple: false });
		
		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			$('#loc_image').val(attachment.url);
		});
	
		mediaUploader.open();
	});
	
	if($('#locpicker').locationpicker != null){
		// Add location picker
		$('#locpicker').locationpicker({
			
			location: {
				latitude: typeof locData != "undefined" ? locData.latitude : 43.943966465658924,
				longitude: typeof locData != "undefined" ? locData.longitude : 10.933070182800293
			},
			radius: 0,
			inputBinding: {
				latitudeInput: $('#latitude'),
				longitudeInput: $('#longitude')
			}
		});
	}
});