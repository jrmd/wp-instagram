<?php

class InstagramAPI {
	protected $token = '';

	public function setToken( $token ) {
		$this->token = $token;
	}

	public function getToken() {
		return $this->token;
	}

	public function getMedia( $count = 12 ) {

		if ( $this->hasError() || ! $this->getToken() ) {
			return false;
		}

		$url = 'https://api.instagram.com/v1/users/self/media/recent/?access_token=';
		$url .= $this->getToken();
		$url .= '&count=';
		$url .= $count;

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		$result = curl_exec( $ch );

		curl_close( $ch );

		$result = json_decode( $result );

		if ( $result->meta->code !== 200 ) {
			$this->setError($result);
			return false;
		}

		return $result->data;
	}

	public function getUser() {

		if ( $error = $this->hasError() ) {
			return $error;
		}


		$url = 'https://api.instagram.com/v1/users/self/?access_token=';
		$url .= $this->getToken();

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		$result = curl_exec( $ch );

		curl_close( $ch );

		$result = json_decode( $result );

		return $result;
	}

	public function hasError () {
		return get_transient( 'insta_feed_failed' ) ?? false;
	}

	public function setError ( $response ) {
		set_transient( 'insta_feed_failed', $response, DAY_IN_SECONDS );
	}
}
