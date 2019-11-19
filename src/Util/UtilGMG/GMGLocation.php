<?php

namespace App\Util\UtilGMG;

use stdClass;

/**
 * Description of GMGLocation
 * @author zaealan
 */
class GMGLocation {

    /** @var string Address to which the detail belong */
    public $searchBy = '';

    /** @var string Address to which the detail belong */
    public $address = '';

    /** @var string Latitude of the location */
    public $latitude = '';

    /** @var string Longitude of the location */
    public $longitude = '';

    /** @var string Country of the location */
    public $country = '';

    /** @var string Country of the location */
    public $countryShort = '';

    /** @var string Locality of the location */
    public $locality = '';

    /** @var string District of the location */
    public $district = '';

    /** @var string District of the location */
    public $districtShort = '';

    /** @var string Postal code of the location */
    public $postcode = '';

    /** @var string Town of the location */
    public $town = '';

    /** @var string Street number */
    public $streetNumber = '';

    /** @var string Street address */
    public $streetAddress = '';

    /** @var boolean Whether the location is valid or not */
    public $isValid = false;

    /** @var boolean Indica si esta busqueda tenia como resultado mas de un zipcode */
    public $hasMoreZipcodes = false;

    /** @var boolean Whether the location is valid or not */
    public $moreZipcodes = [];

    /** @var string Whether the location is valid or not */
     private $keyTimeZone = null;

    /** @var string Whether the location is valid or not */
    public $timeZoneId = '';

    /** @var string Whether the location is valid or not */
    public $timeZoneName = '';

    /**
     * Create a new GMGLocation object
     * @param string    $searchBy         Paramters of search
     * @param \stdClass $dataFromService The data retrieved from the Geocoding service
     */
    public function __construct($searchBy, stdClass $dataFromService, $alreadyConfirmedData = array(), $keyTimeZone = '') {
        $this->searchBy = $searchBy;
        isset($keyTimeZone) ? $this->keyTimeZone = $keyTimeZone: null;
        if (!empty($alreadyConfirmedData)) {
            foreach ($alreadyConfirmedData as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
        $this->populateDetail($dataFromService);
    }

    /**
     * Checks whether the data passed to the class was valid
     * @return boolean True if the data is valid and false otherwise
     */
    public function isValid() {
        return $this->isValid;
    }

    /**
     * Populates the object with the detail from the service
     * @param  \stdClass $locationDetail The address detail i.e. which was retrieved from the API
     * @return boolean          True if successfuly populated the detail and false otherwise
     */
    private function populateDetail(stdClass $locationDetail) {

        // The data from the API is returned under the `results` key
        if (!property_exists($locationDetail, 'results')) {
            $this->isValid = false;
            return false;
        }

        $auxIndexNexZipcode = 0;

        if (!isset($locationDetail->results[0])) {
            $this->isValid = false;
            return false;
        }

        $this->latitude = $locationDetail->results[0]->geometry->location->lat;
        $this->longitude = $locationDetail->results[0]->geometry->location->lng;

        if(isset($this->keyTimeZone) &&  $this->keyTimeZone != "") {
            $result = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/timezone/json?location=$this->latitude,$this->longitude&timestamp=0&key=" . $this->keyTimeZone),true);
            isset($result['timeZoneName']) ? $this->timeZoneName = $result['timeZoneName'] : null;
            isset($result['timeZoneId']) ? $this->timeZoneId = $result['timeZoneId'] : null;
        }

        foreach ($locationDetail->results as $key => $zipResult) {          
            if (in_array('postal_code', $zipResult->types)) {
                if ($this->isValid) {
                    $this->hasMoreZipcodes = true;
                    $auxiliarNewStdClass = new stdClass();

                    $auxiliarNewStdClass->results[] = $locationDetail->results[$key];
                    unset($locationDetail->results[$auxIndexNexZipcode]);

                    $this->moreZipcodes[] = new GMGLocation($this->searchBy, $auxiliarNewStdClass, ['locality' => $this->locality, 'district' => $this->district]);
                    $auxIndexNexZipcode = $key;
                    continue;
                }

                $auxIndexNexZipcode = $key;
                $this->isValid = true;

                foreach ($zipResult->address_components as $component) {
                    if (in_array('street_number', $component->types)) {
                        $this->streetNumber = $component->long_name;
                    } elseif (in_array('locality', $component->types) && $this->locality == '') {
                        $this->locality = $component->long_name;                        
                    } elseif (in_array('postal_town', $component->types)) {
                        $this->town = $component->long_name;
                    } elseif (in_array('administrative_area_level_2', $component->types)) {
                        $this->country = $component->long_name;
                    } elseif (in_array('country', $component->types)) {
                        $this->country = $component->long_name;
                        $this->countryShort = $component->short_name;
                    } elseif (in_array('administrative_area_level_1', $component->types)) {
                        $this->district = $component->long_name;
                        $this->districtShort = $component->short_name;
                    } elseif (in_array('postal_code', $component->types)) {
                        $this->postcode = $component->long_name;
                    } elseif (in_array('route', $component->types)) {
                        $this->streetAddress = $component->long_name;
                    }
                }
            } elseif (in_array('administrative_area_level_2', $zipResult->types) && $this->locality == '') {
                foreach ($zipResult->address_components as $component) {
                    if ($this->locality == '' && in_array('administrative_area_level_2', $component->types)) {
                        $this->locality = $component->long_name;
                    }
                }
            } elseif ((in_array('premise', $zipResult->types) || in_array('street_address', $zipResult->types)) && $this->locality == ''){
                foreach ($zipResult->address_components as $component) {
                    if ($this->locality == '' && in_array('sublocality_level_1', $component->types)) {
                        $this->locality = $component->long_name;
                    }elseif (in_array('locality', $component->types) && $this->locality == '') {
                        $this->locality = $component->long_name;                        
                    } elseif (in_array('route', $component->types)) {
                        $this->streetAddress = $component->long_name;
                    } elseif (in_array('street_number', $component->types)) {
                        $this->streetNumber = $component->long_name;
                    } elseif (in_array('postal_code', $component->types)) {
                        $this->postcode = $component->long_name;
                    } elseif (in_array('administrative_area_level_1', $component->types)) {
                        $this->district = $component->long_name;
                        $this->districtShort = $component->short_name;
                    } elseif (in_array('administrative_area_level_2', $component->types)) {
                        $this->country = $component->long_name;
                    } elseif (in_array('country', $component->types)) {
                        $this->country = $component->long_name;
                        $this->countryShort = $component->short_name;
                    }
                }
            
            } else {
                continue;
            }
        }

        return true;
    }

    /**
     * Gets the timeZoneId
     * @return boolean
     */
    public function getTimeZoneId() {
        return $this->timeZoneId;
    }

    /**
     * Gets the timeZoneName
     * @return boolean
     */
    public function getTimeZoneName() {
        return $this->timeZoneName;
    }

    /**
     * Gets the address
     * @return boolean
     */
    public function getHasMoreZipcodes() {
        return $this->hasMoreZipcodes;
    }

    /**
     * Gets the address
     * @return array [GMGLocation] 
     */
    public function getMoreZipcodes() {
        return $this->moreZipcodes;
    }

    /**
     * Gets the address
     * @return string
     */
    public function getSearchBy($default = '') {
        return $this->searchBy ? : $default;
    }

    /**
     * Gets the latitude of the location
     * @return string
     */
    public function getAddress($default = '') {
        return $this->address ? : $default;
    }

    /**
     * Gets the latitude of the location
     * @return string
     */
    public function getLatitude($default = '') {
        return $this->latitude ? : $default;
    }

    /**
     * Gets the longitude of the location
     * @return string
     */
    public function getLongitude($default = '') {
        return $this->longitude ? : $default;
    }

    /**
     * Gets the country of the location
     * @return string
     */
    public function getCountry($default = '') {
        return $this->country ? : $default;
    }

    /**
     * Gets the country of the location
     * @return string
     */
    public function getCountryShort($default = '') {
        return $this->countryShort ? : $default;
    }

    /**
     * Gets the locality of the location
     * @return string
     */
    public function getLocality($default = '') {
        return $this->locality ? : $default;
    }

    /**
     * Gets the district of the location
     * @return string
     */
    public function getDistrict($default = '') {
        return $this->district ? : $default;
    }

    /**
     * Gets the district of the location
     * @return string
     */
    public function getDistrictShort($default = '') {
        if ($this->countryShort == 'PR') {
            return $this->countryShort ? : $default;
        } else {
            return $this->districtShort ? : $default;
        }
    }

    /**
     * Gets the post code for the location
     * @return string
     */
    public function getPostcode($default = '') {
        return $this->postcode ? : $default;
    }

    /**
     * Gets the town for the location
     * @return string
     */
    public function getTown($default = '') {
        return $this->town ? : $default;
    }

    /**
     * Gets the street number for the location
     * @return string
     */
    public function getStreetNumber($default = '') {
        return $this->streetNumber ? : $default;
    }

    /**
     * Gets the street address
     * @return string
     */
    public function getStreetAddress($default = '') {
        return $this->streetAddress ? : $default;
    }

}
