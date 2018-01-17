<h2>Update your settings</h2>
<p class="message"><?php echo filter_input( INPUT_GET, 'msg' ); ?></p>
<form name="my-account-form" id="my-account-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
	<?php wp_nonce_field( 'hgi_my-account', 'hgi_my-account-nonce_field', true ); ?>
	<input type="hidden" name="action" value="hgi_update_user">

	<p>
		<label for="hgia_timezone">Your Timezone</label>
		<select name="hgia_timezone" id="hgia_timezone" class="input_select">
		<?php
		foreach ( $tz_a as $key => $tz ) {
			echo sprintf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $timezone, esc_attr( $key ), false ), $tz );
		}
		?>
		</select>
	</p>

	<p>
		<label for="hgi_password">Change your password. If you fill the password, it will be changed and you will be logged out.</label><br />
		<input type="password" name="hgi_password" value="" placeholder="New Password" /><br />
		<input type="password" name="hgi_retype_password" value="" placeholder="Retype password" />
	</p>
	<input type="submit" name="submit" value="Update Settings" />
</form>
