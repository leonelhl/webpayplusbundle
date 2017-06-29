<?php

namespace LeoX\WebPayPlusBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use LeoX\WebPayPlusBundle\Entity\WebPayLog;

class DemoWebPayPlusController extends Controller {

    public function compraAction() {
        return $this->render('WebPayPlusBundle:Demo:compra.html.twig', array(
                    'buyOrder' => '12345',
                    'amount' => '1990',
        ));
    }

    /* Metodo que comienza la comunicacion con WebPayPlus y tiene todos los datos para una transferencia */

    public function initTransactionAction(Request $request) {

        $moneda = $request->get('moneda');
        $amount = $request->get('amount');
        $buyOrder = $request->get('buyOrder');

        $webpayservice = $this->container->get('webpay_normal');
        $response = $webpayservice->initTransaction($amount, $buyOrder, $moneda);

        $tokenWebpay = '';
        $urlRedirect = '';

        if (empty($response['error'])) {
            $em = $this->getDoctrine()->getManager();
            $tokenWebpay = $response['tokenWebpay'];
            $urlRedirect = $response['urlRedirect'];
            // LLenando los Log de Transferencia
            $log = new WebPayLog();
            $log->setEstado(WebPayLog::EnProceso);
            $log->setFecha(new \DateTime('now'));
            $log->setMoneda($moneda);
            $log->setMonto($amount);
            $log->setOrdenCompra($buyOrder);
            $log->setSesion($request->getSession()->getId());
            $log->setTokenWs($tokenWebpay);
            $em->persist($log);
            $em->flush();

            /* Esta línea redirecciona completamente a la próxima URL, útil si no es necesario mostrar todos los pasos e ir 
              pasando directamente a todas las páginas de WebPay Plus */

            //return new \Symfony\Component\HttpFoundation\RedirectResponse($urlRedirect . '?token_ws=' . $tokenWebpay, 307);
        }

        return $this->render('WebPayPlusBundle:Demo:init.html.twig', array(
                    'tokenWebpay' => $tokenWebpay,
                    'urlRedirect' => $urlRedirect,
                    'error' => $response['error'],
        ));
    }

    public function transactionResultAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $token_ws = $request->get('token_ws');

        $webpayservice = $this->container->get('webpay_normal');
        $response = $webpayservice->transactionResult($token_ws);

        $token = '';
        $authorizationCode = '';
        $paymentTypeCode = '';
        $responseCode = '';
        $amount = '';
        $commerceCode = '';
        $buyOrder = '';
        $url = '';

        $log = $em->getRepository('WebPayPlusBundle:WebPayLog')->findLastLogByToken($token_ws);

        if (empty($response['error'])) {
            $token = $response['token'];
            $authorizationCode = $response['authorizationCode'];
            $paymentTypeCode = $response['paymentTypeCode'];
            $responseCode = $response['responseCode'];
            $amount = $response['amount'];
            $commerceCode = $response['commerceCode'];
            $buyOrder = $response['buyOrder'];
            $sharesNumber = $response['sharesNumber'];
            $cardnumber = $response['cardnumber'];
            $url = $response['url'];
            $transactionDate = $response['transactionDate'];

            // LLenando los Log de Transferencia
            $log->setEstado(WebPayLog::Aceptado);
            $log->setAuthorizationCode($authorizationCode);
            $log->setCardLastNumbers($cardnumber);
            $log->setFecha(new \DateTime($transactionDate));
            $log->setPaymentTypeCode($paymentTypeCode);
            $log->setResponseCode($responseCode);
            $log->setSharesNumber($sharesNumber);
            $em->persist($log);
            $em->flush();
        } else {
            $log->setEstado(WebPayLog::Rechazado);
            $em->persist($log);
            $em->flush();
        }

        return $this->render('WebPayPlusBundle:Demo:result.html.twig', array(
                    'token' => $token,
                    'authorizationCode' => $authorizationCode,
                    'paymentTypeCode' => $paymentTypeCode,
                    'responseCode' => $responseCode,
                    'amount' => $amount,
                    'commerceCode' => $commerceCode,
                    'buyOrder' => $buyOrder,
                    'url' => $url,
                    'error' => $response['error'],
        ));
    }

    /* Pagina final, aqui redirige transbank al usuario despues que termina todo el proceso  */
    public function finalAction(Request $request) {
        return $this->render('WebPayPlusBundle:Demo:final.html.twig', array(
                    'token_ws' => $request->get('token_ws'),
        ));
    }

}
