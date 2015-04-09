<?php
/*
Plugin Name: Company Directory
Plugin Script: staff-directory.php
Plugin URI: http://goldplugins.com/our-plugins/company-directory/
Description: Create a directory of your staff members and show it on your website!
Version: 1.2
Author: GoldPlugins
Author URI: http://goldplugins.com/
*/
require_once('gold-framework/plugin-base.php');
require_once('gold-framework/staff-directory-plugin.settings.page.class.php');
require_once('include/sd_kg.php');

class StaffDirectoryPlugin extends StaffDirectory_GoldPlugin
{
	var $plugin_title = 'Staff Directory';
	var $prefix = 'staff_dir';
	var $proUser = false;
	
	function __construct()
	{	
		$this->create_post_types();
		$this->register_taxonomies();
		$this->add_hooks();
		$this->add_stylesheets_and_scripts();
		$this->SettingsPage = new StaffDirectoryPlugin_SettingsPage($this);
			
		// check the reg key
		$this->verify_registration_key();
		
		//add Custom CSS
		add_action( 'wp_head', array($this,'output_custom_css'));
		
		parent::__construct();
	}
	
	function add_hooks()
	{
		add_shortcode('staff_list', array($this, 'staff_list_shortcode'));
		add_shortcode('staff_member', array($this, 'staff_member_shortcode'));
		add_action('init', array($this, 'remove_features_from_custom_post_type'));
		add_filter( 'template_include', array($this, 'override_template_location'), 1 );
		// add our custom meta boxes
		add_action( 'admin_menu', array($this, 'add_meta_boxes'));
		//flush rewrite rules - only do this once!
		register_activation_hook( __FILE__, array($this, 'rewrite_flush' ) );
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
		
		//adds single staff member shortcode to staff member list
		add_filter('manage_staff-member_posts_columns', array($this, 'column_head'), 10);  
		add_action('manage_staff-member_posts_custom_column', array($this, 'columns_content'), 10, 2); 
		
		//load list of current posts that have featured images	
		$supportedTypes = get_theme_support( 'post-thumbnails' );
		
		//none set, add them just to our type
		if( $supportedTypes === false ){
			add_theme_support( 'post-thumbnails', array( 'staff-member' ) );       
			//for the banner images    
		}
		//specifics set, add our to the array
		elseif( is_array( $supportedTypes ) ){
			$supportedTypes[0][] = 'staff-member';
			add_theme_support( 'post-thumbnails', $supportedTypes[0] );
			//for the banner images
		}
	}
	
	function register_taxonomies()
	{
		$this->add_taxonomy('staff-member-category', 'staff-member', 'Staff Category', 'Staff Categories');
		
		//adds staff members by category shortcode displayed
		add_filter('manage_edit-staff-member-category_columns', array($this, 'cat_column_head'), 10);  
		add_action('manage_staff-member-category_custom_column', array($this, 'cat_columns_content'), 10, 3);
	}

	function add_meta_boxes(){
		add_meta_box( 'staff_member_shortcode', 'Shortcodes', array($this,'display_shortcodes_meta_box'), 'staff-member', 'side', 'default' );
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
	
	/* output a list of all staff members */
	function staff_list_shortcode($atts, $content = '')
	{
		// merge any settings specified by the shortcode with our defaults
		$defaults = array(	'caption' => '',
							'show_photos' => 'true',
							'style' => 'list',
							'columns' => 'name,title,email,phone',
							'category' => false,
							'count' => -1
						);
		$atts = shortcode_atts($defaults, $atts);
		$atts['columns'] = array_map('trim', explode(',', $atts['columns']));
		
		// get a Custom Loop for the staff custom post type, and pass it to the template
		$staff_loop = $this->get_staff_members_loop($atts['count'],$atts['category']);
		
		// $vars will be available in the template
		$vars = array('staff_loop' => $staff_loop);
		
		//only pro version of plugin can use styles other than List
		if(!$this->is_pro()){
			$atts['style'] = 'list';
		}
		
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
	
	/* output a single staff members */
	function staff_member_shortcode($atts, $content = '')
	{
		// merge any settings specified by the shortcode with our defaults
		$defaults = array(	'caption' => '',
							'show_photos' => 'true',
							'style' => 'list',
							'columns' => 'name,title,email,phone',
							'category' => false,
							'id' => false,
							'count' => -1
						);
						
		$atts = shortcode_atts($defaults, $atts);
		
		$html = '';
		
		if(!$atts['id']){
			//forgot to pass an ID!
			//do nothing!
		} else {		
			$atts['columns'] = array_map('trim', explode(',', $atts['columns']));
			
			// get a Custom Loop for the staff custom post type, and pass it to the template
			$staff_loop = $this->get_staff_members_loop($atts['count'],$atts['category'],$atts['id']);
			
			// $vars will be available in the template
			$vars = array('staff_loop' => $staff_loop);

			//single staff members use the single-staff-member-shortcode template, not the single-staff-member template
			//if you use single-staff-member template, you will get nested loops
			$templatePath = plugin_dir_path( __FILE__ ) . 'templates/single-staff-member-shortcode.php';
			
			$html = $this->render_template($templatePath, $vars);
		}
		
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
	// TBD: provide options to control how staff members are ordered
	private function get_staff_members_loop($count = -1, $taxonomy = false, $id = false)
	{
		//setup conditions based upon parameters
		//no id, no taxonomy passed
		if(!$taxonomy && !$id){
			$conditions = array('post_type' => 'staff-member',
								'post_count' => $count,
								'orderby' => 'meta_value',
								'meta_key' => '_ikcf_last_name',
								'order' => 'ASC',
			);
		//no taxonomy passed
		//id passed
		} elseif(!$taxonomy){			
			$conditions = array('post_type' => 'staff-member',
								'p' => $id
			);
		//no id passed
		//category passed
		} elseif(!$id){			
			$conditions = array('post_type' => 'staff-member',
								'post_count' => $count,
								'orderby' => 'meta_value',
								'meta_key' => '_ikcf_last_name',
								'order' => 'ASC',
								'tax_query' => array(
									array(
										'taxonomy' => 'staff-member-category',
										'field'    => 'slug',
										'terms'    => $taxonomy,
									),
								),
			);
		}
		
		
		return new WP_Query($conditions);
	}
	
	// check the reg key, and set $this->isPro to true/false reflecting whether the Pro version has been registered
	function verify_registration_key()
	{
        $this->options = get_option( 'sd_options' );
		if (isset($options['api_key']) && 
			isset($options['registration_email'])) {
		
				// check the key
				$keychecker = new S_D_KeyChecker();
				$correct_key = $keychecker->computeKeyEJ($options['registration_email']);
				if (strcmp($options['api_key'], $correct_key) == 0) {
					$this->proUser = true;
				} else {
					$this->proUser = false;
				}
		
		} else {
			// keys not set, so can't be valid.
			$this->proUser = false;
			
		}
		
		// look for the Pro plugin - this is also a way to be validated
		$plugin = "company-directory-pro/company-directory-pro.php";
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );			
		if(is_plugin_active($plugin)){
			$this->proUser = true;
		}
		
	}
	
	function is_pro() 
	{
		return $this->proUser;
	}

	//only do this once
	function rewrite_flush() {
		
		flush_rewrite_rules();
	}
	
	//this is the heading of the new column we're adding to the staff member posts list
	function column_head($defaults) {  
		$defaults = array_slice($defaults, 0, 2, true) +
		array("single_shortcode" => "Shortcode") +
		array_slice($defaults, 2, count($defaults)-2, true);
		return $defaults;  
	}  

	//this content is displayed in the staff member post list
	function columns_content($column_name, $post_ID) {  
		if ($column_name == 'single_shortcode') {  
			echo "<input type=\"text\" value=\"[staff_member id={$post_ID}]\" />";
		}  
	} 

	//this is the heading of the new column we're adding to the staff member category list
	function cat_column_head($defaults) {  
		$defaults = array_slice($defaults, 0, 2, true) +
		array("single_shortcode" => "Shortcode") +
		array_slice($defaults, 2, count($defaults)-2, true);
		return $defaults;  
	}  

	//this content is displayed in the staff member category list
	function cat_columns_content($value, $column_name, $tax_id) {  

		$category = get_term_by('id', $tax_id, 'staff-member-category');
		
		return "<input type=\"text\" value=\"[staff_list category='{$category->slug}']\" />"; 
	} 
	
	// Displays a meta box with the shortcodes to display the current Staff member
	function display_shortcodes_meta_box() {
		global $post;
		echo "Add this shortcode to any page where you'd like to <strong>display</strong> this Staff Member:<br />";
		echo "<textarea>[staff_member id=\"{$post->ID}\"]</textarea>";
	}//add Custom CSS
	
	function output_custom_css() {
		//use this to track if css has been output
		global $sd_footer_css_output;
		
		if($sd_footer_css_output){
			return;
		} else {
			$this->options = get_option( 'sd_options' );
			
			echo '<style type="text/css" media="screen">' . $this->options['custom_css'] . "</style>";
			$easy_t_footer_css_output = true;
		}
	}

	
}
$gp_sdp = new StaffDirectoryPlugin();