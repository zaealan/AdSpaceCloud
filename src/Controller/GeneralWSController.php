<?php

namespace App\Controller;

use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of GeneralWSController
 * @author aealan
 * 
 * @Route("/AdSpaceWS", defaults={"_locale"="en"})
 */
class GeneralWSController extends ApiController {
    
    /**
     * Funcion para hacer el polling para un ajax verificando si la peticion
     * de sincronizacion manual de abajo a arriba fue completada con exito
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param Request $request peticion recivida por ajax
     * @param type $id id de la licencia a la que se le solicito la sincronizacion
     * @return type json con la respuesta del polling formateada para el ajax
     * 
     * @Route("/notifyAdvertPlanCollectedData", name="adcws_notify_collected_data", options={ "method_prefix" = false })
     */
    public function checkAndroidSyncManuallyCompleted(Request $request) {
        $em = $this->getDoctrine()->getManager();

//        $path = Util::getValidActiveLogByBaseName($this->realContainer, 'requestForManuallySyncXXX', $this->realContainer->getParameter('level_directory_data_android') . 'requestForManuallySyncXXX0.txt');

//        $this->createFileJson($path, 'Sync Android Up Manually: Llego al check! ' . "\r");

        $placeId = $request->request->get('placeId');
        if ($placeId == null) {
            $placeId = $request->query->get('placeId');
        }

        $advertPlanId = $request->request->get('advertPlanId');
        if ($advertPlanId == null) {
            $advertPlanId = $request->query->get('advertPlanId');
        }

//        $this->createFileJson($path, 'Sync Android Up Manually: nickname' . $paramNick . "\r");
//        $this->createFileJson($path, 'Sync Android Up Manually: lastId' . $paramLastId . "\r");

        if ((int) $advertPlanId  >= 0 && (int) $placeId >= 0) {
            $licenseToSync = $em->getRepository('App:AccountLicense')->find($placeId);

            dump($licenseToSync);
            die;
            
//            $this->createFileJson($path, 'Sync Android Up Manually: Entro 1' . $paramLastId . "\r");

            if ($licenseToSync) {
//                $this->createFileJson($path, 'Sync Android Up Manually: Entro 2' . $paramLastId . "\r");

                if ($paramNick != $licenseToSync->getAlLicenseUsername()) {
                    $responseToAjax['msg'] = 'License not found!';
                    $responseToAjax['result'] = '__KO__';

                    return $this->respondJsonAjax($responseToAjax);
                }

                $responseToAjax['result'] = '__OK__';

                $pollingResultArray = $this->checkForDownUpPolling($id, $paramLastId, $path);

                $responseToAjax['msg'] = $pollingResultArray['msg'];
                $responseToAjax['response_code'] = $pollingResultArray['response_code'];

//                $this->createFileJson($path, 'Sync Android Up Manually: Super!' . $paramLastId . "\r");
            } else {
                $responseToAjax['msg'] = 'License not found!';
                $responseToAjax['result'] = '__KO__';

//                $this->createFileJson($path, 'Sync Android Up Manually: Chango1 ' . $paramLastId . "\r");

                return $this->respondJsonAjax($responseToAjax);
            }
        } else {
//            $this->createFileJson($path, 'Sync Android Up Manually: Chango2 ' . $paramLastId . "\r");

            $responseToAjax['msg'] = 'License not found!';
            $responseToAjax['result'] = '__KO__';

            return $this->respondJsonAjax($responseToAjax);
        }

        return $this->respondJsonAjax($responseToAjax);
    }

}
