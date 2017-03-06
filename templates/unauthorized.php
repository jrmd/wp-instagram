<div class="wrap">
	<h2>Authorize | Instagram Feed</h2>
	<hr>

	<a class="igf-btn authorize" href="https://api.instagram.com/oauth/authorize/?client_id=<?php echo $this::$clientID ?>&redirect_uri=<?php echo urlEncode( admin_url( 'admin.php?page=ig-feed' ) ); ?>&response_type=code">
		<?php _e('Authorize Instagram Feed', 'instagram-feed'); ?>
	</a> <br>
	<small><em>Please note that until you authorize your account the plugin will be unavailable to you.</em></small>
</div>
