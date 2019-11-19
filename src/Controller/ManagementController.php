<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Controller\ParametersNormalizerController;

/**
 * Description of ManagementController
 *
 * @author aealan
 * @Route("/management", defaults={"_locale"="en"})
 */
class ManagementController extends ParametersNormalizerController {

    /**
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param Request $request
     * @return type
     * @throws type
     * 
     * @Route("/index", name="index_management", options={ "method_prefix" = false })
     */
    public function indexManagement(Request $request) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_DATA_BASES_MANAGEMENT, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->
                        isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login'
                                        , ['msg' => 'Your session has expired. Please login again']));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {

                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        return $this->render('Default\managementDashboard.html.twig', [
                    'menu' => 'databases'
        ]);
    }
    
    /**
     * Esta funcion lista los crons que tiene la aplicacion
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param Request $request
     * @return type render view
     * @throws type
     * 
     * @Route("/adpointmanagement", name="adpoint_management_list", options={ "method_prefix" = false })
     */
    public function generalLicenseInformation(Request $request) {

        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_DATA_BASES_MANAGEMENT, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', ['msg' => 'Your session has expired. Please login again']));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $paginator = $this->get('simple_paginator');
        $pageRanges = [10, 25, 50];
        $paginator->pageRanges = $pageRanges;
        $dataSize = $this->getParameter('max_android_database_size_to_consider_cleanse');

        if (null != $request->query->get('itemsPerPage') && '' != $request->query->get('itemsPerPage')) {
            $itemsPerPage = (int) $request->query->get('itemsPerPage');
        } else {
            $itemsPerPage = $pageRanges[0];
        }

        $paginator->setItemsPerPage($itemsPerPage);
        $paginator->setMaxPagerItems(5);

        $search = [];
        $indexSearch = ['alLicenseUsername', 'alContacName', 'alLicenseEmail', 'alAccountLicense', 'deviceUid', 'apkVersion', 'codeInstall', 'androidLastCleanseLeftDays'];
        $order = [];
        $indexOrder = ['order_by_nickname', 'order_by_contac_name', 'order_by_apk_version'];

        $em = $this->getDoctrine()->getManager();

        if (null != $request->query->get('apkVersion') && $request->query->get('apkVersion') != '') {
            $statusSearch = $request->query->get('apkVersion');
        } else {
            $statusSearch = '';
        }

        if (null != $request->query->get('alAccountLicense') && $request->query->get('alAccountLicense') != '') {
            $accountSearch = $request->query->get('alAccountLicense');
        } else {
            $accountSearch = '';
        }

        if (null != $request->query->get('androidLastCleanseLeftDays') && $request->query->get('androidLastCleanseLeftDays') != '') {
            $cleanSearch = $request->query->get('androidLastCleanseLeftDays');
        } else {
            $cleanSearch = '';
        }

        $accountLicense = new AccountLicense();
        $form = $this->createForm(SearchLicenseInformationType::class, $accountLicense, [
            'action' => $this->generateUrl('index_general_license_information_list'),
            'em' => $em,
            'selected_choice' => $statusSearch,
            'selected_choice_companies' => $accountSearch,
            'selected_choice_clean' => $cleanSearch
        ]);

        if ($request->getMethod() == 'POST') {
            /* Capturamos y filtramos los parametros de busqueda */
            $form->handleRequest($request);
            $parameters = $request->request->get('levellicensor_levellicensorbundle_searchlicenseinformation');
            $search = Paginator::filterParameters($indexSearch, $parameters, Paginator::REQUEST_TYPE_ARRAY);

            return $this->redirect($this->generateUrl('adpoint_management_list', $search));
        } elseif ($request->getMethod() == 'GET') {
            /* Capturamos y filtramos los parametros de busqueda */

            $search = Paginator::filterParameters($indexSearch, $request, Paginator::REQUEST_TYPE_REQUEST);
            /* Capturamos y filtramos los parametros de ordenamiento */
            $order = Paginator::filterParameters($indexOrder, $request, Paginator::REQUEST_TYPE_REQUEST, true);
        }

        $entitiesToPaginate = $em->getRepository('App:AccountLicense')->searchLicenseInformationList($search, $order, false, $dataSize);

        $entities = $paginator->paginate($entitiesToPaginate)->getResult();

        // $dateNow = new \DateTime('now');
        foreach ($entities as $index => $license) {
            $daysToMekDiff = $license->getAndroidLastCleanseLeftDays();
            if ($license->getLastConsecutiveAndroidDryingDB() && $daysToMekDiff != null && $daysToMekDiff > 0) {
                $auxDate = clone $license->getLastConsecutiveAndroidDryingDB();
                $entities[$index]->dateToValidateButton = $auxDate->modify("+ $daysToMekDiff day");
                $entities[$index]->dateNow = Util::getCurrentDate()->modify("+" . ($daysToMekDiff - 1) . " day");
            } elseif ($license->getLastConsecutiveAndroidDryingDB() == null && $daysToMekDiff > 0) {
                $entities[$index]->dateToValidateButton = Util::getCurrentDate()->modify("-1 day");
                $entities[$index]->dateNow = Util::getCurrentDate()->modify("-1 day");
            } elseif ($daysToMekDiff == null || $daysToMekDiff <= 0) {
                $entities[$index]->dateToValidateButton = Util::getCurrentDate()->modify("-1 day");
                $entities[$index]->dateNow = Util::getCurrentDate()->modify("-1 day");
            } else {
                $entities[$index]->dateToValidateButton = Util::getCurrentDate();
                $entities[$index]->dateNow = Util::getCurrentDate();
            }

            $entities[$index]->databaseHumanSize = Utilx::humanFilesize($license->getAndroidDatabaseSize());
        }
        /* Construimos las url para las peticiones get del ordenador y paginador */
        $params = Paginator::getUrlFromParameters($indexSearch, $search);
        $orderBy = Paginator::getUrlOrderFromParameters($indexOrder, $order);

        return $this->render('SuperAdmin\licenseGeneralInformationList.html.twig', [
                    'entities' => $entities,
                    'menu' => 'databases',
                    'submenu' => 'general_imformation',
                    'form' => $form->createView(),
                    'paginator' => $paginator,
                    'params' => $params,
                    'search' => $search,
                    'orderBy' => $orderBy,
        ]);
    }
}
