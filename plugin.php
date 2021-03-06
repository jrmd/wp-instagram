<?php
/**
 * @wordpress-plugin
 * Plugin Name:       WP Instagram Feed (Multisite)
 * Plugin URI:        http://github.com/jrmd/wp-instagram
 * Description:       Add instagram support to wordpress for developers
 * Version:           1.0.3
 * Author:            Jerome Duncan
 * Author URI:        http://itsjero.me
 * License:           MIT
 */

require_once __DIR__ . '/src/Instagram-API.php';
require_once __DIR__ . '/src/class-wp-ms-instagram-feed.php';

new MS_Instagram_Feed();

function ig_feed_media( $count = 18, $size = 'thumbnail' ) {

	$images = get_transient( 'insta_feed_media_' . $size . '_' . $count );

	if ( $images ) {
		return $images;
	}

	// set_transient
	$ig = new MS_Instagram_Feed();
	$data = $ig->getMedia( $count );

	if ( ! $data ) {
		$backupMedia = get_transient( 'insta_feed_backup_media_' . $size . '_' . $count );
		$backupMedia = $backupMedia ?: false;

		return $backupMedia;
	}

	$images = [];

	foreach ( $data as $key => $img ) {
		$images[] = [
			'src'  => isset($img->images->{$size}->url) ? $img->images->{$size}->url : false,
			'link' => isset($img->link) ? $img->link : false,
			'alt'  => isset($img->caption->text) ? $img->caption->text : false,
		];
	}

	set_transient( 'insta_feed_media_' . $size . '_' . $count, $images, DAY_IN_SECONDS );
	set_transient( 'insta_feed_backup_media_' . $size . '_' . $count, $images, 30 * DAY_IN_SECONDS );

	return $images;
}


function ig_feed_user () {
	$ig = new MS_Instagram_Feed();
	$data = $ig->getUser();
	return $data;
}
