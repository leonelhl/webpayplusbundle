<?php

namespace LeoX\WebPayPlusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="webpay_nullify_log")
 */
class WebPayNullifyLog {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * Token de la reserva
     * @ORM\Column(type="string", length=255)
     */
    protected $ordenCompra;

    /**
     * Codigo de autorizacion
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $authorizationCode;
    
    /**
     * Codigo de autorizacion
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $authorizationCodeNullify;
    
     /**
     * Fecha de la transaccion
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $authorizationDate;
    
    /**
     * Monto a pagar
     * @ORM\Column(type="integer")
     */
    protected $totalamount; 
    
    /**
     * Monto a pagar
     * @ORM\Column(type="integer")
     */
    protected $rebackamount;
    
    /**
     * Token de la transaccion dado por transbank
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $token;
    
    /**
     * Monto a pagar
     * @ORM\Column(type="integer")
     */
    protected $balance;

    /**
     * @var string
     *
     * @ORM\Column(name="moneda", type="string", length=10)
     */
    private $moneda;
    
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
     * Set ordenCompra
     *
     * @param string $ordenCompra
     *
     * @return WebPayNullifyLog
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
     * Set authorizationCode
     *
     * @param string $authorizationCode
     *
     * @return WebPayNullifyLog
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
     * Set authorizationCodeNullify
     *
     * @param string $authorizationCodeNullify
     *
     * @return WebPayNullifyLog
     */
    public function setAuthorizationCodeNullify($authorizationCodeNullify)
    {
        $this->authorizationCodeNullify = $authorizationCodeNullify;

        return $this;
    }

    /**
     * Get authorizationCodeNullify
     *
     * @return string
     */
    public function getAuthorizationCodeNullify()
    {
        return $this->authorizationCodeNullify;
    }

    /**
     * Set authorizationDate
     *
     * @param \DateTime $authorizationDate
     *
     * @return WebPayNullifyLog
     */
    public function setAuthorizationDate($authorizationDate)
    {
        $this->authorizationDate = $authorizationDate;

        return $this;
    }

    /**
     * Get authorizationDate
     *
     * @return \DateTime
     */
    public function getAuthorizationDate()
    {
        return $this->authorizationDate;
    }

    /**
     * Set totalamount
     *
     * @param integer $totalamount
     *
     * @return WebPayNullifyLog
     */
    public function setTotalamount($totalamount)
    {
        $this->totalamount = $totalamount;

        return $this;
    }

    /**
     * Get totalamount
     *
     * @return integer
     */
    public function getTotalamount()
    {
        return $this->totalamount;
    }

    /**
     * Set rebackamount
     *
     * @param integer $rebackamount
     *
     * @return WebPayNullifyLog
     */
    public function setRebackamount($rebackamount)
    {
        $this->rebackamount = $rebackamount;

        return $this;
    }

    /**
     * Get rebackamount
     *
     * @return integer
     */
    public function getRebackamount()
    {
        return $this->rebackamount;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return WebPayNullifyLog
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set balance
     *
     * @param integer $balance
     *
     * @return WebPayNullifyLog
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * Get balance
     *
     * @return integer
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Set moneda
     *
     * @param string $moneda
     *
     * @return WebPayNullifyLog
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
}
