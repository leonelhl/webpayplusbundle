<?php

require_once('soap-wsse.php');
require_once('xmlseclibs.php');

class MySoap extends \SoapClient {

    private $useSSL = false;

    function __construct($wsdl, $options, $moneda) {
        $locationparts = parse_url($wsdl);
        $this->useSSL = $locationparts['scheme'] == "https" ? true : false;
        
        define('CERTIFICADO_COMERCIO', __DIR__ . '/../keys/public.'.$moneda.'.crt');
        define('PRIVATE_KEY', __DIR__ . '/../keys/private.'.$moneda.'.key');
        
        return parent::__construct($wsdl, $options);
    }

    function __doRequest($request, $location, $saction, $version, $one_way = 0) {

        if ($this->useSSL) {
            $locationparts = parse_url($location);
            $location = 'https://';
            if (isset($locationparts['host']))
                $location .= $locationparts['host'];
            if (isset($locationparts['port']))
                $location .= ':' . $locationparts['port'];
            if (isset($locationparts['path']))
                $location .= $locationparts['path'];
            if (isset($locationparts['query']))
                $location .= '?' . $locationparts['query'];
        }
        $doc = new \DOMDocument('1.0');

        $doc->loadXML($request);
        $objWSSE = new WSSESoap($doc);
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array(
            'type' => 'private'
        ));

        /** False para cargar en modo texto, true para archivo */
        $objKey->loadKey(PRIVATE_KEY, TRUE);
        $options = array(
            "insertBefore" => TRUE
        );
        $objWSSE->signSoapDoc($objKey, $options);
        $objWSSE->addIssuerSerial(CERTIFICADO_COMERCIO);
        
        $objKey = new XMLSecurityKey(XMLSecurityKey::AES256_CBC);
        $objKey->generateSessionKey();
        $retVal = parent::__doRequest($objWSSE->saveXML(), $location, $saction, $version);
        
        $doc = new \DOMDocument();
        $doc->loadXML($retVal);
        return $doc->saveXML();
    }

}

?>
