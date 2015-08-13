<?php
/*
This file is part of Company Directory.

Company Directory is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Company Directory is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Company Directory.  If not, see <http://www.gnu.org/licenses/>.

*/

class GP_Staff_List_Widget extends WP_Widget
{
	function __construct()
	{
		$widget_ops = array(
			'classname' => 'GP_Staff_List_Widget GP_Staff_List_Widget_Compact',
			'description' => 'Displays a list of your staff.'
		);
		parent::__construct('GP_Staff_List_Widget', 'Staff List', $widget_ops);
	}

	function form($instance){
		$instance = wp_parse_args( 
			(array) $instance, 
			array( 	'title' => '',
					'use_excerpt' => 0,
					'count' => 1,
					'category' => '',
					'style' => true,
					'show_name' => true,
					'show_title' => true,
					'show_bio' => true,
					'show_photo' => true,
					'show_email' => true,
					'show_address' => true,
					'show_website' => true,
					) 
		);
		
		$title = !empty($instance['title']) ? $instance['title'] : 'Our Staff';
		$category = !empty($instance['category']) ? $instance['category'] : '';
		$style = !empty($instance['style']) ? $instance['style'] : '';
		$show_name = isset($instance['show_name']) ? $instance['show_name'] : true;
		$show_title = isset($instance['show_title']) ? $instance['show_title'] : true;
		$show_bio = isset($instance['show_bio']) ? $instance['show_bio'] : true;
		$show_photo = isset($instance['show_photo']) ? $instance['show_photo'] : true;
		$show_email = isset($instance['show_email']) ? $instance['show_email'] : true;
		$show_phone = isset($instance['show_phone']) ? $instance['show_phone'] : true;
		$show_address = isset($instance['show_address']) ? $instance['show_address'] : true;
		$show_website = isset($instance['show_website']) ? $instance['show_website'] : true;
				
		$staff_categories = get_terms( 'staff-member-category', 'orderby=title&hide_empty=0' );
		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label><br />
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('category'); ?>">Category:</label><br />
				<select name="<?php echo $this->get_field_name('category'); ?>" id="<?php echo $this->get_field_id('category'); ?>">
					<option value="" <?php if(esc_attr($category) == ""): echo 'selected="SELECTED"'; endif; ?>>All Categories</option>
					<?php foreach($staff_categories as $cat):?>
						<option value="<?php echo $cat->slug; ?>" <?php if(esc_attr($category) == $cat->slug): echo 'selected="SELECTED"'; endif; ?>><?php echo htmlentities($cat->name); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('style'); ?>">Style:</label><br />
				<select name="<?php echo $this->get_field_name('style'); ?>" id="<?php echo $this->get_field_id('style'); ?>">
					<option value="list" <?php if(esc_attr($style) == "list"): echo 'selected="SELECTED"'; endif; ?>>List View</option>
					<?php if ($this->is_pro()): ?>
					<option value="grid" <?php if(esc_attr($style) == "grid"): echo 'selected="SELECTED"'; endif; ?>>Grid View</option>
					<option value="table" <?php if(esc_attr($style) == "table"): echo 'selected="SELECTED"'; endif; ?>>Table View</option>
					<?php else: ?>
					<option disabled="true" value="grid" <?php if(esc_attr($style) == "grid"): echo 'selected="SELECTED"'; endif; ?>>Grid View (Requires PRO)</option>
					<option disabled="true" value="table" <?php if(esc_attr($style) == "table"): echo 'selected="SELECTED"'; endif; ?>>Table View (Requires PRO)</option>
					<?php endif; ?>
				</select>
			</p>	
			<fieldset class="gp_admin_widget_fieldset">
				<legend><strong>Fields To Display</strong></legend>
				<p>					
					<label for="<?php echo $this->get_field_id('show_name'); ?>">
						<input name="<?php echo $this->get_field_name('show_name'); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo $this->get_field_id('show_name'); ?>" name="<?php echo $this->get_field_name('show_name'); ?>" type="checkbox" value="1" <?php if($show_name){ ?>checked="CHECKED"<?php } ?>/>
						Name
					</label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('show_title'); ?>">
						<input name="<?php echo $this->get_field_name('show_title'); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" type="checkbox" value="1" <?php if($show_title){ ?>checked="CHECKED"<?php } ?>/>
						Title
					</label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('show_bio'); ?>">
						<input name="<?php echo $this->get_field_name('show_bio'); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo $this->get_field_id('show_bio'); ?>" name="<?php echo $this->get_field_name('show_bio'); ?>" type="checkbox" value="1" <?php if($show_bio){ ?>checked="CHECKED"<?php } ?>/>
						Bio
					</label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('show_photo'); ?>">
						<input name="<?php echo $this->get_field_name('show_photo'); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo $this->get_field_id('show_photo'); ?>" name="<?php echo $this->get_field_name('show_photo'); ?>" type="checkbox" value="1" <?php if($show_photo){ ?>checked="CHECKED"<?php } ?>/>
						Photo
					</label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('show_email'); ?>">
						<input name="<?php echo $this->get_field_name('show_email'); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo $this->get_field_id('show_email'); ?>" name="<?php echo $this->get_field_name('show_email'); ?>" type="checkbox" value="1" <?php if($show_email){ ?>checked="CHECKED"<?php } ?>/>
						Email
					</label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('show_phone'); ?>">
						<input name="<?php echo $this->get_field_name('show_phone'); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo $this->get_field_id('show_phone'); ?>" name="<?php echo $this->get_field_name('show_phone'); ?>" type="checkbox" value="1" <?php if($show_phone){ ?>checked="CHECKED"<?php } ?>/>
						Phone
					</label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('show_address'); ?>">
						<input name="<?php echo $this->get_field_name('show_address'); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo $this->get_field_id('show_address'); ?>" name="<?php echo $this->get_field_name('show_address'); ?>" type="checkbox" value="1" <?php if($show_address){ ?>checked="CHECKED"<?php } ?>/>
						Address
					</label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('show_website'); ?>">
						<input name="<?php echo $this->get_field_name('show_website'); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo $this->get_field_id('show_website'); ?>" name="<?php echo $this->get_field_name('show_website'); ?>" type="checkbox" value="1" <?php if($show_website){ ?>checked="CHECKED"<?php } ?>/>
						Website
					</label>
				</p>			
			</fieldset>
					
		<?php
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['count'] = $new_instance['count'];
		$instance['category'] = $new_instance['category'];
		$instance['style'] = $new_instance['style'];
		$instance['show_name'] = $new_instance['show_name'];
		$instance['show_title'] = $new_instance['show_title'];
		$instance['show_bio'] = $new_instance['show_bio'];
		$instance['show_photo'] = $new_instance['show_photo'];
		$instance['show_email'] = $new_instance['show_email'];
		$instance['show_address'] = $new_instance['show_address'];
		$instance['show_website'] = $new_instance['show_website'];
		$instance['show_phone'] = $new_instance['show_phone'];
		return $instance;
	}

	function widget($args, $instance)
	{
		extract($args, EXTR_SKIP);

		
		$title = !empty($instance['title']) ? $instance['title'] : '';
		$title = apply_filters('widget_title', $title);
		
		$category = !empty($instance['category']) ? $instance['category'] : '';
		$style = !empty($instance['style']) ? $instance['style'] : '';
		$show_name = isset($instance['show_name']) ? $instance['show_name'] : true;
		$show_title = isset($instance['show_title']) ? $instance['show_title'] : true;
		$show_bio = isset($instance['show_bio']) ? $instance['show_bio'] : true;
		$show_photo = isset($instance['show_photo']) ? $instance['show_photo'] : true;
		$show_email = isset($instance['show_email']) ? $instance['show_email'] : true;
		$show_phone = isset($instance['show_phone']) ? $instance['show_phone'] : true;
		$show_address = isset($instance['show_address']) ? $instance['show_address'] : true;
		$show_website = isset($instance['show_website']) ? $instance['show_website'] : true;		
		
		// start the widget
		echo $before_widget;

		if (!empty($title)){
			echo $before_title . $title . $after_title;
		}
		
		// build the shortcode's attributes
		$sc_atts = $this->build_shortcode_atts($instance);				
		$sc = '[staff_list in_widget="1" ' . $sc_atts . ']';
		$output = do_shortcode($sc);
		
		// give the user a chance to modify the output before echo'ing it
		echo apply_filters('staff_list_widget_html', $output);
		
		// finish the widget
		echo $after_widget;
	}
	
	function build_shortcode_atts($instance)
	{
		$atts = '';
		
		$opts['category'] 		= !empty($instance['category']) ? $instance['category'] : '';
		$opts['style'] 			= !empty($instance['style']) ? $instance['style'] : '';
		$opts['show_name'] 		= isset($instance['show_name']) ? $instance['show_name'] : true;
		$opts['show_title'] 	= isset($instance['show_title']) ? $instance['show_title'] : true;
		$opts['show_bio'] 		= isset($instance['show_bio']) ? $instance['show_bio'] : true;
		$opts['show_photo'] 	= isset($instance['show_photo']) ? $instance['show_photo'] : true;
		$opts['show_email'] 	= isset($instance['show_email']) ? $instance['show_email'] : true;
		$opts['show_phone'] 	= isset($instance['show_phone']) ? $instance['show_phone'] : true;
		$opts['show_address'] 	= isset($instance['show_address']) ? $instance['show_address'] : true;
		$opts['show_website'] 	= isset($instance['show_website']) ? $instance['show_website'] : true;
		
		
		// if we're using the Table View, build the column list based on their selections
		if ($opts['style'] == 'table') {
			$opts['columns'] = $this->build_column_list($instance);
		}		
		
		// Add each attribute + value to the string we're building
		foreach( $opts as $key => $val ) {
			if ( $val || !empty($val) || strlen($val) > 0 ) {
				$atts .= sprintf('%s="%s" ', $key, $val);				
			}
		}
		
		// allow the user to filter the attribute string before returning it
		$atts = trim($atts);
		return apply_filters('staff_list_widget_atts', $atts);
	}
	
	function build_column_list($instance)
	{
		$cols = '';
		
		$opts['name'] 		= isset($instance['show_name']) ? $instance['show_name'] : true;
		$opts['title'] 		= isset($instance['show_title']) ? $instance['show_title'] : true;
		$opts['bio'] 		= isset($instance['show_bio']) ? $instance['show_bio'] : true;
		$opts['photo'] 		= isset($instance['show_photo']) ? $instance['show_photo'] : true;
		$opts['email'] 		= isset($instance['show_email']) ? $instance['show_email'] : true;
		$opts['phone'] 		= isset($instance['show_phone']) ? $instance['show_phone'] : true;
		$opts['address']	= isset($instance['show_address']) ? $instance['show_address'] : true;
		$opts['website']	= isset($instance['show_website']) ? $instance['show_website'] : true;
				
		// Add each selected column the string we're building
		foreach( $opts as $key => $val ) {
			if ( $val || !empty($val) ) {
				$cols .= sprintf('%s,', $key);				
			}
		}
		
		// allow the user to filter the column list before returning it
		$cols = rtrim($cols, ',');
		return apply_filters('staff_list_columns', $cols);
	}
	
	function is_pro()
	{
		
		if ( isset($this->proUser) ) {
			return $this->proUser;
		}
		
        $options = get_option( 'sd_options' );
		
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
		
		return $this->proUser;
	}
}