<?php
/**
 * Class DmInstagram_Controllers_Accept
 */
class DmInstagram_Controllers_Accept {
    /**
     * Check if valid url
     * @var bool
     */
    public $isValid = false;

    /**
     * Query params from the url
     * @var array
     */
    private $data = array();

    /**
     * Decoded response
     * @var object
     */
    private $instagramObject;

    /**
     * Parse the url and get only the query
     * @param $url string
     * @return mixed
     */
    public function parseUrl($url) {
        $data = parse_url($url, PHP_URL_QUERY);

        if(is_null($data)) {
            return false;
        } else {
            // Parse successful
            parse_str($data, $this->data);
        }
    }

    /**
     * Retrieve the data from the response
     * @param $data string
     * @param $data2 string
     * @return object|bool
     */
    public function parseData($data, $data2 = '') {
        $return = false;
        if(empty($data2)) {
            if(isset($this->instagramObject->$data))
                $return = $this->instagramObject->$data;
        } else {
            if(isset($this->instagramObject->$data->$data2))
                $return = $this->instagramObject->$data->$data2;
        }

        return $return;
    }

    /**
     * Convert the json decode to an object
     * @param $response string
     * @return bool|object
     */
    private function parseResponse($response) {
        if(is_null($response))
            return false;

        $this->instagramObject = json_decode($response);

        return $this->instagramObject;
    }
    /**
     * Public Getters
     */
    public function getData($key = '') {
        if(empty($key))
            return false;

        return $this->data[$key];
    }

    public function getResponse() {
        return $this->instagramObject;
    }

    /**
     * Public Setters
     */
    public function setResponse($response) {
        $this->parseResponse($response);
    }
}