<?php

function add_location_submenu(){
		
	wp_register_style( 'form_style', plugin_dir_url( __FILE__ ).'style/form_style.css' );
	wp_enqueue_style( 'form_style' );
	

	
	?>
	<h2> Add a new location</h2>
	<form action='<?php echo esc_url( admin_url('admin-post.php') ); ?>' method='post'>
	<div class="wikinearby_form" >
		<p>
		<label for="loc_name">Location name:</label> 
		<input style="max-width:30ch" class="widefat" id="loc_name" name="loc_name" type="text" value="Location" required>
		</p>

		<p>
		<label for="latitude">Latitude:</label> 
		<input style="max-width:30ch" class="widefat" id="latitude" name="latitude" type="number" step="0.000001" value="" required>
		</p>

		<p>
		<label for="longitude">Longitude:</label> 
		<input style="max-width:30ch" class="widefat" id="longitude" name="longitude" type="number" step="0.000001" value="" required>
		</p>

		<p>
		<label for="km_range">Maximum distance of interesting places(in kilometers):</label> 
		<input style="max-width:30ch" class="widefat" id="km_range" name="km_range" type="number" step="1" min="1" max="100" value="10" required>
		</p>

        <p>
        <label for="loc_image">Image:</label>
        <input style="max-width:100ch" class="widefat" id="loc_image" name="loc_image" type="text" value="" />
        <input id="upload_image_button" type="button" class="button-primary" value="Insert Image" />
        </p>
		
		
        
		<p>
		<label for="show_coord">Show coordinates:</label> 
		<input class="widefat" id="show_coord" name="show_coord" type="checkbox" >
		</p>
        
        <p>
		<label for="pre_load">Preload nearby places:</label> 
		<input class="widefat" id="pre_load" name="pre_load" type="checkbox"  >
		</p>
		
		<input type="hidden" name="action" value="add_location">

		
		</div>
		
		<input class='button button-primary' type='submit' name='submit' value='Add location'>
	</form>
	<?php
	
}



