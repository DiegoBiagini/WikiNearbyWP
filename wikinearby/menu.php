<?php




function wikinearby_menu(){
	?>
	<h2>Wikinearby</h2>
	<p>Here are the location you registered, you can put them inside a post by copy pasting the shortcode.</p>
	
	
	<?php

	
	$saved_locations = get_option('wikinearby_saved_locations');
	if($saved_locations === false){
		echo "ERROR";
	}
	else
		$saved_locations->print_locations_table();

}