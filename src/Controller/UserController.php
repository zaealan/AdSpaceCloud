<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\User;
use App\Entity\Module;
use App\Entity\UserModules;
use App\Form\UserType;
use App\Util\AccessControl;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Controller\ParametersNormalizerController;

/**
 * UserController
 * 
 * @author aealan
 * @Route("/user", defaults={"_locale"="en"})
 */
class UserController extends ParametersNormalizerController {

    /**
     * Lists all User entities.
     *
     * @Route("/", name="adpoint_users", options={ "method_prefix" = false })
     */
    public function index(Request $request) {
        $lostSession = 'Su sesion ha expirado, porfavor ingrese nuevamente';
        return $this->redirect($this->generateUrl('adspace_login', ['msg' => $lostSession]));
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/create", name="adpoint_user_create", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function create(Request $request) {

        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_USERS_CREATE, $request);
        $validating = $this->validateAccess($access_control);
        if (!$validating) {
            $lostSession = 'Su sesion ha expirado, porfavor ingrese nuevamente';
            return $this->redirect($this->generateUrl('adspace_login', ['msg' => $lostSession]));
        }

        $userSession = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $type = '';

        /*
         * Se utiliza para validar si trae el id del reseller,
         * ocurre cuando se crea un usuario desde resellers
         */
        $dataForm = $request->request->getIterator()->getArrayCopy();
        $entity = new User();
        $form = $this->createCreateForm($entity, $em, $type);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setUsDateCreated(new \DateTime('now'));
            $entity->setUsStatus(true);
            $entity->setIsOMTUser(false);
            $entity->setUsUserParent($userSession);

            $encoder = $this->realContainer->get('security_password_encoder');
            $password = $encoder->encodePassword($entity, $entity->getPassword());
            $entity->setPassword($password);

            $params = $request->request->get('levellicensor_levellicensorbundle_user');

            if (isset($params['usCompany']) && $params['usCompany'] != '') {
                $companyEntity = $em->getRepository('App:Company')->find($params['usCompany']);
            } else {
                $companyEntity = null;
            }

            $entity->setUsCompany($companyEntity);

            $em->persist($entity);
            $em->flush();

            if ($companyEntity) {
                $this->createPrivilegesUserDefault($companyEntity, $entity);
            }

            if (isset($dataForm['reseller_id']) && $dataForm['reseller_id'] != '') {
                return $this->redirect($this->generateUrl('company_managment_companies_users_list', array(
                                    'companyId' => $dataForm['reseller_id']
                                        )
                ));
                //Cambiar el argumento
            } else {
                return $this->redirect($this->generateUrl('adpoint_managment_company_users_list'));
            }
        }

        return $this->render('User\new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'menu' => 'users',
        ));
    }

    /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity, $em, $type = '') {
        $form = $this->createForm(UserType::class, $entity, [
            'action' => $this->generateUrl('adpoint_managment_company_users_list'),
            'em' => $em,
            'selected_choice_type' => $type,
            'selected_choice_companies' => null
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    /**
     * Displays a form to create a new User entity.
     *
     */
    public function newUser(Request $request) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_USERS_CREATE, $request);
        $validating = $this->validateAccess($access_control);
        if (!$validating) {
            $lostSession = 'Su sesion ha expirado, porfavor ingrese nuevamente';
            return $this->redirect($this->generateUrl('adspace_login', array('msg' => $lostSession)));
        }

        $em = $this->getDoctrine()->getManager();

        $type = '';

        $entity = new User();
        $form = $this->createCreateForm($entity, $em, $type);

        return $this->render('User\new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'menu' => 'users',
        ));
    }

    /**
     * Finds and displays a User entity.
     *
     */
    public function show(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_USERS, $request);
        $validating = $this->validateAccess($access_control);
        if (!$validating) {
            $lostSession = 'Su sesion ha expirado, porfavor ingrese nuevamente';
            return $this->redirect($this->generateUrl('adspace_login', array('msg' => $lostSession)));
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('App:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        return $this->render('User\show.html.twig', array(
                    'entity' => $entity,
                    'menu' => 'users',
        ));
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="adpoint_user_edit", options={ "method_prefix" = false })
     */
    public function edit(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_USERS_EDIT, $request);
        $validating = $this->validateAccess($access_control);
        if (!$validating) {
            $lostSession = 'Su sesion ha expirado, porfavor ingrese nuevamente';
            return $this->redirect($this->generateUrl('adspace_login', array('msg' => $lostSession)));
        }

        $em = $this->getDoctrine()->getManager();

        $reseller_entity = "";

        $entity = $em->getRepository('App:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $type = $entity->getUsType();
        $company = $entity->getUsCompany()->getId();

        $reseller_entity = $em->getRepository('App:Company')->find($company);

        $editForm = $this->createEditForm($entity, $em, $type, $company);

        return $this->render('User\edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'menu' => 'users',
                    'reseller' => $reseller_entity
        ));
    }

    /**
     * Esta funcion permite cargar los modulos habilitados para una compañia, para
     * permitir habilitar o deshabilitar dichos modulos al usuario seleccionado
     * @author Aealan Z - kijho Technologies <lrobledo@kijho.com>
     * @param integer $id identificador del usuario de la compañia
     * @return \HttpRequest formulario de edicion de privilegios de los modulos
     * @throws \Exception en caso de no encontrar la compañia, subasta fisica o usuario
     * 
     * @Route("/{id}/editPrivileges", name="adpoint_user_privileges", options={ "method_prefix" = false })
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
     * Creates a form to edit a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(User $entity, $em, $type = '', $company = '') {
        $form = $this->createForm(UserType::class, $entity, [
            'action' => $this->generateUrl('adpoint_user_update', ['id' => $entity->getId()]),
            'method' => 'POST',
            'em' => $em,
            'selected_choice_type' => $type
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    /**
     * Edits an existing User entity.
     *
     * @Route("/{id}/update", name="adpoint_user_update", options={ "method_prefix" = false })
     */
    public function update(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_USERS_EDIT, $request);
        $validating = $this->validateAccess($access_control);
        if (!$validating) {
            $lostSession = 'Su sesion ha expirado, porfavor ingrese nuevamente';
            return $this->redirect($this->generateUrl('adspace_login', array('msg' => $lostSession)));
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('App:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $type = '';

        //Se utiliza para validar si trae el id del reseller, ocurre cuando se crea un usuario desde resellers
        $dataForm = $request->request->getIterator()->getArrayCopy();

        $oldStatus = $entity->getUsStatus();
        $oldUserName = $entity->getUsername();
        $oldPass = $entity->getPassword();
        $oldSalt = $entity->getSalt();

        $editForm = $this->createEditForm($entity, $em, $type);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $entity->setUsStatus($oldStatus);

            $params = $request->request->get('levellicensor_levellicensorbundle_user');

            if (isset($params['usCompany']) && $params['usCompany'] != '') {
                $companyEntity = $em->getRepository('App:Company')->find($params['usCompany']);
            } else {
                $companyEntity = null;
            }

            $entity->setUsCompany($companyEntity);
            /*
             * Se pregunta si existe la clave y nombre de usuario en el
             * formulario, para proceder a encodificarlo
             */
            if ($params['password']["first"] == '' || !isset($params['username']) || $params['username'] == '') {

                $entity->setUsername($oldUserName);
                $entity->setPassword($oldPass);
                $entity->setSalt($oldSalt);
                $entity->setIsOMTUser(false);
            } else {
                // Evita que se cambie por inspección de elemento
                $entity->setUsername($oldUserName);
                $encoder = $this->realContainer->get('security_password_encoder');
                $password = $encoder->encodePassword($entity, $entity->getPassword());
                $entity->setPassword($password);
            }

            $em->persist($entity);
            $em->flush();

            if (isset($dataForm['reseller_id']) && $dataForm['reseller_id'] != '') {
                return $this->redirect($this->generateUrl('company_managment_companies_users_list', array('companyId' => $dataForm['reseller_id'])
                ));
                //Cambiar el argumento
            } else {
                return $this->redirect($this->generateUrl('user'));
            }
        }

        return $this->render('User\edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'menu' => 'users',
        ));
    }

    /**
     * Esta funcion permite eliminar de forma logica al usuario
     * @param type $id: id del usuario a eliminar
     * @return type
     * @throws type
     * 
     * @Route("/{id}/delete", name="adpoint_user_delete", options={ "method_prefix" = false })
     */
    public function delete(Request $request, $id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_USERS_DELETE, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED &&
                false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $response['msg'] = 'Access Denied !';
            $response['result'] = '__KO__';
        } else {
            $em = $this->getDoctrine()->getManager();

            $response['msg'] = 'Deleted Success!';
            $response['result'] = '__OK__';

            $userDelt = $em->find('App:User', $id);

            if (!$userDelt) {
                $response['msg'] = 'User Not Found!';
                $response['result'] = '__KO__';
            } else {
                $userDelt->setDeleted(true);

                $em->persist($userDelt);
                $em->flush();
            }
        }

        $r = new Response(json_encode($response));
        $r->headers->set('Content-Type', 'application/json');
        return $r;
    }

    /**
     * esta funcion permite la creacion de los modulos por defecto que van agragar a un usuario de compañia segun el rol
     * @param type $companyId
     * @param type $id
     * @param type $userId
     * @throws \Exception en caso de no encontrar la compañia, subasta fisica o usuario
     */
    private function createPrivilegesUserDefault($company, $user) {

        $em = $this->getDoctrine()->getManager();

        //hacemos un conteo de los modulos que tiene habilitados la subasta fisica cmAccess
        $modulesCompany = $em->getRepository('App:CompanyModule')->findBy(['cmCompany' => $company->getId(), 'cmAccess' => true]);
        $countModulesCompany = count($modulesCompany);

        //condicion para setear el arreglo de los modulos que se van agragar por defecto segun el tipo de rol del usuario creado
        //tener cuidado al crear los arreglos con los mudulos se debe de poner en orden primero el padre de los modulos
        $moduleActiveUserDefault = null;
        if ($user->getUsType() == User::USER_ADMINISTRATOR) {
            $moduleActiveUserDefault = [Module::MODULE_LICENSOR_USERS, Module::MODULE_LICENSOR_LICENSE, Module::MODULE_LICENSOR_ACCOUNT, Module::MODULE_LICENSOR_COMPANY];
        } elseif ($user->getUsType() == User::USER_LICENSE_MANAGER) {
            $moduleActiveUserDefault = [Module::MODULE_LICENSOR_LICENSE, Module::MODULE_LICENSOR_ACCOUNT];
        } elseif ($user->getUsType() == User::USER_REPORT_VIEWER) {
            $moduleActiveUserDefault = [Module::MODULE_LICENSOR_REPORTS, Module::MODULE_LICENSOR_REPORTS_LICENSES_SOLD];
        }

        //luego buscamos los modulos creados para este usuario
        $modulesUserCompany = $moduleActiveUserDefault;

        $countModulesUser = count($moduleActiveUserDefault);

        for ($i = 0; $i < $countModulesCompany; $i++) {
            $module = $modulesCompany[$i];

            for ($j = 0; $j < $countModulesUser; $j++) {

                if ($module->getCmModule()->getMoSlug() == $modulesUserCompany[$j] ||
                        (null != $module->getCmModule()->getParent() &&
                        $module->getCmModule()->getParent()->getMoSlug() == $modulesUserCompany[$j])) {

                    $newModuleUser = new UserModules();
                    $newModuleUser->setUmCompanyModu($module);
                    $newModuleUser->setUmModule($module->getCmModule());
                    $newModuleUser->setUmUser($user);
                    $newModuleUser->setUmAccess(true);

                    $em->persist($newModuleUser);
                    $em->flush();
                    break;
                }
            }
        }
    }

    /**
     * @param Request $request
     * @param type $reseller_id
     * @return type
     * 
     * @Route("/{reseller_id}/newUser", name="adpoint_new_reseller_user", options={ "method_prefix" = false })
     */
    public function newUserForReseller(Request $request, $reseller_id) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_USERS_CREATE, $request);
        $validating = $this->validateAccess($access_control);
        if (!$validating) {
            $lostSession = 'Su sesion ha expirado, porfavor ingrese nuevamente';
            return $this->redirect($this->generateUrl('adspace_login', array('msg' => $lostSession)));
        }

        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('App:Company')->find($reseller_id);
        $type = '';

        $entity = new User();
        $form = $this->createCreateForm($entity, $em, $type);

        return $this->render('User\new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'menu' => 'users',
                    'company' => $company
        ));
    }

    /**
     * @param Request $request
     * @return Response
     * 
     * @Route("/searchUsername", name="adpoint_search_username", options={ "method_prefix" = false }, methods={"POST"})
     */
    public function searchUserName(Request $request) {
        
        $username = $request->request->get('username');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('App:User')->findBy(array('username' => $username));
        if (empty($user)) {
            $response['userExist'] = 0;
        } else {
            $response['userExist'] = 1;
        }
        $r = new Response(json_encode($response));
        $r->headers->set('Content-Type', 'application/json');
        return $r;
    }

    /**
     * Validar si el usuario logeado tiene privilegios para acceder al modulo
     * indicado por el access_control
     * @param integer $access_control numero de acceso
     * @return Redirect redirige al login en el caso de no tener privilegios
     * @throws AccessDeniedException
     */
    private function validateAccess($access_control) {
        /*
         * validar que permisos o que el usuario sea SUPER_ADMIN
         */
        if ($access_control !== AccessControl::ACCESS_GRANTED &&
                false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            /*
             * validar la existencia actual se session
             */
            if ($access_control == AccessControl::SESSION_LOST) {
                return false;
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                //code for access denied
                throw $this->createAccessDeniedException('Access Denied');
            }

            return false;
        }
        return true;
    }

}
