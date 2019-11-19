<?php

namespace App\Util\UtilGMG;

use stdClass;

/**
 * Description of Geocode
 * @author zaealan
 */
class Geocode {

    /**
     * API URL through which the address will be obtained.
     */
    private $serviceUrl = "://maps.googleapis.com/maps/api/geocode/json?";

    /**
     * API URL through which the address will be obtained. $validParameters
     */
    private $validParametersGeocoding = ['address', 'bounds', 'language', 'region', 'components'];

    /**
     * API URL through which the address will be obtained. $validParameters
     */
    private $validParametersReverseGeocoding = ['latlng', 'place_id', 'language', 'result_type', 'location_type', 'sensor', 'components', 'address'];

    /**
     * Array containing the query results
     */
    private $serviceResults;

    /**
     * 
     */
    public $keyTimeZone = null;

    /**
     * Constructor
     * @param string $key Google Maps Geocoding API key
     */
    public function __construct($key = '', $keyTimeZone = '') {
        $this->serviceUrl = (!empty($key)) ? 'https' . $this->serviceUrl . "key={$key}" : 'http' . $this->serviceUrl;
        isset($keyTimeZone) && $keyTimeZone != ""  ?  $this->keyTimeZone = $keyTimeZone : null;
    }

    /**
     * Returns the private $serviceUrl
     * @return string The service URL
     */
    public function getServiceUrl() {
        return $this->serviceUrl;
    }

    /**
     * get
     *
     * Sends request to the passed Google Geocode API URL and fetches the address details and returns them
     *
     * @param  string $url Google geocode API URL containing the address or latitude/longitude
     * @return bool|object false if no data is returned by URL and the detail otherwise
     */
    public function getBy($searchBy, $isReverse = false) {

        if (empty($searchBy) || !is_array($searchBy)) {
            throw new \UnexpectedValueException("Invalid GMG search parameters");
        }

        $validParameters = [];

        if ($isReverse) {
            $validParameters = $this->validParametersReverseGeocoding;
        } else {
            $validParameters = $this->validParametersGeocoding;
        }

        $url = $this->getServiceUrl();

        foreach ($searchBy as $parameter => $value) {
            if (in_array($parameter, $validParameters)) {
                $url .= "&$parameter=" . urlencode($value);
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $serviceResults = json_decode(curl_exec($ch));
        if ($serviceResults && $serviceResults->status === 'OK') {
            $this->serviceResults = $serviceResults;
            return new GMGLocation($searchBy, $this->serviceResults, [], $this->keyTimeZone);
        }

        return new GMGLocation($searchBy, new stdClass(), [], $this->keyTimeZone);
    }

}
