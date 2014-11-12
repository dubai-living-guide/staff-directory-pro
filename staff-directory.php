<?php
/*
Plugin Name: Company Directory
Plugin Script: staff-directory.php
Plugin URI: http://goldplugins.com/our-plugins/company-directory/
Description: Create a directory of your staff members and show it on your website!
Version: 1.1
Author: GoldPlugins
Author URI: http://goldplugins.com/
*/
require_once('gold-framework/plugin-base.php');
require_once('gold-framework/staff-directory-plugin.settings.page.class.php');

class StaffDirectoryPlugin extends StaffDirectory_GoldPlugin
{
	var $plugin_title = 'Staff Directory';
	var $prefix = 'staff_dir';
	function __construct()
	{
		$this->create_post_types();
		$this->add_hooks();
		$this->add_stylesheets_and_scripts();
		//$this->add_settings_page('Staff Directory', 'Staff Directory');
		//$this->SettingsPage = new StaffDirectoryPlugin_SettingsPage($this);
		parent::__construct();
	}
	
	function add_hooks()
	{
		add_shortcode('staff_list', array($this, 'staff_list_shortcode'));
		add_action('init', array($this, 'remove_features_from_custom_post_type'));
		add_filter( 'template_include', array($this, 'override_template_location'), 1 );
		parent::add_hooks();
	}
	
	function create_post_types()
	{
		$postType = array('name' => 'Staff Member', 'plural' => 'Staff Members', 'slug' => 'staff-members');
		$customFields = array();
		$customFields[] = array('name' => 'first_name', 'title' => 'First Name', 'description' => 'Steven, Anna', 'type' => 'text');	
		$customFields[] = array('name' => 'last_name', 'title' => 'Last Name', 'description' => 'Example: Smith, Goldstein', 'type' => 'text');	
		$customFields[] = array('name' => 'title', 'title' => 'Title', 'description' => 'Example: Director of Sales, Customer Service Team Member, Project Manager', 'type' => 'text');	
		$customFields[] = array('name' => 'phone', 'title' => 'Phone', 'description' => 'Best phone number to reach this person', 'type' => 'text');
		$customFields[] = array('name' => 'email', 'title' => 'Email', 'description' => 'Email address for this person', 'type' => 'text');
		$this->add_custom_post_type($postType, $customFields);
	}
	
	/* Disable some of the normal WordPress features on the Staff Member custom post type (the editor, author, comments, excerpt) */
	function remove_features_from_custom_post_type()
	{
		//remove_post_type_support( 'staff-member', 'editor' );
		remove_post_type_support( 'staff-member', 'excerpt' );
		remove_post_type_support( 'staff-member', 'comments' );
		remove_post_type_support( 'staff-member', 'author' );
	}

	function add_stylesheets_and_scripts()
	{
		$cssUrl = plugins_url( 'assets/css/staff-directory.css' , __FILE__ );
		$this->add_stylesheet('staff-directory-css',  $cssUrl);
		
/* 		$jsUrl = plugins_url( 'assets/js/wp-banners.js' , __FILE__ );
		$this->add_script('wp-banners-js',  $jsUrl);
 */		
		
	}
	

	function override_template_location( $template_path ) {
		if ( get_post_type() == 'staff-member' )
		{
			if ( is_single() )
			{
				// checks if the file exists in the theme first,
				// otherwise serve the file from the plugin
				if ( $theme_file = locate_template( array ( 'single-staff-member.php' ) ) ) {
					$template_path = $theme_file;
				} else {
					$template_path = plugin_dir_path( __FILE__ ) . 'templates/single-staff-member.php';
				}
			}
		}
		return $template_path;
	}

	/* Shortcodes */
	
	/* output a list of all locations */
	function staff_list_shortcode($atts, $content = '')
	{
		// merge any settings specified by the shortcode with our defaults
		$defaults = array(	'caption' => '',
							'show_photos' => 'true',
							'style' => 'list',
							'columns' => 'name,title,email,phone',
						);
		$atts = shortcode_atts($defaults, $atts);
		$atts['columns'] = array_map('trim', explode(',', $atts['columns']));
		
		// get a Custom Loop for the staff custom post type, and pass it to the template
		$staff_loop = $this->get_staff_members_loop();
		
		// $vars will be available in the template
		$vars = array('staff_loop' => $staff_loop);

		// render the 'template-staff-list.php' file (can be overridden by a file with the same name in the active theme)
		switch ($atts['style'])
		{
			case 'grid':
				$templatePath = plugin_dir_path( __FILE__ ) . 'templates/staff-list-grid.php';
			break;

			case 'table':
				$templatePath = plugin_dir_path( __FILE__ ) . 'templates/staff-list-table.php';
				$vars['columns'] = $atts['columns'];
			break;
			
			default:
			case 'list':
				$templatePath = plugin_dir_path( __FILE__ ) . 'templates/staff-list.php';
			break;
		}
		$html = $this->render_template($templatePath, $vars);
		return $html;
	}
	
	private function build_staff_member_html($staff_member)
	{
		$bio_link = get_permalink($staff_member->ID);
		$html = '';
		return $html;
	}
	
	function output_settings_page()
	{
		echo '<h3>Staff Directory Settings</h3>';	
	}
	
	
	// returns a list of all locations in the database, sorted by the title, ascending
	private function get_all_staff_members()
	{
		$conditions = array('post_type' => 'staff-member',
							'post_count' => -1,
							'orderby' => 'meta_value',
							'meta_key' => '_ikcf_last_name',
							'order' => 'ASC',
					);
		$all = get_posts($conditions);	
		return $all;
	}

	// returns a list of all locations in the database, sorted by the title, ascending
	private function get_staff_members_loop()
	{
		$conditions = array('post_type' => 'staff-member',
							'post_count' => -1,
							'orderby' => 'meta_value',
							'meta_key' => '_ikcf_last_name',
							'order' => 'ASC',
					);
		return new WP_Query($conditions);
	}
	
	function is_pro() 
	{
		return false;
	}
	
}
$gp_sdp = new StaffDirectoryPlugin();