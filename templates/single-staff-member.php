<?php
	global $staff_data;

	$my_phone = $staff_data['phone'];			
	$my_email = $staff_data['email'];
	$my_title = $staff_data['title'];
?>
<div class="staff-member single-staff-member">
	<!-- Featured Image -->
	<?php $post_thumbnail_src = get_the_post_thumbnail($staff_data['ID'], 'thumbnail'); ?>
	<?php if (!empty($post_thumbnail_src)): ?>
		<div class="staff-photo"><?php echo $post_thumbnail_src; ?></div>
	<?php endif; ?>
	<div class="staff-member-right">
		<h3 class="staff-member-name"><?php echo $staff_data['full_name']; ?></h3>
		<?php if ($my_title): ?><p class="staff-member-title"><?php echo $my_title ?></p><?php endif; ?>
		<div class="staff-member-bio"><?php echo wpautop($staff_data['content']); ?></div>
		<?php if ($my_phone || $my_email): ?>			
		<div class="staff-member-contacts">
			<h4>Contact</h4>
			<?php if ($my_phone): ?><p class="staff-member-phone"><strong>Phone:</strong> <?php echo $my_phone ?></p><?php endif; ?>
			<?php if ($my_email): ?><p class="staff-member-email"><strong>Email:</strong> <a href="mailto:<?php echo $my_email ?>"><?php echo $my_email ?></a></p><?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</div>