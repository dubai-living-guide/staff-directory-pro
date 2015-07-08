<?php
	global $staff_data;

	$my_phone = htmlspecialchars($staff_data['phone']);
	$my_email = htmlspecialchars($staff_data['email']);
	$my_title = htmlspecialchars($staff_data['title']);
	$my_website = htmlspecialchars($staff_data['website']);
	$my_address = nl2br(htmlspecialchars($staff_data['address']));
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

		<?php if ($my_address): ?>
		<div class="staff-member-address">
			<h4>Mailing Address</h4>
			<p class="addr">
				<?php echo $my_address ?>
			</p>
		</div>
		<?php endif; ?>
		
		<?php if ($my_phone || $my_email || $my_website): ?>
		<div class="staff-member-contacts">
			<h4>Contact</h4>
			<?php if ($my_phone): ?><p class="staff-member-phone"><strong>Phone:</strong> <?php echo $my_phone ?></p><?php endif; ?>
			<?php if ($my_email): ?><p class="staff-member-email"><strong>Email:</strong> <a href="mailto:<?php echo $my_email ?>"><?php echo $my_email ?></a></p><?php endif; ?>
			<?php if ($my_website): ?><p class="staff-member-website"><strong>Website:</strong> <a href="<?php echo $my_website ?>"><?php echo $my_website ?></a></p><?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</div>