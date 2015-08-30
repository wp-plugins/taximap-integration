<?php
/*
Plugin Name: TaxiMap Integration
Plugin URI: http://blog.taximap.co.uk/2015/08/wordpress-plugin/
Description: Displays the TaxiMap taxi fare price calculator on your site via shortcode [taximap] or widget.
Version: 1.0.1
Author: M Williams
Author URI: http://nimbus.agency
*/


function taximap(){
	ob_start();
	return tm_renderiFrame();
}



function tm_scripts(){
	wp_enqueue_style( 'style-name', plugins_url( 'taximap.css', __FILE__ ) );
	wp_enqueue_script( 'script-name', plugins_url( 'taximap.js', __FILE__ ) );
}


function tm_renderiFrame(){
	$taximapId=esc_attr( get_option('taximap_id') );
	$tmIframe="";
	if($taximapId==''){
		$taximapId='10000';	//default to TaxiMap Demo Account
		$tmIframe.='<div class="tm_alert">Warning: TaxiMap ID not set - Admin must add a TaxiMap ID. </div>';
	}
	$tmIframe.='<div class="taximap"><!-- Version 150830 -->';
	$tmIframe.='<iframe src="//taximap.co.uk/plugin/taxi_map_frame.asp?wp=shortcodeWP&i1=1&f=1&uid='.$taximapId.'"></iframe>';
	$tmIframe.='<!-- Powered by TaxiMap.co.uk -->';
	$tmIframe.='</div>';
	return $tmIframe;
}

add_action('wp_enqueue_scripts','tm_scripts');

add_shortcode('taximap', 'taximap');

// create custom plugin settings menu
add_action('admin_menu', 'taximap_integration_create_menu');

function taximap_integration_create_menu() {

	//create new top-level menu
	add_menu_page('TaxiMap Integration', 'TaxiMap Settings', 'administrator', __FILE__, 'taximap_integration_settings_page','dashicons-location' );

	//call register settings function
	add_action( 'admin_init', 'taximap_integration_settings' );
}


function taximap_integration_settings() {
	//register our settings
	register_setting( 'TaxiMap-integration-settings-group', 'taximap_id' );
}

function taximap_integration_settings_page() {
?>
<div class="wrap">
<h2>TaxiMap Integration</h2>
<p>You will need to get your TaxiMap Membership No. from <a href="https://taximap.co.uk/members/" target="_blank">http://taximap.co.uk</a> (found as the top item in the STATUS table on the right side after loggin in) and enter it below...<br />All other configuration is done from within your <a href="https://taximap.co.uk/members/" target="_blank">TaxiMap account</a>.</p>
	<form method="post" action="options.php"> 
		
		<?php settings_fields( 'TaxiMap-integration-settings-group' ); ?>
		<?php do_settings_sections( 'TaxiMap-integration-settings-group' ); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">TaxiMap User ID (Membership No):</th>
					<td><input type="text" name="taximap_id" value="<?php echo esc_attr(get_option('taximap_id')); ?>" /></td>
				</tr>
			</table>
		
	<?php submit_button(); ?>
	</form>
	
<p>To display the plugin on a page/post, just add the following short-code where you want it to appear (for more info, see plug-in read-me file)<br /><b>[taximap]</b></p>
</div>
<?php }


//**** TAXIMAP WIDGET *****\\

class TaxiMapWidget extends WP_Widget
{
  function TaxiMapWidget()
  {
    $widget_ops = array('classname' => 'TaxiMapWidget', 'description' => 'Displays TaxiMap fare price calculator in your widebar (or widget area)' );
    $this->WP_Widget('TaxiMapWidget', 'TaxiMap', $widget_ops);
  }
 
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'height' => '320', 'taximapId' => esc_attr(get_option('taximap_id')) ) );
    $title = $instance['title'];
	$taximapIdForWidget = $instance['taximapId'];
	$height = $instance['height'];
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
  <p><label for="<?php echo $this->get_field_id('taximapId'); ?>">TaxiMap Membership No: <input class="widefat" id="<?php echo $this->get_field_id('taximapId'); ?>" name="<?php echo $this->get_field_name('taximapId'); ?>" type="text" value="<?php echo attribute_escape($taximapIdForWidget); ?>" /></label></p>
  <p><label for="<?php echo $this->get_field_id('height'); ?>">Height (pixels): <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo attribute_escape($height); ?>" /></label></p>
<?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
	$instance['taximapId'] = $new_instance['taximapId'];
	$instance['height'] = $new_instance['height'];
    return $instance;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
	$height = $instance['height'];
	$taximapId = $instance['taximapId'];
 
    if (!empty($title))
      echo $before_title . $title . $after_title;
 
    // WIDGET CODE GOES HERE
    //echo "<h1>This is my new widget!</h1>";
	echo '<div class="tm_widget"><iframe style="height:'.$height.'px;" src="//taximap.co.uk/plugin/taxi_map_frame.asp?wp=widget&i1=1&f=1&uid='.$taximapId.'"></iframe></div>';
 
    echo $after_widget;
  }
 
}
add_action( 'widgets_init', create_function('', 'return register_widget("TaxiMapWidget");') );
?>