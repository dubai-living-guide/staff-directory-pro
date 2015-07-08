<div class="staff-list">
	<?php if($staff_loop->have_posts()): while($staff_loop->have_posts()): $staff_loop->the_post(); ?>
		<?php
			extract ( cd_get_staff_metadata(get_the_ID(), $options) );
		?>
		<div class="staff-member">		
			<?php if ( $show_photo && has_post_thumbnail() ): ?>
				<div class="staff-photo">
					<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
				</div>
			<?php endif; ?>
			
			<div class="staff-member-right">
				<?php if ($show_name): ?>
				<h3 class="staff-member-name">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h3>
				<?php endif; ?>				

				<?php if ($show_title): ?>
					<p class="staff-member-title"><?php echo $my_title ?></p>
				<?php endif; ?>

				<?php if ($show_bio): ?>
				<div class="staff-member-bio"><?php the_content(); ?></div>
				<?php endif; ?>				

				<?php if ($show_address && $my_address): ?>
				<div class="staff-member-address">
					<h4>Mailing Address</h4>
					<p class="addr">
						<?php echo nl2br($my_address); ?>
					</p>
				</div>
				<?php endif; ?>

				<?php if ( ($show_phone && $my_phone) || ($show_email && $my_email) || ($show_website && $my_website) ): ?>
				<div class="staff-member-contacts">
					<h4>Contact</h4>
					<?php if ($show_phone && $my_phone): ?><p class="staff-member-phone"><strong>Phone:</strong> <?php echo $my_phone ?></p><?php endif; ?>
					<?php if ($show_email && $my_email): ?><p class="staff-member-email"><strong>Email:</strong> <a href="mailto:<?php echo $my_email ?>"><?php echo $my_email ?></a></p><?php endif; ?>
					<?php if ($show_website && $my_website): ?><p class="staff-member-website"><strong>Website:</strong> <a href="<?php echo $my_website ?>"><?php echo $my_website ?></a></p><?php endif; ?>
				</div>
				<?php endif; ?>
			</div>			
		</div>
	<?php endwhile; endif; ?>
	<?php wp_reset_query(); ?>
</div>