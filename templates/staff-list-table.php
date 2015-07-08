<div class="staff-list">
	<table class="staff-table">
		<thead>
			<tr>
			<?php 
				foreach($columns as $col_key)
				{
					$display = str_replace('_', ' ', $col_key);
					$display = ucwords($display);
					echo "<th>" . $display . "</th>";
				}
			?>
			</tr>
		</thead>
		<tbody>		
		<?php if($staff_loop->have_posts()): while($staff_loop->have_posts()): $staff_loop->the_post(); ?>
			<tr>
				<?php 
					foreach($columns as $col_key)
					{
						echo "<td>";						
						switch($col_key)
						{
							case 'name':
								// return the post title
								$val = get_the_title();
								//$val = htmlentities($val);
								$val = '<a href="' . get_the_permalink() . '">' . $val . '</a>';
							break;

							case 'bio':
								// return the post body
								$val = get_the_content();
							break;
							
							case 'photo':
								// return the featured image
								$img = get_the_post_thumbnail(get_the_ID(), 'thumbnail');
								$val = sprintf('<a href="%s">%s</a>', get_the_permalink(), $img);
							break;

							case 'email':
								// return the email, linked with 'mailto'
								$email = get_post_meta(get_the_ID(), '_ikcf_email', true);
								$val = sprintf('<a href="mailto:%s">%s</a>', $email, $email);
							break;
							
							case 'phone':
								// return the email, linked with 'mailto'
								$phone = get_post_meta(get_the_ID(), '_ikcf_phone', true);
								$val = sprintf('<a href="tel:%s">%s</a>', $phone, $phone);
							break;

							case 'website':
								// return the featured image
								$img = get_the_post_thumbnail(get_the_ID(), 'thumbnail');
								$val = sprintf('<a href="%s">%s</a>', get_the_permalink(), $img);
							break;

							default:
								// for everything else (phone, email, etc) look for a corresponding _ikcf_{$col_key} meta key
								$meta_key = str_replace(' ', '_', $col_key);
								$meta_key = sanitize_title($meta_key);
								$meta_key = '_ikcf_' . $meta_key;
								$val = get_post_meta(get_the_ID(), $meta_key, true);								
								$val = htmlentities($val);
							break;
						}
						echo $val;
						echo "</td>";
					}
				?>
			</tr>
	<?php endwhile; endif; ?>
	<?php wp_reset_query(); ?>
		</tbody>
	</table>
	
</div>