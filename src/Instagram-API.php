<?php

class InstagramAPI {
	protected $token = '';
	protected $clientID = '';
	protected $clientSecret = '';

	public function __construct( $clientID, $clientSecret) {
		$this->clientID = $clientID;
		$this->clientSecret = $clientSecret;
	}

	public function authorize( $code ) {
		$url = 'https://api.instagram.com/oauth/access_token';

		$fields = [
    		'client_id' => $this->clientID,
			'client_secret' => $this->clientSecret,
			'grant_type' => 'authorization_code',
			'redirect_uri' =>  urlEncode( admin_url( 'admin.php?page=ig-feed' ) ),
			'code' => $code,
		];

		$fieldsString = '';

		foreach ( $fields as $key => $value ) {
			$fieldsString .= $key . '=' . $value . '&';
		}

		$fieldsString = rtrim( $fieldsString, '&' );

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_POST, count( $fields ) );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $fieldsString );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		$result = curl_exec( $ch );
		//close connection
		curl_close( $ch );

		$data = json_decode($result);

		if ( isset( $data->access_token ) ) {
			$this->setToken( $data->access_token );

			return $data->access_token;
		}

		return false;
	}

	public function setToken( $token ) {
		$this->token = $token;
	}

	public function getToken() {
		return $this->token;
	}

	public function getMedia( $count = 12 ) {

		if ( $this->hasError() ) {
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
		return get_transient( 'move_insta_feed_failed' ) ?? false;
	}

	public function setError ( $response ) {
		set_transient( 'move_insta_feed_failed', $response, DAY_IN_SECONDS );
	}
}
