<?php

namespace App\Controller;

use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\WebService;
use App\Util\Util;

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
     * @Route("/locationLoginAndPlanValidation", name="adcws_login_plan_validation", options={ "method_prefix" = false })
     */
    public function locationLoginAndPlanValidation(Request $request) {
        $em = $this->getDoctrine()->getManager();

//        $path = Util::getValidActiveLogByBaseName($this->realContainer, 'requestForManuallySyncXXX', $this->realContainer->getParameter('level_directory_data_android') . 'requestForManuallySyncXXX0.txt');

//        $this->createFileJson($path, 'Sync Android Up Manually: Llego al check! ' . "\r");

        $placeId = $request->request->get('placeNickname');
        if ($placeId == null) {
            $placeId = $request->query->get('placeNickname');
        }

        $deviceId = $request->request->get('deviceId');
        if ($deviceId == null) {
            $deviceId = $request->query->get('deviceId');
        }
        
        $androidDeviceId = $request->request->get('androidDeviceId');
        if ($androidDeviceId == null) {
            $androidDeviceId = $request->query->get('androidDeviceId');
        }

        if ((($deviceId == '' || $deviceId == null) && ($androidDeviceId == '' || $androidDeviceId == null)) || $placeId == '' || $placeId == null) {
            return $this->setStatusCode(WebService::HTTP_CODE_BAD_REQUEST)
                            ->respondWithError('Wrong data!'
                                , WebService::CODE_OBJECT_NOT_FOUND, $this->getMeta($request));
        }
        
        $licenseToSync = $em->getRepository('App:AccountLicense')->findBy(['alLicenseUsername' => $placeId]);

        if (!isset($licenseToSync[0])) {
            return $this->setStatusCode(WebService::HTTP_CODE_BAD_REQUEST)
                            ->respondWithError('AdvertPlace not found!'
                                , WebService::CODE_OBJECT_NOT_FOUND, $this->getMeta($request));
        }
        
        $licenseToSync = $licenseToSync[0];

        if ($licenseToSync->getDeviceUid() != $deviceId && ($licenseToSync->getDeviceUid() != null || $licenseToSync->getDeviceUid() != '') && ($deviceId != null || $deviceId != '')) {
            return $this->setStatusCode(WebService::HTTP_CODE_FORBIDDEN)
                            ->respondWithError('Place logued with other active device!'
                                , WebService::CODE_UNAUTHORIZED, $this->getMeta($request));
        }
        
        if ($licenseToSync->getAndroidDeviceUid() != $androidDeviceId && ($licenseToSync->getAndroidDeviceUid() != null || $licenseToSync->getAndroidDeviceUid() != '') && ($androidDeviceId != null || $androidDeviceId != '')) {
            return $this->setStatusCode(WebService::HTTP_CODE_FORBIDDEN)
                            ->respondWithError('Place logued with other active tablet device!'
                                , WebService::CODE_UNAUTHORIZED, $this->getMeta($request));
        }

        $licenseSuspect = $em->getRepository('App:AccountLicense')->findBy(['deviceUid' => $deviceId]);

        if (isset($licenseSuspect[0]) && $licenseSuspect[0]->getId() != $licenseToSync->getId()) {
            return $this->setStatusCode(WebService::HTTP_CODE_FORBIDDEN)
                            ->respondWithError('Device logued with other active AdvertPlace!'
                                , WebService::CODE_UNAUTHORIZED, $this->getMeta($request));
        }

        if ($deviceId) {
            $licenseToSync->setDeviceUid($deviceId);
        }
        
        if ($androidDeviceId) {
            $licenseToSync->setAndroidDeviceUid($androidDeviceId);
        }

        $em->persist($licenseToSync);
        $em->flush();

        $actualDate = Util::getCurrentDate();

        $advertPlanRepository = $em->getRepository('App:AdvertisePlan');
        $activePlanArray = $advertPlanRepository->getActualActiveAdvertPlanForDevice($licenseToSync->getId(), $actualDate->format('Y-m-d H:i'));

        if (!isset($activePlanArray[0])) {
            return $this->metaResponse($request, 'There is no active AdvertPlan for this place...'
                            , WebService::CODE_OK_CREATED, ["Luis Enrique Robledo- Nov. 24/2019"]);

        } 

        $theActivePlanArray = $activePlanArray[0];

        $advertPlanFileRepository = $em->getRepository('App:AdvertisePlan');
        $activePlanFilesArray = $advertPlanFileRepository->getAdvertPlanFilesForDevice($theActivePlanArray['id']);

        $convertedActiveAdvertiseResponseArray = Util::beautifyActiveAdvertFullArray($this->realContainer, $licenseToSync, $theActivePlanArray, $activePlanFilesArray);
        
        return $this->metaResponse($request, $convertedActiveAdvertiseResponseArray
                        , WebService::CODE_SUCCESS, ["Luis Enrique Robledo- Nov. 24/2019"]);       
    }
    
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
    public function notifyAdvertPlanCollectedData(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $placeNickname = $request->request->get('placeNickname');
        if ($placeNickname == null) {
            $placeNickname = $request->query->get('placeNickname');
        }

        $advertPlanFileId = $request->request->get('advertPlanFileId');
        if ($advertPlanFileId == null) {
            $advertPlanFileId = $request->query->get('advertPlanFileId');
        }
        
        $deviceId = $request->request->get('deviceId');
        if ($deviceId == null) {
            $deviceId = $request->query->get('deviceId');
        }
        
        $numberOfWatches = $request->request->get('numberOfWatches');
        if ($numberOfWatches == null) {
            $numberOfWatches = $request->query->get('numberOfWatches');
        }
        
        $numberOfInteractions = $request->request->get('numberOfInteractions');
        if ($numberOfInteractions == null) {
            $numberOfInteractions = $request->query->get('numberOfInteractions');
        }

        if ($deviceId == '' || $deviceId == null || $advertPlanFileId == '' || $advertPlanFileId == null || $placeNickname == '' || $placeNickname == null) {
            return $this->setStatusCode(WebService::HTTP_CODE_BAD_REQUEST)
                            ->respondWithError('Wrong data!'
                                , WebService::CODE_OBJECT_NOT_FOUND, $this->getMeta($request));
        }
        
        $licenseToSync = $em->getRepository('App:AccountLicense')->findBy(['alLicenseUsername' => $placeNickname]);

        if (!isset($licenseToSync[0])) {
            return $this->setStatusCode(WebService::HTTP_CODE_BAD_REQUEST)
                            ->respondWithError('AdvertPlace not found!'
                                , WebService::CODE_OBJECT_NOT_FOUND, $this->getMeta($request));
        }
        
        $licenseToSync = $licenseToSync[0];
        
        if ($licenseToSync->getDeviceUid() != $deviceId && ($licenseToSync->getDeviceUid() != null || $licenseToSync->getDeviceUid() != '') && ($deviceId != null || $deviceId != '')) {
            return $this->setStatusCode(WebService::HTTP_CODE_FORBIDDEN)
                            ->respondWithError('Place logued with other active device!'
                                , WebService::CODE_UNAUTHORIZED, $this->getMeta($request));
        }

        $licenseSuspect = $em->getRepository('App:AccountLicense')->findBy(['deviceUid' => $deviceId]);

        if (isset($licenseSuspect[0]) && $licenseSuspect[0]->getId() != $licenseToSync->getId()) {
            return $this->setStatusCode(WebService::HTTP_CODE_FORBIDDEN)
                            ->respondWithError('Device logued with other active AdvertPlace!'
                                , WebService::CODE_UNAUTHORIZED, $this->getMeta($request));
        }
        
        $advertPlanFile = $em->getRepository('App:AdvertPlanFile')->find($advertPlanFileId);
        
        if (!$advertPlanFile) {
            return $this->setStatusCode(WebService::HTTP_CODE_BAD_REQUEST)
                            ->respondWithError('AdvertPlanFile not found!'
                                , WebService::CODE_OBJECT_NOT_FOUND, $this->getMeta($request));
        }
            
        if ($advertPlanFile->getNumberOfWatches() < (int) $numberOfWatches) {
            $advertPlanFile->setNumberOfWatches($numberOfWatches);
        }
        
        if ($advertPlanFile->getNumberOfInteractions() < (int) $numberOfInteractions) {
            $advertPlanFile->setNumberOfInteractions($numberOfInteractions);
        }

        $em->persist($advertPlanFile);
        $em->flush();
        
        return $this->metaResponse($request, "AdvertPlan file counters with '$advertPlanFileId' id updated successfully!"
                        , WebService::CODE_SUCCESS, ["Luis Enrique Robledo- Nov. 24/2019"]); 
        
    }

}
