<?php

class MS_Instagram_Feed {
    public static $key = '_wp_ig_feed_';
    public static $api = '';

    public function __construct() {
    	$this::$api = new InstagramAPI();
        $this->is_multisite();
    	add_action( 'admin_menu', [ $this, 'createMenuPages' ] );
    	add_action( 'admin_enqueue_scripts', [ $this, 'adminStyles' ] );
    }

    public function is_multisite() {
        $this->multisite = is_multisite();

        if ( $this->multisite ) {
            $this->blog_id = get_current_blog_id();
        }
    }

    public function adminStyles() {
    	wp_enqueue_style( 'ig-feed-styles', plugins_url('/../styles/main.css', __FILE__), false, '1.0.0', 'all');
    }

    public function createMenuPages() {
    	add_menu_page( 'Instagram', 'Instagram', 'edit_posts', 'ig-feed', [ $this, 'displayMenuPage' ], 'dashicons-format-image' );
    }

    public function displayMenuPage() {
    	$authed = $this->isAuthorized() ?: false;

    	if ( ! $authed && isset( $_GET['token'] ) ) {
    		$authed = $_GET['token'];

    		if ( $authed ) {
				$this->setOrUpdateOption( 'access_token', $authed );
				$this->successMessage();
    		} else {
    			$this->errorMessage();
    		}
    	}

    	if ( ! $authed ) {
	   		require_once __DIR__ . '/../templates/unauthorized.php';
	   		return;
    	}

    	if ( isset( $_GET['action'] ) && 'revoke' === $_GET['action'] ) {
    		$this->revokeAccount();
    		$this->successMessage('You have revoked access to your account');

	   		require_once __DIR__ . '/../templates/unauthorized.php';
	   		return;
    	}

    	$this::$api->setToken( $authed );

		require_once __DIR__ . '/../templates/admin.php';
    }

    protected function setOrUpdateOption( $key, $value ) {
    	if ( $this->getOption( $key ) ) {
    		$this->updateOption( $key, $value );
    		return;
    	}

    	$this->setOption( $key, $value );
    }

    protected function setOption ( $key, $value ) {
        if ( $this->multisite ) {
            return add_blog_option( $this->blog_id, $this::$key . $key, $value );
        }

    	add_option( $this::$key . $key, $value );
    }

    protected function updateOption( $key, $value ) {
        if ( $this->multisite ) {
        	update_blog_option( $this->blog_id, $this::$key . $key, $value );
        }

    	update_option( $this::$key . $key, $value );
    }

    protected function removeOption( $key ) {
        if ( $this->multisite ) {
    	    delete_blog_option( $this->blog_id, $this::$key . $key );
        }

    	delete_option( $this::$key . $key );
    }

    protected function getOption( $key ) {
        if ( $this->multisite ) {
    	    get_blog_option( $this->blog_id, $this::$key . $key );
        }

        return get_option( $this::$key . $key );
    }

    protected function isAuthorized() {
    	if ( $this->getOption( 'access_token' ) ) {
    		return $this->getOption( 'access_token' );
    	}

    	return false;
    }

    public function getMedia( $count = 12 ) {
        if ( ! $this->isAuthorized() ) {
            return false;
        }

    	$this::$api->setToken( $this->getOption( 'access_token' ) );
    	return $this::$api->getMedia( $count );
    }

    public function getUser() {
        if ( ! $this->isAuthorized() ) {
            return false;
        }

    	$this::$api->setToken( $this->getOption( 'access_token' ) );
    	return $this::$api->getUser();
    }

    protected function errorMessage() {
    	?>
	<div class="notice notice-error is-dismissible">
        <p><?php _e( 'Something went wrong! Try again', 'sample-text-domain' ); ?></p>
    </div>
    	<?php
    }

    protected function successMessage( $message = 'You have authorized your account.' ) {
    	?>
	<div class="notice notice-success is-dismissible">
        <p><?php _e( 'Success! ' . $message, 'sample-text-domain' ); ?></p>
    </div>
    	<?php
    }

    protected function revokeAccount () {

        // delete transients for current account
        global $wpdb;
        $sql = "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_insta_feed_%'";
        $wpdb->get_results( $sql );

        // remove access token
        $this->removeOption( 'access_token' );        
    }
}
