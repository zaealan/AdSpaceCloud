<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\AccountLicense;
use App\Entity\Module;
use App\Entity\Account;
use App\Entity\AdvertisePlan;
use App\Entity\AdvertPlanFile;
use App\Form\AdvertisePlanType;
use App\Form\SearchLicenseType;
use App\Form\SearchAccountType;
use App\Form\AccountLicenseType;
use App\Util\Util;
use App\Util\Paginator;
use App\Util\AccessControl;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Controller\ParametersNormalizerController;
use DateTime;
use App\Form\AdvertPlanFileType;

/**
 * Description of AdPointsAccountsController
 * @author aealan
 *
 * @Route("/adPointsAccounts", defaults={"_locale"="en"})
 */
class AdPointsAccountsController extends ParametersNormalizerController {

    /**
     * Lists all AccountLicense entities.
     *
     * @Route("/", name="adpoint_accounts", options={ "method_prefix" = false })
     */
    public function index(Request $request) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_ACCOUNT, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', ['msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente']));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

//        $paginator = $this->get('simple_paginator');
        $pageRanges = [10, 25, 50];
//        $paginator->pageRanges = $pageRanges;

        if (null != $request->query->get('itemsPerPage') && '' != $request->query->get('itemsPerPage')) {
            $itemsPerPage = (int) $request->query->get('itemsPerPage');
        } else {
            $itemsPerPage = $pageRanges[0];
        }

//        $paginator->setItemsPerPage($itemsPerPage);
//        $paginator->setMaxPagerItems(5);

        $search = [];
        $indexSearch = ['acName', 'acContactName', 'acEmail', 'deleted', 'alLicenseUsername', 'deviceUid'];
        $order = [];
        $indexOrder = ['order_by_contac_name', 'order_by_account_email', 'order_by_account_name'];

        $em = $this->getDoctrine()->getManager();

        if (null != $request->query->get('deleted') && $request->query->get('deleted') != '') {
            $statusSearch = $request->query->get('deleted');
        } else {
            $statusSearch = '';
        }

        $accountLicense = new Account();
        $form = $this->createForm(SearchAccountType::class, $accountLicense, [
            'action' => $this->generateUrl('adpoint_accounts'),
            'selected_choice' => $statusSearch,
        ]);

        $userLicensor = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $userLicensor->getId();

        if ($request->getMethod() == 'POST') {
            /* Capturamos y filtramos los parametros de busqueda */
            $form->handleRequest($request);
            $parameters = $request->request->get('levellicensor_levellicensorbundle_searchaccount');
            $search = Paginator::filterParameters($indexSearch, $parameters, Paginator::REQUEST_TYPE_ARRAY);

            return $this->redirect($this->generateUrl('adpoint_accounts', $search));
        } elseif ($request->getMethod() == 'GET') {
            /* Capturamos y filtramos los parametros de busqueda */
            $search = Paginator::filterParameters($indexSearch, $request, Paginator::REQUEST_TYPE_REQUEST);
            $search['usCompany'] = $userLicensor->getUsCompany()->getId();

            /* Capturamos y filtramos los parametros de ordenamiento */
            $order = Paginator::filterParameters($indexOrder, $request, Paginator::REQUEST_TYPE_REQUEST, true);
        }

        if (!empty($search)) {
            if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINISTRATOR')) {
                $search['userId'] = $userId;
                $entitiesToPaginate = $em->getRepository('App:Account')->accountForAdminUsers($search, $order);

                $searchL['usCompany'] = $userLicensor->getUsCompany()->getId();
                $count = count($entitiesToPaginate);
                for ($i = 0; $i < $count; ++$i) {
                    $searchL['alAccountLicense'] = $entitiesToPaginate[$i]->getId();
                    $entitiesToPaginate[$i]->licenseNum = $em->getRepository('App:AccountLicense')
                            ->accountLicensesForAdminUsers($searchL, '', true);
                }
            } else if ($this->get('security.authorization_checker')->isGranted('ROLE_LICENSE_MANAGER')) {
                $search['acUser'] = $userLicensor->getId();
                $entitiesToPaginate = $em->getRepository('App:Account')->accountForNormalUsers($search, $order);

                $searchL['alUserCreator'] = $userLicensor->getId();
                $searchL['usCompany'] = $userLicensor->getUsCompany()->getId();
                $count = count($entitiesToPaginate);
                for ($i = 0; $i < $count; ++$i) {
                    $searchL['alAccountLicense'] = $entitiesToPaginate[$i]->getId();
                    $entitiesToPaginate[$i]->licenseNum = $em->getRepository('App:AccountLicense')
                            ->accountLicensesForNormalUsers($searchL, '', true);
                }
            } else if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
                $entitiesToPaginate = $em->getRepository('App:Account')->accountForSuperAdminUsers($search, $order);

                $count = count($entitiesToPaginate);
                for ($i = 0; $i < $count; ++$i) {
                    $searchL['alAccountLicense'] = $entitiesToPaginate[$i]->getId();

                    $entitiesToPaginate[$i]->licenseNum = $em->getRepository('App:AccountLicense')->accountLicensesForSuperAdminUsers($searchL, '', true);
                }
            } else {
                $entitiesToPaginate = [];
            }

//            $entities = $paginator->paginate($entitiesToPaginate)->getResult();
        } else {
//            $entities = $paginator->paginate([])->getResult();
        }

        $entities = $entitiesToPaginate;

        /* Construimos las url para las peticiones get del ordenador y paginador */
        $params = Paginator::getUrlFromParameters($indexSearch, $search);
        $orderBy = Paginator::getUrlOrderFromParameters($indexOrder, $order);

        return $this->render('Account\index.html.twig', array(
                    'entities' => $entities,
                    'menu' => 'accounts',
                    'form' => $form->createView(),
//                    'paginator' => $paginator,
                    'params' => $params,
                    'search' => $search,
                    'orderBy' => $orderBy,
                    'itemsPerPage' => $itemsPerPage
        ));
    }

    /**
     * Lists all AccountLicense entities by account company.
     *
     * @Route("/{accountId}/AdPointsByCompany", name="adpoint_points_list", options={ "method_prefix" = false })
     */
    public function licensesListFromAccount(Request $request, $accountId) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_LICENSE, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', ['msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente']));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

//        $paginator = $this->get('simple_paginator');
        $pageRanges = [10, 25, 50];
//        $paginator->pageRanges = $pageRanges;

        if (null != $request->query->get('itemsPerPage') && '' != $request->query->get('itemsPerPage')) {
            $itemsPerPage = (int) $request->query->get('itemsPerPage');
        } else {
            $itemsPerPage = $pageRanges[0];
        }

//        $paginator->setItemsPerPage($itemsPerPage);
//        $paginator->setMaxPagerItems(5);

        $search = [];
        $indexSearch = ['alContacName', 'alLicenseEmail', 'alLicenseStatus', 'alRestaurantName', 'alLicenseUsername', 'deviceUid'];
        $order = [];
        $indexOrder = ['order_by_contac_name', 'order_by_license_email', 'order_by_restaurant_name', 'order_by_nickname'];

        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('App:Account')->find($accountId);

        if (null != $request->query->get('alLicenseStatus') && $request->query->get('alLicenseStatus') != '') {
            $statusSearch = $request->query->get('alLicenseStatus');
        } else {
            $statusSearch = '';
        }

        $accountLicense = new AccountLicense();
        $form = $this->createForm(SearchLicenseType::class, $accountLicense, [
            'action' => $this->generateUrl('adpoint_points_list', ['accountId' => $accountId]),
            'selected_choice' => $statusSearch,
        ]);

        $userLicensor = $this->get('security.token_storage')->getToken()->getUser();

        if ($request->getMethod() == 'POST') {
            /* Capturamos y filtramos los parametros de busqueda */
            $form->handleRequest($request);
            $parameters = $request->request->get('levellicensor_levellicensorbundle_searchlicense');
            $search = Paginator::filterParameters($indexSearch, $parameters, Paginator::REQUEST_TYPE_ARRAY);
            $search['accountId'] = $accountId;
            $search['alAccountLicense'] = $accountId;

            return $this->redirect($this->generateUrl('adpoint_points_list', $search));
        } elseif ($request->getMethod() == 'GET') {
            /* Capturamos y filtramos los parametros de busqueda */
            $search = Paginator::filterParameters($indexSearch, $request, Paginator::REQUEST_TYPE_REQUEST);
            $search['accountId'] = $accountId;
            $search['alAccountLicense'] = $accountId;
            $search['usCompany'] = $userLicensor->getUsCompany()->getId();

            /* Capturamos y filtramos los parametros de ordenamiento */
            $order = Paginator::filterParameters($indexOrder, $request, Paginator::REQUEST_TYPE_REQUEST, true);
        }

        if (!empty($search)) {
            if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINISTRATOR')) {
                $entitiesToPaginate = $em->getRepository('App:AccountLicense')
                        ->accountLicensesForAdminUsers($search, $order, false);
            } else if ($this->get('security.authorization_checker')->isGranted('ROLE_LICENSE_MANAGER')) {
                $search['usCompany'] = $userLicensor->getUsCompany()->getId();
                $search['alUserCreator'] = $userLicensor->getId();
                $entitiesToPaginate = $em->getRepository('App:AccountLicense')
                        ->accountLicensesForNormalUsers($search, $order, false);
            } else if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
                $entitiesToPaginate = $em->getRepository('App:AccountLicense')
                        ->accountLicensesForSuperAdminUsers($search, $order, false);
            } else {
                $entitiesToPaginate = [];
            }

            foreach ($entitiesToPaginate as $key => $license) {
                $actualDate = Util::getCurrentDate()->format('Y-m-d H:i:s');

                $activeAdvertPlans = $em->getRepository('App:AdvertisePlan')->getActualActiveAdvertPlanForDevice($license[0]->getId(), $actualDate);
                $cheduledAdvertPlans = $em->getRepository('App:AdvertisePlan')->getCheduledAdvertPlanForDevice($license[0]->getId(), $actualDate);
                $oldAdvertPlans = $em->getRepository('App:AdvertisePlan')->getOldAdvertPlanForDevice($license[0]->getId(), $actualDate);

                $entitiesToPaginate[$key][0]->activeNum = count($activeAdvertPlans);
                $entitiesToPaginate[$key][0]->cheduledNum = count($cheduledAdvertPlans);

                if (count($oldAdvertPlans) > 9) {
                    $entitiesToPaginate[$key][0]->oldNum = '+9';
                } else {
                    $entitiesToPaginate[$key][0]->oldNum = count($oldAdvertPlans);
                }
            }

//            $entities = $paginator->paginate($entitiesToPaginate)->getResult();
        } else {
//            $entities = $paginator->paginate([])->getResult();
        }

        $entities = $entitiesToPaginate;

        /* Construimos las url para las peticiones get del ordenador y paginador */
        $params = Paginator::getUrlFromParameters($indexSearch, $search);
        $orderBy = Paginator::getUrlOrderFromParameters($indexOrder, $order);

        return $this->render('Account\accountLicensesList.html.twig', [
                    'entities' => $entities,
                    'account' => $account,
                    'menu' => 'accounts',
                    'form' => $form->createView(),
//                    'paginator' => $paginator,
                    'params' => $params,
                    'search' => $search,
                    'orderBy' => $orderBy,
                    'itemsPerPage' => $itemsPerPage
        ]);
    }

    /**
     * Displays a form to create a new AccountLicense entity.
     *
     * @Route("/{id}/new", name="adpoint_point_new", options={ "method_prefix" = false })
     */
    public function newAccountLicense(Request $request, $id = 0) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_LICENSE_CREATE, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', ['msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente']));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $em = $this->getDoctrine()->getManager();
        $entityAccount = $em->getRepository('App:Account')->find($id);
        $entity = new AccountLicense();
        $entity->setAlAccountLicense($entityAccount);
        $form = $this->createCreateForm($entity);

        $countries = $em->getRepository('App:Country')->findAll();
        $states = $em->getRepository('App:State')->findBy(['stCountry' => 1]);

        $idIsKijho = $em->getRepository('App:Company')->findBy(['coIsKijho' => 1]);

        $currentCompany = $this->get('security.token_storage')->getToken()->getUser()->getUsCompany()->getId();

        $arrFind = ["lr.id", "IDENTITY(lr.lrCompanyId) as account", "lr.lrDevicesType", "lr.lrPrice"];
        $arrFindBy = [
            ["lrCompanyId", "lrCompanyId1", $idIsKijho, "AND"],
            ["lrCompanyId", "lrCompanyId2", $currentCompany, "OR"]
        ];

        $licenseRate = $em->getRepository('App:LicenseRate')->getDataRateByCompany($arrFind, $arrFindBy);

        $arrRate = [];
        foreach ($licenseRate as $rate) {
            if ($rate['account'] === $currentCompany) {
                array_push($arrRate, $rate);
            }
        }

        if (count($arrRate) > 0) {
            $licenseRate = $arrRate;
        }

        return $this->render('AccountLicense\new.html.twig', [
                    'entity' => $entity,
                    'countries' => $countries,
                    'states' => $states,
                    'form' => $form->createView(),
                    'rate' => $licenseRate,
                    'menu' => 'accounts',
        ]);
    }

    /**
     * Creates a new AccountLicense entity.
     *
     * @Route("/create", name="adpoint_point_create", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function create(Request $request) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_LICENSE_CREATE, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', array('msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente')));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $userSession = $this->get('security.token_storage')->getToken()->getUser();

        $entity = new AccountLicense();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $params = $request->request->getIterator()->getArrayCopy();

        $notificationMessage = '';
        $booleanIsOk = true;
        $restaurantName = "";

//        $typeValueArray = [];
//        $typeValueArray[] = ['type' => 'regex', 'pattern' => "/^[A-Za-z0-9 _]*[A-Za-z0-9][A-Za-z0-9 _]*$/", 'field' => 'Contact Name', 'message' => 'Allowed numbers and letters only in <strong>%s</strong> field!', 'data' => $entity->getAlContacName()];
////        $typeValueArray[] = ['type' => 'regex', 'pattern' => "/^[A-Za-z0-9 _]*[A-Za-z0-9][A-Za-z0-9 _]*$/", 'field' => 'Restaurant Name', 'message' => 'Allowed numbers and letters only in <strong>%s</strong> field!', 'data' => $entity->getAlRestaurantName()];
//        $typeValueArray[] = ['type' => 'regex', 'pattern' => "/^.{1,90}$/", 'field' => 'Address', 'message' => 'Allowed a maximum of 90 characters in <strong>%s</strong> field!', 'data' => $entity->getAlAddres()];
//
//        $validationResult = ValidatorUtil::validateThis($this->get('symfony_validator'), $typeValueArray);

        if ($form->isValid()) {
            $entity->setAlDateCreated(new \DateTime('now'));
            $entity->setAlUserCreator($userSession);

//            $resutlArray = Util::validateAndSaveCityZipcodeBlock($this->realContainer, $em, $params, $entity);
            $resutlArray = Util::validateAndSaveAddressAutoComplete($this->realContainer, $em, $params, $entity);

            if ($resutlArray['status']) {
                $booleanIsOk = $resutlArray['status'];
                $entity = $resutlArray['entity'];

                $ruta = $this->getParameter('app_directory_versions_dir');
                $lastJsonRuta = $ruta . 'lastRelease.json';

                if (is_file($lastJsonRuta)) {
                    $lastJson = file_get_contents($lastJsonRuta);
                    $lastVersionArr = json_decode($lastJson, true);
                    $versionStr = 'version';

                    if (isset($lastVersionArr[$versionStr])) {
                        $entity->setAndroidVersionName($lastVersionArr[$versionStr]);
                    }
                }

                $licenseNickName = $params['levellicensor_levellicensorbundle_accountlicense']['alRestaurantName'];

                $licenseNickName = Util::replaceCharactersEspecials($licenseNickName);

                $licenseNickName = strtolower(substr($licenseNickName, 0, 8) . '_');

                /* se pone estado inicial en 0 indicando que esta creada y que necesita activarse */
                $entity->setAlLicenseStatus(AccountLicense::LICENSE_STATUS_ACTIVE);
                $restaurantName = $entity->getAlRestaurantName();

                $entity->setLevelZeroPercentage($entity->getLevelZeroPercentage() / 100);
                $entity->setLevelZeroGatewayPercentage($entity->getLevelZeroGatewayPercentage() / 100);

                $em->persist($entity);
                $em->flush();

                $insertId = $entity->getId();
                $licenseNickName .= $insertId;
                $entity->setAlLicenseUsername($licenseNickName);

                /**
                 * Se inicia generación de ***l1c3ns3_k3y***
                 * Se genera un ID a través de la función uniqId de php. Una vez se
                 * genera el Id, se concatena al nickname generado anteriormente, para
                 * posteriormente ser encriptado a través del servicio Cipher del
                 * proyecto:
                 *
                 * @prefix : insertId
                 * @prelicense key format: licenseNickName + "-" + uniqId(prefix)
                 * @final license key: {CipherService} encrypt(prelicense key format)
                 */
                /*
                 * Se pone el nickname como numero de licencia
                 * temporalmetne
                 */
                $entity->setAlLicenseKey($licenseNickName);

                $em->persist($entity);
                $em->flush();

//                $cantClient = 0;
//
//                if (isset($params["hdPrices"]["alCantDevices"])) {
//                    $cantClient = $params["hdPrices"]["alCantDevices"];
//                }
//
//                $sumPrice = $params["hdPrices"]["server"];

                /* crear base de datos */
//                $this->setDataLicense($em, $entity);

                /*
                 * Enviar correos informando la creacion de la licencia
                 */
//                $account = $em->getRepository('App:Account')->find($entity->getAlAccountLicense());
//                $arrInfo = ["nameTo" => $entity->getAlContacName(),
//                    "emailTo" => [$entity->getAlLicenseEmail() => $entity->getAlContacName()],
//                    "emailCc" => [$account->getAcEmail() => $account->getAcContactName()],
//                    "nameLicense" => $entity->getAlRestaurantName(),
//                    "nickname" => $licenseNickName,
//                    "qtydevice" => ($cantClient + 1),
//                    "sumTotal" => $sumPrice];
//                $this->mailAccountLicense($arrInfo);

                return $this->redirect($this->generateUrl('adpoint_points_list', ['accountId' => $entity->getAlAccountLicense()->getId()]));
            } else {
                $notificationMessage = $resutlArray['message'];

                $this->get('session')->getFlashBag()->add('msgError', $notificationMessage);
            }
        } else {
            $notificationMessage = 'Invalid form parameters!';

            if (!$validationResult[0]) {
                foreach ($validationResult[1] as $value) {
                    if (!$value['isValid']) {
                        $this->get('session')->getFlashBag()->add('msgError', sprintf($value['message'], $value['field']));
                    }
                }
            }
        }

        $countries = $em->getRepository('App:Country')->findAll();

        $idIsKijho = $em->getRepository('App:Company')->findBy(['coIsKijho' => 1]);

        $currentCompany = $this->get('security.token_storage')->getToken()->getUser()->getUsCompany()->getId();

        $arrFind = ["lr.id", "IDENTITY(lr.lrCompanyId) as account", "lr.lrDevicesType", "lr.lrPrice"];
        $arrFindBy = [
            ["lrCompanyId", "lrCompanyId1", $idIsKijho, "AND"],
            ["lrCompanyId", "lrCompanyId2", $currentCompany, "OR"]
        ];

        $licenseRate = $em->getRepository('App:LicenseRate')->getDataRateByCompany($arrFind, $arrFindBy);
        $arrRate = [];

        foreach ($licenseRate as $rate) {
            if ($rate['account'] === $currentCompany) {
                array_push($arrRate, $rate);
            }
        }

        if (count($arrRate) > 0) {
            $licenseRate = $arrRate;
        }

        return $this->render('AccountLicense\new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'menu' => 'accounts',
                    'isOk' => $booleanIsOk,
                    'notificationMessage' => $notificationMessage,
                    'states' => "",
                    'rate' => $licenseRate,
                    'countries' => $countries
        ));
    }

    /**
     * Lists all advertise plans for an advertise point.
     *
     * @Route("/{id}/adPlanList", name="adpoint_adplans_list", options={ "method_prefix" = false })
     */
    public function adPlansList(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('App:AccountLicense')
                ->find($id);

        if (!$entity) {
            throw $this->createAccessDeniedException('License not found');
        }

        $entities = $em->getRepository('App:AdvertisePlan')->findBy(['advertPlace' => $id]);

        return $this->render('AccountLicense\Sublicense\index.html.twig', [
                    'entities' => $entities,
                    'entity' => $entity,
                    'menu' => 'accounts',
        ]);
    }

    /**
     * Displays a form to create a new AdPlacePlan entity.
     *
     * @Route("/{id}/adPlanNew", name="adpoint_adplans_new", options={ "method_prefix" = false })
     */
    public function newSublicense(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_LICENSE_CREATE, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', ['msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente']));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entityLicense = $em->getRepository('App:AccountLicense')->find($id);

        if (!$entityLicense) {
            throw $this->createAccessDeniedException('License not found');
        }

        $entity = new AdvertisePlan();
        $entity->setAdvertPlace($entityLicense);
        $form = $this->createSublicenseForm($entity, $this->realContainer->getParameter('default_clients_per_plan'));

        for ($i = 0; $i < $this->realContainer->getParameter('default_clients_per_plan'); ++$i) {
            $entityFile = new AdvertPlanFile();
            $fileForms[] = $this->createAdvertPlanFileForm($entityFile, ($i + 2))->createView();
        }

        return $this->render('AccountLicense\Sublicense\new.html.twig', [
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'clientsNumber' => $this->realContainer->getParameter('default_clients_per_plan'),
                    'showImagesForm' => false,
                    'fileForms' => $fileForms,
                    'menu' => 'accounts',
                    'isNew' => true
        ]);
    }

    /**
     * Creates a form to create a AccountLicense entity.
     *
     * @param AccountLicense $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSublicenseForm(AdvertisePlan $entity, $clientsNumber, $selectedChoice = null, $selectedSeconds = null) {
        $form = $this->createForm(AdvertisePlanType::class, $entity, [
            'action' => $this->generateUrl('adpoint_adplans_create', ['id' => $entity->getAdvertPlace()->getId()]),
            'clientsNumber' => $clientsNumber,
            'selected_choice' => $selectedChoice,
            'selected_seconds_choice' => $selectedSeconds,
        ]);

        return $form;
    }

    /**
     * Creates a form to create a AccountLicense entity.
     *
     * @param AccountLicense $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createAdvertPlanFileForm(AdvertPlanFile $entity, $formNumber, $selectedChoice = null, $selectedSeconds = null) {

        $form = $this->createForm(AdvertPlanFileType::class, $entity, [
            'action' => '#',
            'selected_choice' => $selectedChoice,
            'selected_seconds_choice' => $selectedSeconds,
            'form_number' => $formNumber
        ]);

        return $form;
    }

    /**
     * Crear AdvertisePlan
     * @param Request $request
     * @param type $id
     * @param type $channel
     * @param type $name
     * @return type
     *
     * @Route("/{id}/adPlanCreate", name="adpoint_adplans_create", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function createAdvertPlan(Request $request, $id) {

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirect($this->generateUrl('adspace_login', ['msg' => 'You need superadmin access!']));
        }

        $em = $this->getDoctrine()->getManager();

        $params = $request->request->getIterator()->getArrayCopy();

        $license = $em->getRepository('App:AccountLicense')->find($id);

        if (!$license) {
            throw $this->createAccessDeniedException('No license found!');
        }

        $validChannelName = true;

        $subLicenesWithChannel = $em->getRepository('App:AdvertisePlan')
                ->findBy(['name' => $params['adspace_sublicense']['name']]);

        if (!empty($subLicenesWithChannel)) {
            $validChannelName = false;
        }

        $parametersArray = $request->request->getIterator()->getArrayCopy();

        $startingDate = $parametersArray['adspace_sublicense']['startingDate'];
        $endingDate = $parametersArray['adspace_sublicense']['endingDate'];

        $sublicense = null;
        if (isset($parametersArray['adspace_sublicense']['id']) && $parametersArray['adspace_sublicense']['id']) {
            $sublicense = $em->getRepository('App:AdvertisePlan')->find($parametersArray['adspace_sublicense']['id']);
        }

        if (!$sublicense) {
            $sublicense = new AdvertisePlan();
        }

        try {
            $auxNowDate = Util::getCurrentDate();

            $startingDateTime = new DateTime($startingDate);
            $startingDateTime->modify('-1 day');
            $endingDateTime = new DateTime($endingDate);

            $parametersArray['adspace_sublicense']['startingDate'] = $startingDateTime->format('m/d/Y H:i');
            $parametersArray['adspace_sublicense']['endingDate'] = $endingDateTime->format('m/d/Y H:i');

            $request->request->replace($parametersArray);

            $actualDate = Util::getCurrentDate();

            $sublicense->setAdvertPlace($license);

            $sublicense->setCreatedDate($actualDate);
            $sublicense->setStartingDate($startingDateTime);
            $sublicense->setEndingDate($endingDateTime);
            $sublicense->setRerunTimes(0);

            if ($startingDateTime < $auxNowDate && $auxNowDate < $endingDateTime) {
                $sublicense->setStatus(AdvertisePlan::ADVERT_PLAN_STATUS_RUNNING);
            } else {
                $sublicense->setStatus(AdvertisePlan::ADVERT_PLAN_STATUS_SCHEDULED);
            }

            $someDefaultPoint = 1;
            $baseNumberOfAvertises = $someDefaultPoint * 15;

//            dump($parametersArray['adspace_sublicense']['clientsNumber']);
//            die;

            $sublicense->setTimeDurationInSeconds($baseNumberOfAvertises);

//            dump($parametersArray);
//            dump($sublicense);

            $actualClientsNumeber = (int) $sublicense->getClientsNumber();

//            dump($sublicense);
//            die;

            $sublicense->setClientsNumber($parametersArray['adspace_sublicense']['clientsNumber']);

            $sublicense->setName($parametersArray['adspace_sublicense']['name']);
            $sublicense->setDescription($parametersArray['adspace_sublicense']['description']);

            $em->persist($license);
            $em->persist($sublicense);

            $em->flush();

//            dump($request);
//            dump($actualClientsNumeber);
            //////////// Hacer una consulta para que me traiga la cantidad de clientes que tiene ingresados un plan de publicidad y conforme a esto enviar un contador que con diferencia del total
            // de clientes indicados para el plan de publicidad, determine cuantos mas hay que guardar y faltan por guardar.
            if ($actualClientsNumeber) {
                for ($i = 0; $i < $actualClientsNumeber; ++$i) {
                    $entityFile = new AdvertPlanFile();
                    $auxForm = $this->createAdvertPlanFileForm($entityFile, $i);
                    $auxForm->handleRequest($request);

                    $fileForms[] = $auxForm->createView();

                    // fileName Monitor Image

                    $advertFile = $auxForm['fileName']->getData();

//                    dump($auxForm);
//                    dump($advertFile);
//                    die;

                    if ($advertFile) {
                        $originalFilename = pathinfo($advertFile->getClientOriginalName(), PATHINFO_FILENAME);

                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $advertFile->guessExtension();

                        try {
                            $isDirectoryReady = Util::createLicenseDirectory($this->realContainer, $license, $license->getAlLicenseUsername());
                            $theAccountLicenseDirector = $isDirectoryReady['directory'];

                            if (!isset($isDirectoryReady['result']) || $isDirectoryReady['result'] == '__KO__') {
                                $msnError = 'An error occurred while creating your directory in server ';
                                $this->get('session')->getFlashBag()->add('msgError', "AdPointPlan error <strong>" . $msnError . "</strong>");
                            } else {
                                // Archivo Monitor
                                $entityFile->setFileName($newFilename);

                                $entityFile->setOriginalName($originalFilename);
                                if ($advertFile && $advertFile->guessClientExtension() == 'png') {
                                    $entityFile->setExtension('jpg');
                                    $entityFile->setMimetype('image/jpg');
                                } else {
                                    $entityFile->setMimetype($advertFile->getMimeType());
                                    $entityFile->setExtension($advertFile->getClientOriginalExtension());
                                }
                                $entityFile->setSize($advertFile->getSize());

                                $entityFile->setAdvertPlan($sublicense);
                                $entityFile->setLicense($license);
                                $entityFile->setAdvertPlan($sublicense);
                                $entityFile->setSorting($i + 1);
                                $entityFile->setIsUploadedInAws(false);

                                $entityFile->setTitle(isset($params['title']) ? $params['title'] : null);
                                $entityFile->setDescription(isset($params['description']) ? $params['description'] : null);

                                $advertFile->move(
                                        $theAccountLicenseDirector,
                                        $newFilename
                                );
                            }
                        } catch (\Exception $e) {
                            $this->get('session')->getFlashBag()->add('msgError', "AdPlacePlan monitor image error <strong>" . $ex->getMessage() . "</strong>");
                        }
                    }

                    // backGroundFileName

                    $advertFile = $auxForm['backGroundFileName']->getData();

                    if ($advertFile) {
                        $originalFilename = pathinfo($advertFile->getClientOriginalName(), PATHINFO_FILENAME);

                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $advertFile->guessExtension();

                        try {
                            $isDirectoryReady = Util::createLicenseDirectory($this->realContainer, $license, $license->getAlLicenseUsername());
                            $theAccountLicenseDirector = $isDirectoryReady['directory'];

                            if (!isset($isDirectoryReady['result']) || $isDirectoryReady['result'] == '__KO__') {
                                $msnError = 'An error occurred while creating your directory in server ';
                                $this->get('session')->getFlashBag()->add('msgError', "AdPointPlan error <strong>" . $msnError . "</strong>");
                            } else {
                                // Archivo Monitor
                                $entityFile->setBackGroundFileName($newFilename);
                                $entityFile->setOriginalBackGroundName($originalFilename);
                                if ($advertFile && $advertFile->guessClientExtension() == 'png') {
                                    $entityFile->setBackGroundExtension('jpg');
                                    $entityFile->setBackGroundMimetype('image/jpg');
                                } else {
                                    $entityFile->setBackGroundMimetype($advertFile->getMimeType());
                                    $entityFile->setBackGroundExtension($advertFile->getClientOriginalExtension());
                                }
                                $entityFile->setBackGroundSize($advertFile->getSize());

                                $advertFile->move(
                                        $theAccountLicenseDirector,
                                        $newFilename
                                );
                            }
                        } catch (\Exception $e) {
                            $this->get('session')->getFlashBag()->add('msgError', "AdPlacePlan Background image error <strong>" . $ex->getMessage() . "</strong>");
                        }
                    }

                    // logoFileName

                    $advertFile = $auxForm['logoFileName']->getData();

                    if ($advertFile) {
                        $originalFilename = pathinfo($advertFile->getClientOriginalName(), PATHINFO_FILENAME);

                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $advertFile->guessExtension();

                        try {
                            $isDirectoryReady = Util::createLicenseDirectory($this->realContainer, $license, $license->getAlLicenseUsername());
                            $theAccountLicenseDirector = $isDirectoryReady['directory'];

                            if (!isset($isDirectoryReady['result']) || $isDirectoryReady['result'] == '__KO__') {
                                $msnError = 'An error occurred while creating your directory in server ';
                                $this->get('session')->getFlashBag()->add('msgError', "AdPointPlan error <strong>" . $msnError . "</strong>");
                            } else {
                                // Archivo Monitor
                                $entityFile->setLogoFileName($newFilename);
                                $entityFile->setOriginalLogoName($originalFilename);
                                if ($advertFile && $advertFile->guessClientExtension() == 'png') {
                                    $entityFile->setLogoExtension('jpg');
                                    $entityFile->setLogoMimetype('image/jpg');
                                } else {
                                    $entityFile->setLogoMimetype($advertFile->getMimeType());
                                    $entityFile->setLogoExtension($advertFile->getClientOriginalExtension());
                                }
                                $entityFile->setLogoSize($advertFile->getSize());

                                $advertFile->move(
                                        $theAccountLicenseDirector,
                                        $newFilename
                                );
                            }
                        } catch (\Exception $e) {
                            $this->get('session')->getFlashBag()->add('msgError', "AdPlacePlan Background image error <strong>" . $ex->getMessage() . "</strong>");
                        }
                    }

                    // dev1FileName

                    $advertFile = $auxForm['dev1FileName']->getData();

                    if ($advertFile) {
                        $originalFilename = pathinfo($advertFile->getClientOriginalName(), PATHINFO_FILENAME);

                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $advertFile->guessExtension();

                        try {
                            $isDirectoryReady = Util::createLicenseDirectory($this->realContainer, $license, $license->getAlLicenseUsername());
                            $theAccountLicenseDirector = $isDirectoryReady['directory'];

                            if (!isset($isDirectoryReady['result']) || $isDirectoryReady['result'] == '__KO__') {
                                $msnError = 'An error occurred while creating your directory in server ';
                                $this->get('session')->getFlashBag()->add('msgError', "AdPointPlan error <strong>" . $msnError . "</strong>");
                            } else {
                                // Archivo Monitor
                                $entityFile->setDev1FileName($newFilename);
                                $entityFile->setOriginalDev1Name($originalFilename);
                                if ($advertFile && $advertFile->guessClientExtension() == 'png') {
                                    $entityFile->setDev1Extension('jpg');
                                    $entityFile->setDev1Mimetype('image/jpg');
                                } else {
                                    $entityFile->setDev1Mimetype($advertFile->getMimeType());
                                    $entityFile->setDev1Extension($advertFile->getClientOriginalExtension());
                                }
                                $entityFile->setDev1Size($advertFile->getSize());

                                $advertFile->move(
                                        $theAccountLicenseDirector,
                                        $newFilename
                                );
                            }
                        } catch (\Exception $e) {
                            $this->get('session')->getFlashBag()->add('msgError', "AdPlacePlan dev1 image error <strong>" . $ex->getMessage() . "</strong>");
                        }
                    }

                    // dev2FileName

                    $advertFile = $auxForm['dev2FileName']->getData();

                    if ($advertFile) {
                        $originalFilename = pathinfo($advertFile->getClientOriginalName(), PATHINFO_FILENAME);

                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $advertFile->guessExtension();

                        try {
                            $isDirectoryReady = Util::createLicenseDirectory($this->realContainer, $license, $license->getAlLicenseUsername());
                            $theAccountLicenseDirector = $isDirectoryReady['directory'];

                            if (!isset($isDirectoryReady['result']) || $isDirectoryReady['result'] == '__KO__') {
                                $msnError = 'An error occurred while creating your directory in server ';
                                $this->get('session')->getFlashBag()->add('msgError', "AdPointPlan error <strong>" . $msnError . "</strong>");
                            } else {
                                // Archivo Monitor
                                $entityFile->setDev2FileName($newFilename);
                                $entityFile->setOriginalDev2Name($originalFilename);
                                if ($advertFile && $advertFile->guessClientExtension() == 'png') {
                                    $entityFile->setDev2Extension('jpg');
                                    $entityFile->setDev2Mimetype('image/jpg');
                                } else {
                                    $entityFile->setDev2Mimetype($advertFile->getMimeType());
                                    $entityFile->setDev2Extension($advertFile->getClientOriginalExtension());
                                }
                                $entityFile->setDev2Size($advertFile->getSize());

                                $advertFile->move(
                                        $theAccountLicenseDirector,
                                        $newFilename
                                );
                            }
                        } catch (\Exception $e) {
                            $this->get('session')->getFlashBag()->add('msgError', "AdPlacePlan dev2 image error <strong>" . $ex->getMessage() . "</strong>");
                        }
                    }

                    // dev3FileName

                    $advertFile = $auxForm['dev3FileName']->getData();

                    if ($advertFile) {
                        $originalFilename = pathinfo($advertFile->getClientOriginalName(), PATHINFO_FILENAME);

                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $advertFile->guessExtension();

                        try {
                            $isDirectoryReady = Util::createLicenseDirectory($this->realContainer, $license, $license->getAlLicenseUsername());
                            $theAccountLicenseDirector = $isDirectoryReady['directory'];

                            if (!isset($isDirectoryReady['result']) || $isDirectoryReady['result'] == '__KO__') {
                                $msnError = 'An error occurred while creating your directory in server ';
                                $this->get('session')->getFlashBag()->add('msgError', "AdPointPlan error <strong>" . $msnError . "</strong>");
                            } else {
                                // Archivo Monitor
                                $entityFile->setDev3FileName($newFilename);
                                $entityFile->setOriginalDev3Name($originalFilename);
                                if ($advertFile && $advertFile->guessClientExtension() == 'png') {
                                    $entityFile->setDev3Extension('jpg');
                                    $entityFile->setDev3Mimetype('image/jpg');
                                } else {
                                    $entityFile->setDev3Mimetype($advertFile->getMimeType());
                                    $entityFile->setDev3Extension($advertFile->getClientOriginalExtension());
                                }
                                $entityFile->setDev3Size($advertFile->getSize());

                                $advertFile->move(
                                        $theAccountLicenseDirector,
                                        $newFilename
                                );
                            }
                        } catch (\Exception $e) {
                            $this->get('session')->getFlashBag()->add('msgError', "AdPlacePlan dev3 image error <strong>" . $ex->getMessage() . "</strong>");
                        }
                    }

                    // dev4FileName

                    $advertFile = $auxForm['dev4FileName']->getData();

                    if ($advertFile) {
                        $originalFilename = pathinfo($advertFile->getClientOriginalName(), PATHINFO_FILENAME);

                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $advertFile->guessExtension();

                        try {
                            $isDirectoryReady = Util::createLicenseDirectory($this->realContainer, $license, $license->getAlLicenseUsername());
                            $theAccountLicenseDirector = $isDirectoryReady['directory'];

                            if (!isset($isDirectoryReady['result']) || $isDirectoryReady['result'] == '__KO__') {
                                $msnError = 'An error occurred while creating your directory in server ';
                                $this->get('session')->getFlashBag()->add('msgError', "AdPointPlan error <strong>" . $msnError . "</strong>");
                            } else {
                                // Archivo Monitor
                                $entityFile->setDev4FileName($newFilename);
                                $entityFile->setOriginalDev4Name($originalFilename);
                                if ($advertFile && $advertFile->guessClientExtension() == 'png') {
                                    $entityFile->setDev4Extension('jpg');
                                    $entityFile->setDev4Mimetype('image/jpg');
                                } else {
                                    $entityFile->setDev4Mimetype($advertFile->getMimeType());
                                    $entityFile->setDev4Extension($advertFile->getClientOriginalExtension());
                                }
                                $entityFile->setDev4Size($advertFile->getSize());

                                $advertFile->move(
                                        $theAccountLicenseDirector,
                                        $newFilename
                                );
                            }
                        } catch (\Exception $e) {
                            $this->get('session')->getFlashBag()->add('msgError', "AdPlacePlan dev4 image error <strong>" . $ex->getMessage() . "</strong>");
                        }
                    }

                    // dev5FileName

                    $advertFile = $auxForm['dev5FileName']->getData();

                    if ($advertFile) {
                        $originalFilename = pathinfo($advertFile->getClientOriginalName(), PATHINFO_FILENAME);

                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $advertFile->guessExtension();

                        try {
                            $isDirectoryReady = Util::createLicenseDirectory($this->realContainer, $license, $license->getAlLicenseUsername());
                            $theAccountLicenseDirector = $isDirectoryReady['directory'];

                            if (!isset($isDirectoryReady['result']) || $isDirectoryReady['result'] == '__KO__') {
                                $msnError = 'An error occurred while creating your directory in server ';
                                $this->get('session')->getFlashBag()->add('msgError', "AdPointPlan error <strong>" . $msnError . "</strong>");
                            } else {
                                // Archivo Monitor
                                $entityFile->setDev5FileName($newFilename);
                                $entityFile->setOriginalDev5Name($originalFilename);
                                if ($advertFile && $advertFile->guessClientExtension() == 'png') {
                                    $entityFile->setDev5Extension('jpg');
                                    $entityFile->setDev5Mimetype('image/jpg');
                                } else {
                                    $entityFile->setDev5Mimetype($advertFile->getMimeType());
                                    $entityFile->setDev5Extension($advertFile->getClientOriginalExtension());
                                }
                                $entityFile->setDev5Size($advertFile->getSize());

                                $advertFile->move(
                                        $theAccountLicenseDirector,
                                        $newFilename
                                );
                            }
                        } catch (\Exception $e) {
                            $this->get('session')->getFlashBag()->add('msgError', "AdPlacePlan dev5 image error <strong>" . $ex->getMessage() . "</strong>");
                        }
                    }

                    // dev6FileName

                    $advertFile = $auxForm['dev6FileName']->getData();

                    if ($advertFile) {
                        $originalFilename = pathinfo($advertFile->getClientOriginalName(), PATHINFO_FILENAME);

                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $advertFile->guessExtension();

                        try {
                            $isDirectoryReady = Util::createLicenseDirectory($this->realContainer, $license, $license->getAlLicenseUsername());
                            $theAccountLicenseDirector = $isDirectoryReady['directory'];

                            if (!isset($isDirectoryReady['result']) || $isDirectoryReady['result'] == '__KO__') {
                                $msnError = 'An error occurred while creating your directory in server ';
                                $this->get('session')->getFlashBag()->add('msgError', "AdPointPlan error <strong>" . $msnError . "</strong>");
                            } else {
                                // Archivo Monitor
                                $entityFile->setDev6FileName($newFilename);
                                $entityFile->setOriginalDev6Name($originalFilename);
                                if ($advertFile && $advertFile->guessClientExtension() == 'png') {
                                    $entityFile->setDev6Extension('jpg');
                                    $entityFile->setDev6Mimetype('image/jpg');
                                } else {
                                    $entityFile->setDev6Mimetype($advertFile->getMimeType());
                                    $entityFile->setDev6Extension($advertFile->getClientOriginalExtension());
                                }
                                $entityFile->setDev6Size($advertFile->getSize());

                                $advertFile->move(
                                        $theAccountLicenseDirector,
                                        $newFilename
                                );
                            }
                        } catch (\Exception $e) {
                            $this->get('session')->getFlashBag()->add('msgError', "AdPlacePlan dev6 image error <strong>" . $ex->getMessage() . "</strong>");
                        }
                    }

                    $em->persist($entityFile);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('msgNotification', "AdPointPlan saved successfully!");

                return $this->redirect($this->generateUrl('adpoint_adplans_list', ['id' => $license->getId()]));
            } elseif (isset($parametersArray['adspace_sublicense']['clientsNumber'])) {
                $entity = new AdvertisePlan();
                $entity->setAdvertPlace($license);
                $form = $this->createSublicenseForm($entity, $parametersArray['adspace_sublicense']['clientsNumber']);

                for ($i = 0; $i < $parametersArray['adspace_sublicense']['clientsNumber']; ++$i) {
                    $entityFile = new AdvertPlanFile();
                    $fileForms[] = $this->createAdvertPlanFileForm($entityFile, $i)->createView();
                }
            }
//            else {
//                dump('Se toteo esto!');
//                die;
//            }
        } catch (\Exception $ex) {
//            dump($ex->getMessage() . ' ' . $ex->getTraceAsString());
//            die;

            $form = $this->createSublicenseForm($sublicense, 2);
            $form->handleRequest($request);

            $this->get('session')->getFlashBag()->add('msgError', "Advert Plan error <strong>" . $ex->getMessage() . "</strong>");

            for ($i = 0; $i < $parametersArray['adspace_sublicense']['clientsNumber']; ++$i) {
                $entityFile = new AdvertPlanFile();
                $fileForms[] = $this->createAdvertPlanFileForm($entityFile, $i)->createView();
            }
        }

        if (!isset($parametersArray['adspace_sublicense']['clientsNumber'])) {
            $parametersArray['adspace_sublicense']['clientsNumber'] = $actualClientsNumeber;
        }

        $form = $this->createSublicenseForm($sublicense, $parametersArray['adspace_sublicense']['clientsNumber']);

        return $this->render('AccountLicense\Sublicense\new.html.twig', [
                    'entity' => $sublicense,
                    'form' => $form->createView(),
                    'clientsNumber' => $parametersArray['adspace_sublicense']['clientsNumber'],
                    'fileForms' => array_reverse($fileForms),
                    'showImagesForm' => true,
                    'menu' => 'accounts',
                    'isNew' => true
        ]);
    }

    /**
     * Displays a form to create a new AdPlacePlan entity.
     *
     * @Route("/{subId}/adPlanEdit", name="adpoint_adplans_edit", options={ "method_prefix" = false })
     */
    public function editSublicense(Request $request, $subId) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_LICENSE_CREATE, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', ['msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente']));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entityLicense = $em->getRepository('App:AdvertisePlan')->find($subId);

        if (!$entityLicense) {
            throw $this->createAccessDeniedException('AdvertisePlan not found');
        }

        $form = $this->createSublicenseForm($entityLicense);

        return $this->render('AccountLicense\Sublicense\new.html.twig', [
                    'entity' => $entityLicense,
                    'form' => $form->createView(),
                    'menu' => 'accounts',
                    'isNew' => true
        ]);
    }

    /**
     * Actualiza sublicencias
     * @param Request $request
     * @param type $licenseId
     * @param type $channel
     * @param type $name
     * @return type
     *
     * @Route("/{subId}/adPlanCreate", name="adpoint_adplans_update", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function updateSublicense(Request $request, $subId) {

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirect($this->generateUrl('adspace_login', array('msg' => 'You need superadmin access!')));
        }

        $em = $this->getDoctrine()->getManager();

        $params = $request->request->getIterator()->getArrayCopy();

//        dump('Hola');
//        die;

        /*
         * Encontrar la licencia segun el ID dado
         */
        $sublicense = $em->getRepository('App:AdvertisePlan')->find($subId);

        if (!$sublicense) {
            throw $this->createAccessDeniedException('AdvertisePlan not found');
        }

        $validCountry = true;
        $validChannelName = true;

        /*
         * Condicion para licencias de conuntry CO
         */
//        if ($sublicense->getLicense()->getCity()->getCiState()->getStCountry()->getCoVal() != 'CO') {
//            $validCountry = false;
//        }

        $subLicenesWithChannel = $em->getRepository('App:AdvertisePlan')
                ->findBy(['name' => $params['adspace_sublicense']['name']]);

        if (!empty($subLicenesWithChannel) && $subLicenesWithChannel[0]->getId() != $sublicense->getId()) {
            $validChannelName = false;
        }

        $form = $this->createSublicenseForm($sublicense);
        $form->handleRequest($request);

        if ($form->isValid() && $validCountry && $validChannelName) {

            $em->persist($sublicense);

            $em->flush();

            /*
             * hacer conexion con el repositorio de la licencia afectada
             * para almacenar la nueva sublicencia creada
             */
//            $licenseDataBaseEntity = $em->getRepository('Licensor:LicenseDataBase')
//                    ->findBy(['license' => $sublicense->getLicense()->getId()]);

            /* datos de conexion */
//            $dataOptionsRoot = array(
//                'dbname' => $licenseDataBaseEntity[0]->getDbname(),
//                'user' => $licenseDataBaseEntity[0]->getDbuser(),
//                'password' => $licenseDataBaseEntity[0]->getDbpass(),
//                'host' => $licenseDataBaseEntity[0]->getDbhost(),
//                'driver' => 'pdo_mysql',
//            );

            /* entityManager de la nueva conexion */
//            $em = Util::emCreateConfiguration($this->realContainer, $dataOptionsRoot);
//            $lvlSubLicencia = new LvlSubLicense();
//
//            $lvlSubLicencia->setsublName($sublicense->getChannelName());
//            $lvlSubLicencia->setAndroidSync(null);
//            $em->persist($lvlSubLicencia);
//            $em->flush();
//
//            $arrDataRegist = [
//                'tableName' => 'sub_license',
//                'tableId' => $lvlSubLicencia->getId(),
//                'tableAction' => RegistActions::ACTION_UPDATE
//            ];
//            $this->createRegist($em, $arrDataRegist);

            $this->get('session')->getFlashBag()->add('msgNotification', "AdPointPlan updated successfully!");

            return $this->redirect($this->generateUrl('adpoint_adplans_list', [
                                'id' => $sublicense->getLicense()->getId()]));
        } else {
            if (!$validChannelName) {
                $this->get('session')->getFlashBag()->add('msgError', "Sublicense with <strong>" . $params['levellicensor_levellicensorbundle_sublicense']['channelName'] . "</strong> channel already registered in licensor! ");
            }
        }

        return $this->render('AccountLicense\Sublicense\new.html.twig', array(
                    'entity' => $sublicense,
                    'form' => $form->createView(),
                    'menu' => 'accounts',
                    'isNew' => false
        ));
    }

    /**
     * funcion encargada de crear un registro en la tabla LicenseDataBase
     * con nombre de base de datos usuario y password con caracteristicas especiales
     * @param type $em
     * @param type $entity
     * @return boolean
     * @author KJ-Hector Hdz
     */
//    private function setDataLicense($em, $entity) {
//        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_LICENSE_CREATE);
//        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
//            if ($access_control == AccessControl::SESSION_LOST) {
//                return $this->redirect($this->generateUrl('adspace_login', ['msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente']));
//            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
//                throw $this->createAccessDeniedException('Access Denied');
//            }
//        }
//
//        $insertId = $entity->getId();
//
//        if ($insertId > 0) {
//            $companyName = strtolower(Util::replaceCharactersEspecials($entity->getAlRestaurantName()));
//
//            /**
//             * validaicon de longitud de nombre de restaurante
//             */
//            if (!(strlen($companyName) <= 10)) {
//                $companyName = substr($companyName, 0, 5) . substr($companyName, -5);
//            }
//
//            return true;
//        }
//        return false;
//    }

    /**
     * Edits an existing AccountLicense entity.
     *
     * @Route("/{id}/update", name="adpoint_point_update", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function update(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_LICENSE_EDIT, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', ['msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente']));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('App:AccountLicense')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AccountLicense entity.');
        }

        $alStatus = $entity->getAlLicenseStatus();

        $oldIsPlusLicense = $entity->getIsPlusLicense();

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        $typeValueArray = [];
        $typeValueArray[] = ['type' => 'regex', 'pattern' => "/^[A-Za-z0-9 _]*[A-Za-z0-9][A-Za-z0-9 _]*$/", 'field' => 'Contact Name', 'message' => 'Allowed numbers and letters only in <strong>%s</strong> field!', 'data' => $entity->getAlContacName()];
//        $typeValueArray[] = ['type' => 'regex', 'pattern' => "/^[A-Za-z0-9 _]*[A-Za-z0-9][A-Za-z0-9 _]*$/", 'field' => 'Restaurant Name', 'message' => 'Allowed numbers and letters only in <strong>%s</strong> field!', 'data' => $entity->getAlRestaurantName()];
        $typeValueArray[] = ['type' => 'regex', 'pattern' => "/^.{1,90}$/", 'field' => 'Address', 'message' => 'Allowed a maximum of 90 characters in <strong>%s</strong> field!', 'data' => $entity->getAlAddres()];

        $validationResult = ValidatorUtil::validateThis($this->get('symfony_validator'), $typeValueArray);

        $booleanIsOk = false;

        if ($editForm->isValid() && $validationResult[0]) {
            $params = $request->request->getIterator()->getArrayCopy();
//            $resutlArray = Util::validateAndSaveCityZipcodeBlock($this->realContainer, $em, $params, $entity);
            $resutlArray = Util::validateAndSaveAddressAutoComplete($this->realContainer, $em, $params, $entity);

            if ($resutlArray['status']) {
                $booleanIsOk = $resutlArray['status'];
                $entity = $resutlArray['entity'];
                $entity->setAlLicenseStatus($alStatus);

                $em = $this->getDoctrine()->getManager();

                if (isset($params['levellicensor_levellicensorbundle_accountlicense']) && !isset($params['levellicensor_levellicensorbundle_accountlicense']['hasLevelZero'])) {
                    $entity->setHasLevelZero(false);
                    $entity->setLevelZeroPercentage(0);
                    $entity->setLevelZeroGatewayPercentage(0);
                }

                $entity->setLevelZeroPercentage($entity->getLevelZeroPercentage() / 100);
                $entity->setLevelZeroGatewayPercentage($entity->getLevelZeroGatewayPercentage() / 100);

                $this->changesByLicenseInfoUpdate($em, $entity);

                $isPlusLicense = $entity->getIsPlusLicense();

                if (!$isPlusLicense && $oldIsPlusLicense != $isPlusLicense) {
                    $devicesByLicense = $em->getRepository('App:LicenseDevice')->findBy(['ldLicenseId' => $entity->getId()]);

                    foreach ($devicesByLicense as $device) {
                        $device->setLdIsPlusDevice(LicenseDevice::DEVICE_PAYMENT_REGULAR);

                        $em->persist($device);
                        $em->flush();
                    }
                } elseif ($isPlusLicense && $oldIsPlusLicense != $isPlusLicense) {
                    $devicesByLicense = $em->getRepository('App:LicenseDevice')->findBy(['ldLicenseId' => $entity->getId()]);

                    foreach ($devicesByLicense as $device) {
                        $device->setLdIsPlusDevice(LicenseDevice::DEVICE_PAYMENT_PLUS);

                        $em->persist($device);
                        $em->flush();
                    }
                }

                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('adpoint_points_list', ['accountId' => $entity->getAlAccountLicense()->getId()]));
            } else {
                $notificationMessage = $resutlArray['message'];

                $this->get('session')->getFlashBag()->add('msgError', $notificationMessage);
            }
        } else {
            $notificationMessage = 'Invalid form parameters!';

            if (!$validationResult[0]) {
                foreach ($validationResult[1] as $value) {
                    if (!$value['isValid']) {
                        $this->get('session')->getFlashBag()->add('msgError', sprintf($value['message'], $value['field']));
                    }
                }
            }
        }

        $countries = $em->getRepository('App:Country')->findAll();

        if ($entity->getCity()) {
            $country = $entity->getCity()->getCiState()->getStCountry();
            $states = $em->getRepository('App:State')->findBy(['stCountry' => $country->getCoId()]);
        } else {
            $states = $em->getRepository('App:State')->findBy(['stCountry' => 1]);
        }

        return $this->render('AccountLicense\edit.html.twig', [
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'menu' => 'accounts',
                    'isOk' => $booleanIsOk,
                    'notificationMessage' => $notificationMessage,
                    'countries' => $countries,
                    'states' => $states,
        ]);
    }

    /**
     * Creates a form to create a AccountLicense entity.
     *
     * @param AccountLicense $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(AccountLicense $entity) {
        $form = $this->createForm(AccountLicenseType::class, $entity, [
            'action' => $this->generateUrl('adpoint_point_create'),
            'method' => 'POST'
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    /**
     * Creates a form to edit a AccountLicense entity.
     *
     * @param AccountLicense $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(AccountLicense $entity) {
        $form = $this->createForm(AccountLicenseType::class, $entity, [
            'action' => $this->generateUrl('adpoint_point_update', ['id' => $entity->getId()]),
            'method' => 'POST'
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    /**
     * Displays a form to edit an existing AccountLicense entity.
     *
     * @Route("/{id}/edit", name="adpoint_point_edit", options={ "method_prefix" = false })
     */
    public function edit(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_LICENSE_EDIT, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', array('msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente')));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('App:AccountLicense')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AccountLicense entity.');
        }

        $editForm = $this->createEditForm($entity);

        $countries = $em->getRepository('App:Country')->findAll();

        $country = $entity->getCity()->getCiState()->getStCountry();
        $states = $em->getRepository('App:State')->findBy(array('stCountry' => $country->getCoId()));

        return $this->render('AccountLicense\edit.html.twig', array(
                    'entity' => $entity,
                    'countries' => $countries,
                    'states' => $states,
                    'form' => $editForm->createView(),
                    'menu' => 'accounts',
        ));
    }

    /**
     * Funcion encargada de cambia el estado de una licencia
     * con el fin de activarla o desactivarla en el momento requerido
     * @param Request $request
     * @param type $id
     * @return \App\Controller\Response
     *
     * @Route("/{id}/changeStatus", name="adpoint_location_changeStatus", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function changeStatus(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_LICENSE_DELETE, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $response['msg'] = 'Access Denied';
            $response['result'] = '__KO__';
        } else {
            $em = $this->getDoctrine()->getManager();
            $newStatus = $request->request->get('newStatus');
            $response['msg'] = 'Change Status Success!';
            $response['result'] = '__OK__';
            $response['newStatus'] = $newStatus;

            $licenseDelt = $em->find('App:AccountLicense', $id);

            if (!$licenseDelt) {
                $response['msg'] = 'Account License Not Found!';
                $response['result'] = '__KO__';
            } else {
                $dataResponse = [];
                $dataResponse["status"] = 200;

                $typePush = 0;
                if (AccountLicense::LICENSE_STATUS_ACTIVE == $newStatus) {
                    $typePush = PushSent::PUSH_TYPE_LICENSE_ENABLED;
                    $dataResponse["actions"] = "enabledlicense";
                } else {
                    $typePush = PushSent::PUSH_TYPE_LICENSE_DISABLED;
                    $dataResponse["actions"] = "disabledlicense";
                }

                $path = Utilx::getValidActiveLogByBaseName($this->realContainer, 'newPushSenderTest', $this->getParameter('level_directory_data_android') . 'newPushSenderTest0.txt');
                Utilx::sendSuperPush($this->realContainer, $em, $dataResponse, $typePush, $licenseDelt, $path);

                $licenseDelt->setAlLicenseStatus($newStatus);

                $em->persist($licenseDelt);
                $em->flush();
            }
            $response['txtNewLicenseStatus'] = $licenseDelt->getTextLicenseStatus();
        }

        $r = new Response(json_encode($response));
        $r->headers->set('Content-Type', 'application/json');
        return $r;
    }

    /**
     * Funcion encargada de resetear el uid asociado a una licencia
     * permitiendo que x dispositivo se loguee y tomando posecion de esta
     * @param Request $request
     * @param type $id
     * @return \App\Controller\Response
     *
     * @Route("/{id}/changeAdvertPlanStatus", name="unlink_licenses_uid_request", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function changeAdvertPlanStatus(Request $request, $id) {

        $params = $request->request->all();
        if (!isset($params["typeResetLogin"])) {
            $response['msg'] = 'Parametros erroneos!';
            $response['result'] = '__KO__';
            return $this->respondJsonAjax($response);
        }

        $em = $this->getDoctrine()->getManager();

        $response['msg'] = 'Estado de la licencia cambiado!';
        $response['result'] = '__OK__';

        $licenseDelt = $em->find('App:AdvertisePlan', $id);

        if (!$licenseDelt) {
            $response['msg'] = 'Plan de publicidad no encontrado!';
            $response['result'] = '__KO__';
        } else {
            $licenseDelt->setStatus($params["typeResetLogin"]);

            $em->persist($licenseDelt);
            $em->flush();
        }

        return $this->respondJsonAjax($response);
    }

}
