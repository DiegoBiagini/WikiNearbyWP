<?php
/**
 * Plugin Name:       WikiNearby
 * Plugin URI:        https://example.com
 * Description:       A widget that allows you to add a location to a post/page and see nearby historical places
 * Version:           0.1
 * Requires PHP:      7.2
 * Author:            Dgbad & Chry
 * Author URI:        https://author.example.com/
 */

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
include(plugin_dir_path( __FILE__ ).'/menu.php');
include(plugin_dir_path( __FILE__ ).'/edit_location_submenu.php');


class Saved_Locations {
    public $locations;
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

    public function print_locations(){

        foreach($this->locations as $w){
            $w->display();
        }

    }

    public function get_location_by_id($id){
        foreach($this->locations as $w){
            if($w->id == $id)
                return $w;
        }
		return null;
    }

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

class Location {
    public $loc_data;
    public $id;

    public function __construct($data){
        $this->loc_data = $data;
        $this->id = 0;
    }

    public function display(){

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
    }

    public function display_table(){
        echo "<tr>";
        echo "<td class='manage-column column-columnname'>".esc_html( $this->loc_data['loc_name'])."</td>";
        echo "<td class='manage-column column-columnname'>".esc_html( $this->loc_data['longitude'])."</td>";
        echo "<td class='manage-column column-columnname'>".esc_html( $this->loc_data['latitude'])."</td>";
        echo "<td class='manage-column column-columnname'>".esc_html( $this->loc_data['km_range'])."</td>";
        echo "<td class='manage-column column-columnname'>".esc_html( '[wikinearby id='.$this->id.']')."</td>";
        echo "<td class='manage-column column-columnname'>";
		echo '<a class="dashicons-before dashicons-edit-large" href="'.esc_html(get_admin_url().'admin.php?page=edit-location-submenu&id='.$this->id).'"></a>';
		echo '<a class="dashicons-before dashicons-trash" href="'.esc_html(get_admin_url().'admin-post.php?action=delete_location&id='.$this->id).'"></a>';
		echo "</td>";

        echo "</tr>";

    }
}

// Activation and deactivation hooks

register_activation_hook( __FILE__, 'wikinearby_activate' );
register_deactivation_hook( __FILE__, 'wikinearby_deactivate' );

// Add option to the DB
function wikinearby_activate(){
    $sav = new Saved_Locations();
    add_option("wikinearby_saved_locations", $sav);

}

// Remove option
function wikinearby_deactivate(){
    delete_option("wikinearby_saved_locations");

}

// Add main menu

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

function wikinearby_add_location(){
    //Save it yo
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



//Register media uploader

function media_uploader_enqueue() {
    	wp_enqueue_media();
    	wp_register_script('media-uploader', plugins_url('js/media-uploader.js' , __FILE__ ), array('jquery'));
    	wp_enqueue_script('media-uploader');
}
    
add_action('admin_enqueue_scripts', 'media_uploader_enqueue');


// Add shortcode
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
        return $found_loc->display();
    }

}
add_shortcode('wikinearby', 'wikinearby_render_shortcode');