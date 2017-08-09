<?php
/**
 * Class Widget
 *
 * Creates the widget of DM Instagram for wordpress
 * @package DmInstagram
 */
class DmInstagram_Widget extends WP_Widget {
    function __construct() {
        parent::WP_Widget('dminstagram_widget', 'DM Instagram Widget');
    }

    /**
     * Front-end display of widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $display = $instance['display'];
        $style = $instance['style'];
        $fullscreen = $instance['fullscreen'];
        $thumbs = $instance['thumbs'];
        $autoplay = $instance['autoplay'];
        $transition = $instance['transition'];
        //$width = $instance['width'];
        $caption = $instance['caption'];

        // Create the array
        $atts = array( 'display' => $display, 'style' => $style, 'thumbs' => $thumbs, 'fullscreen' => $fullscreen,
        'autoplay' => $autoplay, 'transition' => $transition, 'caption' => $caption);

        echo $args['before_widget'];
        if(!empty($title))
            echo $args['before_title'] . $title . $args['after_title'];
        // Display the images
        $this->display($atts);
        echo $args['after_widget'];
    }

    /**
     * Back-end widget form
     * @param array $instance
     * @return string|void
     */
    public function form($instance) {
        // Init value
        $data = array(
            'title' => '',
            'display' => 5,
            'style' => 'default',
            'fullscreen' => 'false',
            'thumbs' => 'false',
            'autoplay' => '5000',
            'transition' => 'slide',
            'caption' => 'false'
        );

        if(isset($instance['title']))
            $data['title'] = $instance['title'];

        if(isset($instance['display']))
            $data['display'] = $instance['display'];

        $data['style'] = $instance['style'];
        $data['fullscreen'] = $instance['fullscreen'];
        $data['thumbs'] = $instance['thumbs'];

        if(isset($instance['autoplay']))
            $data['autoplay'] = $instance['autoplay'];

        $data['transition'] = $instance['transition'];
        $data['caption'] = $instance['caption'];

        $this->formDisplay($data);
    }

    /**
     * Sanitize widget form values as they are saved.
     * @param array $newInstance
     * @param array $oldInstance
     * @return array
     */
    public function update($newInstance, $oldInstance) {
        $instance = array();

        foreach($newInstance as $k => $v) {
            // Check if new value is the same as the old
            if($v != $oldInstance[$k]) {
                $instance[$k] = strip_tags($v);
            } else {
                $instance[$k] = $oldInstance[$k];
            }
        }

        // Sanitize for autoplay
        if(!is_numeric($newInstance['autoplay']))
            $instance['autoplay'] = $oldInstance['autoplay'];

        return $instance;
    }

    /**
     * Back-end form template
     * @param array $data
     */
    private function formDisplay($data) {
        // Available styles
        $styles = array('default', 'default with prettyphoto', 'fotorama');
        // Fullscreen
        $fullscreen = array('false', 'true');
        // Display thumbnails
        $thumbs = array('false', 'true');
        // Transition effects
        $transitions = array('slide', 'crossfade', 'dissolve');
        // Captions
        $caption = array('false', 'true');
        ?>
        <p>
            <label for="<?php echo $this->get_field_name('title'); ?>">
                <?php _e('Title:'); ?>
            </label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr( $data['title'] ); ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_name('display'); ?>">
                <?php _e('Number of images:'); ?>
            </label>
            <input class="widefat" id="<?php echo $this->get_field_id('display'); ?>"
                   name="<?php echo $this->get_field_name('display'); ?>" type="text"
                   value="<?php echo esc_attr( $data['display'] ); ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_name('style'); ?>">
                <?php _e('Style'); ?>
            </label>
            <select class="widefat" id="<?php echo $this->get_field_id('style'); ?>"
                    name="<?php echo $this->get_field_name('style'); ?>">
                <?php
                foreach($styles as $style) {
                    echo "<option value='" . esc_attr($style) . "'";
                    // Check if the style is selected
                    if($style === esc_attr($data['style']))
                        echo " selected=selected";
                    echo ">{$style}</option>";
                }
                ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_name('fullscreen'); ?>">
                <?php _e('Allow Full Screen'); ?>
            </label>
            <select class="widefat" id="<?php echo $this->get_field_id('fullscreen'); ?>"
                    name="<?php echo $this->get_field_name('fullscreen'); ?>">
                <?php
                foreach($fullscreen as $fs) {
                    echo "<option value='{$fs}'";
                    // Check if the style is selected
                    if($fs === $data['fullscreen'])
                        echo " selected=selected";
                    echo ">{$fs}</option>";
                }
                ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_name('thumbs'); ?>">
                <?php _e('Show thumbnails'); ?>
            </label>
            <select class="widefat" id="<?php echo $this->get_field_id('thumbs'); ?>"
                    name="<?php echo $this->get_field_name('thumbs'); ?>">
                <?php
                foreach($thumbs as $thumb) {
                    echo "<option value='{$thumb}'";
                    // Check if the style is selected
                    if($thumb === $data['thumbs'])
                        echo " selected=selected";
                    echo ">{$thumb}</option>";
                }
                ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_name('autoplay'); ?>">
                <?php _e('Autoplay transition in milliseconds: (0 to disable)'); ?>
            </label>
            <input class="widefat" id="<?php echo $this->get_field_id('autoplay'); ?>"
                   name="<?php echo $this->get_field_name('autoplay'); ?>" type="text"
                   value="<?php echo esc_attr( $data['autoplay'] ); ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_name('transition'); ?>">
                <?php _e('Transition effect'); ?>
            </label>
            <select class="widefat" id="<?php echo $this->get_field_id('transition'); ?>"
                    name="<?php echo $this->get_field_name('transition'); ?>">
                <?php
                foreach($transitions as $trans) {
                    echo "<option value='{$trans}'";
                    // Check if the style is selected
                    if($trans === $data['transition'])
                        echo " selected=selected";
                    echo ">{$trans}</option>";
                }
                ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_name('caption'); ?>">
                <?php _e('Show captions'); ?>
            </label>
            <select class="widefat" id="<?php echo $this->get_field_id('caption'); ?>"
                    name="<?php echo $this->get_field_name('caption'); ?>">
                <?php
                foreach($caption as $c) {
                    echo "<option value='{$c}'";
                    // Check if the style is selected
                    if($c === $data['caption'])
                        echo " selected=selected";
                    echo ">{$c}</option>";
                }
                ?>
            </select>
        </p>
    <?php }

    private function display($atts) {
        // Get user id and access token
        $userId = get_option('dm-instagram_uid');
        $accessToken = get_option('dm-instagram_at');

        // If either $userId or $accessToken is empty, we don't do anything
        if($userId == false || $userId == '' || $accessToken == false || $accessToken == '')
            return;

        // Instantiate helper
        require_once(__DIR__ . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "helper.php");
        $helper = new DmInstagram_Helpers_Helper();

        // Retrieve the recent media
        $data = DmInstagram_Init::getInstance($userId, $accessToken);

        // Trim the results
        $recents = $helper->trimResults($data->data, $atts['display']);

        // Note make fotorama display as responsive
        $atts['width'] = '100%';

        // Make prettyphoto style work
        if($atts['style'] == 'default with prettyphoto') {
            $atts['prettyphoto'] = 'true';
            $atts['style'] = 'default';
        }

        // Display the template
        new DmInstagram_Views_View($atts, $recents);
    }
}