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
		add_action('admin_head', array($this, 'output_admin_styles'));
    }

    public function add_menus()
	{
/* 		add_menu_page( 
			$this->plugin_title . ' Settings',
			$this->plugin_title, 
			$this->root->prefix . '-settings',
			'manage_options',
			array( $this, 'output_settings_page' )
		); */
		// Because we want the main menu to be called "Before & After", but the first menu to be called "Settings", we'll need to override the title now by creating a duplicate menu with the correct title ("Settings")
		add_submenu_page('edit.php?post_type=staff-member', $this->root->plugin_title . ' Settings', 'Settings', 'manage_options', $this->root->prefix . '-settings', array( $this, 'output_settings_page' ) );	
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
		
		//registration settings
		 add_settings_section(
            'registration', // ID
            'Upgrade to Staff Directory Pro to Unlock Additional Features and Support!', // Title
            array( $this, 'print_registration_section_info' ), // Callback
            'sd_registration_settings' // Page
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
		//die($id);
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
		// save settings if needed
		if (isset($_POST["update_settings"]))
		{
			// save registration keys if provided
			$reg_keys = array('sd_api_key', 'sd_registration_email');
			foreach($reg_keys as $name) {
				if (isset($_POST[$name])) {
					$val = esc_attr($_POST[$name]);
					update_option($name, $val);
				}
			}			
		}
	
		// Set class property
        $this->options = get_option( 'sd_options' );
        ?>		
		<?php //$this->output_register_plugin_style(); ?>			
        <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <h2><?php echo htmlentities($this->plugin_title)?> Settings</h2>           			
			<form method="post" action="">
				<?php
					// This prints out all hidden setting fields
					settings_fields( 'sd_option_group' );
				?>
				<?php if (!	$this->root->is_pro()):?>
					<div class="sd_registration_settings register_plugin">
					<?php do_settings_sections( 'sd_registration_settings' ); ?>
					<?php submit_button(); ?>			
					</div>
				<? else: ?>
					<div class="register_plugin is_registered">
						<h3>Staff Directory Pro Activated</h3>
						<p><strong>This copy of Staff Directory Pro is registered to <a href="mailto:<?php echo $this->options['registration_email']; ?>"><?php echo htmlentities($this->options['registration_email']); ?></a> for <a href="//<?php echo $this->options['registration_url']; ?>" target="_blank"><?php echo htmlentities($this->options['registration_url']); ?></a>.</strong></p>
						<?php $this->output_hidden_registration_fields(); ?>
					</div>
				<?php endif; ?>
				<?php
					// Output each registered settings group
					foreach ($this->registered_sections as $registered_section) {
						do_settings_sections( $registered_section );
					}
					
					// output the "Save Settings" button at the end
					submit_button();
				?>
				
            </form>
			<?php if ( !$this->root->is_pro() ) { $this->output_mailing_list_form(); } ?>
        </div>		
        <?php
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
    public function print_registration_section_info()
    {
		echo '<p><em><a href="http://goldplugins.com/our-plugins/staff-directory/?utm_source=b_a_plugin&utm_campaign=upgrade&is_pro=0" target="_blank">Click here to purchase Staff Directory Pro.</a> You will receive your API keys by email as soon as you purchase.</a></em></p>';
		print '<strong>Enter your registration information below to enable Staff Directory Pro:</strong>';
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
	
	function output_mailing_list_form()
	{
?>
		<!-- Begin MailChimp Signup Form -->
		<style type="text/css">
			/* MailChimp Form Embed Code - Slim - 08/17/2011 */
			#mc_embed_signup form {display:block; position:relative; text-align:left; padding:10px 0 10px 3%}
			#mc_embed_signup h2 {font-weight:bold; padding:0; margin:15px 0; font-size:1.4em;}
			#mc_embed_signup input {border:1px solid #999; -webkit-appearance:none;}
			#mc_embed_signup input[type=checkbox]{-webkit-appearance:checkbox;}
			#mc_embed_signup input[type=radio]{-webkit-appearance:radio;}
			#mc_embed_signup input:focus {border-color:#333;}
			#mc_embed_signup .button {clear:both; background-color: #aaa; border: 0 none; border-radius:4px; color: #FFFFFF; cursor: pointer; display: inline-block; font-size:15px; font-weight: bold; height: 32px; line-height: 32px; margin: 0 5px 10px 0; padding:0; text-align: center; text-decoration: none; vertical-align: top; white-space: nowrap; width: auto;}
			#mc_embed_signup .button:hover {background-color:#777;}
			#mc_embed_signup .small-meta {font-size: 11px;}
			#mc_embed_signup .nowrap {white-space:nowrap;}     
			#mc_embed_signup .clear {clear:none; display:inline;}

			#mc_embed_signup h3 { color: #008000; display:block; font-size:19px; padding-bottom:10px; font-weight:bold; margin: 0 0 10px;}
			#mc_embed_signup .explain {
				color: #808080;
				width: 600px;
			}
			#mc_embed_signup label {
				color: #000000;
				display: block;
				font-size: 15px;
				font-weight: bold;
				padding-bottom: 10px;
			}
			#mc_embed_signup input.email {display:block; padding:8px 0; margin:0 4% 10px 0; text-indent:5px; width:58%; min-width:130px;}

			#mc_embed_signup div#mce-responses {float:left; top:-1.4em; padding:0em .5em 0em .5em; overflow:hidden; width:90%;margin: 0 5%; clear: both;}
			#mc_embed_signup div.response {margin:1em 0; padding:1em .5em .5em 0; font-weight:bold; float:left; top:-1.5em; z-index:1; width:80%;}
			#mc_embed_signup #mce-error-response {display:none;}
			#mc_embed_signup #mce-success-response {color:#529214; display:none;}
			#mc_embed_signup label.error {display:block; float:none; width:auto; margin-left:1.05em; text-align:left; padding:.5em 0;}		
			#mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif; }
				#mc_embed_signup{    
						background-color: white;
						border: 1px solid #DCDCDC;
						clear: left;
						color: #008000;
						font: 14px Helvetica,Arial,sans-serif;
						margin-top: 10px;
						margin-bottom: 0px;
						max-width: 800px;
						padding: 5px 12px 0px;
			}
			#mc_embed_signup form{padding: 10px}

			#mc_embed_signup .special-offer {
				color: #808080;
				margin: 0;
				padding: 0 0 3px;
				text-transform: uppercase;
			}
			#mc_embed_signup .button {
			  background: #5dd934;
			  background-image: -webkit-linear-gradient(top, #5dd934, #549e18);
			  background-image: -moz-linear-gradient(top, #5dd934, #549e18);
			  background-image: -ms-linear-gradient(top, #5dd934, #549e18);
			  background-image: -o-linear-gradient(top, #5dd934, #549e18);
			  background-image: linear-gradient(to bottom, #5dd934, #549e18);
			  -webkit-border-radius: 5;
			  -moz-border-radius: 5;
			  border-radius: 5px;
			  font-family: Arial;
			  color: #ffffff;
			  font-size: 20px;
			  padding: 10px 20px 10px 20px;
			  line-height: 1.5;
			  height: auto;
			  margin-top: 7px;
			  text-decoration: none;
			}

			#mc_embed_signup .button:hover {
			  background: #65e831;
			  background-image: -webkit-linear-gradient(top, #65e831, #5dd934);
			  background-image: -moz-linear-gradient(top, #65e831, #5dd934);
			  background-image: -ms-linear-gradient(top, #65e831, #5dd934);
			  background-image: -o-linear-gradient(top, #65e831, #5dd934);
			  background-image: linear-gradient(to bottom, #65e831, #5dd934);
			  text-decoration: none;
			}
			#signup_wrapper {
				max-width: 800px;
				margin-bottom: 20px;
				margin-top: 30px;
			}
			#signup_wrapper .u_to_p
			{
				font-size: 10px;
				margin: 0;
				padding: 2px 0 0 3px;				
			}
		</style>
		<div id="signup_wrapper">
			<div id="mc_embed_signup">
				<form action="http://illuminatikarate.us2.list-manage2.com/subscribe/post?u=403e206455845b3b4bd0c08dc&amp;id=934e059cff" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
					<p class="special-offer">Special Offer:</p>
					<h3>Sign-up for our newsletter now, and we'll give you a discount on Staff Directory Pro!</h3>
					<label for="mce-EMAIL">Your Email:</label>
					<input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
					<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
					<div style="position: absolute; left: -5000px;"><input type="text" name="b_403e206455845b3b4bd0c08dc_934e059cff" tabindex="-1" value=""></div>
					<div class="clear"><input type="submit" value="Subscribe Now" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
					<p class="explain"><strong>What To Expect:</strong> You'll receive you around one email from us each month, jam-packed with special offers and tips for getting the most out of WordPress. Of course, you can unsubscribe at any time.</p>
				</form>
			</div>
			<p class="u_to_p"><a href="http://goldplugins.com/our-plugins/before-and-after/?utm_source=plugin&utm_campaign=upgrade_small">Upgrade to Staff Directory Pro now</a> to remove banners like this one.</p>
		</div>
		<!--End mc_embed_signup-->
<?php	
	}
	
	function output_admin_styles()
	{
		?>
		<style>
			.register_plugin {
				border: 1px solid green;
				background-color: lightyellow;
				padding: 25px;
				width: 750px;
				margin-top: 10px;
			}
			.register_plugin.is_registered {
				background-color: #EEFFF7;
				padding: 10px 16px 0;
			}
			.register_plugin h3 {
				padding-top: 0;
				margin-top: 0;
			}
			.register_plugin .field {
				padding-bottom: 10px;
			}
			.register_plugin .submit {
				padding-top: 10px;
				margin: 0;
			}
			.register_plugin label {
				display: block;
			}
			.register_plugin input[type="text"] {
				width: 350px;
			}
			/* Add/Edit page */
			.sd_options input[type="radio"] {
				float: left;
				margin: 3px 5px 0 0;
			}
			.sd_options .secondary-option {
				padding: 10px 0 10px 20px;
			}			
		</style>
		<?php
	}
}