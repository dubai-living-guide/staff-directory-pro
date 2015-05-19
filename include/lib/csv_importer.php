<?php
class StaffDirectoryPlugin_Importer
{
	var $root;
	var $last_error = '';
	var $records_imported = 0;
	static $csv_headers = array('Full Name','Body','First Name','Last Name','Title','Phone','Email','Categories');
	
    public function __construct($root)
    {
		$this->root = $root;
	}	

	public static function get_csv_headers()
	{
		return self::$csv_headers;
	}

	public static function output_form()
	{
		echo '<form method="POST" action="" enctype="multipart/form-data">';
		
		// Load Importer API
		require_once ABSPATH . 'wp-admin/includes/import.php';

		if ( !class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) )
				require_once $class_wp_importer;
		}
		
		echo "<p>Please select a .CSV file from your computer to import. The first line of your CSV will need to match the example headers below, or the import will not work.</p>";
		echo "<p><strong>CSV Headers (required):</strong></p>";
		printf ("<p><code>%s</code></p>", "'" . implode("','",self::$csv_headers) . "'" );
		echo '<div class="gp_upload_file_wrapper">';
		wp_import_upload_form( add_query_arg('step', 1) );
		echo '<input type="hidden" name="_company_dir_do_import" value="_company_dir_do_import" />';
		echo "<p><strong>Note: </strong> Depending on your server settings, you may need to run the import several times if your script times out.</p>";
		echo '</div>';
		echo '</form>';
	}
	
	public function process_import()
	{
		$errors = array();
		
		// Load Importer API
		require_once ABSPATH . 'wp-admin/includes/import.php';

		if ( !class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) )
				require_once $class_wp_importer;
		}		
		
		if(!empty($_FILES))
		{
			$file = wp_import_handle_upload();

			if ( isset( $file['error'] ) ) {
				$this->last_error = sprintf('<p><strong>Sorry, there has been an error.</strong><br />%s</p>', esc_html( $file['error'] ));
				return false;
			} else if ( ! file_exists( $file['file'] ) ) {
				$err_msg = sprintf('The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', esc_html( $file['file'] ) );
				$this->last_error = sprintf('<p><strong>Sorry, there has been an error.</strong><br />%s</p>', $err_msg );
				return false;
			}
			
			$file_id = (int) $file['id'];
			$file_name = get_attached_file($file_id);
			
			if (file_exists($file_name)) {
				$result = $this->import_posts_from_csv($file_name);
			} else {
				$this->last_error = sprintf('<p><strong>Sorry, there has been an unknown error. Please try again.</strong></p>');
				return false;
			}			
		}
		
		// all worked!
		return $result;
	}
	
	//process data from CSV import
	private function import_posts_from_csv($posts_file)
	{
		//increase execution time before beginning import, as this could take a while
		set_time_limit(0);		
		
		$posts = $this->csv_to_array($posts_file);
		$messages = array();
		$success_count = 0;
		$fail_count = 0;
		
		foreach($posts as $post)
		{
			// title and body are always required
			$full_name = isset($post['Full Name']) ? $post['Full Name']  : '';
			$the_body = isset($post['Body']) ? $post['Body']  : '';
			
			// look for a staff member with the same full name, to prevent duplicates
			$find_dupe = get_page_by_title( $full_name, OBJECT, 'staff-member' );
			
			// if no one with that name was found, continue with inserting the new staff member
			if( empty($find_dupe) )
			{
				$new_post = array(
					'post_title'    => $full_name,
					'post_content'  => $the_body,
					'post_status'   => 'publish',
					'post_type'     => 'staff-member'
				);
				
				$new_id = wp_insert_post($new_post);

				// assign Staff Member Categories if any were specified
				// NOTE: we are using wp_set_object_terms instead of adding a tax_input key to wp_insert_posts, because 
				// it is less likely to fail b/c of permissions and load order (i.e., taxonomy may not have been created yet)
				if (!empty($post['Categories'])) {
					$post_cats = explode(',', $post['Categories']);
					$post_cats = array_map('intval', $post_cats); // sanitize to ints
					wp_set_object_terms($new_id, $post_cats, 'staff-member-category');
				}
				
				// Save the custom fields. Default everything to empty strings
				$first_name = isset($post['First Name']) ? $post['First Name'] : '';
				$last_name = isset($post['Last Name']) ? $post['Last Name'] : '';
				$title = isset($post['Title']) ? $post['Title'] : "";
				$phone = isset($post['Phone']) ? $post['Phone'] : "";
				$email = isset($post['Email']) ? $post['Email'] : "";
								
				update_post_meta( $new_id, '_ikcf_first_name', $first_name );
				update_post_meta( $new_id, '_ikcf_last_name', $last_name );
				update_post_meta( $new_id, '_ikcf_title', $title );
				update_post_meta( $new_id, '_ikcf_phone', $phone );
				update_post_meta( $new_id, '_ikcf_email', $email );
				
				// Successfully added the post! Update success_count and continue.
				$messages[] = sprintf("Successfully imported '%s!'", $full_name);
				$success_count++;
			}
			else {
				// Rejected as duplicate. Update fail_count and continue.
				$messages[] = sprintf("Could not import '%s'; rejected as duplicate.", $full_name);
				$fail_count++;				
			}
		}
		return array(
			'imported' => $success_count,
			'failed' => $fail_count,
			'messages' => $messages,
		);
	}
		
	//convert CSV to array
	private function csv_to_array($filename='', $delimiter=','){
		if(!file_exists($filename) || !is_readable($filename))
			return FALSE;

		$header = NULL;
		$data = array();
		
		if (($handle = fopen($filename, 'r')) !== FALSE)
		{
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
			{
				if(!$header){
					$header = $row;
				} else {
					if (count($header) == count($row)) {
						$data[] = array_combine($header, $row);
					}
				}
			}
			fclose($handle);
		}
		return $data;
	}
	
}