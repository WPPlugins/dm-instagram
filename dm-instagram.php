<?php
/*
Plugin Name: DM Instagram
Plugin URI: http://donmhi.co/
Description: Connect your intagram with to your website. Display your latest uploads immediately to your website automatically.
Version: 1.2
Author: donMhico
Author http://donmhi.co
License: GPL2
*/

define('DMINSTAGRAM', plugin_dir_path(__FILE__));

require_once(DMINSTAGRAM . DIRECTORY_SEPARATOR . "models" . DIRECTORY_SEPARATOR . "request.php");
require_once(DMINSTAGRAM . DIRECTORY_SEPARATOR . "controllers" . DIRECTORY_SEPARATOR . "accept.php");
require_once(DMINSTAGRAM . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "display.php");
require_once(DMINSTAGRAM . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "helper.php");
require_once(DMINSTAGRAM . DIRECTORY_SEPARATOR . "init.php");
require_once(DMINSTAGRAM . DIRECTORY_SEPARATOR . "widget.php");
require_once(DMINSTAGRAM . DIRECTORY_SEPARATOR . "plugin-page.php");

class DmInstagram {
    /**
     * Client ID from Instagram API
     * @var string
     */
    private $clientId;

    /**
     * Client Secret from Instagram API
     * @var string
     */
    private $clientSecret;

    /**
     * Redirect URI declared on Instagram API
     * @var string
     */
    private $redirectUri;

    /**
     * Access token
     * @var string
     */
    private $accessToken;

    /**
     * User id
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    protected $optionName = 'dm-instagram';

    /**
     * Option fields
     * @var array
     */
    protected $data = array(
        'client_id' => '',
        'client_secret' => '',
        'redirect_uri'=> ''
    );

    function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Create the plugin page
        new DmInstagram_PluginPage($this->optionName, $this->data);

        $this->init();

        add_action('admin_init', array($this, 'adminInit'));
        add_action('widgets_init', array($this, 'widget'));
        add_shortcode('dminstagram', array($this, 'shortcode'));

        // Add scripts
        add_action('wp_enqueue_scripts', array($this, 'scripts'));

        // Add styles
        add_action('wp_enqueue_scripts', array($this, 'styles'));
    }

    public function activate() {
        // Save settings
        update_option($this->optionName, $this->data);
        // Save access token
        update_option($this->optionName . '_at', '');
        // Save user id
        update_option($this->optionName . '_uid', '');
    }

    public function deactivate() {
        delete_option($this->optionName);
    }

    public function scripts() {
        // Only load if we're on the front end
        if(!is_admin()) {
            wp_deregister_script('fotorama');
            wp_deregister_script('prettyPhoto');

            wp_register_script('fotorama', 'http://fotorama.s3.amazonaws.com/4.3.0/fotorama.js', array('jquery'));
            wp_register_script('prettyPhoto', plugins_url($this->optionName . '/assets/prettyphoto/js/jquery.prettyPhoto.js'), array('jquery'));
            wp_register_script('dminstagram', plugins_url($this->optionName . '/assets/js/dminstagram.js'), array('prettyPhoto'));

            wp_enqueue_script('fotorama');
            wp_enqueue_script('prettyPhoto');
            wp_enqueue_script('dminstagram');
        }
    }

    public function styles() {
        // Only load if we're on the front end
        if(!is_admin()) {
            wp_deregister_style('fotorama');
            wp_deregister_style('prettyPhoto');

            wp_register_style('dminstagram', plugins_url($this->optionName . '/assets/css/style.css'), array(), '1.1');
            wp_register_style('fotorama', 'http://fotorama.s3.amazonaws.com/4.3.0/fotorama.css');
            wp_register_style('prettyPhoto', plugins_url($this->optionName . '/assets/prettyphoto/css/prettyPhoto.css'));

            wp_enqueue_style('dminstagram');
            wp_enqueue_style('fotorama');
            wp_enqueue_style('prettyPhoto');
        }
    }

    /**
     * Init the data
     */
    public function init() {
        $data = get_option($this->optionName);

        $this->clientId = $data['client_id'];
        $this->clientSecret = $data['client_secret'];
        $this->redirectUri = $data['redirect_uri'];
        $this->accessToken = get_option($this->optionName . '_at');
        $this->userId = get_option($this->optionName . '_uid');

        // Request the media
        if($this->accessToken != '' && $this->accessToken != false && $this->userId != '' && $this->userId != false)
            DmInstagram_Init::getInstance($this->userId, $this->accessToken);

    }

    /**
     * Accept the permission response from instagram and display admin notices
     */
    public function adminInit() {
        // Accept the response from instagram
        if(isset($_GET['dminstagram']))
        {
            // Process the permission response
            $this->processResponse();
        }

        $display = true;
        // Don't display notice if we're on the Dm Instagram Option page
        if(isset($_GET['page'])) {
            if($_GET['page'] == 'dm_instagram_options')
                $display = false;
        }

        // Check if only the token is unavailable
        if($display && $this->accessToken == '' && $this->clientId != '' && $this->clientSecret != ''
            && $this->redirectUri != '' && !isset($_GET['error_reason']) && $_GET['code'] != '400') {
            add_action('admin_notices', array($this, 'noticeNoToken'));
        }

        // Check if the setting fields needs to be populated
        if($display && (empty($this->clientId) || empty($this->clientSecret) || empty($this->clientSecret))) {
            add_action('admin_notices', array($this,'noticeNoApiData'));
        }
    }

    public function widget() {
        register_widget( 'DmInstagram_Widget' );
    }

    /**
     * Shortcode for DM Instragram
     */
    public function shortcode($atts, $content = null ) {
        // Get the media
        $recentMedia = $this->getRecentMedia();

        // If error received
        if($recentMedia === false) {
            return;
        } else {
            $configs = shortcode_atts(array(
                'display' => 5,
                'style' => 'default',
                'thumbs' => 'false',
                'fullscreen' => 'false',
                'width' => '325',
                'autoplay' => '0',
                'transition' => 'slide',
                'prettyphoto' => 'false',
                'caption' => 'false'
            ),$atts);

            // Trim the results
            $helper = new DmInstagram_Helpers_Helper();
            $recents = $helper->trimResults($recentMedia->data, $configs['display']);

            // Display
            ob_start();
            new DmInstagram_Views_View($configs, $recents);
            $output = ob_get_contents();
            ob_end_clean();

            return $output;
        } // End if

    }

    /**
     * Get the recent media of the user
     * @return bool|object
     */
    private function getRecentMedia() {
        // Check if access token and userId are present
        if($this->accessToken == '' || $this->accessToken == 'false' ||
            $this->userId == '' || $this->userId == 'false')
            return false;

        // Get the data
        $data = DmInstagram_Init::getInstance($this->userId, $this->accessToken);

        return $data;
    }

    /**
     * Process the permission response
     */
    private function processResponse() {
        if(isset($_GET['error'])) {
            switch($_GET['error_reason']) {
                case 'user_denied':
                    add_action('admin_notices', array($this, 'noticeUserDenied'));
                    break;
                default:
                    add_action('admin_notices', array($this, 'noticeUserDenied'));
                    break;
            }
        } elseif(isset($_GET['code']) && $_GET['code'] == 400) {
            // Error
            add_action('admin_notices', array($this, 'noticeCodeError'));
        } elseif(isset($_GET['code'])) {
            // Success
            // Request for the access token
            $result = $this->requestAccessToken($_GET['code']);

            // Check if ACCESS TOKEN is retrieve
            if($result) {
                add_action('admin_notices', array($this, 'noticeSuccess'));
            } else {
                add_action('admin_notices', array($this, 'noticeCodeError'));
            }
        }
    }

    /**
     * Request access token for the plugin
     * @param $code string
     * @return bool
     */
    private function requestAccessToken($code) {
        // Init
        $request = new DmInstagram_Models_Request();
        $accept = new DmInstagram_Controllers_Accept();

        // Set the data
        $request->setClientId($this->clientId);
        $request->setClientSecret($this->clientSecret);
        $request->setRedirectUri($this->redirectUri);

        // Request for the token
        $result = $request->requestAccessToken($code);

        // Make the response an object
        $accept->setResponse($result);

        // Get the access token
        if($accept->parseData('access_token') != null && $accept->parseData('user', 'id') != null) {
            $this->saveAccessToken($accept->parseData('access_token'), $accept->parseData('user', 'id'));
            return true;
        } else {
            add_action('admin_notices', array($this, 'noticeCodeError'));
            return false;
        }
    }

    /**
     * Save the access token and user_id in the db
     * @param $accessToken string
     * @param $userId string
     */
    private function saveAccessToken($accessToken, $userId) {
        update_option($this->optionName . '_at', $accessToken);
        update_option($this->optionName . '_uid', $userId);
        // Update $this->accessToken
        $this->accessToken = $accessToken;
    }

    /**
     * Display if the user didn't authorize the plugin yet
     */
    public function noticeNoToken() { ?>
        <div class="error">
            <p>Your near to show your latest instagram photos to your image.
                Click <a href="<?php echo admin_url('options-general.php?page=dm_instagram_options'); ?>">here</a>.</p>
        </div>
    <?php }

    /**
     * Display if the client_id, client_secret and redirect_uri settings are not yet set
     */
    public function noticeNoApiData() { ?>
        <div class="error">
            <p>You need to populate the DM Instagram Settings to start showing your instagram images.
                Click <a href="<?php echo admin_url('options-general.php?page=dm_instagram_options'); ?>">here</a>.</p>
        </div>
    <?php }

    /**
     * Display if the user denied the authorization
     */
    public function noticeUserDenied() { ?>
        <div class="error">
            <p>Please Authorize the Instagram permission request to start using the plugin.</p>
        </div>
    <?php }

    /**
     * Display if unexpected error occured
     */
    public function noticeCodeError() {?>
        <div class="error">
            <p>An unexpected error occured. Please try again the link <a href="<?php echo admin_url('options-general.php?page=dm_instagram_options'); ?>">here</a>.</p>
        </div>
    <?php }

    /**
     * Display if success
     */
    public function noticeSuccess() { ?>
        <div class="updated">
            <p>DM Instagram is now properly installed. Happy sharing.</p>
        </div>
    <?php }
}
new DmInstagram();