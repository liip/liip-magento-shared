<?php

class Liip_Shared_Helper_Geocoder extends Mage_Core_Helper_Abstract
{
    /**
     * Uses the google maps geocoding API
     *
     * @return  [lat, lng, 'latitude' => lat, 'longitude' => lng]
     * @see https://developers.google.com/maps/documentation/geocoding/
     */
    public function fetchGeolocation($place)
    {
        $privateKey = Mage::getStoreConfig("liip/geocoder/key");
        $url = Mage::getStoreConfig("liip/geocoder/url");
        $client = Mage::getStoreConfig("liip/geocoder/client");
        if ($privateKey != '') {
            $url = $this->signUrl($url . '&client='. $client . '&address='.urlencode($place), $privateKey);
        } else {
            $url.='&address='.urlencode($place);
        }
        $xmlStr = Mage::getModel('liip/connection_curl', $url)->get();
        return $this->extractV3Geolocation($xmlStr);
    }

    /**
     * @param   string  $xmlStr
     * @return  array
     */
    protected function extractV3Geolocation($xmlStr)
    {
        $xml = simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOERROR);
        if ($xml === false || !isset($xml->result->geometry->location)) {
            return false;
        }

        $lat = (string)$xml->result->geometry->location->lat;
        $lng = (string)$xml->result->geometry->location->lng;
        return array(0 => $lat, 1 => $lng, 'latitude' => $lat, 'longitude' => $lng);
    }

    /**
     * @param   string  $xmlStr
     * @return  array
     */
    protected function extractGeolocation($xmlStr)
    {
        $xml = simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOERROR);
        if ($xml === false || !isset($xml->Response->Placemark->Point->coordinates)) {
            return false;
        }

        $cords = (string)$xml->Response->Placemark->Point->coordinates[0];
        $coordinates = explode(',', $cords);
        return array(0 => $coordinates[1], 1 => $coordinates[0], 'latitude' => $coordinates[1], 'longitude' => $coordinates[0]);
    }

    // Google Maps API Signature
    // Encode a string to URL-safe base64
    protected function encodeBase64UrlSafe($value)
    {
      return str_replace(array('+', '/'), array('-', '_'),
        base64_encode($value));
    }

    // Decode a string from URL-safe base64
    protected function decodeBase64UrlSafe($value)
    {
      return base64_decode(str_replace(array('-', '_'), array('+', '/'),
        $value));
    }

    // Sign a URL with a given crypto key
    // Note that this URL must be properly URL-encoded
    protected function signUrl($myUrlToSign, $privateKey)
    {
      // parse the url
      $url = parse_url($myUrlToSign);

      $urlPartToSign = $url['path'] . "?" . $url['query'];

      // Decode the private key into its binary format
      $decodedKey = $this->decodeBase64UrlSafe($privateKey);

      // Create a signature using the private key and the URL-encoded
      // string using HMAC SHA1. This signature will be binary.
      $signature = hash_hmac("sha1",$urlPartToSign, $decodedKey,  true);

      $encodedSignature = $this->encodeBase64UrlSafe($signature);

      return $myUrlToSign."&signature=".$encodedSignature;
    }
}

