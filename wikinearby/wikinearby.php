<?php
/**
 * Plugin Name:       WikiNearby
 * Plugin URI:        https://example.com
 * Description:       A widget that allows you to add a location to a post/page and see nearby historical places
 * Version:           0.9
 * Requires PHP:      7.2
 * Author:            Dgbad & Chry
 * Author URI:        https://author.example.com/
 */

// Includes
include(plugin_dir_path( __FILE__ ).'/menu.php');
include(plugin_dir_path( __FILE__ ).'/edit_location_submenu.php');
include(plugin_dir_path( __FILE__ ).'/notices.php');

// Class that will store a collection of Locations
class Saved_Locations {
	// Array of references to Locations
    public $locations;
	// Id to keep track of new Locations
    public $prog_id;

    public function __construct(){
        $this->locations = array();
        $this->prog_id = 1;
    }
	
    public function add_location($loc){
        $loc->set_id($this->prog_id);

        $this->locations[$this->prog_id] = $loc;

        $this->prog_id++;
    }

    public function delete_location($id){
        unset($this->locations[$id]);
    }
	
	public function update_location($id, $new_location){
		$this->locations[$id] = $new_location;
	}
	
	// Returns a Location given its id
    public function get_location_by_id($id){
		return $this->locations[$id];
    }
	
	// Display the table of locations, used in admin page
    public function print_locations_table(){

?>
<table style="width:95%" class="widefat fixed">
    <thead>
        <tr>
            <th>Location</th>
            <th>Longitude</th>
            <th>Latitude</th>
            <th>Range</th>
            <th>Shortcode</th>
            <th></th>
        </tr>
    </thead>
    <tbody class="alternate">
        <?php
        foreach($this->locations as $w){
            $w->display_table();
        }
        echo "</tbody></table>";

    }

}

// Class that will store the data of a single location
class Location {
	// Array containing name, latitude, longitude, etc
    public $loc_data;

    public function __construct($data){
        $this->loc_data = $data;
		$this->set_id(0);
    }
	
	public function set_id($id){
		$this->loc_data['id'] = $id;
	}
	
	//Used to display on front end
    public function display(){
		
		// Localize the script with new data
		$data_array = array(
			'latitude' => $this->loc_data['latitude'],
			'longitude' => $this->loc_data['longitude'],
			'km_range' => $this->loc_data['km_range'],
			'show_coord' => empty($this->loc_data['show_coord']) ? 0 : 1,
			'max_results' => $this->loc_data['max_results'],
			'lang' => $this->loc_data['lang'],
			'plugin_path' =>  plugin_dir_url( __FILE__ )
			
		);
		wp_localize_script( 'wikinearby-apicall', 'data', $data_array );
		wp_enqueue_script('wikinearby-apicall');
		?>
        <div id="wkn-main-content-container">
            <div id="wkn-main-content-wrapper">
                <div id="wkn-main-content-box">
                    <div id="wkn-main-content">
                        <div id="wkn-img-container">
                            <img id="wkn-img-place" src="<?php echo esc_url($this->loc_data['loc_image']) ?>">
                        </div>
                        <h3>  <?php echo esc_html( $this->loc_data['loc_name']); ?></h3>
                        <h4 id="wkn-my-coords"></h4>
						<div id="wkn-req-load"></div>
                        <div id="wkn-nearby-place">
                            <h3 class="text-center"> <button id="wkn-collapse-btn" type="button" data-toggle="collapse" data-target="#carousels">Nearby Places <i class="fa fa-caret-up fa-xs"></i></button></h3>
                            <div class="container-fluid">

                            <!-- PAGE CAROUSEL-CONTENT -->
                            <div class="carousel slide collapse in" data-ride="carousel"  id="carousels" >
                                <div class="carousel-inner">
                                    <div class="container-fluid">
                                        <div id="wkn-nearby-wrap" class="row">
                                            
                                            <!-- WIKIPEDIA-DINAMIC-CONTENT -->
                                            
                                        </div>
                                        <div id="carousel-buttons" >
                                            <a class="carousel-control-prev" href="#carousels" data-slide="prev">
                                                <button id="wkn-control-prev" class="carousel-control-prev-icon" aria-hidden="true">
                                                    <i class="fa fa-arrow-left"></i>
                                                </button>
                                            </a>
                                            <a class="carousel-control-next" href="#carousels" data-slide="next">
                                                <button id="wkn-control-next" class="carousel-control-next-icon" aria-hidden="true">
                                                    <i class="fa fa-arrow-right"></i>
                                                </button>
                                            </a>
                                        </div>
                                        <div id="carousel-indexs">

                                        </div>
                                    </div>
                                </div>                             
                            </div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<?php

    }
	
	// Used to display in admin page
    public function display_table(){
        echo "<tr>";
        echo "<td class='manage-column column-columnname'>".esc_html( $this->loc_data['loc_name'])."</td>";
        echo "<td class='manage-column column-columnname'>".esc_html( $this->loc_data['longitude'])."</td>";
        echo "<td class='manage-column column-columnname'>".esc_html( $this->loc_data['latitude'])."</td>";
        echo "<td class='manage-column column-columnname'>".esc_html( $this->loc_data['km_range'])."</td>";
        echo "<td class='manage-column column-columnname'>".esc_html( '[wikinearby id='.$this->loc_data['id'].']')."</td>";
        echo "<td class='manage-column column-columnname'>";
		// Button to edit location, sends a GET to edit-location-submenu with the id of the location you want to modify
		echo '<a class="dashicons-before dashicons-edit-large" href="'.esc_html(get_admin_url().'admin.php?page=edit-location-submenu&id='.$this->loc_data['id']).'"></a>';
		// Button to remove location, sends a GET to admin-post with the id of the location to remove
		echo '<a  class="dashicons-before dashicons-trash" href="'.esc_html(get_admin_url().'admin-post.php?action=delete_location&id='.$this->loc_data['id']).'"></a>';
		echo "</td>";

        echo "</tr>";

    }
}

// Activation and deactivation hooks

register_activation_hook( __FILE__, 'wikinearby_activate' );
register_deactivation_hook( __FILE__, 'wikinearby_deactivate' );
register_uninstall_hook( __FILE__, 'wikinearby_uninstall' );


// Check if option is in the DB, if not create it; then activate the shortcode
function wikinearby_activate(){
    $saved_locations = get_option('wikinearby_saved_locations');
	
    if($saved_locations === false){
        $sav = new Saved_Locations();
		add_option("wikinearby_saved_locations", $sav);
	}

}

// Do nothing for now
function wikinearby_deactivate(){

}

// Delete data
function wikinearby_uninstall(){
	delete_option("wikinearby_saved_locations");
}

//Register styles and scripts
		
add_action('init', 'register_styles_scripts');
function register_styles_scripts(){
	//Back end
	wp_register_script("google-api","http://maps.google.com/maps/api/js?sensor=false&libraries=places");
	wp_register_script( 'wikinearby-locpicker', plugin_dir_url( __FILE__ ).'js/locationpicker.jquery.js' );
	
	wp_register_style( 'form_style', plugin_dir_url( __FILE__ ).'style/form_style.css' );
	
	//Front end styles/scripts
	wp_register_style( 'wkn_style', plugin_dir_url( __FILE__ ).'/style/wkn_style.css' );
	wp_register_style( 'fa_style', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" );
	wp_register_style( 'bs_style', "https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" );
	
	wp_register_script('jquery_script', "https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js");
	wp_register_script('bs_script', "https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js");
	
	wp_register_script( 'wikinearby-apicall', plugin_dir_url( __FILE__ ).'js/wikinearby-apicall.js' );


}

add_action( 'wp_enqueue_scripts', 'enqueue_styles_scripts_frontend' );
function enqueue_styles_scripts_frontend(){
	
	wp_enqueue_style('wkn_style');
	wp_enqueue_style("fa_style");
	wp_enqueue_style("bs_style");

		
	wp_enqueue_script('jquery_script');	
	wp_enqueue_script("bs_script");

}

add_action( 'admin_enqueue_scripts', 'enqueue_styles_scripts_backend' );
function enqueue_styles_scripts_backend(){
	wp_enqueue_style( 'form_style' );
	wp_enqueue_script("google-api");
	
}




// Add menu

add_action( 'admin_menu', 'wikinearby_menu_page' );

function wikinearby_menu_page(){
    add_menu_page(
        'WikiNearby',
        'WikiNearby',
        'manage_options',
        'wikinearby-menu',
        'wikinearby_menu',
		plugin_dir_url(__FILE__ ).'assets/icon.png'
		
    );
    add_submenu_page( 'wikinearby-menu', 'Add new location', 'Add new location', 'manage_options', 'edit-location-submenu', 'edit_location_submenu');
}



//Add post actions
// Called when a location is added, retrieves the Saved_Locations obj and adds a Location
// Locations data is passed through POST
function wikinearby_add_location(){
    unset($_POST['action']);
    $loc = new Location($_POST);

    $saved_locations = get_option('wikinearby_saved_locations');
    if($saved_locations === false)
		add_flash_notice( __("Error"), "error", true );
    else{
        $saved_locations->add_location($loc);

        update_option('wikinearby_saved_locations', $saved_locations);
		
		add_flash_notice( __("Location added successfully"), "success", true );

    }

    wp_redirect(get_admin_url().'admin.php?page=edit-location-submenu');
	

}

add_action('admin_post_add_location', 'wikinearby_add_location');

// Called when a location is modified, checks if the given location exists in the Saved_Locations, if it exists it updates it 
// Modified parameters are passed through POST
function wikinearby_edit_location(){
    //Create new location
    unset($_POST['action']);
    $loc = new Location($_POST);
	$loc->set_id($_POST['id']);

    $saved_locations = get_option('wikinearby_saved_locations');
    if($saved_locations === false)
		add_flash_notice( __("Error"), "error", true );
    else{
        $saved_locations->update_location($_POST['id'], $loc);

        update_option('wikinearby_saved_locations', $saved_locations);
		
		add_flash_notice( __("Location modified succesfully"), "success", true );

    }

    wp_redirect(get_admin_url().'admin.php?page=wikinearby-menu');
	
}

add_action('admin_post_edit_location', 'wikinearby_edit_location');

// Deletes a single location in Saved_Locations given its id, this id is passed through GET
function wikinearby_delete_location(){


	$id = $_GET['id'];

    $saved_locations = get_option('wikinearby_saved_locations');
    if($saved_locations === false)
		add_flash_notice( __("Error"), "error", true );
    else{
        $saved_locations->delete_location($id);

        update_option('wikinearby_saved_locations', $saved_locations);
		
		add_flash_notice( __("Location removed succesfully"), "success", true );

    }
	
    wp_redirect(get_admin_url().'admin.php?page=wikinearby-menu');
	

}

add_action('admin_post_delete_location', 'wikinearby_delete_location');

// Deletes all locations by reinitializing the Saved_Locations object
function wikinearby_delete_all_locations(){

	update_option('wikinearby_saved_locations', new Saved_Locations());

    wp_redirect(get_admin_url().'admin.php?page=wikinearby-menu');
}

add_action('admin_post_delete_all', 'wikinearby_delete_all_locations');


//Register media uploader

function media_uploader_enqueue() {
    	wp_enqueue_media();
    	wp_register_script('media-uploader', plugins_url('js/media-uploader.js' , __FILE__ ), array('jquery'));
    	wp_enqueue_script('media-uploader');
}
    
add_action('admin_enqueue_scripts', 'media_uploader_enqueue');


// Render shortcode to post/page
// Checks if the id is inside the Saved_Locations objects, if it's found it returns the displayed Location
function wikinearby_render_shortcode($atts = [], $content = null, $tag = ''){
    //Normalize
    $atts = array_change_key_case((array)$atts, CASE_LOWER);

    $wikinearby_atts = shortcode_atts(
        ['id' => '',] , $atts, $tag);
    
    if(empty($wikinearby_atts['id']))
        return 'Wrong shortcode';

    //Find the correct location
    $given_id = $wikinearby_atts['id'];

    $saved_locations = get_option('wikinearby_saved_locations');
    if($saved_locations === false)
        return "ERROR";
    else{
        $found_loc =  $saved_locations->get_location_by_id($given_id);
		if($found_loc === null)
			return 'No location found';
		
		ob_start(); 
		$found_loc->display();
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

}

// Then add the shortcode
add_shortcode('wikinearby', 'wikinearby_render_shortcode');

