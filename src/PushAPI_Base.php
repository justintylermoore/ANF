<?php

/**
 * @file
 * Base class for AppleNews classes.
 */

namespace ChapterThree\AppleNews;

/**
 * Base class for AppleNews classes.
 */
class PushAPI_Base extends PushAPI_Abstract {

  private $api_key_id = '';
  private $api_key_secret = '';
  private $endpoint = '';
  protected $path = '';
  protected $method = '';
  protected $arguments = [];
  protected $curl;
  protected $datetime;

  public function __construct($key, $secret, $endpoint) {
    $this->api_key_id = $key;
    $this->api_key_secret = $secret;
    $this->endpoint = $endpoint;
    $this->curl = new \Curl\Curl;
    $this->datetime = gmdate(\DateTime::ISO8601);
  }

  /**
   * Authentication.
   */
  protected function Authentication(Array $args = []) {
    $cannonical_request = strtoupper($this->method) . $this->Path() . strval($this->datetime);
    $key = base64_decode($this->api_key_secret);
    $hashed = hash_hmac('sha256', $cannonical_request, $key, true);
    $signature = rtrim(base64_encode($hashed), "\n");
    return sprintf('HHMAC; key=%s; signature=%s; date=%s', $this->api_key_id, strval($signature), $this->datetime);
  }

  /**
   * Build a path by replacing path arguments.
   */
  protected function Path() {
    $params = array();
    foreach ($this->arguments as $argument => $value) {
      $params["{{$argument}}"] = $value;
    }
    $path = str_replace(array_keys($params), array_values($params), $this->path);
    return $this->endpoint . $path;
  }

  /**
   * Build headers.
   */
  protected function Headers(Array $args = []) {
    return [
      'Authorization' => $this->Authentication($args),
    ];
  }

  /**
   * Preprocess request
   */
  protected function PreprocessRequest($method, $path, Array $arguments = []) {
    $this->method = $method;
    $this->arguments = $arguments;
    $this->path = $path;
  }

  /**
   * Get response.
   */
  protected function Response($response) {
    return $response;
  }

  /**
   * Create GET request.
   */
  public function Get($path, Array $arguments = []) {
    $this->PreprocessRequest(__FUNCTION__, $path, $arguments);
    try {
      foreach ($this->Headers() as $prop => $val) {
        $this->curl->setHeader($prop, $val);
      }
      $response = $this->curl->get($this->Path());
      $this->curl->close();
      return $this->Response($response);
    }
    catch (\ErrorException $e) {
      // Need to write ClientException handling
    }
  }

  public function Post($path, Array $arguments = [], Array $data = []) {
    $this->PreprocessRequest(__FUNCTION__, $path, $arguments);
    // See implementation in PushAPI_Post.php
    // $response = $this->curl->post($this->Path());
  }

  public function Delete($path, Array $arguments = []) {
    $this->PreprocessRequest(__FUNCTION__, $path, $arguments);
    // See implementation in PushAPI_Delete.php
    // $response = $this->curl->delete($this->Path());
  }

}
