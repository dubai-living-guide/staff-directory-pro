<?php
class StaffDirectoryPlugin_Exporter
{
	var $csv_headers = array('Full Name','Body','First Name','Last Name','Title','Phone','Email','Address','Website','Categories');
	
	public static function get_csv_headers()
	{
		return $csv_headers;
	}

	public static function output_form()
	{
		?>
		<form method="POST" action="">
			<p>Click the "Export Staff Members" button below to download a CSV file of your records.</p>			
			<input type="hidden" name="_company_dir_do_export" value="_company_dir_do_export" />
			<p><strong>Tip:</strong> You can use this export file as a template to import your own staff members.</p>			
			<p class="submit">
				<input type="submit" class="button" value="Export Staff Members" />
			</p>
		</form>
		<?php
	}
	
	/* Renders a CSV file to STDOUT representing every staff member in the database
	 * NOTE: this file is, and must remain, compatible with the Importer
	 */
	public function process_export($filename = "export.csv")
	{
		//load records
		$args = array(
			'posts_per_page'   => -1,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'post_date',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'staff-member',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'post_status'      => 'publish',
			'suppress_filters' => true 				
		);
		
		$posts = get_posts($args);
		
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Description: File Transfer');
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename={$filename}");
		header("Expires: 0");
		header("Pragma: public");
		
		
		// open file handle to STDOUT
		$fh = @fopen( 'php://output', 'w' );
		
		// output the headers first
		fputcsv($fh, $this->csv_headers);
			
		// now output one row for each testimonial
		foreach($posts as $post)
		{
			$row = array();
			$row['full_name'] = $post->post_title;
			$row['body'] = $post->post_content;
			$row['first_name'] = get_post_meta( $post->ID, '_ikcf_first_name', true );
			$row['last_name'] = get_post_meta( $post->ID, '_ikcf_last_name', true );
			$row['title'] = get_post_meta( $post->ID, '_ikcf_title', true );
			$row['phone'] = get_post_meta( $post->ID, '_ikcf_phone', true );
			$row['email'] = get_post_meta( $post->ID, '_ikcf_email', true );
			$row['address'] = get_post_meta( $post->ID, '_ikcf_address', true );
			$row['website'] = get_post_meta( $post->ID, '_ikcf_website', true );
			$row['categories'] = $this->list_taxonomy_ids( $post->ID, 'staff-member-category' );			
			fputcsv($fh, $row);
		}
		
		// Close the file handle
		fclose($fh);
	}
	
	/* 
	 * Get a comma separated list of IDs representing each term of $taxonomy that $post_id belongs to
	 *
	 * @returns comma separated list of IDs, or empty string if no terms are assigned
	*/
	function list_taxonomy_ids($post_id, $taxonomy)
	{
		$terms = wp_get_post_terms( $post_id, $taxonomy ); // could also pass a 3rd param, $args
		if (is_wp_error($terms)) {
			return '';
		}
		else {
			$term_list = array();
			foreach ($terms as $t) {
				$term_list[] = $t->term_id;
			}
			return implode(',', $term_list);
		}
	}
}