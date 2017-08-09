<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . "models" . DIRECTORY_SEPARATOR . "request.php");
require_once(__DIR__ . DIRECTORY_SEPARATOR . "controllers" . DIRECTORY_SEPARATOR . "accept.php");

/**
 * Class DmInstagram_Init
 */
class DmInstagram_Init {
    /**
     * @var string
     */
    private static $userId;

    /**
     * @var string
     */
    private static $accessToken;

    /**
     * Only instance
     * @var null
     */
    private static $instance = null;

    /**
     * Request for the response from instagram
     * @return bool|object
     */
    private static function requestMedia() {
        $accept = new DmInstagram_Controllers_Accept();
        $request = new DmInstagram_Models_Request();

        $accept->setResponse($request->getRecentMedia(self::$userId, self::$accessToken));
        // Get the response
        $data = $accept->getResponse();
        // code = 200 is success
        if($data->meta->code != 200)
            return false;
        else
            return $data;
    }

    /**
     * Singleton
     * @param $userId
     * @param $accessToken
     * @return null|object
     */
    public static function getInstance($userId, $accessToken) {
        if(is_null(self::$instance)) {
            self::$userId = $userId;
            self::$accessToken = $accessToken;
            self::$instance = self::requestMedia();
        }

        return self::$instance;
    }
}