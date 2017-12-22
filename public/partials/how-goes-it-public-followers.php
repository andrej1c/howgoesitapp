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
						<?php echo ( 'active' === $follower['follower_status'] ) ? '<a href="#">Disconnect</a> --Implement' : 'Non active, click on link in email.'; ?>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<a href="<?php echo $follow_url; ?>">Invite More</a>
		<?php } else { ?>
		<p>
			You have no followers yet.
		</p>
		<a href="<?php echo $follow_url; ?>">Invite Someone</a>
		<?php
}
?>
</div>
