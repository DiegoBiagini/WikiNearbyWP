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


class WikiNearby_widget extends WP_Widget {
    
    /**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'WikiNearby',
			'description' => 'A widget to add a location and display nearby important places',
		);
		parent::__construct( 'WikiNearby', 'WikiNearby', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
        echo $args['before_widget'];
        echo "Location:".esc_html( $instance['location_name'])."<br>";
        echo "Longitude:".esc_html( $instance['longitude'])."<br>";
        echo "Latitude:".esc_html( $instance['latitude'])."<br>";
        echo "Km range:".esc_html( $instance['km_range'])."<br>";
        echo "Show coord:".esc_html( $instance['show_coord'])."<br>";
        echo "Preload:".esc_html( $instance['pre_load'])."<br>";


        echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
        $location_name = !empty($instance['location_name']) ? $instance['location_name'] : esc_html('Location');
        
        $latitude = !empty($instance['latitude']) ? $instance['latitude'] : '';
        $longitude = !empty($instance['longitude']) ? $instance['longitude'] : '';
        $km_range = !empty($instance['km_range']) ? $instance['km_range'] : 10;
        $show_coord = !empty($instance['show_coord']) ? $instance['show_coord'] : 'off';
        $pre_load = !empty($instance['pre_load']) ? $instance['pre_load'] : 'off';

		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'location_name' ) ); ?>">Location name:</label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'location_name' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'location_name' ) ); ?>" type="text" value="<?php echo esc_attr( $location_name ); ?>">
		</p>

		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'latitude' ) ); ?>">Latitude:</label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'latitude' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'latitude' ) ); ?>" type="number" step="0.000001" value="<?php echo esc_attr( $latitude ); ?>">
		</p>

		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'longitude' ) ); ?>">Longitude:</label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'longitude' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'longitude' ) ); ?>" type="number" step="0.000001" value="<?php echo esc_attr( $longitude ); ?>">
		</p>

		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'km_range' ) ); ?>">Maximum distance of interesting places(in kilometers):</label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'km_range' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'km_range' ) ); ?>" type="number" step="1" min="1" max="100" value="<?php echo esc_attr( $km_range ); ?>">
		</p>
        
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'show_coord' ) ); ?>">Show coordinates:</label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'show_coord' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_coord' ) ); ?>" type="checkbox" <?php checked( $instance[ 'show_coord' ], 'on' ); ?> >
		</p>
        
        <p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'pre_load' ) ); ?>">Preload nearby places:</label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'pre_load' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'pre_load' ) ); ?>" type="checkbox" <?php checked( $instance[ 'pre_load' ], 'on' ); ?> >
		</p>

		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
        $instance = array();
        
        $instance['location_name'] = !empty($new_instance['location_name']) ? sanitize_text_field($new_instance['location_name']) : '';
        $instance['latitude'] = !empty($new_instance['latitude']) ? sanitize_text_field($new_instance['latitude']) : '';
        $instance['longitude'] = !empty($new_instance['longitude']) ? sanitize_text_field($new_instance['longitude']) : '';
        $instance['km_range'] = !empty($new_instance['km_range']) ? sanitize_text_field($new_instance['km_range']) : '';

        $instance['show_coord'] = $new_instance['show_coord'];
        $instance['pre_load'] = $new_instance['pre_load'];

        
		return $instance;
	}
}


// register Wikinearby widget
function register_wikinearby_widget() {
    register_widget( 'WikiNearby_widget' );
}
add_action( 'widgets_init', 'register_wikinearby_widget' );

// Add shortcode for the widget
function wikinearby_render_shortcode($atts = [], $content = null, $tag = ''){
    //Normalize
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
 
    // override default attributes with user attributes
    $wikinearby_atts = shortcode_atts([ 'location_name' => 'None', 'latitude' => 0, 'longitude' => 0, 'km_range' => 10, 'show_coord' => 'off', 'pre_load' => 'off']
                                      , $atts, $tag);
    
    ob_start();
    the_widget('WikiNearby_widget', $wikinearby_atts);
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}
add_shortcode('wikinearby', 'wikinearby_render_shortcode');


