<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Controller\ParametersNormalizerController;

/**
 * DefaultController controller.
 *
 * @Route("/cloud", defaults={"_locale"="en"})
 */
class DefaultController extends ParametersNormalizerController {

    /**
     *
     * @param \App\Controller\Request $request
     * @return type
     *
     * @Route("/index", name="index", options={ "method_prefix" = false })
     */
    public function index() {
        return new Response('
            <html>
                <body>
                    <h1>Space Marketing Cloud</h1>
                </body>
            </html>
        ');
    }

    /**
     *
     * @param \App\Controller\Request $request
     * @return type
     *
     * @Route("/login", name="adspace_login", options={ "method_prefix" = false })
     */
    public function login(Request $request) {

        $helper = $this->get('symfony_security_authentication_utils');

        $msg = $request->query->get('msg');

        $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();

        return $this->render('Default\login.html.twig', array(
                    'error' => $helper->getLastAuthenticationError(),
                    'last_username' => $helper->getLastUsername(),
                    'msg' => $msg,
                    'csrf_token' => $csrfToken
        ));
    }

    /**
     * Esta funcion se enfcarga de crear una variable de session que
     * aumentara segun la cantidad de intentos de login que tenga el
     * usuario
     *
     * @Route("/sessionAptemps", name="adspace_session_by_login", options={ "method_prefix" = false })
     */
    public function sessionByLoginCompany(Request $request) {
        $response = [];
        $session = $request->getSession();

        if ($request->getMethod() == 'POST') {
            $value = $request->request->get('model');
            $session->set(sha1('counterRequestAjaxPostCompany'), $value);
            $response['result'] = '__OK__';
        }

        $r = new Response(json_encode($response));
        $r->headers->set('Content-Type', 'application/html');
        return $r;
    }

    /**
     * Esta funcion permite validar los permisos del usuario logueado y guardarlos en un arreglo en session
     * el cual se usara en toda la aplicacion para mostrar u ocultar iconos y enlaces a los modulos correctos
     *
     * @return \HttpRequest pagina de inicio de la aplicacion
     *
     * @Route("/accessControl", name="adspace_access_control", options={ "method_prefix" = false })
     */
    public function accessControl(Request $request) {
        $theToken = $this->get('security.token_storage')->getToken();

        $em = $this->getDoctrine()->getManager();

        if (null != $theToken) {

            $user = $theToken->getUser();

            $rolesArray = $user->getRoles();

            if (!empty($rolesArray) && $rolesArray[0] == 'ROLE_INACTIVE') {
                $request->getSession()->invalidate(1);
                sleep(1);
                return $this->redirect($this->generateUrl('adspace_login', ['msg' => 'User Inactive']));
            }

            $search['only_active'] = true;
            $modulesUserCompany = $em->getRepository('App:UserModules')->findUserModulesToEdition($user->getId(), $search);

            $activeModules = [];
            for ($i = 0; $i < count($modulesUserCompany); $i++) {
                $activeModules[$i] = $modulesUserCompany[$i]->getUmModule()->getMoSlug();
            }

            $session = $request->getSession();
            $session->set('activeModules', $activeModules);
        }

        $referer = $request->getUri();

        if (strpos($referer, 'readedNoty=')) {
            $theNotyIdArray = explode('#readedNoty=', $referer);

            $newSeenNoty = $em->getRepository('App:BellNotificationSeenBy')->find($theNotyIdArray[1]);
            if ($newSeenNoty) {
                $actualUnmodifiedDate = Util::getCurrentDate();

                $newSeenNoty->setDateSeen($actualUnmodifiedDate);
                $newSeenNoty->setNotificationStatus(BellNotificationSeenBy::STATUS_NOTIFIED);

                $em->persist($newSeenNoty);
                $em->flush();
            }
        }

        return $this->redirect($this->generateUrl('adspace_dashboard'));
    }

    /**
     * @return type
     *
     * @Route("/dashboard", name="adspace_dashboard", options={ "method_prefix" = false })
     */
    public function dashboard() {

//        $diffDate = new \stdClass();
//        $diffDate->days = 1;
//
//        dump($diffDate->days);
//        die;

        if ('anon.' == $this->get('security.token_storage')->getToken()->getUser()) {
            return $this->redirect($this->generateUrl('adspace_logout'));
        }

        return $this->render('Default\dashboard.html.twig', [
                    'menu' => 'dashboard',
        ]);
    }

    /**
     * @return Response
     *
     * @Route("/getSession", name="adspace_get_session", options={ "method_prefix" = false })
     */
    public function getStatusSession() {
        $resp = [];
        $status = true;
        $response = 'Session in ok';
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_LICENSE);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                $response = "Session has expired";
                $status = false;
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                $response = "Access Denied";
                $status = false;
            }
        }

        $resp["msn"] = "__OK__";
        $resp["response"] = $response;
        $resp["status"] = $status;

        $r = new Response(json_encode($response));
        $r->headers->set('Content-Type', 'application/html');
        return $r;
    }

}
