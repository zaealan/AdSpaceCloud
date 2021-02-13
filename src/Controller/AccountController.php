<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Module;
use App\Entity\Account;
use App\Entity\AccountLicense;
use App\Entity\PushSent;
use App\Form\AccountType;
use App\Form\SearchAccountType;
use App\Util\Util;
use App\Util\Paginator;
use App\Util\WS\Util as Utilx;
use App\Util\ValidatorUtil;
use App\Util\AccessControl;
use App\Controller\ParametersNormalizerController;

/**
 * AccountController
 *
 * @Route("/AdPointAccount", defaults={"_locale"="en"})
 */
class AccountController extends ParametersNormalizerController {

    /**
     * Lists all Account entities.
     *
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

        $paginator = $this->get('simple_paginator');
        $pageRanges = [10, 25, 50];
        $paginator->pageRanges = $pageRanges;

        if (null != $request->query->get('itemsPerPage') && '' != $request->query->get('itemsPerPage')) {
            $itemsPerPage = (int) $request->query->get('itemsPerPage');
        } else {
            $itemsPerPage = $pageRanges[0];
        }

        $paginator->setItemsPerPage($itemsPerPage);
        $paginator->setMaxPagerItems(5);

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
                    $entitiesToPaginate[$i]->licenseNum = $em->getRepository('App:AccountLicense')
                            ->accountLicensesForSuperAdminUsers($searchL, '', true);
                }
            } else {
                $entitiesToPaginate = [];
            }

            $entities = $paginator->paginate($entitiesToPaginate)->getResult();
        } else {
            $entities = $paginator->paginate([])->getResult();
        }

        /* Construimos las url para las peticiones get del ordenador y paginador */
        $params = Paginator::getUrlFromParameters($indexSearch, $search);
        $orderBy = Paginator::getUrlOrderFromParameters($indexOrder, $order);

        return $this->render('Account\index.html.twig', array(
                    'entities' => $entities,
                    'menu' => 'accounts',
                    'form' => $form->createView(),
                    'paginator' => $paginator,
                    'params' => $params,
                    'search' => $search,
                    'orderBy' => $orderBy,
        ));
    }

    /**
     * Creates a new Account entity.
     *
     * @Route("/new", name="adpoint_account_create", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function create(Request $request) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_ACCOUNT_CREATE, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', array('msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente')));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $entity = new Account();
        $userSession = $this->get('security.token_storage')->getToken()->getUser();

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

//        $typeValueArray = [];
//        $typeValueArray[] = ['type' => 'regex', 'pattern' => "/^[A-Za-z0-9 _]*[A-Za-z0-9][A-Za-z0-9 _]*$/", 'field' => 'Client Full Name', 'message' => 'Allowed numbers and letters only in <strong>%s</strong> field!', 'data' => $entity->getAcContactName()];
//        $typeValueArray[] = ['type' => 'regex', 'pattern' => "/^[A-Za-z0-9 _]*[A-Za-z0-9][A-Za-z0-9 _]*$/", 'field' => 'Account Name', 'message' => 'Allowed numbers and letters only in <strong>%s</strong> field!', 'data' => $entity->getAcName()];
//
//        $validationResult = ValidatorUtil::validateThis($this->get('symfony_validator'), $typeValueArray);

        $entity->setAcName(Util::replaceCharactersEspecials($entity->getAcName(), false));
        $checkAccountList = $em->getRepository('App:Account')->checkAccountExist($entity);

        foreach ($checkAccountList as $key => $checkAccount) {
            $validationResult = [];
            if (isset($checkAccount) && strtolower($checkAccount->getAcName()) === strtolower($entity->getAcName())) {
                $validationResult[0] = false;
                $validationResult[1][0]['isValid'] = false;
                $validationResult[1][0]['message'] = 'Account name already exists';
                if (isset($validationResult[1][1])) {
                    unset($validationResult[1][1]);
                }
            }
            // if (isset($checkAccount) && $checkAccount->getAcEmail() === $entity->getAcEmail()) {
            //     $validationResult[0] = false;
            //     $validationResult[1][0]['isValid'] = false;
            //     $validationResult[1][0]['message'] = 'Email already exists';
            //     if (isset($validationResult[1][1])) {
            //         unset($validationResult[1][1]);
            //     }
            // }
            // if (isset($checkAccount) && $checkAccount->getAcPhoneNumber() === $entity->getAcPhoneNumber()) {
            //     $validationResult[0] = false;
            //     $validationResult[1][0]['isValid'] = false;
            //     $validationResult[1][0]['message'] = 'The phone number already exists';
            //     if (isset($validationResult[1][1])) {
            //         unset($validationResult[1][1]);
            //     }
            // }
        }


        if ($form->isValid()) {
            $entity->setAcDateCreated(new \DateTime('NOW'));
            $entity->setAcUser($userSession);
            $em = $this->getDoctrine()->getManager();

            $params = $request->request->getIterator()->getArrayCopy();
//            $resutlArray = Util::validateAndSaveCityZipcodeBlock($this->realContainer, $em, $params, $entity);
            $resutlArray = Util::validateAndSaveAddressAutoComplete($this->realContainer, $em, $params, $entity);

            if ($resutlArray['status']) {
                $em->persist($entity);
                $em->flush();

                $entity->setAcName(Util::replaceCharactersEspecials($entity->getAcName(), false));

                $insertId = $entity->getId();
                $nickName = strtolower(substr(Util::replaceCharactersEspecials($entity->getAcName()), 0, 10)) . "$insertId";
                $entity->setAcNickName($nickName);

                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('adpoint_accounts'));
            } else {
                $notificationMessage = $resutlArray['message'];
                $this->get('session')->getFlashBag()->add('msgError', $notificationMessage);
            }
        } else {
            $notificationMessage = (string) $form->getErrors(true, false);

//            if (!$validationResult[0]) {
//                foreach ($validationResult[1] as $value) {
//                    if (!$value['isValid']) {
//                        $this->get('session')->getFlashBag()->add('msgError', sprintf($value['message'], $value['field']));
//                    }
//                }
//            }
        }

        $countries = $em->getRepository('App:Country')->findAll();
        $states = $em->getRepository('App:State')->findBy(['stCountry' => 1]);

        return $this->render('Account\new.html.twig', [
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'menu' => 'accounts',
                    'countries' => $countries,
                    'states' => $states,
                    'notificationMessage' => $notificationMessage,
        ]);
    }

    /**
     * Creates a form to create a Account entity.
     *
     * @param Account $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Account $entity) {
        $form = $this->createForm(AccountType::class, $entity, [
            'action' => $this->generateUrl('adpoint_account_create'),
            'method' => 'POST'
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    /**
     * Displays a form to create a new Account entity.
     *
     * @Route("/new", name="adpoint_account_new", options={ "method_prefix" = false })
     */
    public function newAccount(Request $request) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_ACCOUNT_CREATE, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', array('msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente')));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = new Account();
        $form = $this->createCreateForm($entity);

        $countries = $em->getRepository('App:Country')->findAll();
        $states = $em->getRepository('App:State')->findBy(array('stCountry' => 1));

        return $this->render('Account\new.html.twig', array(
                    'entity' => $entity,
                    'countries' => $countries,
                    'states' => $states,
                    'form' => $form->createView(),
                    'menu' => 'accounts',
        ));
    }

    /**
     * Finds and displays a Account entity.
     *
     */
    public function show(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_ACCOUNT, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', array('msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente')));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('App:Account')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Account entity.');
        }

        return $this->render('Account\show.html.twig', array(
                    'entity' => $entity,
                    'menu' => 'accounts',
        ));
    }

    /**
     * Displays a form to edit an existing Account entity.
     *
     * @Route("/{id}/edit", name="adpoint_account_edit", options={ "method_prefix" = false })
     */
    public function edit(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_ACCOUNT_EDIT, $request);
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
            throw $this->createNotFoundException('Unable to find Account entity.');
        }

        $editForm = $this->createEditForm($entity);

        $countries = $em->getRepository('App:Country')->findAll();
        $country = $entity->getCity()->getCiState()->getStCountry();
        $states = $em->getRepository('App:State')->findBy(array('stCountry' => $country->getCoId()));

        return $this->render('Account\edit.html.twig', array(
                    'entity' => $entity,
                    'countries' => $countries,
                    'states' => $states,
                    'form' => $editForm->createView(),
                    'menu' => 'accounts',
        ));
    }

    /**
     * Creates a form to edit a Account entity.
     *
     * @param Account $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(AccountLicense $entity) {
        $form = $this->createForm(AccountType::class, $entity, [
            'action' => $this->generateUrl('adpoint_account_update', ['id' => $entity->getId()]),
            'method' => 'POST'
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    /**
     * Edits an existing Account entity.
     *
     * @Route("/{id}/update", name="adpoint_account_update", options={ "method_prefix" = false })
     */
    public function update(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_ACCOUNT_EDIT, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', array('msg' => 'Su sesion ha expirado, porfavor ingrese nuevamente')));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('App:Account')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Account entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

//        $typeValueArray = [];
//        $typeValueArray[] = ['type' => 'regex', 'pattern' => "/^[A-Za-z0-9 _]*[A-Za-z0-9][A-Za-z0-9 _]*$/", 'field' => 'Client Full Name', 'message' => 'Allowed numbers and letters only in <strong>%s</strong> field!', 'data' => $entity->getAcContactName()];
//        $typeValueArray[] = ['type' => 'regex', 'pattern' => "/^[A-Za-z0-9 _]*[A-Za-z0-9][A-Za-z0-9 _]*$/", 'field' => 'Account Name', 'message' => 'Allowed numbers and letters only in <strong>%s</strong> field!', 'data' => $entity->getAcName()];
//
//        $validationResult = ValidatorUtil::validateThis($this->get('symfony_validator'), $typeValueArray);

        $entity->setAcName(Util::replaceCharactersEspecials($entity->getAcName(), false));
        $checkAccountList = $em->getRepository('App:Account')->checkAccountExist($entity);

        foreach ($checkAccountList as $key => $checkAccount) {
            $validationResult = [];
            if (isset($checkAccount) && strtolower($checkAccount->getAcName()) === strtolower($entity->getAcName()) && $checkAccount->getId() !== $entity->getId()) {
                $validationResult[0] = false;
                $validationResult[1][0]['isValid'] = false;
                $validationResult[1][0]['message'] = 'Account name already exists';
                if (isset($validationResult[1][1])) {
                    unset($validationResult[1][1]);
                }
            }

//            if (isset($checkAccount) && $checkAccount->getAcEmail() === $entity->getAcEmail() && $checkAccount->getId() !== $entity->getId()) {
//                $validationResult[0] = false;
//                $validationResult[1][0]['isValid'] = false;
//                $validationResult[1][0]['message'] = 'Email already exists';
//                if (isset($validationResult[1][1])) {
//                    unset($validationResult[1][1]);
//                }
//            }
//
//            if (isset($checkAccount) && $checkAccount->getAcPhoneNumber() === $entity->getAcPhoneNumber() && $checkAccount->getId() !== $entity->getId()) {
//                $validationResult[0] = false;
//                $validationResult[1][0]['isValid'] = false;
//                $validationResult[1][0]['message'] = 'The phone number already exists';
//                if (isset($validationResult[1][1])) {
//                    unset($validationResult[1][1]);
//                }
//            }
        }


        if ($editForm->isValid()) {
            $params = $request->request->getIterator()->getArrayCopy();
//            $resutlArray = Util::validateAndSaveCityZipcodeBlock($this->realContainer, $em, $params, $entity);
            $resutlArray = Util::validateAndSaveAddressAutoComplete($this->realContainer, $em, $params, $entity);
            if ($resutlArray['status']) {
                $em->flush();
                return $this->redirect($this->generateUrl('adpoint_accounts'));
            } else {
                $notificationMessage = $resutlArray['message'];
            }

//            dump($notificationMessage);
//            die;
        } else {
            $notificationMessage = (string) $editForm->getErrors(true, false);

//            if (!$validationResult[0]) {
//                foreach ($validationResult[1] as $value) {
//                    if (!$value['isValid']) {
//                        $this->get('session')->getFlashBag()->add('msgError', sprintf($value['message'], $value['field']));
//                    }
//                }
//            }
        }

        $countries = $em->getRepository('App:Country')->findAll();
        $states = $em->getRepository('App:State')->findBy(['stCountry' => 1]);

        return $this->render('Account\edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'menu' => 'accounts',
                    'countries' => $countries,
                    'states' => $states,
                    'notificationMessage' => $notificationMessage,
        ));
    }

    /**
     * Deletes a Account entity.
     *
     */
    public function delete(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_ACCOUNT_DELETE, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $response['msg'] = 'Access Denied !';
            $response['result'] = '__KO__';
        } else {
            $em = $this->getDoctrine()->getManager();

            $response['msg'] = 'Deleted Success!';
            $response['result'] = '__OK__';

            $accountDel = $em->find('App:Account', $id);

            if (!$accountDel) {
                $response['msg'] = 'Account Not Found!';
                $response['result'] = '__KO__';
            } else {
                $accountDel->setDeleted(true);
                $em->persist($accountDel);
                $em->flush();
            }
        }

        $r = new Response(json_encode($response));
        $r->headers->set('Content-Type', 'application/json');
        return $r;
    }

    public function changeStatus(Request $request, $accountId) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_ACCOUNT_DELETE, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $response['msg'] = 'Access Denied !';
            $response['result'] = '__KO__';
        } else {
            $em = $this->getDoctrine()->getManager();
            $curStatus = $request->request->get('status');
            $account = $em->getRepository('App:Account')->find($accountId);
            if (!$account) {
                $response['msg'] = 'Oops! Unable to find Account entity!';
                $response['result'] = '__KO__';
            } else {
                $dataResponse = [];
                $dataResponse["status"] = 200;

                $typePush = 0;
                $licenseStatus = 0;
                $reverseLicenseStatus = 0;
                if ($curStatus == 1) {
                    $typePush = PushSent::PUSH_TYPE_LICENSE_DISABLED;
                    $licenseStatus = AccountLicense::LICENSE_STATUS_INACTIVE;
                    $reverseLicenseStatus = AccountLicense::LICENSE_STATUS_ACTIVE;
                    $dataResponse["actions"] = "disabledlicense";
                    $account->setDeleted(true);
                } else {
                    $typePush = PushSent::PUSH_TYPE_LICENSE_ENABLED;
                    $licenseStatus = AccountLicense::LICENSE_STATUS_ACTIVE;
                    $reverseLicenseStatus = AccountLicense::LICENSE_STATUS_INACTIVE;
                    $dataResponse["actions"] = "enabledlicense";
                    $account->setDeleted(false);
                }

                $licensesByAccount = $em->getRepository('App:AccountLicense')->findBy(['alAccountLicense' => $account->getId(), 'alLicenseStatus' => $reverseLicenseStatus]);

                foreach ($licensesByAccount as $license) {

                    //////////// toca mirar eso bn porque no esta funcionando como deberia...
                    $path = Utilx::getValidActiveLogByBaseName($this->realContainer, 'newPushSenderTest', $this->getParameter('level_directory_data_android') . 'newPushSenderTest0.txt');
                    Utilx::sendSuperPush($this->realContainer, $em, $dataResponse, $typePush, $license, $path);
                }

                $em->persist($account);
                $em->flush();
                $response['msg'] = 'Change status ok';
                $response['result'] = '__OK__';
                $response['newStatus'] = $account->accountStatus();
            }
        }
        $r = new Response(json_encode($response));
        $r->headers->set('Content-Type', 'application/json');
        return $r;
    }

    public function createFileJson($path, $text) {
        $file = fopen($path, "a+");
        fwrite($file, $text);
        fclose($file);
    }

}
