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
