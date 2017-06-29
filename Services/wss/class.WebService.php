<?php
class getTransactionResult{
    var $tokenInput;//string
}

class getTransactionResultResponse{
    var $return;//transactionResultOutput
}

class transactionResultOutput{
    var $accountingDate;//string
    var $buyOrder;//string
    var $cardDetail;//cardDetail
    var $detailOutput;//wsTransactionDetailOutput
    var $sessionId;//string
    var $transactionDate;//dateTime
    var $urlRedirection;//string
    var $VCI;//string
}

class cardDetail{
    var $cardNumber;//string
    var $cardExpirationDate;//string
}

class wsTransactionDetailOutput{
    var $authorizationCode;//string
    var $paymentTypeCode;//string
    var $responseCode;//int
}

class wsTransactionDetail{
    var $sharesAmount;//decimal
    var $sharesNumber;//int
    var $amount;//decimal
    var $commerceCode;//string
    var $buyOrder;//string
}

class acknowledgeTransaction{
    var $tokenInput;//string
}

class acknowledgeTransactionResponse{
}

class initTransaction{
    var $wsInitTransactionInput;//wsInitTransactionInput
}

class wsInitTransactionInput{
    var $wSTransactionType;//wsTransactionType
    var $commerceId;//string
    var $buyOrder;//string
    var $sessionId;//string
    var $returnURL;//anyURI
    var $finalURL;//anyURI
    var $transactionDetails;//wsTransactionDetail
    var $wPMDetail;//wpmDetailInput
}

class wpmDetailInput{
    var $serviceId;//string
    var $cardHolderId;//string
    var $cardHolderName;//string
    var $cardHolderLastName1;//string
    var $cardHolderLastName2;//string
    var $cardHolderMail;//string
    var $cellPhoneNumber;//string
    var $expirationDate;//dateTime
    var $commerceMail;//string
    var $ufFlag;//boolean
}

class initTransactionResponse{
    var $return;//wsInitTransactionOutput
}

class wsInitTransactionOutput{
    var $token;//string
    var $url;//string
}

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

class WebPayService
{
    var $soapClient;

    private static $classmap = array('getTransactionResult'=>'getTransactionResult'
        ,'getTransactionResultResponse'=>'getTransactionResultResponse'
        ,'transactionResultOutput'=>'transactionResultOutput'
        ,'cardDetail'=>'cardDetail'
        ,'wsTransactionDetailOutput'=>'wsTransactionDetailOutput'
        ,'wsTransactionDetail'=>'wsTransactionDetail'
        ,'acknowledgeTransaction'=>'acknowledgeTransaction'
        ,'acknowledgeTransactionResponse'=>'acknowledgeTransactionResponse'
        ,'initTransaction'=>'initTransaction'
        ,'wsInitTransactionInput'=>'wsInitTransactionInput'
        ,'wpmDetailInput'=>'wpmDetailInput'
        ,'initTransactionResponse'=>'initTransactionResponse'
        ,'wsInitTransactionOutput'=>'wsInitTransactionOutput'
    );

    function __construct($url='https://webpay3gint.transbank.cl/WSWebpayTransaction/cxf/WSWebpayService?wsdl', $moneda){
        $this->soapClient = new MySoap($url,array("classmap"=>self::$classmap,"trace" => true,"exceptions" => true), $moneda);
    }

    function getTransactionResult($getTransactionResult){
        $getTransactionResultResponse = $this->soapClient->getTransactionResult($getTransactionResult);
        return $getTransactionResultResponse;
    }

    function acknowledgeTransaction($acknowledgeTransaction){
        $acknowledgeTransactionResponse = $this->soapClient->acknowledgeTransaction($acknowledgeTransaction);
        return $acknowledgeTransactionResponse;
    }

    function initTransaction($initTransaction){
        $initTransactionResponse = $this->soapClient->initTransaction($initTransaction);
        return $initTransactionResponse;
    }
}
?>
