<?php

namespace LeoX\WebPayPlusBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use LeoX\WebPayPlusBundle\Entity\WebPayNullifyLog;

class DemoWebPayPlusNullifyController extends Controller {

    public function anularAction() {

        return $this->render('WebPayPlusBundle:DemoNullify:anular.html.twig', array(
                    'buyOrder' => '12345',
                    'totalamount' => '1990',
                    'rebackamount' => '1000',
                    'authorizationCode' => '1213',
        ));
    }

    /* Metodo que comienza la comunicacion con WebPayPlus y tiene todos los datos para una transferencia */

    public function nullifyAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $buyOrder = $request->get('buyOrder');
        $rebackamount = $request->get('rebackamount');
        
        $log = $em->getRepository('WebPayPlusBundle:WebPayLog')->findLastLogByAuth($buyOrder);
        $authorizationCode = $log->getAuthorizationCode();

        $moneda = $log->getMoneda();
        $totalamount = $log->getMonto();

        $webpayServiceNullify = $this->container->get('webpay_nullify');
        $response = $webpayServiceNullify->nullify($buyOrder, $authorizationCode, $totalamount, $rebackamount, $moneda);

        $tokenWebpayNullify = '';
        $authorizationDate = '';
        $balance = '';
        $nullifiedAmount = '';

        if (empty($response['error'])) {
            $tokenWebpayNullify = $response['tokenWebpayNullify'];
            $authorizationCode = $response['authorizationCode'];
            $authorizationDate = $response['authorizationDate'];
            $balance = $response['balance'];
            $nullifiedAmount = $response['nullifiedAmount'];
            
            // Guardando los log de la Transaccion
            $log = new WebPayNullifyLog();
            $log->setOrdenCompra($buyOrder);
            $log->setToken($response['tokenWebpayNullify']);
            $log->setMoneda($moneda);
            $log->setTotalamount($totalamount);
            $log->setRebackamount($rebackamount);
            $log->setAuthorizationCode($authorizationCode);
            $log->setAuthorizationCodeNullify($response['authorizationCode']);
            $log->setAuthorizationDate(new \DateTime($response['authorizationDate']));
            $log->setBalance($response['balance']);
            $em->persist($log);
            $em->flush();
        }

        return $this->render('WebPayPlusBundle:DemoNullify:nullify.html.twig', array(
                    'tokenWebpayNullify' => $tokenWebpayNullify,
                    'authorizationCode' => $authorizationCode,
                    'authorizationDate' => $authorizationDate,
                    'balance' => $balance,
                    'nullifiedAmount' => $nullifiedAmount,
                    'error' => $response['error'],
        ));
    }

}
