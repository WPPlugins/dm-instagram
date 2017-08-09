<?php
/**
 * Class DmInstagram_Views_View
 */
class DmInstagram_Views_View {
    function __construct($atts, $data) {
        $this->displayTemplate($atts, $data);
    }

    /**
     * Display template
     * @param array $atts
     * @param $data
     * @return bool
     */
    private function displayTemplate($atts = array(), $data) {
        if(empty($atts))
            return false;

        extract($atts, EXTR_PREFIX_SAME, "dm");

        // config container
        $config = '';
        // Display template
        switch($style) {
            case 'default':
                if($prettyphoto == 'true')
                    include(__DIR__ . DIRECTORY_SEPARATOR . 'shortcode-default-prettyphoto.php');
                else
                    include(__DIR__ . DIRECTORY_SEPARATOR . 'shortcode-default.php');
                break;
            case 'fotorama':
                // Check if fullscreen
                if($fullscreen == 'true')
                    $config .= ' data-allow-full-screen="true"';
                // Check if responsive
                if($width != 'false')
                    $config .= " data-width='{$width}'";
                // Check if autoplay
                if($autoplay != '0')
                    $config .= " data-autoplay='{$autoplay}'";
                // Transition
                $config .= " data-transition='{$transition}'";

                if($thumbs == 'false' )
                    include(__DIR__ . DIRECTORY_SEPARATOR . 'shortcode-fotorama.php');
                else
                    include(__DIR__ . DIRECTORY_SEPARATOR . 'shortcode-fotorama-thumbs.php');
                // TODO thumbs doesn't support captions for now
                break;
            default;
                break;
        }

    }
}