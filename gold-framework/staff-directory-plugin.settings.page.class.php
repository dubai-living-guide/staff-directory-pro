<?php
class StaffDirectoryPlugin_SettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
	private $plugin_title;
	private $root;
	private $settings;
	private $registered_sections = array();
	
	

    /**
     * Start up
     */
    public function __construct($root)
    {
		$this->root = $root;
		$this->plugin_title = $root->plugin_title;
        add_action( 'admin_init', array( $this, 'create_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_menus' ), 10 );		   
		
		//add stylesheet for admin
		add_action( 'admin_init', array($this,'admin_init') );
    }
	
	public function add_menus(){
		$title = $this->plugin_title . " Settings";
		$page_title = $this->plugin_title . " Settings";
		$top_level_slug = $this->root->prefix . "-settings";
		
		//create new top-level menu
		add_menu_page($page_title, $title, 'administrator', $top_level_slug, array($this, 'output_settings_page'));
		add_submenu_page($top_level_slug , 'Basic Options', 'Basic Options', 'administrator', $top_level_slug, array($this, 'output_settings_page'));
		add_submenu_page($top_level_slug , 'Help & Instructions', 'Help & Instructions', 'administrator', 'company-directory-help', array($this, 'help_settings_page'));
	} 
	
    public function add_settings_group($group, $key, $display, $type = 'text')
	{
	
	}

    /**
     * Register and add settings
     */
    public function create_settings()
    {        	      	
		// Generic setting. We need this for some reason so that we have a chance to save everything else.
        register_setting(
            'sd_option_group', // Option group
            'sd_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );
		
		//general settings
		 add_settings_section(
            'general', // ID
            'Basic Options', // Title
            array( $this, 'print_general_section_info' ), // Callback
            'sd_general_settings' // Page
        );    
		
		//need to do this so these are output after the registration info
		$this->registered_sections[] = 'sd_general_settings';
		
		//we don't need to add this to the registered sections array as these options are directly called
		//registration settings
		 add_settings_section(
            'registration', // ID
            'Pro Registration', // Title
            array( $this, 'print_registration_section_info' ), // Callback
            'sd_registration_settings' // Page
        );  
		
        add_settings_field(
            'sd_custom_css', // ID
            'Custom CSS', // Title 
            array( $this, 'custom_css_callback' ), // Callback
            'sd_general_settings', // Page
            'general' // Section           
        );

		add_settings_field(
            'sd_templates_detected', // ID
            'Custom Templates', // Title 
            array( $this, 'custom_templates_callback' ), // Callback
            'sd_general_settings', // Page
            'general' // Section           
        );  

        add_settings_field(
            'sd_registration_email', // ID
            'Email', // Title 
            array( $this, 'registration_email_callback' ), // Callback
            'sd_registration_settings', // Page
            'registration' // Section           
        );       
        add_settings_field(
            'sd_api_key', // ID
            'API Key', // Title 
            array( $this, 'api_key_callback' ), // Callback
            'sd_registration_settings', // Page
            'registration' // Section           
        );      

		
		/*
		$colors = array('red' => 'Red', 
						'green' => 'Green', 
						'blue' => 'Blue', 
					);


		$this->create_settings_section('general', 'General Settings', 'General settings go here.');
		$this->add_setting('general', 'first_name', 'First Name', 'text');
		$this->add_setting('general', 'last_name', 'Last Name', 'text');

		$this->create_settings_section('additional', 'Additional Settings', 'Additional settings go here.');
		$this->add_setting('additional', 'bio', 'Bio', 'textarea');
		$this->add_setting('additional', 'favorite_color', 'Favorite Color', 'select', array('options' => $colors));
		*/
    }

	/*
	 * Adds a new plugin settings section. 
	 */
    public function create_settings_section($section, $title, $description = '')
	{
		$page_key = $this->root->prefix . $section . '_settings';
		
		// Register the $section if we haven't seen it before
		if ( !in_array($page_key, $this->registered_sections) )
		{
			add_settings_section(
				$section, // ID
				$title, // Title
				array( $this, 'print_section_description' ), // Callback
				$page_key // Page
			); 
			$this->section_metadata[$section] = array('title' => $title,
													  'description' => $description);			
			$this->registered_sections[] = $page_key;
		}
	}
	
	/*
	 * Adds a new plugin setting. 
	 * Note: From here, the setting is expected to "just work", meaning the framework will handle everything else (e.g., providing inputs on the settings screen)
	 */
    public function add_setting($section, $id, $title, $type = 'text', $extras = array())
	{
		$id= $this->root->prefix . '_' . $id;
		// Prepare an array of params to pass to the callback function
		$args = $extras;
		$args['id']= $id;
		$args['title']= $title;
		$args['type']= $type;
		$args['value']= ''; // TODO: should this be a default? the current value (as pulled from the database?)

		// Register the setting with WordPress
        add_settings_field(
            $id, // ID :: This is specified by $id param
            $title, // Title :: This is specified by $title param 
            array( $this, 'output_setting_field' ), // Callback, a generic function
            $this->root->prefix . $section . '_settings', // Page:: Will probably be the same for all settings. Maybe optional? Either way, use a $root->prefix instead of b_a_
            $section,
			$args
        );   
		
		/** The Plan
		 *
		 *  1) Replace "Callback" (3rd param) with a generic function, output_setting_field
		 *  2) Output_setting_field would look up the type of field, and any other meta, by the $key (hoping we can glean this from what is passed from the WP hook)
		       Note: we will store any metadata we need to in the private variables, as we cannot pass anything directly
			3)
		 */	     
	}	
	
	function output_setting_field($args)
	{	
		$defaults = array('id' => '',
						  'value' => '',
						  'class' => '',
						  'options' => array(),
						);
		$args = array_merge($defaults, $args);
		
		switch($args['type'])
		{
			
			default:
			case 'text':
				$output = '<input id="' . $args['id'] . '" value="' . htmlentities($args['value']) . '" class="regular-text ' . $args['class'] . '" />';
				break;

			case 'textarea':
				$output = '<textarea id="' . $args['id'] . '" class="large-text ' . $args['class'] . '" />' . htmlentities($args['value']) . '</textarea>';
				break;

			case 'select':
				$output = '<select id="' . $args['id'] . '" class="' . $args['class'] . '">' . htmlentities($args['value']);
				foreach($args['options'] as $option_value => $display) {
					if ( strlen($args['value']) > 0 && strcmp($args['value'], $option_value) == 0 ) {
						// this is the current value, so add the "selected" attribute
						$output .= '<option value="' . $option_value . '" selected="selected">' . $display . '</option>';
					} else {					
						$output .= '<option value="' . $option_value . '">' . $display . '</option>';
					}
				}
				$output .= '</select>';
				break;

			case 'checkbox':
				/* TODO: checkboxes */
				break;

			case 'radio':
				/* TODO: radio buttons */
				break;

			case 'font':
				/* TODO: font inputs */
				break;
		}	
		
		// TODO: add a hookable filter?
		
		echo $output;
	}
	
    /**
     * Options page callback
     */
    public function output_settings_page()
    {	
		// Set class property
        $this->options = get_option( 'sd_options' );
		
		$this->settings_page_top();
        ?>		
            <div id="icon-options-general" class="icon32"></div>         			
			<form method="post" action="options.php">
				<?php
					// This prints out all hidden setting fields
					settings_fields( 'sd_option_group' );
				?>	
				<?php if (!	$this->root->is_pro()):?>
					<p class="plugin_is_not_registered">✘ Your plugin is not registered and activated. You will not be able to use the PRO features until you upgrade. <a href="http://goldplugins.com/our-plugins/company-directory/upgrade-to-company-directory-pro/?utm_source=registration_fields" target="_blank">Click here</a> to upgrade today!</p>
					<div class="sd_registration_settings register_plugin">
					<?php do_settings_sections( 'sd_registration_settings' ); ?>
					<?php submit_button(); ?>			
					</div>
				<?php else: ?>
					<div class="register_plugin is_registered">
						<h3>Pro Registration</h3>
						<p class="plugin_is_registered">✓ Company Directory Pro is registered and activated. Thank you!</p>
						<?php $this->output_hidden_registration_fields(); ?>
					</div>
				<?php endif; ?>
				<?php
					// Output each registered settings group
					if(count($this->registered_sections) > 0){
						foreach ($this->registered_sections as $registered_section) {
							do_settings_sections( $registered_section );
						}						
						// output the "Save Settings" button at the end
						submit_button();
					}
				?>
            </form>
			<?php $this->output_import_export_settings(); ?>

        </div>		
        <?php
    }
	
	function output_import_export_settings()
	{
		?><h3>Import / Export Staff Members from CSV</h3>
		<?php if($this->root->is_pro()): ?>	
		<form method="POST" action="" enctype="multipart/form-data">					
			<fieldset>
				<legend>Import Staff Members</legend>
				<?php 
					//CSV Importer
					StaffDirectoryPlugin_Importer::output_form();
				?>
			</fieldset>
			<fieldset>
				<legend>Export Staff Members</legend>
				<?php 
					//CSV Exporter
					StaffDirectoryPlugin_Exporter::output_form();
				?>
			</fieldset>
		</form>
		<?php else: ?>
		<form method="POST" action="" enctype="multipart/form-data">					
			<fieldset>
				<legend>Import Staff Members</legend>
				<p class="easy_testimonials_not_registered"><strong>This feature requires Company Directory Pro.</strong>&nbsp;&nbsp;&nbsp;<a class="button" target="blank" href="https://goldplugins.com/our-plugins/company-directory-pro/upgrade-to-company-directory-pro/?utm_campaign=upgrade&utm_source=plugin&utm_banner=import_upgrade">Upgrade Now</a></p>
			</fieldset>
			<fieldset>
				<legend>Export Staff Members</legend>
				<p class="easy_testimonials_not_registered"><strong>This feature requires Company Directory Pro.</strong>&nbsp;&nbsp;&nbsp;<a class="button" target="blank" href="https://goldplugins.com/our-plugins/company-directory-pro/upgrade-to-company-directory-pro/?utm_campaign=upgrade&utm_source=plugin&utm_banner=export_upgrade">Upgrade Now</a></p>	
			</fieldset>
		</form>
		<?php endif;
	}

	//help page / documentation
	function help_settings_page(){		
		$this->settings_page_top();
		
		include(plugin_dir_path( __FILE__ ) . '../assets/pages/help.html');
	}	

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
		foreach($input as $key => $value)
		{
			switch($key)
			{
				case 'id_number':
					$new_input['id_number'] = absint( $input['id_number'] );
				break;

				case 'email':
				case 'subject':
				case 'email_body':
				case 'api_key':
				case 'registration_url':
				case 'registration_email':
					$new_input[$key] = sanitize_text_field( $input[$key] );
				break;			
				
				case 'custom_css':
					$new_input[$key] = $input[$key]; //TBD: figure out proper sanitizing for CSS!
				break;

				default: // don't let any settings through unless they were whitelisted. (skip unknown settings)
					continue;
				break;			
			}
		}
		
        return $new_input;
    }

    /** 
     * Print the description for the given section
     */
    public function print_section_description($args)
    {
		$section = $args['id'];
		$meta = isset($this->section_metadata[$section]) ? $this->section_metadata[$section] : array();
		$desc = isset($meta['description']) ? $meta['description'] : '';
		echo $desc;
    }
	
    /** 
     * Print the Section text
     */	
    public function print_general_section_info()
    {
		echo '<p>The below options can be used to control various bits of output by the plugin.</p>';
    }
	
    public function custom_css_callback()
    {
        printf(
            '<textarea id="custom_css" name="sd_options[custom_css]" style="width:450px" />%s</textarea>',
            isset( $this->options['custom_css'] ) ? esc_attr( $this->options['custom_css']) : ''
        );
    }
	
    public function custom_templates_callback()
    {
		$tpl_path = locate_template('single-staff-member-content.php');
		if (strlen($tpl_path) > 1) {
			printf(
				'<p><strong>Single Staff Member: Custom template detected!</strong></p><p>The template file single-staff-member-content.php, located in your current theme\'s folder, will be used to display each staff member\'s single view.</p>'
				);
			
		}
		else {
			printf(
				'No custom templates detected.'
				);
		}
    }
	
    public function print_registration_section_info()
    {
		echo '<p>Fill out the fields below, if you have purchased the pro version of the plugin, to activate additional features such as the Table or Grid layouts.</p>';
    }
		
    public function api_key_callback()
    {
        printf(
            '<input type="text" id="api_key" name="sd_options[api_key]" value="%s" style="width:450px" />',
            isset( $this->options['api_key'] ) ? esc_attr( $this->options['api_key']) : ''
        );
    }	
    public function registration_email_callback()
    {
        printf(
            '<input type="text" id="registration_email" name="sd_options[registration_email]" value="%s" style="width:450px" />',
            isset( $this->options['registration_email'] ) ? esc_attr( $this->options['registration_email']) : ''
        );
    }
    public function registration_url_callback()
    {
        printf(
            '<input type="text" id="registration_url" name="sd_options[registration_url]" value="%s" style="width:450px" />',
            isset( $this->options['registration_url'] ) ? esc_attr( $this->options['registration_url']) : ''
        );
    }

	function output_hidden_registration_fields()
	{
		$fields = array('api_key', 'registration_url', 'registration_email');
		foreach($fields as $field) {
			$val = isset( $this->options[$field] ) ? esc_attr( $this->options[$field]) : '';
			printf(
				'<input type="hidden" name="sd_options[' . $field . ']" value="%s" />',
				$val
			);
		}
	}
	
	function settings_page_top(){
		$title = "Company Directory Settings";
		$message = "Company Directory Settings Updated.";
		
		global $pagenow;
		global $current_user;
		get_currentuserinfo();
		?>
		<script type="text/javascript">
		jQuery(function () {
			if (typeof(gold_plugins_init_coupon_box) == 'function') {
				gold_plugins_init_coupon_box();
			}
		});
		</script>
		<?php if ($this->root->is_pro()):?>
		<div class="wrap staff_directory_admin_wrap">
		<?php else: ?>
		<div class="wrap staff_directory_admin_wrap not-pro">
		<?php endif; ?>
		<h2><?php echo $title; ?></h2>
		<style type="text/css">			
			fieldset {
				border: 1px solid #ccc !important;
				display: block;
				margin: 20px 0 !important;
				padding: 0 20px !important;
			}
			
			fieldset legend{
				font-size: 18px;
				font-weight: bold;
			}
		</style>
		<?php if (!	$this->root->is_pro()):?>
				<div id="signup_wrapper">
					<?php $this->output_sidebar_coupon_form(); ?>
					<p class="u_to_p"><a href="http://goldplugins.com/our-plugins/company-directory/upgrade-to-company-directory-pro/?utm_source=themes">Upgrade to Company Directory Pro now</a> to remove banners like this one.</p>				
				</div>
				
		<?php endif; ?>
		
		<?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') : ?>
		<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
		<?php endif;
	}
	
	function output_sidebar_coupon_form()
	{
		global $current_user;
		?>
		<div class="topper">
			<h3>Save 20% on Company Directory Pro!</h3>
			<p class="pitch">Sign-up for our newsletter, and we’ll send you a coupon for 20% off your upgrade to Company Directory Pro!</p>
		</div>
		<div id="mc_embed_signup">
			<form action="http://goldplugins.com/atm/atm.php?u=403e206455845b3b4bd0c08dc&amp;id=a70177def0&amp;plug=sdpro" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
				<div class="fields_wrapper">
					<label for="mce-NAME">Your Name:</label>
					<input type="text" value="<?php echo (!empty($current_user->display_name) ?  $current_user->display_name : ''); ?>" name="NAME" class="name" id="mce-NAME" placeholder="Your Name">
					<label for="mce-EMAIL">Your Email:</label>
					<input type="email" value="<?php echo (!empty($current_user->user_email) ?  $current_user->user_email : ''); ?>" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
					<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
					<div style="position: absolute; left: -5000px;"><input type="text" name="b_403e206455845b3b4bd0c08dc_6ad78db648" tabindex="-1" value=""></div>
				</div>
				<div class="clear"><input type="submit" value="Send Me The Coupon Now" name="subscribe" id="mc-embedded-subscribe" class="smallBlueButton"></div>
				<p class="secure"><img src="<?php echo plugins_url( '../assets/img/lock.png', __FILE__ ); ?>" alt="Lock" width="16px" height="16px" />We respect your privacy.</p>
				
				<input type="hidden" name="PRODUCT" value="Company Directory Pro" />
				<input type="hidden" id="mc-upgrade-plugin-name" value="Company Directory Pro"" />
				<input type="hidden" id="mc-upgrade-link-per" value="http://goldplugins.com/purchase/company-directory-pro/single?promo=newsub20" />
				<input type="hidden" id="mc-upgrade-link-biz" value="http://goldplugins.com/purchase/company-directory-pro/business?promo=newsub20" />
				<input type="hidden" id="mc-upgrade-link-dev" value="http://goldplugins.com/purchase/company-directory-pro/developer?promo=newsub20" />

				<div class="features">
					<strong>When you upgrade, you'll instantly unlock:</strong>
					<ul>
						<li>Table Style Layout</li>
						<li>Grid Style Layout</li>
						<li>Outstanding support from our developers</li>
						<li>Remove all banners from the admin area</li>
						<li>And more! We add new features regularly.</li>
					</ul>
				</div>
				<input type="hidden" id="gold_plugins_already_subscribed" name="gold_plugins_already_subscribed" value="<?php echo get_user_setting ('_gp_ml_has_subscribed‏', '0'); ?>" />
			</form>
		</div>			
		<?php			
	}

	function admin_init()
	{
		wp_register_style( 'staff_directory_admin_stylesheet', plugins_url('../assets/css/admin_style.css', __FILE__) );
		wp_enqueue_style( 'staff_directory_admin_stylesheet' );		
		wp_enqueue_script(
			'company-directory-admin',
			plugins_url('../assets/js/staff-directory-admin.js', __FILE__),
			array( 'jquery' ),
			false,
			true
		); 
		wp_enqueue_script(
			'gp-admin_v2',
			plugins_url('../assets/js/gp-admin_v2.js', __FILE__),
			array( 'jquery' ),
			false,
			true
		);	
	}
}