<?php
/*
Plugin Name: Company Directory
Plugin Script: staff-directory.php
Plugin URI: http://goldplugins.com/our-plugins/company-directory/
Description: Create a directory of your staff members and show it on your website!
Version: 1.4.4
Author: GoldPlugins
Author URI: http://goldplugins.com/
*/
require_once('gold-framework/plugin-base.php');
require_once('gold-framework/staff-directory-plugin.settings.page.class.php');
require_once('include/sd_kg.php');
require_once('include/lib/csv_importer.php');
require_once('include/lib/csv_exporter.php');

class StaffDirectoryPlugin extends StaffDirectory_GoldPlugin
{
	var $plugin_title = 'Company Directory';
	var $prefix = 'staff_dir';
	var $proUser = false;
	var $postType;
	var $customFields;
	
	function __construct()
	{	
		$this->setup_post_type_metadata();
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
		add_shortcode('search_staff_members', array($this, 'search_staff_members_shortcode'));
		add_action('init', array($this, 'remove_features_from_custom_post_type'));
				
		/* Allow the user to override the_content template for single staff members */
		add_filter('the_content', array($this, 'single_staff_content_filter'));
		
		// add our custom meta boxes
		add_action( 'admin_menu', array($this, 'add_meta_boxes'));
		
		//flush rewrite rules - only do this once!
		register_activation_hook( __FILE__, array($this, 'rewrite_flush' ) );
		
		$plugin = plugin_basename(__FILE__);
		add_filter( "plugin_action_links_{$plugin}", array($this, 'add_settings_link_to_plugin_action_links') );
		add_filter( 'plugin_row_meta', array($this, 'add_custom_links_to_plugin_description'), 10, 2 );	
				
		// catch CSV import/export trigger
		add_action('admin_init', array($this, 'process_import_export'));
		
		add_action( 'save_post', array( &$this, 'update_name_fields' ), 1, 2 );
		
		parent::add_hooks();
	}
	
	function setup_post_type_metadata()
	{
		$options = get_option( 'sd_options' );		
		$exclude_from_search = ( isset($options['include_in_search']) && $options['include_in_search'] == 0 );		
		$this->postType = array(
			'name' => 'Staff Member',
			'plural' => 'Staff Members',
			'slug' => 'staff-members',
			'exclude_from_search' => $exclude_from_search,
		);
		$this->customFields = array();
		$this->customFields[] = array('name' => 'first_name', 'title' => 'First Name', 'description' => 'Steven, Anna', 'type' => 'text');	
		$this->customFields[] = array('name' => 'last_name', 'title' => 'Last Name', 'description' => 'Example: Smith, Goldstein', 'type' => 'text');	
		$this->customFields[] = array('name' => 'title', 'title' => 'Title', 'description' => 'Example: Director of Sales, Customer Service Team Member, Project Manager', 'type' => 'text');	
		$this->customFields[] = array('name' => 'phone', 'title' => 'Phone', 'description' => 'Best phone number to reach this person', 'type' => 'text');
		$this->customFields[] = array('name' => 'email', 'title' => 'Email', 'description' => 'Email address for this person', 'type' => 'text');		
	}
	
	function create_post_types()
	{
		$this->add_custom_post_type($this->postType, $this->customFields);
		
		//adds single staff member shortcode to staff member list
		add_filter('manage_staff-member_posts_columns', array($this, 'column_head'), 10);  
		add_action('manage_staff-member_posts_custom_column', array($this, 'columns_content'), 10, 2); 
		
		//load list of current posts that have featured images	
		$supportedTypes = get_theme_support( 'post-thumbnails' );
		
		//none set, add them just to our type
		if( $supportedTypes === false ){
			add_theme_support( 'post-thumbnails', array( 'staff-member' ) );        
		}
		//specifics set, add our to the array
		elseif( is_array( $supportedTypes ) ){
			$supportedTypes[0][] = 'staff-member';
			add_theme_support( 'post-thumbnails', $supportedTypes[0] );
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
	}
	
	function single_staff_content_filter($content)
	{
		if ( is_single() && get_post_type() == 'staff-member' ) {
			global $staff_data;
			$staff_data = $this->get_staff_data_for_post();
			$template_content = $this->get_template_content('single-staff-member-content.php');
			return $template_content;
		}
		return $content;
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
			
			//load up the staff data for this ID
			global $staff_data;
			$staff_data = $this->get_staff_data_for_this_post($atts['id']);
			
			//build html using loaded data
			$template_content = $this->get_template_content('single-staff-member-content.php');
			
			$html = $template_content;
		}
		
		return $html;
	}		
	
	// returns a list of all staff members in the database, sorted by the title, ascending
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
	
	function normalize_truthy_value($input)
	{
		$input = strtolower($input);
		$truthy_values = array('yes', 'y', '1', 1, 'true', true);
		return in_array($input, $truthy_values);
	}
	
	function get_template_content($template_name, $default_content = '')
	{	
		$template_path = $this->get_template_path($template_name);
		if (file_exists($template_path)) {
			// load template by including it in an output buffer, so that variables and PHP will be run
			ob_start();
			include($template_path);
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
		// couldn't find a matching template file, so return the default content instead
		return $default_content;
	}
	
	function get_template_path($template_name)
	{
		// checks if the file exists in the theme first,
		// otherwise serve the file from the plugin
		if ( $theme_file = locate_template( array ( $template_name ) ) ) {
			$template_path = $theme_file;
		} else {
			$template_path = plugin_dir_path( __FILE__ ) . 'templates/' . $template_name;
		}
		return $template_path;
	}
	
	/* Loads the meta data for a given staff member (name, phone, email, title, etc) and returns it as an array */
	function get_staff_metadata($post_id)
	{
		$ret = array();
		$staff = get_post($post_id);
		$ret['ID'] = $staff->ID;
		$ret['full_name'] = $staff->post_title;
		$ret['content'] = $staff->post_content;
		$ret['phone'] = $this->get_option_value($staff->ID, 'phone','');
		$ret['email'] = $this->get_option_value($staff->ID, 'email','');
		$ret['title'] = $this->get_option_value($staff->ID, 'title','');
		$ret['first_name'] = $this->get_option_value($staff->ID, 'first_name','');
		$ret['last_name'] = $this->get_option_value($staff->ID, 'last_name','');
		
		return $ret;
	}
	
	//loads staff data for a specific post, when already inside a loop (such as viewing a single staff member)
	function get_staff_data_for_post()
	{
		global $post;
		$staff_data = $this->get_staff_metadata($post->ID);
		//do anything to the data needed here, before returning to template
		return $staff_data;
	}
	
	//loads staff data for a specific post, when passed an ID for that post
	function get_staff_data_for_this_post($id = false)
	{
		$staff_data = $this->get_staff_metadata($id);
		//do anything to the data needed here, before returning to template
		return $staff_data;
	}

	// returns a list of all staff members in the database, sorted by the title, ascending
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
								'nopaging' => true
			);
		//no taxonomy passed
		//id passed
		} elseif(!$taxonomy){			
			$conditions = array('post_type' => 'staff-member',
								'p' => $id,								
								'nopaging' => true
			);
		//no id passed
		//category passed
		} elseif(!$id){			
			$conditions = array('post_type' => 'staff-member',
								'post_count' => $count,
								'orderby' => 'meta_value',
								'meta_key' => '_ikcf_last_name',
								'order' => 'ASC',
								'nopaging' => true,
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
		if (isset($this->options['api_key']) && 
			isset($this->options['registration_email'])) {
				
				// check the key
				$keychecker = new S_D_KeyChecker();
				$correct_key = $keychecker->computeKeyEJ($this->options['registration_email']);
				if (strcmp($this->options['api_key'], $correct_key) == 0) {
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
	
	function is_pro(){
		return $this->proUser;
	}

	//only do this once
	function rewrite_flush() {		
		//we need to manually create the CPT right now, so that we have something to flush the rewrite rules with!
		$gpcpt = new GoldPlugins_StaffDirectory_CustomPostType($this->postType, $this->customFields);
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
	
	//add an inline link to the settings page, before the "deactivate" link
	function add_settings_link_to_plugin_action_links($links) { 
	  $settings_link = '<a href="admin.php?page=staff_dir-settings">Settings</a>';
	  array_unshift($links, $settings_link); 
	  return $links; 
	}

	// add inline links to our plugin's description area on the Plugins page
	function add_custom_links_to_plugin_description($links, $file) {

		/** Get the plugin file name for reference */
		$plugin_file = plugin_basename( __FILE__ );
	 
		/** Check if $plugin_file matches the passed $file name */
		if ( $file == $plugin_file )
		{
			$new_links['settings_link'] = '<a href="admin.php?page=staff_dir-settings">Settings</a>';
			$new_links['support_link'] = '<a href="https://goldplugins.com/contact/?utm-source=plugin_menu&utm_campaign=support&utm_banner=company-directory-plugin-menu" target="_blank">Get Support</a>';
				
			if(!$this->is_pro()){
				$new_links['upgrade_to_pro'] = '<a href="https://goldplugins.com/our-plugins/company-directory-pro/upgrade-to-company-directory-pro/?utm_source=plugin_menu&utm_campaign=upgrade" target="_blank">Upgrade to Pro</a>';
			}
			
			$links = array_merge( $links, $new_links);
		}
		return $links; 
	}
	
	/* Import / Export */
		
	/* Looks for a special POST value, and if its found, outputs a CSV of all Staff Members */
	function process_import_export()
	{
		// look for an Export command
		if ( isset($_POST['_company_dir_do_export']) && $_POST['_company_dir_do_export'] == '_company_dir_do_export' ) {
			$exporter = new StaffDirectoryPlugin_Exporter();
			$exporter->process_export();
			exit();
		}
		// look for an Import command
		else if (isset($_POST['_company_dir_do_import']) && $_POST['_company_dir_do_import'] == '_company_dir_do_import' && !empty($_FILES) ) {
			$importer = new StaffDirectoryPlugin_Importer($this);
			$this->import_result = $importer->process_import();
			if ( $this->import_result !== false ) {
				add_action( 'admin_notices', array( $this, 'display_import_notice' ) );
			}
		}
	}
	
	public function display_import_notice() {
		if ( $this->import_result['failed'] > 0 ) {
			$msg = sprintf("Successfully imported %d entries. %s entries rejected as duplicate.", $this->import_result['imported'], $this->import_result['failed']);
			printf ("<div class='updated'><p>%s</p></div>", $msg);
		}
		else {
			$msg = sprintf("Successfully imported %d entries.", $this->import_result['imported']);
			printf ("<div class='updated'><p>%s</p></div>", $msg);
		}
	}

	function search_staff_members_shortcode()
	{
		add_filter('get_search_form', array($this, 'restrict_search_to_custom_post_type'), 10);
		$search_html = get_search_form();
		remove_filter('get_search_form', array($this, 'restrict_search_to_custom_post_type'));
		return $search_html;
	}
	
	function restrict_search_to_custom_post_type($search_html)
	{
		$post_type = 'staff-member';
		$hidden_input = sprintf('<input type="hidden" name="post_type" value="%s">', $post_type);
		$replace_with = $hidden_input . '</form>';
		return str_replace('</form>', $replace_with, $search_html);
	}
	
	/* If the user did not specify a first and/or last name field, set those fields now */
	function update_name_fields($post_id, $post)
	{
		if ($post->post_type !== 'staff-member') {
			return;
		}
		
		$first_name = get_post_meta($post_id, '_ikcf_first_name', true);
		$last_name = get_post_meta($post_id, '_ikcf_last_name', true);
		$full_name = get_the_title($post_id);
		
		if (empty($first_name)) {
			$f_pos = strpos($full_name, ' ');			
			$f_name = ($f_pos !== FALSE) ? substr($full_name, 0, $f_pos) : $full_name;
			update_post_meta($post_id, '_ikcf_first_name', $f_name);
		}

		if (empty($last_name)) {
			$l_pos = strrpos($full_name, ' ');			
			$l_name = ($f_pos !== FALSE) ? substr($full_name, $l_pos + 1) : $full_name;
			update_post_meta($post_id, '_ikcf_last_name', $l_name);
		}
	}

	
}
$gp_sdp = new StaffDirectoryPlugin();