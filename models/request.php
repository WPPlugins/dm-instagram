<?php
/**
 * Class DmInstagram_Models_Request
 */
class DmInstagram_Models_Request {
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
     * Type of response to be expected from Instagram. Right now only "code" is applicable.
     * @var string
     */
    private $responseType = 'code';

    /**
     * Build and return the URL where the user can login using an instagram account
     * @return bool|string
     */
    public function buildRequestUrl() {
        if(empty($this->clientId) || empty($this->redirectUri))
            return false;

        $baseRequestUrl = "https://api.instagram.com/oauth/authorize/?";

        $requestUrl = $baseRequestUrl . "client_id=" . $this->clientId
            . "&redirect_uri=" . $this->redirectUri
            . "&response_type=" . $this->responseType;

        return $requestUrl;
    }

    public function requestAccessToken($code) {
        $url = 'https://api.instagram.com/oauth/access_token';
        $data = array(
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
            'code' => $code
        );

        $results = $this->streamContextCreate($url, 'POST', $data);

        return $results;
    }

    /**
     * Send request using steam_context_create
     * @param string $url
     * @param string $method
     * @param array $data
     * @return string
     */
    private function streamContextCreate($url, $method, $data = array()) {
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => $method
            )
        );

        if(!empty($data))
            $options['http']['content'] = http_build_query($data);

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }

    /**
     * Request for recent media to instagram
     * @param string $userId
     * @param string $accessToken
     * @return string
     */
    public function getRecentMedia($userId, $accessToken) {
        $url = "https://api.instagram.com/v1/users/{$userId}/media/recent/?access_token={$accessToken}";

        return $this->streamContextCreate($url, 'GET');
    }

    /**
     * Public Setters
     */
    public function setClientId($clientId) {
        if(!is_string($clientId))
            return false;

        $this->clientId = $clientId;

        return true;
    }

    public function setClientSecret($clientSecret) {
        if(!is_string($clientSecret))
            return false;

        $this->clientSecret = $clientSecret;

        return true;
    }

    public function setRedirectUri($redirectUri) {
        if(!is_string($redirectUri))
            return false;

        $this->redirectUri = $redirectUri . '?dminstagram=1';

        return true;
    }

    /**
     * Public Getters
     */
    public function getClientId() {
        return $this->clientId;
    }

    public function getClientSecret() {
        return $this->clientSecret;
    }

    public function getRedirectUri() {
        return $this->redirectUri;
    }
}