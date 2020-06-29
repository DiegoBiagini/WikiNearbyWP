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
	
	
	?>
	<a style="margin-top:30px;" href="<?php echo esc_html(get_admin_url().'admin.php?page=edit-location-submenu');?>"> Add a new location</a>
	<a style="color:red; display:block;" href="<?php echo esc_html(get_admin_url().'admin-post.php?action=delete_all'); ?>">Delete all locations</a>
	<?php
}