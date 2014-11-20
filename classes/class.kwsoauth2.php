<?php
use Ctct\Util\Config;
use Ctct\Auth;
use Ctct\Auth\CtctOAuth2;
use Ctct\Util\RestClient;
use Ctct\Exceptions\OAuth2Exception;

add_action('plugins_loaded', array('KWSOAuth2', 'processResponse'), 20);

/**
 * Class that implements necessary functionality to obtain an access token from a user
 *
 * @package     Auth
 * @author      Constant Contact
 */
class KWSOAuth2 extends CtctOAuth2 {

	private static $token;
	private static $error;
	private static $instance;

	public function __construct($processResponse = true) {

		$this->redirect_uri = $this->getRedirectUri(false);

		parent::__construct(CTCT_APIKEY, CTCT_APISECRET, $this->redirect_uri, new KWSRestClient(CTCT_APIKEY));

		if($processResponse) {
			$this->processResponse();
		}
	}

	static function getInstance() {
		if(empty(self::$instance)) {
			self::$instance = new KWSOAuth2(false);
		}

		return self::$instance;
	}

	static function processResponse($force = false) {

		// Response
		if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'ctct_oauth')) {

			# http://ctct.katz.co/?_wpnonce=13d09269c5&prefix=http&domain=localhost.local/wordpress/&action=ctct_oauth&error=access_denied&error_description=User+denied+authentication./
			if(isset($_REQUEST['error'])) {

				$error_array = array(
					'error' => esc_html( $_REQUEST['error'] ),
					'error_description' => urlencode(untrailingslashit( $_REQUEST['error_description']) )
				);

				$url = add_query_arg( $error_array, admin_url('admin.php?page=constant-contact-api'));

				wp_redirect($url);

				die();
			}

			// Returned a OAuth code. Let's fetch the access token based on that code.
			if(isset($_REQUEST['code'])) {

				try {

					$oauth_code = esc_attr( $_REQUEST['code'] );

					$token = self::getInstance()->getAccessToken( $oauth_code );
					$token['_wpnonce'] = esc_attr( $_REQUEST['_wpnonce'] );
					$token['time'] = time();
					$token['username'] = esc_attr( $_REQUEST['username'] );

					self::getInstance()->saveToken($token, true);

					do_action('ctct_token_saved', $token);

					$admin_url = admin_url('admin.php?page=constant-contact-api&oauth=new');

				} catch(Exception $e) {

					$token = false;

					// Delete configured setting, save error message
					self::getInstance()->saveToken($e->getMessage(), false);

					$admin_url = admin_url('admin.php?page=constant-contact-api');
				}

				do_action('ctct_token_updated', $token);

				// Go to the settings page
				wp_redirect( $admin_url );

				die();
			}
		}
	}

	/**
	 * Get the OAuth URL to redirect to after OAuth login
	 * @param  boolean $urlencode Should the URL be `urlencode()`d?
	 * @return string             URL
	 */
	private function getRedirectUri($urlencode = true) {

		$site_url = trailingslashit( get_bloginfo('url') );
		$site_domain = str_replace(array('http://', 'https://'), '', $site_url );

		$url = add_query_arg(array(
		    	'_wpnonce'	=> wp_create_nonce('ctct_oauth'),
		    	'prefix'	=> is_ssl() ? 'https' : 'http',
		    	'domain'	=> $site_domain,
		    	'action'	=> 'ctct_oauth'
		    ), 'http://ctct.katz.co/');

		return $urlencode ? urlencode($url) : $url;
	}

	/**
	 * Save the token to the DB once fetched in the `ctct_token_response` site option
	 *
	 * @param  array  $token      Token details with keys: `access_token`, `expires_in`, `token_type`, `_wpnonce`, `time`, `username`
	 * @param  boolean $configured Is the token configured? If not, delete `ctct_configured` site option. If yes, add `ctct_configured` site option set to `1`.
	 * @return [type]              [description]
	 */
	public function saveToken($token, $configured = false) {

		if( !$configured ) {
			delete_site_option( 'ctct_configured' );
		} else {
			update_site_option( 'ctct_configured', 1 );
		}

		update_site_option( 'ctct_token_response', $token );
	}

	/**
	 * Get the token response from the WP database and return the token, if exists
	 *
	 * The token response is saved in the DB as an array (`access_token`, `expires_in`, and `token_type`)
	 *
	 * @return string|boolean If token, returns token string. Otherwise, returns false.
	 */
	public function getToken($key = 'access_token') {

		$site_token = get_site_option( 'ctct_token_response', 'error_No_Token_In_Database');

		$token = maybe_unserialize( $site_token );

		if(!empty($token) && is_array($token) && !empty($token[$key])) {
			if($key === 'access_token') {
				self::$token = $token[$key];
			}
			$value = $token[$key];
		} else {

			if($key === 'access_token') {
				self::$error = $token;
				self::$token = false;
			}

			$value = false;
		}

	    return $value;
	}

	public function deleteToken() {
		if( current_user_can('manage_options') ) {
			delete_site_option( 'ctct_configured' );
			delete_site_option( 'ctct_token_response' );
		}
	}

}