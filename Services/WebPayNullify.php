<?php

namespace LeoX\WebPayPlusBundle\Services;
use LeoX\WebPayPlusBundle\Util\Util;

require_once('wss/Soap.php');
require_once('wss/soap-wsse.php');
require_once('wss/soap-validation.php');
require_once('wss/class.WebServiceNullify.php');

define('LOG', __DIR__ . '/logs/tbknullify_comunication.log');

/**
 * TRANSACCIÓN ANULACIÓN:
 */
class WebPayNullify {

    private $container;

    function __construct($container) {
        $this->container = $container;
    }

    /* Metodo que comienza la comunicacion con WebPayPlus y tiene todos los datos para una transferencia */
    public function nullify($buyOrder, $authorizationCode, $totalamount, $rebackamount, $moneda) {

        try {
            define('CERTIFICADO_TBK', __DIR__ . '/keys/public-tbk.' . $moneda . '.crt');
            // Configuracion parametros de la clase nullify
            $nullificationInput = new \nullificationInput();
            $nullificationInput->commerceId = $this->container->getParameter('webpay.commercecode.' . $moneda);
            $nullificationInput->buyOrder = $buyOrder;
            $nullificationInput->authorizedAmount = $totalamount;
            $nullificationInput->authorizationCode = $authorizationCode;
            $nullificationInput->nullifyAmount = $rebackamount;

            $webpayServiceNullify = new \WebPayServiceNullify($this->container->getParameter('webpay.apiNullify'), $moneda);

            $nullifyResponse = $webpayServiceNullify->nullify(
                    array("nullificationInput" => $nullificationInput)
            );

            //Guardando los log de las peticiones realizadas en un fichero para su posterior consulta
            $date = new \DateTime('now');
            $var = '[' . $date->format('Y-m-d H:i:s') . ']';
            file_put_contents(LOG, file_get_contents(LOG) . $var . ' ' . $buyOrder . " - nullify - REQUEST:\n" . $webpayServiceNullify->soapClient->__getLastRequest() . "\n");
            file_put_contents(LOG, file_get_contents(LOG) . $var . ' ' . $buyOrder . " - nullify - RESPONSE:\n" . $webpayServiceNullify->soapClient->__getLastResponse() . "\n");

            // Validando la respuesta de transbank
            $xmlResponse = $webpayServiceNullify->soapClient->__getLastResponse();
            $soapValidation = new \SoapValidation($xmlResponse, CERTIFICADO_TBK);
            $validationResult = $soapValidation->getValidationResult();

            if ($validationResult) {

                $nullificationOutput = $nullifyResponse->return;
                $date = new \DateTime($nullificationOutput->authorizationDate);

                return array(
                    'tokenWebpayNullify' => $nullificationOutput->token,
                    'authorizationCode' => $nullificationOutput->authorizationCode,
                    'authorizationDate' => $date->format('Y-m-d H:i:s'),
                    'balance' => $nullificationOutput->balance,
                    'nullifiedAmount' => $nullificationOutput->nullifiedAmount,
                    'error' => ""
                );
            } else {
                return array(
                    'error' => "Problema con el certificado o la respuesta no pertenece a Transbank",
                );
            }
        } catch (\SoapFault $e) {
            return array(
                'error' => "SOAP FAULT - ".Util::cleanString($e->getMessage()),
            );
        } catch (\Exception $e) {
            return array(
                'error' => "No se pudo completar la conexión con Webpay \n",
            );
        }
    }

}
