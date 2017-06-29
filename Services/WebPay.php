<?php

namespace LeoX\WebPayPlusBundle\Services;

require_once('wss/Soap.php');
require_once('wss/soap-wsse.php');
require_once('wss/soap-validation.php');
require_once('wss/class.WebService.php');

define('LOG', __DIR__ . '/logs/tbk_comunication.log');

/**
 * TRANSACCIÓN DE AUTORIZACIÓN NORMAL:
 * Una transacción de autorización normal (o transacción normal),corresponde a una solicitud de 
 * autorización financiera de un pago con tarjetas de crédito o débito, en donde quién realiza el pago
 * ingresa al sitio del comercio, selecciona productos o servicio, y el ingreso asociado a los datos de la
 * tarjeta de crédito o débito lo realiza en forma segura en Webpay
 * 
 *  Respuestas WebPay: 
 * 
 *  TSY: Autenticación exitosa
 *  TSN: autenticación fallida.
 *  TO : Tiempo máximo excedido para autenticación.
 *  ABO: Autenticación abortada por tarjetahabiente.
 *  U3 : Error interno en la autenticación.
 *  Puede ser vacío si la transacción no se autentico.
 *
 *  Códigos Resultado
 * 
 *  0  Transacción aprobada.
 *  -1 Rechazo de transacción.
 *  -2 Transacción debe reintentarse.
 *  -3 Error en transacción.
 *  -4 Rechazo de transacción.
 *  -5 Rechazo por error de tasa.
 *  -6 Excede cupo máximo mensual.
 *  -7 Excede límite diario por transacción.
 *  -8 Rubro no autorizado.
 */
class WebPay {

    private $request;
    private $container;

    function __construct($container) {
        $this->container = $container;
        $this->request = $container->get('request');
    }

    /** Descripción de codigos de resultado */
    private static $RESULT_CODES = array(
        "0" => "Transacción aprobada",
        "-1" => "Rechazo de transacción",
        "-2" => "Transacción debe reintentarse",
        "-3" => "Error en transacción",
        "-4" => "Rechazo de transacción",
        "-5" => "Rechazo por error de tasa",
        "-6" => "Excede cupo máximo mensual",
        "-7" => "Excede límite diario por transacción",
        "-8" => "Rubro no autorizado",
    );

    /**
     * Descripción según codigo de resultado Webpay (Ver Codigo Resultados) 
     * */
    function _getReason($code) {
        return WebPay::$RESULT_CODES[$code];
    }

    //Auxiliar para determinar la direccion absoluta
    private function obtenerdir() {
        $request = $this->request;
        $port = "";
        if ($request->getPort() != '80') {
            if ($request->isSecure()) {
                if ($request->getPort() != '443') {
                    $port = ':' . $request->getPort();
                }
            } else {
                $port = ':' . $request->getPort();
            }
        }
        $dir = $request->getScheme() . '://' . $request->getHost() . $port;
        return $dir;
    }

    function generateUrl($route) {
        return $this->container->get('router')->generate($route);
    }

    /* Metodo que comienza la comunicacion con WebPayPlus y tiene todos los datos para una transferencia */

    public function initTransaction($amount, $buyOrder, $moneda) {
        try {

            define('CERTIFICADO_TBK', __DIR__ . '/keys/public-tbk.' . $moneda . '.crt');
            // Configuracion parametros de la clase Webpay
            $transactionType = $this->container->getParameter('webpay.transactiontype');
            $commerceId = $this->container->getParameter('webpay.commercecode.' . $moneda); // Para iniciar la transaccion dependiendo del tipo de moneda que eligió el usuario
            $sessionId = $this->request->getSession()->getId();
            $returnUrl = $this->obtenerdir() . $this->generateUrl($this->container->getParameter('webpay.ruteresult')) . '?moneda=' . $moneda;
            $finalUrl = $this->obtenerdir() . $this->generateUrl($this->container->getParameter('webpay.rutefinal'));
            //$commerceCode = $this->container->getParameter('webpay.commercecode');

            $wsInitTransactionInput = new \wsInitTransactionInput();
            $wsTransactionDetail = new \wsTransactionDetail();

            // Variables de tipo string 
            $wsInitTransactionInput->wSTransactionType = $transactionType;
            $wsInitTransactionInput->commerceId = $commerceId;
            $wsInitTransactionInput->buyOrder = $buyOrder;
            $wsInitTransactionInput->sessionId = $sessionId;
            $wsInitTransactionInput->returnURL = $returnUrl;
            $wsInitTransactionInput->finalURL = $finalUrl;

            $wsTransactionDetail->commerceCode = $commerceId;
            $wsTransactionDetail->buyOrder = $buyOrder;
            $wsTransactionDetail->amount = $amount;

            $wsInitTransactionInput->transactionDetails = $wsTransactionDetail;

            $webpayService = new \WebPayService($this->container->getParameter('webpay.api'), $moneda);
            $initTransactionResponse = $webpayService->initTransaction(
                    array("wsInitTransactionInput" => $wsInitTransactionInput)
            );

            $date = new \DateTime('now');
            $var = '[' . $date->format('Y-m-d H:i:s') . ']';
            //Guardando los log de las peticiones realizadas en un fichero para su posterior consulta
            file_put_contents(LOG, file_get_contents(LOG) . $var . ' ' . $buyOrder . " - initTransaction - REQUEST:\n" . $webpayService->soapClient->__getLastRequest() . "\n");
            file_put_contents(LOG, file_get_contents(LOG) . $var . ' ' . $buyOrder . " - initTransaction - RESPONSE:\n" . $webpayService->soapClient->__getLastResponse() . "\n");

            // Validando de que la respuesta sea de transbank
            $xmlResponse = $webpayService->soapClient->__getLastResponse();
            $soapValidation = new \SoapValidation($xmlResponse, CERTIFICADO_TBK);
            $validationResult = $soapValidation->getValidationResult();

            if ($validationResult) {

                $wsInitTransactionOutput = $initTransactionResponse->return;
                return array(
                    'tokenWebpay' => $wsInitTransactionOutput->token,
                    'urlRedirect' => $wsInitTransactionOutput->url,
                    'error' => ""
                );
            } else {
                return array(
                    'error' => "No se pudo completar la conexi&oacute;n con Webpay",
                );
            }
        } catch (\Exception $e) {
            return array(
                'error' => "No se pudo completar la conexi&oacute;n con Webpay",
            );
        }
    }

    /* Se recomienda que el resultado de la autorización sea persistida en los sistemas del comercio, ya que este método se puede invocar una única vez por transacción */
    public function transactionResult($token) {

        try {

            $em = $this->container->get('doctrine')->getManager();
            $log = $em->getRepository('WebPayPlusBundle:WebPayLog')->findLastLogByToken($token);
            $moneda = $log->getMoneda();
            
            define('CERTIFICADO_TBK', __DIR__ . '/keys/public-tbk.' . $moneda . '.crt');
            
            /* Ac· se mostrar· el resultado de la transacciÛn */

            $webpayService = new \WebPayService($this->container->getParameter('webpay.api'), $moneda);
            $getTransactionResult = new \getTransactionResult();
            $getTransactionResult->tokenInput = $token;
            $getTransactionResultResponse = $webpayService->getTransactionResult($getTransactionResult);

            $transactionResultOutput = $getTransactionResultResponse->return;
            $wsTransactionDetailOutput = $transactionResultOutput->detailOutput;
            $buyOrder = $wsTransactionDetailOutput->buyOrder;
            // Guardando los log de las peticiones realizadas en un fichero para su posterior consulta
            $date = new \DateTime('now');
            $var = '[' . $date->format('Y-m-d H:i:s') . ']';
            file_put_contents(LOG, file_get_contents(LOG) . $var . ' ' . $buyOrder . " - transactionResult - REQUEST:\n" . $webpayService->soapClient->__getLastRequest() . "\n");
            file_put_contents(LOG, file_get_contents(LOG) . $var . ' ' . $buyOrder . " - transactionResult - RESPONSE:\n" . $webpayService->soapClient->__getLastResponse() . "\n");

            /* Validacion de firma del requerimiento de respuesta enviado por Webpay */
            $xmlResponse = $webpayService->soapClient->__getLastResponse();
            $soapValidation = new \SoapValidation($xmlResponse, CERTIFICADO_TBK);
            $validationResult = $soapValidation->getValidationResult();

            if ($validationResult) {
                // Validando que no exista transaccion duplicada para asi evitar consumir el metodo acknowledgeTransaction
                $duplicate = $em->getRepository('WebPayPlusBundle:WebPayLog')->findLogAuthorizedDuplicate($buyOrder);
                if ($duplicate) {
                    return array('error' => "Orden de Compra Previamente Autorizada / Reserva forzada");
                }
                $log = $em->getRepository('WebPayPlusBundle:WebPayLog')->findLastLogByToken($token);
                // LLenando los Log de Transferencia
                $log->setAuthorizationCode($wsTransactionDetailOutput->authorizationCode);
                $em->persist($log);
                $em->flush();

                $acknowledgeTransaction = new \acknowledgeTransaction();
                $acknowledgeTransaction->tokenInput = $token;
                $acknowledgeTransactionResponse = $webpayService->acknowledgeTransaction($acknowledgeTransaction);

                //Guardando los log de las peticiones realizadas en un fichero para su posterior consulta
                $date = new \DateTime('now');
                $var = '[' . $date->format('Y-m-d H:i:s') . ']';
                file_put_contents(LOG, file_get_contents(LOG) . $var . ' ' . $buyOrder . " - acknowledgeTransaction - REQUEST:\n" . $webpayService->soapClient->__getLastRequest() . "\n");
                file_put_contents(LOG, file_get_contents(LOG) . $var . ' ' . $buyOrder . " - acknowledgeTransaction - RESPONSE:\n" . $webpayService->soapClient->__getLastResponse() . "\n");

                $xmlResponse = $webpayService->soapClient->__getLastResponse();
                $soapValidation = new \SoapValidation($xmlResponse, CERTIFICADO_TBK);
                $validationResult = $soapValidation->getValidationResult();

                if ($validationResult) {

                    /* Validacion de firma correcta */
                    $transactionResultOutput = $getTransactionResultResponse->return;

                    $wsTransactionDetailOutput = $transactionResultOutput->detailOutput;

                    /* Esto indica que la transaccion esta autorizada */
                    if (($transactionResultOutput->VCI == "TSY" || $transactionResultOutput->VCI == "") && $wsTransactionDetailOutput->responseCode == 0) {

                        /* URL donde se debe continuar el flujo */
                        $url = $transactionResultOutput->urlRedirection;

                        /* Codigo de autorizacion */
                        $authorizationCode = $wsTransactionDetailOutput->authorizationCode;

                        /* Tipo de Pago */
                        $paymentTypeCode = $wsTransactionDetailOutput->paymentTypeCode;

                        /* Codigo de respuesta */
                        $responseCode = $wsTransactionDetailOutput->responseCode;

                        /* Monto de la transaccion */
                        $amount = $wsTransactionDetailOutput->amount;

                        /* Codigo de comercio */
                        $commerceCode = $wsTransactionDetailOutput->commerceCode;

                        /* Orden de compra enviada por el comercio al inicio de la transaccion */
                        $buyOrder = $wsTransactionDetailOutput->buyOrder;

                        /* Ultimos 4 digitos de la tarjeta */
                        $cardnumber = $transactionResultOutput->cardDetail->cardNumber;

                        $transactionDate = $transactionResultOutput->transactionDate;

                        $sharesNumber = $wsTransactionDetailOutput->sharesNumber;

                        return array(
                            'token' => $token,
                            'authorizationCode' => $authorizationCode,
                            'paymentTypeCode' => $paymentTypeCode,
                            'responseCode' => $responseCode,
                            'amount' => $amount,
                            'commerceCode' => $commerceCode,
                            'buyOrder' => $buyOrder,
                            'url' => $url,
                            'sharesNumber' => $sharesNumber,
                            'cardnumber' => $cardnumber,
                            'transactionDate' => $transactionDate,
                            'error' => ''
                        );
                    } else {
                        return array(
                            'error' => $this->_getReason($wsTransactionDetailOutput->responseCode)
                        );
                    }
                } else {
                    return array('error' => "Error enviando acknowledgeTransaction a Webpay");
                }
            } else {
                return array('error' => "Error la respuesta no viene de Webpay");
            }
        } catch (\Exception $e) {
            return array(
                'error' => "No se pudo completar la conexi&oacute;n con Webpay",
            );
        }
    }

}
