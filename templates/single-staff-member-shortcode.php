	<?php if($staff_loop->have_posts()): while($staff_loop->have_posts()): $staff_loop->the_post(); ?>
	<?php
		$my_phone = get_post_meta(get_the_ID(), '_ikcf_phone', true);			
		$my_email = get_post_meta(get_the_ID(), '_ikcf_email', true);
		$my_title = get_post_meta(get_the_ID(), '_ikcf_title', true);
	?>
	<div class="staff-member single-staff-member">
		<?php if ( has_post_thumbnail() ): ?>
			<div class="staff-photo"><?php the_post_thumbnail(); ?></div>
		<?php endif; ?>
		<div class="staff-member-right">
			<h3 class="staff-member-name"><?php the_title(); ?></h3>
			<?php if ($my_title): ?><p class="staff-member-title"><?php echo $my_title ?></p><?php endif; ?>
			<div class="staff-member-bio"><?php the_content(); ?></div>
			<?php if ($my_phone || $my_email): ?>			
			<div class="staff-member-contacts">
				<h4>Contact</h4>
				<?php if ($my_phone): ?><p class="staff-member-phone"><strong>Phone:</strong> <?php echo $my_phone ?></p><?php endif; ?>
				<?php if ($my_email): ?><p class="staff-member-email"><strong>Email:</strong> <a href="mailto:<?php echo $my_email ?>"><?php echo $my_email ?></a></p><?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
<?php endwhile; endif; ?>
<?php wp_reset_query(); ?>