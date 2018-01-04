<div class="followers">
	<?php
	if ( 0 < count( $followers ) ) { ?>
			<table>
				<thead>
					<tr>
						<th>
							Name
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $followers as $follower ) { ?>
				<tr>
					<td>
						<?php echo esc_attr( $follower['follower_name'] ); ?>
					</td>
					<td>
						<?php
						if ( 'active' === $follower['follower_status'] ) {
							?>
							<form name="disconnect_form" class="disconnect_form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
								<input type="hidden" name="follower_id" value="<?php echo esc_attr( $follower['follower_id'] ); ?>">
								<?php echo wp_nonce_field( 'hgi_disconnect', 'hgi_disconnect_nonce_field', true, false ); ?>
								<input type="hidden" name="action" value="hgi_disconnect_follower">
								<button type="submit" name="submit_disconnect">Disconnect</button>
							</form>
							<?php
						} else {
							echo 'Non active, click on link in email.';
						}
?>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<a href="<?php echo esc_url( $follow_url ); ?>">Invite More</a>
		<?php } else { ?>
		<p>
			You have no followers yet.
		</p>
		<a href="<?php echo esc_url( $follow_url ); ?>">Invite Someone</a>
		<?php
}
?>
</div>
