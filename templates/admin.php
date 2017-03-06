<?php $response = ig_feed_user();
$currentUser = ($response->meta->code ?? false) && $response->meta->code !== 200 ? false : $response->data;
?>

<div class="wrap">
	<h2>Options | Instagram Feed</h2>
	<hr>
	<a href="<?php echo admin_url( 'admin.php?page=ig-feed&action=revoke' ) ?>" class="igf-btn revoke">
		Revoke current access token
	</a>
	<hr>
<?php if ( $currentUser ): ?>
	<p>Currently logged in as <img src="<?php echo $currentUser->profile_picture; ?>" alt="<?php echo $currentUser->full_name; ?>" style="width: 25px; border-radius: 50%; vertical-align: middle; margin-right: 4px;"><?php echo $currentUser->full_name; ?></p>

<?php else: ?>
	<p>Something went wrong when fetching your user</p>
	<p>Error <?php echo $response->error_code; ?> - <?php echo $response->error_message; ?></p>
<?php endif; ?>
</div>
