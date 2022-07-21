<?php
/**
 * Magento Module developed by NoStress Commerce
 *
 * NOTICE OF LICENSE
 *
 * This program is licensed under the Koongo software licence (by NoStress Commerce).
 * With the purchase, download of the software or the installation of the software
 * in your application you accept the licence agreement. The allowed usage is outlined in the
 * Koongo software licence which can be found under https://docs.koongo.com/display/koongo/License+Conditions
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at https://store.koongo.com/.
 *
 * See the Koongo software licence agreement for more details.
 * @copyright Copyright (c) 2017 NoStress Commerce (http://www.nostresscommerce.cz, http://www.koongo.com/)
 *
 */

/**
 * Base model for REST API client
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\Api;

class Restclient extends \Nostress\Koongo\Model\AbstractModel
{
    /**
     * The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
     */
    const CURTL_CONNECT_TIMEOUT = 5;
    /**
     * The maximum number of seconds to allow cURL functions to execute.
     */
    const CURTL_RUN_TIMEOUT = 10;

    /** Conversion of returned body */
    const REQUEST_RETURN_CONVERSION_JSON_TO_ARRAY = "json_to_array"; //Convert return body from json to array
    const REQUEST_RETURN_CONVERSION_JSON_TO_OBJECT = "json_to_object"; //Convert return body from json to std object
    const REQUEST_RETURN_CONVERSION_NO_CHANGE = "no_change"; //Do not format return body

    /*
     * API call header
     */
    protected $_headers;

    /**
     * Default base url. Must be defined by certain client.
     * @var unknown_type
     */
    protected $_defaultBaseEndpointUrl = "";

    /**
     * Send http request
     * @param String  $requestType GET, POST,PUT, PATCH, GET
     * @param Resource or url $resource
     * @param Array of query $query
     * @param Array or Json of parameters $payload
     * @param boolean $payloadToJson Transform payload to json
     * @param string $returnBodyConversion Conversion of returned body.
     * @return mixed
     */
    public function sendRequest($requestType, $resource, $query = null, $payload = null, $payloadToJson = true, $returnBodyConversion = self::REQUEST_RETURN_CONVERSION_JSON_TO_ARRAY)
    {
        return $this->_sendRequest($requestType, $resource, $query, $payload, $payloadToJson, $returnBodyConversion);
    }

    /**
     * Add a custom header to the request.
     *
     * @param string $header
     * @param string $value
     */
    protected function _addHeader($header, $value)
    {
        $this->_headers[$header] = "$header: $value";
    }

    /**
     * Clean header
     */
    protected function _cleanHeader()
    {
        $this->_headers = [];
    }

    /**
     * Performs a get request
     *
     * @param Resource or url $resource
     * @param Array of query $query
     * @param string $returnBodyConversion Conversion of returned body.
     * @return mixed
     */
    protected function _sendRequestGet($resource, $query = null, $returnBodyConversion = self::REQUEST_RETURN_CONVERSION_JSON_TO_ARRAY)
    {
        return $this->_sendRequest("GET", $resource, $query, null, true, $returnBodyConversion);
    }

    /**
     * Performs a put request
     *
     * @param Resource or url $resource
     * @param Array or Json of parameters $payload
     * @param Array of query $query
     * @param string $returnBodyConversion Conversion of returned body.
     * @return mixed
     */
    protected function _sendRequestPut($resource, $payload, $query = null, $returnBodyConversion = self::REQUEST_RETURN_CONVERSION_JSON_TO_ARRAY)
    {
        return $this->_sendRequest("PUT", $resource, $query, $payload, true, $returnBodyConversion);
    }

    /**
     * Performs a post request
     *
     * @param Resource or url $resource
     * @param Array or Json of parameters $payload
     * @param boolean $toJson Transform payload to json
     * @param Array of query $query
     * @param string $returnBodyConversion Conversion of returned body.
     * @return mixed
     */
    protected function _sendRequestPost($resource, $payload, $toJson = true, $query = null, $returnBodyConversion = self::REQUEST_RETURN_CONVERSION_JSON_TO_ARRAY)
    {
        return $this->_sendRequest("POST", $resource, $query, $payload, $toJson, $returnBodyConversion);
    }

    /**
     * Performs a patch request
     *
     * @param Resource or url $resource
     * @param Array or Json of parameters $payload
     * @param boolean $toJson Transform payload to json
     * @param Array of query $query
     * @param string $returnBodyConversion Conversion of returned body.
     * @return mixed
     */
    protected function _sendRequestPatch($resource, $payload, $toJson = true, $query = null, $returnBodyConversion = self::REQUEST_RETURN_CONVERSION_JSON_TO_ARRAY)
    {
        return $this->_sendRequest("PATCH", $resource, $query, $payload, $toJson, $returnBodyConversion);
    }

    /**
     * Performs a delete request
     *
     * @param Resource or url $resource
     * @param string $returnBodyConversion Conversion of returned body.
     * @return mixed
     */
    protected function _sendRequestDelete($resource, $query = null, $returnBodyConversion = self::REQUEST_RETURN_CONVERSION_JSON_TO_ARRAY)
    {
        return $this->_sendRequest("DELETE", $resource, $query, null, true, $returnBodyConversion);
    }

    /**
     * Send http request
     * @param String  $requestType GET, POST,PUT, PATCH, GET
     * @param Resource or url $resource
     * @param Array of query $query
     * @param Array or Json of parameters $payload
     * @param boolean $payloadToJson Transform payload to json
     * @param string $returnBodyConversion Conversion of returned body.
     * @return mixed
     */
    protected function _sendRequest($requestType, $resource, $query = null, $payload = null, $payloadToJson = true, $returnBodyConversion = self::REQUEST_RETURN_CONVERSION_JSON_TO_ARRAY)
    {
        $this->_beforeRequest();
        $url = $this->_prepareUrl($resource, $query);

        if ($payloadToJson) {
            $payload = json_encode($payload);
        }

        $this->_beforeRequestUpdateHeader($url, $payload);
//     	echo $url."<br>";
//     	echo $payload."<br>";
//     	d( $this->_headers);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, 0); // 1 - print curl inter messages
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        switch ($requestType) {
            case "GET":
                curl_setopt($curl, CURLOPT_HTTPGET, 1);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
                break;
            case "POST":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            case "PATCH":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
                break;

        }
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::CURTL_CONNECT_TIMEOUT);
        curl_setopt($curl, CURLOPT_TIMEOUT, self::CURTL_RUN_TIMEOUT); //timeout in seconds

        $response = curl_exec($curl);

        $curlError = curl_error($curl);
        $curlErrorNo = curl_errno($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        curl_close($curl);

        if ($http_status >= 200 && $http_status <= 299) {
            return $this->_convertRequestReturnBody($body, $returnBodyConversion);
        } else {
            if (empty($body)) {
                $body = "Curl " . $curlError . " - Error no " . $curlErrorNo . " HTTP return code " . $http_status;
            }

            $this->_handleResponseError($url, $http_status, $body);
        }
    }

    /**
     * Convert return body by defined fomat
     * @param String $body Request returned body
     * @param String $returnBodyConversion Conversion of returned body.
     * @return mixed|unknown
     */
    protected function _convertRequestReturnBody($body, $returnBodyConversion)
    {
        switch ($returnBodyConversion) {
            case self::REQUEST_RETURN_CONVERSION_JSON_TO_ARRAY:
                return json_decode($body, true);
                break;
            case self::REQUEST_RETURN_CONVERSION_JSON_TO_OBJECT:
                return json_decode($body, false);
                break;
            case self::REQUEST_RETURN_CONVERSION_NO_CHANGE:
            default:
                return $body;
        }
    }

    /**
     * Perform operations before http request
     * @return Koongo_Api_Client_Rest_Abstract
     */
    protected function _beforeRequest()
    {
        return $this;
    }

    /**
     * Update header before http request
     * @param unknown_type $url
     * @param unknown_type $payload
     * @return Koongo_Api_Client_Rest_Abstract
     */
    protected function _beforeRequestUpdateHeader($url, $payload)
    {
        return $this;
    }

    /**
     * Prepare url from given resource and query
     * @param Resource or url $resource
     * @param Array of query $query
     * @return string
     */
    protected function _prepareUrl($resource, $query = null)
    {
        if (strpos($resource, 'http') === false) {
            $url = $this->_getEndpointUrlBase();
            $url = trim($url, "/") . "/" . trim($resource, "/");
        } else {
            $url = $resource;
        }

        if (is_array($query)) {
            $url .= '?' . http_build_query($query);
        } elseif (isset($query)) {
            $url .= '?' . $query;
        }

        return $url;
    }

    public function __call($name, $arguments)
    {
        return $this->_get($name);
    }

    public function __get($name)
    {
        return $this->_get($name);
    }

    protected function _getEndpointUrlBase()
    {
        return $this->_defaultBaseEndpointUrl;
    }

    /**
     * Handle HTTP response error
     * @param int $responseCode
     * @param array $responseBody
     *
     * @throws WebshopappApiException
     */
    protected function _handleResponseError($url, $responseCode, $responseBody)
    {
        $body = json_decode($responseBody, true);
        if (!$body) {
            $body = $responseBody;
        }
        if (isset($body['error'])) {
            $message = $body['error'];
        } elseif (isset($body['message'])) {
            $message = $body['message'];
        } else {
            $message = $responseBody;
        }

        if (isset($body['error_description'])) {
            $message .= ":" . $body['error_description'];
        }

        if (isset($body['status'])) {
            $responseCode = intval($body['status']);
        }

        throw new \Exception($message . " in " . $url, $responseCode);
    }
}
