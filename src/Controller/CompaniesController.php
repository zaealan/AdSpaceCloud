<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\User;
use App\Entity\Module;
use App\Entity\Company;
use App\Entity\LicenseRate;
use App\Entity\UserModules;
use App\Entity\CompanyModule;
use App\Form\CompanyType;
use App\Form\SearchUserType;
use App\Form\LicenseRateType;
use App\Form\SearchCompanyType;
use App\Util\Util;
use App\Util\Paginator;
use App\Util\AccessControl;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Controller\ParametersNormalizerController;

/**
 * Description of CompaniesController
 *
 * @author aealan
 * @Route("/companies", defaults={"_locale"="en"})
 */
class CompaniesController extends ParametersNormalizerController {

    /**
     * 
     * @param Request $request
     * @return type
     * @throws type
     * 
     * @Route("/index", name="adpoint_companies", options={ "method_prefix" = false })
     */
    public function index(Request $request) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_COMPANY, $request);
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
        $indexSearch = ['coCompanyName', 'coCompanyIdentification', 'coStatus'];
        $order = [];
        $indexOrder = ['order_by_company_name', 'order_by_company_identification', 'order_by_company_status'];

        $em = $this->getDoctrine()->getManager();

        if (null != $request->query->get('coStatus') && $request->query->get('coStatus') != '') {
            $statusSearch = $request->query->get('coStatus');
        } else {
            $statusSearch = '';
        }

//        dump($this->container->get('form.factory'));
//        die;
        
        $accountLicense = new Company();
        $form = $this->createForm(SearchCompanyType::class, $accountLicense, [
            'action' => $this->generateUrl('adpoint_companies'),
            'selected_choice' => $statusSearch,
        ]);

        $userLicensor = $this->get('security.token_storage')->getToken()->getUser();

        if ($request->getMethod() == 'POST') {
            /* Capturamos y filtramos los parametros de busqueda */
            $form->handleRequest($request);
            $parameters = $request->request->get('levellicensor_levellicensorbundle_searchcompany');
            $search = Paginator::filterParameters($indexSearch, $parameters, Paginator::REQUEST_TYPE_ARRAY);

            return $this->redirect($this->generateUrl('adpoint_companies', $search));
        } elseif ($request->getMethod() == 'GET') {
            /* Capturamos y filtramos los parametros de busqueda */
            $search = Paginator::filterParameters($indexSearch, $request, Paginator::REQUEST_TYPE_REQUEST);

            /* Capturamos y filtramos los parametros de ordenamiento */
            $order = Paginator::filterParameters($indexOrder, $request, Paginator::REQUEST_TYPE_REQUEST, true);
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $entitiesToPaginate = $em->getRepository('App:Company')->findResellersForSuperadminUser($search, $order);
        } else if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINISTRATOR')) {
            $usersOfCompany = $em->getRepository('App:User')->findBy(['usCompany' => $userLicensor->getUsCompany()->getId()]);

            array_push($usersOfCompany, $userLicensor);

            $userId = '(';
            $comma = '';
            foreach ($usersOfCompany as $user) {
                if ($user->getUsType() != User::USER_SUPER_ADMIN) {
                    $userId .= $comma . $user->getId();
                    $comma = ',';
                }
            }
            $userId .= ')';

            $search['userCreators'] = $userId;
            $search['myCompany'] = $userLicensor->getUsCompany()->getId();

            $entitiesToPaginate = $em->getRepository('App:Company')->findResellersForAdminUser($search, $order);
        } else {
            $entitiesToPaginate = [];
        }

        $entities = $entitiesToPaginate;
        
//        $entities = $paginator->paginate($entitiesToPaginate)->getResult();

        /* Construimos las url para las peticiones get del ordenador y paginador */
        $params = Paginator::getUrlFromParameters($indexSearch, $search);
        $orderBy = Paginator::getUrlOrderFromParameters($indexOrder, $order);

        return $this->render('Company\index.html.twig', array(
                    'entities' => $entities,
                    'menu' => 'companies',
                    'form' => $form->createView(),
//                    'paginator' => $paginator,
                    'params' => $params,
                    'search' => $search,
                    'orderBy' => $orderBy,
                    'itemsPerPage' => $itemsPerPage
        ));
    }

    /**
     * @return type
     * @throws type
     * 
     * @Route("/resellercompanies", name="adpoint_reseller_companies", options={ "method_prefix" = false })
     */
    public function companiesList() {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createNotFoundException("Access Denied");
        }

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('App:Company')->findAll();

        return $this->render('SuperAdmin\managementCompanyList.html.twig', [
                    'entities' => $entities,
                    'menu' => 'managment',
        ]);
    }
    
    /**
     * Displays a form to create a new User entity.
     * 
     * @Route("/new", name="adpoint_company_new", options={ "method_prefix" = false })
     */
    public function newCompany(Request $request) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_COMPANY_CREATE, $request);
        if ($access_control != AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', array('msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente')));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = new Company();
        $entityLicRate = new LicenseRate();

        $form = $this->createCreateForm($entity);
        $formLicRate = $this->createCreateFormLicenseRate($entityLicRate);

        $countries = $em->getRepository('App:Country')->findAll();
        return $this->render('Company\new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'formRates' => $formLicRate->createView(),
                    'menu' => 'companies',
                    'countries' => $countries,
        ));
    }

    /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Company $entity) {
        $form = $this->createForm(CompanyType::class, $entity, [
            'action' => $this->generateUrl('adpoint_company_create'),
            'method' => 'POST'
        ]);
        
        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateFormLicenseRate(LicenseRate $entity) {
        $form = $this->createForm(LicenseRateType::class, $entity, [
            'action' => $this->generateUrl('adpoint_company_create'),
            'method' => 'POST'
        ]);

        return $form;
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/create", name="adpoint_company_create", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function create(Request $request) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_COMPANY_CREATE, $request);
        if ($access_control != AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', array('msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente')));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $userSession = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $entity = new Company();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $params = $request->request->getIterator()->getArrayCopy();

        if ($form->isValid()) {
            $userSession = $this->get('security.token_storage')->getToken()->getUser();
            $entity->setCoDateCreated(new \DateTime('now'));
            $attrToSet = array('city', 'zipCode');
            $resutlArray = Util::validateAndSaveCityZipcodeBlock($this->realContainer, $em, $params, $entity, $attrToSet);
            if ($resutlArray['status']) {
                $entity->setCoUserCreator($userSession);
                $entity->setCoStatus(1);

                $em->persist($entity);
                $em->flush();

                $data = [
                    ["device" => 1, "price" => $params['levellicensor_levellicensorbundle_licenserate']['lrPriceServerAndroid']],
                    ["device" => 2, "price" => $params['levellicensor_levellicensorbundle_licenserate']['lrPriceClientAndroid']]
                ];
                $this->createLicenseRateCompany($data, $entity);
                $this->createModulesForCompany($entity);
                return $this->redirect($this->generateUrl('super_managment_companies_privileges', array(
                                    'companyId' => $entity->getId())));
            } else {
                $notificationMessage = $resutlArray['message'];
            }
        }
        return $this->render('Company\new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'notificationMessage' => $notificationMessage,
                    'menu' => 'companies',
        ));
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/{id}/edit", name="adpoint_company_edit", options={ "method_prefix" = false })
     */
    public function edit(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_COMPANY_EDIT, $request);
        if ($access_control != AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', ['msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente']));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('App:Company')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $city = $entity->getCity()->getId();
        $country = $entity->getCity()->getCiState()->getStCountry();
        $states = $em->getRepository('App:State')->findBy(['stCountry' => $country->getCoId()]);
        $entityLicRate = $em->getRepository('App:LicenseRate')
                ->findBy(["lrCompanyId" => $id]);

        $editForm = $this->createEditForm($entity);
        $formLicRate = $this->createEditFormLicRate($entity, $entityLicRate[0], $em);
        $countries = $em->getRepository('App:Country')->findAll();
        return $this->render('Company\edit.html.twig', [
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'formRates' => $formLicRate->createView(),
                    'licenseRates' => $entityLicRate,
                    'menu' => 'companies',
                    'countries' => $countries,
                    'states' => $states,
        ]);
    }

    /**
     * Esta funcion permite cargar cargar los modulos de la aplicacion y mostrar
     * un formulario para la edicion de dichos modulos para cada compañia
     * @author Aealan Z - kijho Technologies <lrobledo@kijho.com>
     * @param integer $companyId identificador de la compañia
     * @return \HttpRequest formulariuo de edicion de privilegios de los modulos
     * @throws \Exception en caso de no encontrar la compañia o subasta fisica
     * 
     * @Route("/{companyId}/privileges", name="adpoint_companies_privileges_list", options={ "method_prefix" = false })
     */
    public function companiesPrivileges($companyId) {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createNotFoundException("Access Denied !");
        }

        $em = $this->getDoctrine()->getManager();

        $company = $em->getRepository('App:Company')->find($companyId);
        if (!$company) {
            throw $this->createNotFoundException('Company not Found !');
        }

        //hacemos un conteo de los modulos en el sistema
        $modules = $em->getRepository('App:Module')->findAll();
        $countModules = count($modules);
        //luego buscamos los modulos creados para esta compañia
        $modulesCompany = $em->getRepository('App:CompanyModule')->findModulesCompanyForEdit($companyId);

        $countModulesCompany = count($modulesCompany);

        /*
         * Si no son iguales los modulos creados a los que tiene la subasta,
         * debemos verificar que modulos faltan por asignarle
         * a la subasta y asignarlos como inactivos
         */
        if ($countModulesCompany < $countModules) {
            for ($i = 0; $i < $countModules; ++$i) {
                $module = $modules[$i];
                $moduleFound = false;
                for ($j = 0; $j < $countModulesCompany; ++$j) {
                    if ($module->getId() == $modulesCompany[$j]->getCmModule()->getId()) {
                        $moduleFound = true;
                        break;
                    }
                }

                /* creamos el modulo para la subasta porque no lo tenia */
                if ($moduleFound == false) {
                    $newModuleCompany = new CompanyModule();
                    $newModuleCompany->setCmModule($module);
                    $newModuleCompany->setCmCompany($company);
                    $newModuleCompany->setCmAccess(false);
                    $em->persist($newModuleCompany);
                    $em->flush();
                }
            }
            /* volvemos a consultar los modulos para la subasta, pues ya cambiaron */
            $modulesCompany = $em->getRepository('App:CompanyModule')->findModulesCompanyForEdit($companyId);
        }

        return $this->render('SuperAdmin\editCompanyModuleAccess.html.twig', [
                    'company' => $company,
                    'menu' => 'managment',
                    'modulesCompany' => $modulesCompany,
        ]);
    }
    
    /**
     * Creates a form to edit a Company entity.
     *
     * @param Company $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Company $entity) {
        $form = $this->createForm(CompanyType::class, $entity, [
            'action' => $this->generateUrl('adpoint_company_edit', ['id' => $entity->getId()]),
            'method' => 'POST'
        ]);
        
        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    private function createEditFormLicRate(Company $entity, LicenseRate $entityLicenseR) {
        $form = $this->createForm(LicenseRateType::class, $entityLicenseR, [
            'action' => $this->generateUrl('adpoint_company_update', ['id' => $entity->getId()]),
            'method' => 'POST'
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    /**
     * Edits an existing Company entity.
     * 
     * @Route("/{id}/update", name="adpoint_company_update", options={ "method_prefix" = false })
     */
    public function update(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_COMPANY_EDIT, $request);
        if ($access_control != AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', array('msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente')));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('App:Company')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Company entity.');
        }

        $oldStatus = $entity->getCoStatus();
        $dateCreated = $entity->getCoDateCreated();
        $oldUserCreator = $entity->getCoUserCreator();
        $city = $request->request->get('cityx');

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        $paramsPost = $request->request->getIterator()->getArrayCopy();

        $notificationMessage = [];

        if ($editForm->isValid()) {

            $entity->setCoStatus($oldStatus);
            $entity->setCoDateCreated($dateCreated);
            $attrToSet = ['city', 'zipCode'];
            $resutlArray = Util::validateAndSaveCityZipcodeBlock($this->realContainer, $em, $paramsPost, $entity, $attrToSet);

            if ($resutlArray['status']) {
                $entity->setCoUserCreator($oldUserCreator);

                $request->request->get('levellicensor_levellicensorbundle_company');

                $em->persist($entity);
                $em->flush();

                $this->updateLicenseRateCompany($entity->getId(), $paramsPost["levellicensor_levellicensorbundle_licenserate"]);

                return $this->redirect($this->generateUrl('company'));
            } else {
                $notificationMessage = $resutlArray['message'];
            }
        }

        return $this->render('Company\edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'menu' => 'companies',
                    'notification' => $notificationMessage
        ));
    }

    /**
     * Funcion encargada de cambia el estado de una licencia
     * con el fin de activarla o desactivarla en el momento requerido
     * @param Request $request
     * @param type $id
     * @return \App\Controller\Response
     * 
     * @Route("/{id}/changeStatus", name="adpoint_company_changeStatus", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function changeStatus(Request $request, $id) {
        if (!$this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_COMPANY_DELETE, $request) && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $response['msg'] = 'Access Denied !';
            $response['result'] = '__KO__';
        } else {
            $em = $this->getDoctrine()->getManager();
            $newStatus = $request->request->get('newStatus');
            $response['msg'] = 'Change Status Success!';
            $response['result'] = '__OK__';
            $response['newStatus'] = $newStatus;

            $stCompany = $em->find('App:Company', $id);

            if (!$stCompany) {
                $response['msg'] = 'Company Not Found!';
                $response['result'] = '__KO__';
            } else {
                $stCompany->setCoStatus($newStatus);

                $em->persist($stCompany);
                $em->flush();
            }
            $response['txtNewCompanyStatus'] = $stCompany->getTextStatus();
        }

        $r = new Response(json_encode($response));
        $r->headers->set('Content-Type', 'application/json');
        return $r;
    }

    private function createModulesForCompany($company) {
        $em = $this->getDoctrine()->getManager();

        $systemModules = $em->getRepository('App:Module')->findAll();

        if (!empty($systemModules)) {
            foreach ($systemModules as $module) {
                $newCompanyModule = new CompanyModule();
                $newCompanyModule->setCmAccess(false);
                $newCompanyModule->setCmCompany($company);
                $newCompanyModule->setCmModule($module);

                $em->persist($newCompanyModule);
                $em->flush();
            }
        }
    }

    public function createLicenseRateCompany($data, $entityCompany) {
        $em = $this->getDoctrine()->getManager();
        foreach ($data as $myData) {
            $entityLicRate = new LicenseRate();
            $entityLicRate->setLrCompanyId($entityCompany);
            $entityLicRate->setLrDevicesType($myData['device']);
            $entityLicRate->setLrPrice($myData['price']);
            $em->persist($entityLicRate);
        }
        $em->flush();
    }

    public function updateLicenseRateCompany($companyId, $params) {
        $em = $this->getDoctrine()->getManager();
        $entityLicRate = $em->getRepository('App:LicenseRate')
                ->findBy(array("lrCompanyId" => $companyId));
        foreach ($entityLicRate as $licRate) {
            if ($licRate->getLrDevicesType() == 1) {
                $price = $params['lrPriceServerAndroid'];
            } elseif ($licRate->getLrDevicesType() == 2) {
                $price = $params['lrPriceClientAndroid'];
            } else {
                continue;
            }
            $licRate->setLrPrice($price);
            $em->persist($licRate);
        }
        $em->flush();
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/companyUsersManagement", name="adpoint_managment_company_users_list", options={ "method_prefix" = false })
     */
    public function usersCompaniesList(Request $request) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_COMPANY, $request);
        if ($access_control != AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', array('msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente')));
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

        if (null != $request->query->get('companyId') && '' != $request->query->get('companyId')) {
            $companyId = $request->query->get('companyId');
        } else {
            $companyId = null;
        }
        
//        dump($companyId);
//        die;
        
//        $paginator->setItemsPerPage($itemsPerPage);
//        $paginator->setMaxPagerItems(5);

        $search = [];
        $indexSearch = ['usName', 'usEmail', 'usStatus', 'usType'];
        $order = [];
        $indexOrder = ['order_by_user_name', 'order_by_user_email', 'order_by_user_status', 'order_by_user_type'];

        $em = $this->getDoctrine()->getManager();

        if (null != $request->query->get('usStatus') && $request->query->get('usStatus') != '') {
            $statusSearch = $request->query->get('usStatus');
        } else {
            $statusSearch = '';
        }

        if (null != $request->query->get('usType') && $request->query->get('usType') != '') {
            $typeSearch = $request->query->get('usType');
        } else {
            $typeSearch = '';
        }

        $user = new User();

        $isSuperAdmin = false;
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $isSuperAdmin = true;
        }

        $form = $this->createForm(SearchUserType::class, $user, [
            'action' => $this->generateUrl('adpoint_users'),
            'selected_choice_status' => $statusSearch,
            'selected_choice_type' => $typeSearch,
            'is_superadmin' => $isSuperAdmin
        ]);

        $userLicensor = $this->get('security.token_storage')->getToken()->getUser();

        if ($request->getMethod() == 'POST') {
            /* Capturamos y filtramos los parametros de busqueda */
            $form->handleRequest($request);
            $parameters = $request->request->get('levellicensor_levellicensorbundle_searchuser');
            $search = Paginator::filterParameters($indexSearch, $parameters, Paginator::REQUEST_TYPE_ARRAY);
            $search['companyId'] = $companyId;

            return $this->redirect($this->generateUrl('adpoint_managment_company_users_list', $search));
        } elseif ($request->getMethod() == 'GET') {
            /* Capturamos y filtramos los parametros de busqueda */
            $search = Paginator::filterParameters($indexSearch, $request, Paginator::REQUEST_TYPE_REQUEST);

            /* Capturamos y filtramos los parametros de ordenamiento */
            $order = Paginator::filterParameters($indexOrder, $request, Paginator::REQUEST_TYPE_REQUEST, true);
        }

        $search['notSuper'] = User::USER_SUPER_ADMIN;
//        $search['usCompany'] = $companyId;

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINISTRATOR')) {
            $search['me'] = $userLicensor->getId();
            $entitiesToPaginate = $em->getRepository('App:User')->usersForAdminUsers($search, $order);
        } else if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $search['notMe'] = $userLicensor->getId();
            $entitiesToPaginate = $em->getRepository('App:User')->usersForSuperadminUsers($search, $order);
        } else {
            $entitiesToPaginate = [];
        }

//        $entities = $paginator->paginate($entitiesToPaginate)->getResult();
        $entities = $entitiesToPaginate;

        /* Construimos las url para las peticiones get del ordenador y paginador */
        $params = Paginator::getUrlFromParameters($indexSearch, $search);
        $orderBy = Paginator::getUrlOrderFromParameters($indexOrder, $order);
        $entityReseller = $em->getRepository('App:Company')->find($companyId);

        return $this->render('Company\magnametCompanyUserList.html.twig', [
                    'entities' => $entities,
                    'menu' => 'managment',
                    'entityReseller' => $entityReseller,
                    'form' => $form->createView(),
//                    'paginator' => $paginator,
                    'params' => $params,
                    'search' => $search,
                    'orderBy' => $orderBy,
                    'itemsPerPage' => $itemsPerPage
        ]);
    }
    
    /**
     * Esta funcion permite cargar los modulos habilitados para una compañia, para
     * permitir habilitar o deshabilitar dichos modulos al usuario seleccionado
     * @author Aealan Z - kijho Technologies <lrobledo@kijho.com>
     * @param integer $id identificador del usuario de la compañia
     * @return \HttpRequest formulario de edicion de privilegios de los modulos
     * @throws \Exception en caso de no encontrar la compañia, subasta fisica o usuario
     * 
     * @Route("/{id}/updatePrivilegesUser", name="adpoint_update_user_privileges", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function editUserPrivileges($id) {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createNotFoundException("Access Denied !");
        }

        $secureToken = $this->get('security.token_storage')->getToken();

        if (!$secureToken) {
            throw $this->createNotFoundException('Access Denied');
        }

        $em = $this->getDoctrine()->getManager();
        $userCompany = $em->getRepository('App:User')->find($id);
        if (!$userCompany) {
            throw $this->createNotFoundException('User not found.');
        }

        $company = $em->getRepository('App:Company')->find($userCompany->getUsCompany()->getId());

        if (!$company) {
            throw $this->createNotFoundException('Unable to find company entity.');
        }

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createNotFoundException("Access Denied !");
        }

        //hacemos un conteo de los modulos que tiene habilitados la subasta fisica
        $modulesCompany = $em->getRepository('App:CompanyModule')
                ->findBy(['cmCompany' => $userCompany->getUsCompany()->getId(), 'cmAccess' => true]);
        $countModulesCompany = count($modulesCompany);

        //luego buscamos los modulos creados para este usuario
        $modulesUserCompany = $em->getRepository('App:UserModules')
                ->findUserModulesToEdition($userCompany->getId(), $userCompany->getUsCompany()->getId());
        $countModulesUser = count($modulesUserCompany);

        /*
         * Si no son iguales los modulos asignados a la compañia
         * que los modulos asignados al usuario,
         * realizamos una busqueda para saber que modulos le faltan
         * al usuario y los creamos como desactivados
         */
        if ($countModulesUser < $countModulesCompany) {

            for ($i = 0; $i < $countModulesCompany; $i++) {
                $module = $modulesCompany[$i];
                $moduleFound = false;

                for ($j = 0; $j < $countModulesUser; $j++) {

                    $cmModuleId = $modulesUserCompany[$j]->getUmCompanyModu()->getCmModule()->getId();
                    if ($module->getCmModule()->getId() == $cmModuleId) {
                        $moduleFound = true;
                        break;
                    }
                }

                /* creamos el modulo para la subasta porque no lo tenia */
                if ($moduleFound == false) {
                    $newModuleUser = new UserModules();
                    $newModuleUser->setUmCompanyModu($module);
                    $newModuleUser->setUmUser($userCompany);
                    $newModuleUser->setUmAccess(false);
                    $em->persist($newModuleUser);
                    $em->flush();
                }
            }
            //volvemos a consultar los modulos para el usuario, pues ya cambiaron
            $modulesUserCompany = $em->getRepository('App:UserModules')
                    ->findUserModulesToEdition($userCompany->getId());
        }

        return $this->render('SuperAdmin\editUserModuleAccess.html.twig', array(
                    'user' => $userCompany,
                    'menu' => 'managment',
                    'company' => $userCompany->getUsCompany(),
                    'modulesUserCompany' => $modulesUserCompany,
        ));
    }
    
    /**
     * Esta funcion permite actualizar los privilegios de los modulos indicados
     * para una compañia, ya sea habilitando o deshabilitando dichos modulos
     * @author Aealan Z - kijho Technologies <lrobledo@kijho.com>
     * @param \Symfony\Component\HttpFoundation\Request $request datos de la solicitud
     * @return \Symfony\Component\HttpFoundation\Response objeto JSOn con el mensaje de respuesta
     * 
     * @Route("/updatePrivilegesCompany", name="adpoint_update_company_privileges", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function updatePrivilegesCompany(Request $request) {
        // Verificacion de qeu la accion sea hecha por un Super Admin
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $response['msg'] = "Access Denied !";
            $response['result'] = '__KO__';
        } else {
            // Actualizacio de los modulos
            $moduleIds = json_decode($request->request->get('moduleId'));
            $isActive = (boolean) $request->request->get('isActive');
            $companyId = $request->request->get('companyId');
            $em = $this->getDoctrine()->getManager();

            $response['msg'] = "Module not found";
            $response['result'] = '__KO__';
            for ($i = 0; $i < count($moduleIds); $i++) {
                $moduleCompany = $em->getRepository('App:CompanyModule')
                        ->findBy(array('cmCompany' => $companyId, 'cmId' => $moduleIds[$i]));
                if ($moduleCompany) {
                    foreach ($moduleCompany as $module) {
                        $module->setCmAccess((int) $isActive);
                        $em->persist($module);
                    }
                    $response['msg'] = "Module Updated";
                    $response['result'] = '__OK__';
                }
            }
            $em->flush();
        }

        $resp = new Response(json_encode($response));
        $resp->headers->set('Content-Type', 'application/json');
        return $resp;
    }
    
}
