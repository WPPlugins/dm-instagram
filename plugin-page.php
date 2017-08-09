<?php
/**
 * Class DmInstagram_PluginPage
 */
class DmInstagram_PluginPage {
    protected $optionName;
    protected $data;

    function __construct($optionName, $data) {
        $this->optionName = $optionName;
        $this->data = $data;

        add_action('admin_init', array($this, 'adminInit'));
        add_action('admin_menu', array($this, 'pluginPage'));
    }

    public function adminInit() {
        register_setting('dm_instagram_options', $this->optionName, array($this, 'validate'));
    }

    public function pluginPage() {
        add_options_page('DM Instagram', 'DM Instagram', 'manage_options',
            'dm_instagram_options', array($this, 'pluginPageContent'));
    }

    public function validate($input) {
        $valid = array();

        $valid['client_id'] = sanitize_text_field($input['client_id']);
        $valid['client_secret'] = sanitize_text_field($input['client_secret']);
        $valid['redirect_uri'] = sanitize_text_field($input['redirect_uri']);


        foreach($valid as $k => $v) {
            // Check if empty
            if(strlen($v) == 0) {
                add_settings_error(
                  $k,
                  $k . '_texterror',
                  'Please enter a valid ' . $k,
                  'error'
                );

                // Set to the default
                $valid[$k] = $this->data[$k];
            }
        }

        return $valid;
    }

    public function pluginPageContent() {
        $options = get_option($this->optionName);
        $accessToken = get_option('dm-instagram_at');
        ?>
        <div id="icon-options-general" class="icon32"><br></div>
        <div class="wrap">
            <h2>DM Instagram Settings</h2>
            <?php
                // Don't show if access token is already saved
                if($accessToken === '' || $accessToken === false)
                    echo $this->displayInstagramUrl($options);
            ?>
            <form method="post" action="options.php">
                <?php settings_fields('dm_instagram_options'); ?>
                <table class="form-table">
                    <?php foreach($options as $k => $v) { ?>
                    <tr valign="top">
                        <th scope="row">
                            <?php echo ucwords(str_replace('_',' ', $k)); ?>
                        </th>
                        <td>
                            <input type="text" name="<?php echo $this->optionName . "[{$k}]"; ?>"
                                   value="<?php echo $options[$k]; ?>"/>
                            <?php
                            // Display the description only once
                            if($k == 'client_id') {
                            ?>
                            <p class="description">
                                Instructions on how to get these values <a href="http://donmhi.co">here</a>.
                            </p>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>"/>
                </p>
            </form>
        </div>
    <?php }

    /**
     * Display the instagram permission link
     * @param $options array
     * @return string
     */
    private function displayInstagramUrl($options) {
        $display = true;
        // Check if all needed data is provided
        foreach($this->data as $k => $v) {
             if(empty($options[$k]))
                 $display = false;
        }

        if($display) {
            $requestUrl = $this->getInstagramUrl($options);
            if($requestUrl) {
                $link = "<a href='{$requestUrl}'>Sign in with Instagram</a>";
            }
                return $link;
        } else {
            return '';
        }
    }

    /**
     * Build the request url
     * @param $options
     * @return bool|string
     */
    private function getInstagramUrl($options) {
        $request = new DmInstagram_Models_Request();
        // Set the data
        $request->setClientId($options['client_id']);
        $request->setClientSecret($options['client_secret']);
        $request->setRedirectUri($options['redirect_uri']);

        return $request->buildRequestUrl();
    }
}