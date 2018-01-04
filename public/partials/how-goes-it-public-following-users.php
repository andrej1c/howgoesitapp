<div class="followers">
<?php
if ( 0 < count( $nonactive_user_codes_a ) ) { ?>
	<p>
		You've entered a valid code(s) <strong><?php echo implode( ', ', $nonactive_user_codes_a ); ?></strong>. We're waiting for final authorization from your friend(s) to allow you to see their score.
	</p>
<?php } ?>
<?php
if ( 0 < count( $users ) ) {
?>
	<table>
		<thead>
			<tr>
				<th>
					Name
				</th>
				<th>
					Score
				</th>
				<th>
					Last Updated
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $users as $user ) { ?>
			<tr>
				<td>
					<?php echo esc_attr( $user['user_name'] ); ?>
				</td>
				<td>
					<?php echo esc_attr( $user['user_score'] ); ?>
				</td>
				<td>
					<?php echo esc_attr( $user['user_timestamp'] ); ?>
				</td>
				<td>
					<form name="disconnect_form" class="disconnect_form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<input type="hidden" name="user_id" value="<?php echo esc_attr( $user['user_id'] ); ?>">
						<?php echo wp_nonce_field( 'hgi_disconnect', 'hgi_disconnect_nonce_field', true, false ); ?>
						<input type="hidden" name="action" value="hgi_disconnect_user">
						<button type="submit" name="submit_disconnect">Disconnect</button>
					</form>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
<?php } else { ?>
	<p>
		You are not following anyone yet.
	</p>
<?php
}
?>
</div>
