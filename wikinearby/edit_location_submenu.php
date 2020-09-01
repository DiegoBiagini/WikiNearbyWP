<?php

function edit_location_submenu(){
	
	
	// Check if it came from the edit link, if this is true initialize the fields to the given Location
	$editing = false;
	$selected_location = null;
	if(isset($_GET['id'])) {
		$editing = true;
		
		$saved_locations = get_option('wikinearby_saved_locations');
		$selected_location = $saved_locations->get_location_by_id($_GET['id']);
		
		// Localize the script with previous location
		$data_array = array(
			'latitude' => $selected_location->loc_data['latitude'],
			'longitude' => $selected_location->loc_data['longitude']
		);
		wp_localize_script( 'wikinearby-locpicker', 'locData', $data_array );
	}
	
	wp_enqueue_script('wikinearby-locpicker', plugin_dir_url( __FILE__ ).'js/locationpicker.jquery.js', array('locationpicker'));

	
	?>
	<h2><?php echo($editing ?  'Edit location' : 'Add a new location'); ?></h2>
	
		<?php settings_errors(); ?>

	<form action='<?php echo esc_url( admin_url('admin-post.php') ); ?>' method='post'>
	<div class="wikinearby_form" >
		<p>
		<label for="loc_name">Location name:</label> 
		<input style="max-width:30ch" class="widefat" id="loc_name" name="loc_name" type="text" value="<?php echo($editing ?  $selected_location->loc_data['loc_name'] : 'Location'); ?>" required>
		</p>

		<p>
		<label for="latitude">Latitude:</label> 
		<input style="max-width:30ch" class="widefat" id="latitude" name="latitude" type="number" step="any" value="<?php echo($editing ?  $selected_location->loc_data['latitude'] : ''); ?>" required>
		</p>

		<p>
		<label for="longitude">Longitude:</label> 
		<input style="max-width:30ch" class="widefat" id="longitude" name="longitude" type="number" step="any" value="<?php echo($editing ?  $selected_location->loc_data['longitude'] : ''); ?>" required>
		</p>
		
		<div id="locpicker" style="width: 500px; height: 400px;"></div>

		<p>
		<label for="km_range">Maximum distance of interesting places(in kilometers):</label> 
		<input style="max-width:7ch" class="widefat" id="km_range" name="km_range" type="number" step="1" min="1" max="100" value="<?php echo($editing ?  $selected_location->loc_data['km_range'] : '10'); ?>" required>
		</p>
		
		<p>
		<label for="max_results">Maximum number of results:</label> 
		<input style="max-width:7ch" class="widefat" id="max_results" name="max_results" type="number" step="1" min="6" max="60" value="<?php echo($editing ?  $selected_location->loc_data['max_results'] : '12'); ?>" required>
		</p>
		
        <p>
        <label for="loc_image">Image:</label>
        <input style="max-width:100ch" class="widefat" id="loc_image" name="loc_image" type="text" value="<?php echo($editing ?  $selected_location->loc_data['loc_image'] : ''); ?>" />
        <input id="upload_image_button" type="button" class="button-primary" value="Insert Image" />
        </p>
		
		
        
		<p>
		<label for="show_coord">Show coordinates:</label> 
		<input class="widefat" id="show_coord" name="show_coord" type="checkbox" <?php echo($editing ?  ($selected_location->loc_data['show_coord'] === 'on' ? 'checked' : '') : ''); ?>>
		</p>
        
		<p>
		<label for="lang">Wikipedia language to search in:</label>
		<select name="lang" id="lang" required>
			<option value="en" <?php echo($selected_location->loc_data['lang'] == 'en' ? 'selected' : ''); ?> >English</option>
			<option value="it" <?php echo($selected_location->loc_data['lang'] == 'it' ? 'selected' : ''); ?>>Italian</option>
			<option value="fr" <?php echo($selected_location->loc_data['lang'] == 'fr' ? 'selected' : ''); ?>>French</option>
			<option value="de" <?php echo($selected_location->loc_data['lang'] == 'de' ? 'selected' : ''); ?>>German</option>
		</select>
		</p>
		
		<input type="hidden" name="action" value="<?php echo($editing ? 'edit_location' : 'add_location' )?>">
		<?php echo( $editing ? '<input type="hidden" name="id" value="'.$_GET['id'].'">' : ''); ?>
		
		</div>
		
		<input class='button button-primary' type='submit' name='submit' value='<?php echo($editing? 'Apply' : 'Add location');?> '>
	</form>
	<a href="<?php echo esc_html(get_admin_url().'admin.php?page=wikinearby-menu');?>"> Go back to locations</a>
	
	<?php
	
}



