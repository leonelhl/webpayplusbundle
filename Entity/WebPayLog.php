<?php

namespace LeoX\WebPayPlusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WebPayLog
 *
 * @ORM\Table(name="webpay_log")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="LeoX\WebPayPlusBundle\Entity\WebPayLogRepository")
 */
class WebPayLog
{
    
    const EnProceso = 1; // Se inicia una transaccion teniendo el token dado por transbank
    const Aceptado = 2; // Aceptado, es cuando pasa la autorizacion y el acknolageTransaction y todo OK
    const Rechazado = 3; // Se disparo alguna exepcion que no fue posible realizar el proceso
    
    const CLP = 1;
    const USD = 2; 
    
    const TR_NORMAL = 1;
    public static $tipos = Array(1 => 'TR_NORMAL_WS');
    public static $estados = Array(1 => 'En Proceso', 2 => 'Aceptado', 3 => 'Rechazado');

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

    /** Respuestas WebPay */
    private static $RESP_WEBPAY = array(
        "TSY" => "Autenticación exitosa",
        "TSN" => "Autenticación fallida",
        "TO" => "Tiempo máximo excedido para autenticación",
        "ABO" => "Autenticación abortada por tarjetahabiente",
        "U3" => "Error interno en la autenticación",
        "" => "No se autentico",
    );
    
    /** Respuestas WebPay */
    private static $TIPOS_MONEDA = array(
        "CLP" => "PESOS CHILENOS",
        "USD" => "Dólares",
    );
    
    /** Tipo de pago de la transacción. */
    private static $TIPOS_PAGO = array(
        "VD" => "Venta Debito",
        "VN" => "Venta Normal",
        "VC" => "Venta en cuotas",
        "SI" => "3 cuotas sin interés",
        "S2" => "2 cuotas sin interés",
        "NC" => "N Cuotas sin interés",
    );      
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="tipoTransaccion", type="integer")
     */
    private $tipoTransaccion = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="ordenCompra", type="string", length=255)
     */
    private $ordenCompra;

    /**
     * @var string
     *
     * @ORM\Column(name="token_ws", type="string", length=255, nullable=true)
     */
    private $tokenWs;

    /**
     * @var integer
     *
     * @ORM\Column(name="monto", type="integer")
     */
    private $monto;

    /**
     * @var string
     *
     * @ORM\Column(name="moneda", type="string", length=10)
     */
    private $moneda;

    /**
     * @var string
     *
     * @ORM\Column(name="sesion", type="string", length=255)
     */
    private $sesion;

    /**
     * @var integer
     *
     * @ORM\Column(name="estado", type="integer")
     */
    private $estado;

    /**
     * @var integer
     *
     * @ORM\Column(name="responseCode", type="integer", nullable=true)
     */
    private $responseCode;

    /**
     * @var string
     *
     * @ORM\Column(name="authorizationCode", type="string", length=10, nullable=true)
     */
    private $authorizationCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="cardLastNumbers", type="integer", nullable=true)
     */
    private $cardLastNumbers;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime",nullable=true)
     */
    private $fecha;

    /**
     * @var string
     *
     * @ORM\Column(name="paymentTypeCode", type="string", length=2, nullable=true)
     */
    private $paymentTypeCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="sharesNumber", type="integer", nullable=true)
     */
    private $sharesNumber;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tipoTransaccion
     *
     * @param integer $tipoTransaccion
     *
     * @return WebPayLog
     */
    public function setTipoTransaccion($tipoTransaccion)
    {
        $this->tipoTransaccion = $tipoTransaccion;

        return $this;
    }

    /**
     * Get tipoTransaccion
     *
     * @return integer
     */
    public function getTipoTransaccion()
    {
        return $this->tipoTransaccion;
    }

    /**
     * Set ordenCompra
     *
     * @param string $ordenCompra
     *
     * @return WebPayLog
     */
    public function setOrdenCompra($ordenCompra)
    {
        $this->ordenCompra = $ordenCompra;

        return $this;
    }

    /**
     * Get ordenCompra
     *
     * @return string
     */
    public function getOrdenCompra()
    {
        return $this->ordenCompra;
    }

    /**
     * Set tokenWs
     *
     * @param string $tokenWs
     *
     * @return WebPayLog
     */
    public function setTokenWs($tokenWs)
    {
        $this->tokenWs = $tokenWs;

        return $this;
    }

    /**
     * Get tokenWs
     *
     * @return string
     */
    public function getTokenWs()
    {
        return $this->tokenWs;
    }

    /**
     * Set monto
     *
     * @param integer $monto
     *
     * @return WebPayLog
     */
    public function setMonto($monto)
    {
        $this->monto = $monto;

        return $this;
    }

    /**
     * Get monto
     *
     * @return integer
     */
    public function getMonto()
    {
        return $this->monto;
    }

    /**
     * Set moneda
     *
     * @param string $moneda
     *
     * @return WebPayLog
     */
    public function setMoneda($moneda)
    {
        $this->moneda = $moneda;

        return $this;
    }

    /**
     * Get moneda
     *
     * @return string
     */
    public function getMoneda()
    {
        return $this->moneda;
    }

    /**
     * Set sesion
     *
     * @param string $sesion
     *
     * @return WebPayLog
     */
    public function setSesion($sesion)
    {
        $this->sesion = $sesion;

        return $this;
    }

    /**
     * Get sesion
     *
     * @return string
     */
    public function getSesion()
    {
        return $this->sesion;
    }

    /**
     * Set estado
     *
     * @param integer $estado
     *
     * @return WebPayLog
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return integer
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set responseCode
     *
     * @param integer $responseCode
     *
     * @return WebPayLog
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * Get responseCode
     *
     * @return integer
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Set authorizationCode
     *
     * @param string $authorizationCode
     *
     * @return WebPayLog
     */
    public function setAuthorizationCode($authorizationCode)
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    /**
     * Get authorizationCode
     *
     * @return string
     */
    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    /**
     * Set cardLastNumbers
     *
     * @param integer $cardLastNumbers
     *
     * @return WebPayLog
     */
    public function setCardLastNumbers($cardLastNumbers)
    {
        $this->cardLastNumbers = $cardLastNumbers;

        return $this;
    }

    /**
     * Get cardLastNumbers
     *
     * @return integer
     */
    public function getCardLastNumbers()
    {
        return $this->cardLastNumbers;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return WebPayLog
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set paymentTypeCode
     *
     * @param string $paymentTypeCode
     *
     * @return WebPayLog
     */
    public function setPaymentTypeCode($paymentTypeCode)
    {
        $this->paymentTypeCode = $paymentTypeCode;

        return $this;
    }

    /**
     * Get paymentTypeCode
     *
     * @return string
     */
    public function getPaymentTypeCode()
    {
        return $this->paymentTypeCode;
    }

    /**
     * Set sharesNumber
     *
     * @param integer $sharesNumber
     *
     * @return WebPayLog
     */
    public function setSharesNumber($sharesNumber)
    {
        $this->sharesNumber = $sharesNumber;

        return $this;
    }

    /**
     * Get sharesNumber
     *
     * @return integer
     */
    public function getSharesNumber()
    {
        return $this->sharesNumber;
    }
}

