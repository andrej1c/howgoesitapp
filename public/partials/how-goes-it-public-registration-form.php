<form name="registrationform" id="loginform" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
	<?php echo wp_nonce_field( 'hgi_registration', 'hgi_nonce_field', true, false ); ?>
	<input type="hidden" name="action" value="hgi_create_user">
	<!--  TODO: Add check for HGI Code and set it to hidden field. -->
	<p>
		<label for="hgia_first_name">First Name</label>
		<input type="text" name="hgia_first_name" id="hgia_first_name" class="input" size="20" value="<?php echo filter_input( INPUT_GET, 'hgia_first_name' ); ?>" autocomplete="off" />
	</p>
	<p>
		<label for="hgia_last_name">Last Name</label>
		<input type="text" name="hgia_last_name" id="hgia_last_name" class="input" size="20" value="<?php echo filter_input( INPUT_GET, 'hgia_last_name' ); ?>" autocomplete="off" />
	</p>
	<p>
		<label for="hgia_email">Email</label>
		<input type="email" name="hgia_email" id="hgia_email" class="input" size="20" value="<?php echo filter_input( INPUT_GET, 'hgia_email' ); ?>" autocomplete="off" />
	</p>
	<p>
		<label for="hgia_password">Password</label>
		<input type="password" name="hgia_password" id="hgia_password" class="input" size="20" autocomplete="off" />
	</p>
	<p>
		<label for="hgia_timezone">Your Timezone</label>
		<select name="hgia_timezone" id="hgia_timezone" class="input_select">
		<?php
		foreach ( $tz_a as $key => $tz ) {
			echo sprintf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( filter_input( INPUT_GET, 'hgia_timezone' ), esc_attr( $key ), false ), $tz );
		}
		?>
		</select>
	</p>

	<input type="submit" name="submit" value="Create Account" />
</form>
