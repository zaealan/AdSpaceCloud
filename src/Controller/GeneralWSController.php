<?php

namespace App\Controller;

use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\WebService;
use App\Util\Util;
use App\Entity\AdvertPlanContactRequest;

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

//        if ($licenseToSync->getDeviceUid() != $deviceId && ($licenseToSync->getDeviceUid() != null || $licenseToSync->getDeviceUid() != '') && ($deviceId != null || $deviceId != '')) {
//            return $this->setStatusCode(WebService::HTTP_CODE_FORBIDDEN)
//                            ->respondWithError('Place logued with other active device!'
//                                    , WebService::CODE_UNAUTHORIZED, $this->getMeta($request));
//        }
//        if ($licenseToSync->getAndroidDeviceUid() != $androidDeviceId && ($licenseToSync->getAndroidDeviceUid() != null || $licenseToSync->getAndroidDeviceUid() != '') && ($androidDeviceId != null || $androidDeviceId != '')) {
//            return $this->setStatusCode(WebService::HTTP_CODE_FORBIDDEN)
//                            ->respondWithError('Place logued with other active tablet device!'
//                                    , WebService::CODE_UNAUTHORIZED, $this->getMeta($request));
//        }
//        $licenseSuspect = $em->getRepository('App:AccountLicense')->findBy(['deviceUid' => $deviceId]);
//        if (isset($licenseSuspect[0]) && $licenseSuspect[0]->getId() != $licenseToSync->getId()) {
//            return $this->setStatusCode(WebService::HTTP_CODE_FORBIDDEN)
//                            ->respondWithError('Device logued with other active AdvertPlace!'
//                                    , WebService::CODE_UNAUTHORIZED, $this->getMeta($request));
//        }
//        if ($deviceId) {
//            $licenseToSync->setDeviceUid($deviceId);
//        }
//
//        if ($androidDeviceId) {
//            $licenseToSync->setAndroidDeviceUid($androidDeviceId);
//        }

        $em->persist($licenseToSync);
        $em->flush();

//        dump($licenseToSync);
//        die;

        $actualDate = Util::getCurrentDate();

        $advertPlanRepository = $em->getRepository('App:AdvertisePlan');
        $activePlanArray = $advertPlanRepository->getActualActiveAdvertPlanForDevice($licenseToSync->getId(), $actualDate->format('Y-m-d H:i'));

//        dump($activePlanArray);
//        die;

        if (!isset($activePlanArray[0])) {
            return $this->metaResponse($request, 'No hay plan activo para este punto de publicidad...'
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

    /**
     * Funcion para hacer el polling para un ajax verificando si la peticion
     * de sincronizacion manual de abajo a arriba fue completada con exito
     * @author Aealan Z <lrobledo@kijho.com> 11/06/2016
     * @param Request $request peticion recivida por ajax
     * @param type $id id de la licencia a la que se le solicito la sincronizacion
     * @return type json con la respuesta del polling formateada para el ajax
     *
     * @Route("/createNewContactDataRecordToAdvertPlan", name="adcws_new_plan_contact_record", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function createNewContactDataRecordToAdvertPlan(Request $request) {

        if (0 !== strpos($request->headers->get('Content-Type'), WebService::CONTENT_TYPE_STRUCTURE)) {
            return $this->errorWrongArgs('Wrong Content', WebService::CODE_WRONG_ARGUMENTS);
        }

        $data = json_decode($request->getContent(), true);

//        dump($data);
//        die;

        if (!isset($data['placeNickname']) || !isset($data['androidDeviceId']) || !isset($data['contactData']) || !isset($data['advertPlanId'])) {
            return $this->errorWrongArgs('Wrong Arguments', WebService::CODE_WRONG_ARGUMENTS);
        }

        if ($data['placeNickname'] == '' || $data['androidDeviceId'] == '' || $data['contactData'] == '' || $data['advertPlanId'] == '') {
            return $this->errorWrongArgs('Empty Arguments.', WebService::CODE_WRONG_ARGUMENTS);
        }

        $licenseUserName = ['alLicenseUsername' => $data['placeNickname']];

        $em = $this->getDoctrine()->getManager();
        $accountLicenseData = $em->getRepository('App:AccountLicense')->findBy($licenseUserName);

        if (!isset($accountLicenseData[0])) {
            return $this->setStatusCode(WebService::HTTP_CODE_BAD_REQUEST)
                            ->respondWithError('AdvertPlace not found!'
                                    , WebService::CODE_OBJECT_NOT_FOUND, $this->getMeta($request));
        }

        $licenseToCheck = $accountLicenseData[0];

        if ($licenseToCheck->getAndroidDeviceUid() != $data['androidDeviceId'] && ($licenseToCheck->getAndroidDeviceUid() != null || $licenseToCheck->getAndroidDeviceUid() != '') && ($data['androidDeviceId'] != null || $data['androidDeviceId'] != '')) {
            return $this->setStatusCode(WebService::HTTP_CODE_FORBIDDEN)
                            ->respondWithError('Place logued with other active tablet device!'
                                    , WebService::CODE_UNAUTHORIZED, $this->getMeta($request));
        }

        $licenseSuspect = $em->getRepository('App:AccountLicense')->findBy(['deviceUid' => $data['androidDeviceId']]);

        if (isset($licenseSuspect[0]) && $licenseSuspect[0]->getId() != $licenseToCheck->getId()) {
            return $this->setStatusCode(WebService::HTTP_CODE_FORBIDDEN)
                            ->respondWithError('Device logued with other active AdvertPlace!'
                                    , WebService::CODE_UNAUTHORIZED, $this->getMeta($request));
        }

        $actualDate = Util::getCurrentDate();

        $advertPlanRepository = $em->getRepository('App:AdvertisePlan');
        $activePlanArray = $advertPlanRepository->getActualActiveAdvertPlanForDevice($licenseToCheck->getId(), $actualDate->format('Y-m-d H:i'));

        if (isset($activePlanArray[0])) {
            if ((int) $activePlanArray[0]['id'] !== (int) $data['advertPlanId']) {
                return $this->setStatusCode(WebService::HTTP_CODE_FORBIDDEN)
                                ->respondWithError('Los planes de publicidad no coinciden'
                                        , WebService::CODE_UNAUTHORIZED, $this->getMeta($request));
            }
        } else {
            return $this->metaResponse($request, 'No hay plan activo para este punto de publicidad...'
                            , WebService::CODE_OK_CREATED, ["Luis Enrique Robledo- Nov. 24/2019"]);
        }

        $actualAdvertPlanOfPlace = $advertPlanRepository->find($data['advertPlanId']);

        $newAdvertPlanContactRequest = new AdvertPlanContactRequest();

        $newAdvertPlanContactRequest->setLicense($licenseToCheck);
        $newAdvertPlanContactRequest->setAdvertPlan($actualAdvertPlanOfPlace);
        $newAdvertPlanContactRequest->setContactUsNotificationStatus(AdvertPlanContactRequest::NOTIFICATION_STATUS_NEW);
        $newAdvertPlanContactRequest->setContactEmail(isset($data['contactData']['email']) ? $data['contactData']['email'] : null);
        $newAdvertPlanContactRequest->setContactPhone(isset($data['contactData']['phone']) ? $data['contactData']['phone'] : null);

        $em->persist($newAdvertPlanContactRequest);
        $em->flush();

        $responseArr['status'] = WebService::CODE_SUCCESS;
        $responseArr['msg'] = 'Solicitud de contacto guardada con exito';
        $responseArr['bigbag'] = false;

        return $this->metaResponse($request, "Solicitud de contacto guardada con exito!"
                        , WebService::CODE_SUCCESS, ["Luis Enrique Robledo- Nov. 24/2019"]);
    }

}
