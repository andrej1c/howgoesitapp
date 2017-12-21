<form name="scoreform" id="scoreform" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
	<?php echo filter_input( INPUT_GET, 'error' ) ? '<p class="error"><strong>Error: </strong>' . filter_input( INPUT_GET, 'error' ) . '</p>' : ''; ?>
	<?php echo filter_input( INPUT_GET, 'success' ) ? '<p class="error"><strong>Success: </strong>' . filter_input( INPUT_GET, 'success' ) . '</p>' : ''; ?>
	<?php echo wp_nonce_field( 'hgi_score', 'hgi_score_nonce_field', true, false ); ?>
	<input type="hidden" name="action" value="hgi_add_score">
	<p>
		<label for="hgia_score">Right Now: </label>
		<select name="hgia_score" id="hgia_score" class="input_select">
			<option value="-1">
				Score
			</option>
			<?php
			for ( $i = 1; $i <= 10; $i++ ) {
			?>
				<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
			<?php } ?>
		</select>

		<input type="submit" name="submit" value="Set" />
	</p>

</form>
<div class="hgi_legend">
	<h4>Legend</h4>
	<div class="hgi_legend_details">
		10 "the greatest"<br /> 9 "a great space"<br /> 7-8 "moving up"<br /> 6 is "just ok"<br /> 5 is "neutral" or "too early to tell"<br /> 4 is "not too bad but coping"<br /> 1-3 "need help" (with levels of desperation)
	</div>
</div>
