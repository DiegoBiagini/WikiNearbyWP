<?php
/**
 * Plugin Name:       WikiNearby
 * Plugin URI:        https://example.com
 * Description:       A widget that allows you to add a location to a post/page and see nearby historical places
 * Version:           0.3
 * Requires PHP:      7.2
 * Author:            Dgbad & Chry
 * Author URI:        https://author.example.com/
 */

// Includes
include(plugin_dir_path( __FILE__ ).'/menu.php');
include(plugin_dir_path( __FILE__ ).'/edit_location_submenu.php');

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
        $loc->id = $this->prog_id;

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
    public $id;

    public function __construct($data){
        $this->loc_data = $data;
        $this->id = 0;
    }
	
	//Used to display on front end
    public function display(){
		wp_register_style( 'wkn_style', plugin_dir_url( __FILE__ ).'style/wkn_style.css' );
		wp_enqueue_style( 'wkn_style' );
		
		wp_register_style( 'fa_style', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" );
		wp_enqueue_style("fa_style");
		
		
		wp_register_script( 'wikinearby-apicall', plugin_dir_url( __FILE__ ).'js/wikinearby-apicall.js' );

		// Localize the script with new data
		$data_array = array(
			'latitude' => $this->loc_data['latitude'],
			'longitude' => $this->loc_data['longitude'],
			'km_range' => $this->loc_data['km_range'],
			'show_coord' => empty($this->loc_data['show_coord']) ? 0 : 1,
			'pre_load' => empty($this->loc_data['pre_load'])? 0 : 1,
			'plugin_path' =>  plugin_dir_url( __FILE__ )
			
		);
		wp_localize_script( 'wikinearby-apicall', 'data', $data_array );
	
		// Enqueued script with localized data.
		wp_enqueue_script( 'wikinearby-apicall' );
		
		?>
		<div class="wkn-main-content-container">
            <div class="wkn-main-content-wrapper">
                <div class="wkn-main-content-box">
                    <div class="wkn-main-content">
                        <h1><span class="fa fa-map-marker" aria-hidden="true"></span></h1>
                        <div class="wkn-img-container">
                            <img class="wkn-img-place" src="<?php echo esc_url($this->loc_data['loc_image']) ?>">
                        </div>
                        <h3> <?php echo esc_html( $this->loc_data['loc_name']); ?></h3>
                        <h4 id="wkn-my-coords"> ( <?php echo esc_html( $this->loc_data['latitude']).','.esc_html( $this->loc_data['longitude'])?> )</h4>                        
                        <div class="wkn-nearby-place">
                            <h3> Nearby Places</h3>
                            <div id="wkn-nearby-wrap" class="wkn-nearby-place-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		
		<?php
		/*

        echo "Location:".esc_html( $this->loc_data['loc_name'])."<br>";
        echo "Longitude:".esc_html( $this->loc_data['longitude'])."<br>";
        echo "Latitude:".esc_html( $this->loc_data['latitude'])."<br>";
        echo "Km range:".esc_html( $this->loc_data['km_range'])."<br>";
        echo "Show coord:".esc_html( $this->loc_data['show_coord'])."<br>";
        echo "Preload:".esc_html( $this->loc_data['pre_load'])."<br>";
        ?>
        <img src="<?php echo esc_url($this->loc_data['loc_image']) ?>" />
        <?php 
        echo "Id:".$id;
		
		*/
    }
	
	// Used to display in admin page
    public function display_table(){
        echo "<tr>";
        echo "<td class='manage-column column-columnname'>".esc_html( $this->loc_data['loc_name'])."</td>";
        echo "<td class='manage-column column-columnname'>".esc_html( $this->loc_data['longitude'])."</td>";
        echo "<td class='manage-column column-columnname'>".esc_html( $this->loc_data['latitude'])."</td>";
        echo "<td class='manage-column column-columnname'>".esc_html( $this->loc_data['km_range'])."</td>";
        echo "<td class='manage-column column-columnname'>".esc_html( '[wikinearby id='.$this->id.']')."</td>";
        echo "<td class='manage-column column-columnname'>";
		// Button to edit location, sends a GET to edit-location-submenu with the id of the location you want to modify
		echo '<a class="dashicons-before dashicons-edit-large" href="'.esc_html(get_admin_url().'admin.php?page=edit-location-submenu&id='.$this->id).'"></a>';
		// Button to remove location, sends a GET to admin-post with the id of the location to remove
		echo '<a class="dashicons-before dashicons-trash" href="'.esc_html(get_admin_url().'admin-post.php?action=delete_location&id='.$this->id).'"></a>';
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
        echo "ERROR";
    else{
        $saved_locations->add_location($loc);

        update_option('wikinearby_saved_locations', $saved_locations);
    }

    wp_redirect(get_admin_url().'admin.php?page=edit-location-submenu');
}

add_action('admin_post_add_location', 'wikinearby_add_location');

// Called when a location is modified, checks if the given location exists in the Saved_Locations, if it exists it updates it 
// Modified parameters are passed through POST
function wikinearby_edit_location(){
    //Save it yo
    unset($_POST['action']);
    $loc = new Location($_POST);
	$loc->id = $_POST['id'];

    $saved_locations = get_option('wikinearby_saved_locations');
    if($saved_locations === false)
        echo "ERROR";
    else{
        $saved_locations->update_location($_POST['id'], $loc);

        update_option('wikinearby_saved_locations', $saved_locations);
    }

    wp_redirect(get_admin_url().'admin.php?page=wikinearby-menu');
}

add_action('admin_post_edit_location', 'wikinearby_edit_location');

// Deletes a single location in Saved_Locations given its id, this id is passed through GET
function wikinearby_delete_location(){


	$id = $_GET['id'];

    $saved_locations = get_option('wikinearby_saved_locations');
    if($saved_locations === false)
        echo "ERROR";
    else{
        $saved_locations->delete_location($id);

        update_option('wikinearby_saved_locations', $saved_locations);
    }

    wp_redirect(get_admin_url().'admin.php?page=wikinearby-menu');
}

add_action('admin_post_delete_location', 'wikinearby_delete_location');

// Deletes all locations by reinitializing the Saved_Locations object
function wikinearby_delete_all_location(){

	update_option('wikinearby_saved_locations', new Saved_Locations());

    wp_redirect(get_admin_url().'admin.php?page=wikinearby-menu');
}

add_action('admin_post_delete_all', 'wikinearby_delete_all_location');


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
