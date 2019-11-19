<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Controller\ParametersNormalizerController;
use App\Util\AccessControl;
use App\Entity\Module;

/**
 * Description of ReportsController
 *
 * @author hector
 * @Route("/reports", defaults={"_locale"="en"})
 */
class ReportsController extends ParametersNormalizerController {

    /**
     * Esta funcion lista los crons que tiene la aplicacion
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param Request $request
     * @return type render view
     * @throws type
     * 
     * @Route("/index", name="adpoint_reports", options={ "method_prefix" = false })
     */
    public function index(Request $request) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_REPORTS, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') && false === $this->get('security.authorization_checker')->isGranted('ROLE_REPORT_VIEWER')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', array('msg' => 'Your session has expired. Please login again')));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $theToken = $this->get('security.token_storage')->getToken();
        $user = $theToken->getUser();

        $em = $this->getDoctrine()->getManager();
        $modulesUserCompany = $em->getRepository('App:UserModules')->findBy(['umModule' => 23, 'umUser' => $user->getId()]);

        if (count($modulesUserCompany) <= 0 && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') && false === $this->get('security.authorization_checker')->isGranted('ROLE_REPORT_VIEWER')) {
            throw $this->createAccessDeniedException('Access Denied!');
        } elseif (count($modulesUserCompany) <= 0) {
            $modulesUserCompany = $em->getRepository('App:Module')->find(23);
            $moduleSlug = $modulesUserCompany->getMoSlug();
        } else {
            $moduleSlug = $modulesUserCompany[0]->getUmModule()->getMoSlug();
        }

        return $this->render('Reports\index.html.twig', array(
                    'menu' => 'reports',
                    'module' => $moduleSlug
        ));
    }

    public function searchLicExpired(Request $request) {
        $access_control = $this->get('access_control')->checkAccessModule(Module::MODULE_LICENSOR_REPORTS, $request);
        if ($access_control !== AccessControl::ACCESS_GRANTED && false === $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') && false === $this->get('security.authorization_checker')->isGranted('ROLE_REPORT_VIEWER')) {
            if ($access_control == AccessControl::SESSION_LOST) {
                return $this->redirect($this->generateUrl('adspace_login', array('msg' => 'Your session has expired. Please login again')));
            } elseif ($access_control == AccessControl::ACCESS_DENIED) {
                //code for access denied
                throw $this->createAccessDeniedException('Access Denied');
            }
        }

        $params = $request->request->getIterator()->getArrayCopy();

        if (!isset($params['endDate'])) {
            $currenDate = new \DateTime("now");
            $endDate = clone($currenDate);
            $endDate->modify("+5 days");
            $params = ['endDate' => $endDate->format('m/d/Y')];
        }
        $endDate = new \DateTime($params['endDate']);
        $endDate->setTime(23, 59, 59);
        return $this->searchLicenseExpiredList($endDate);
    }

    private function searchLicenseExpiredList(\DateTime $endDate) {
        $currenDate = new \DateTime("now");
        $arrDates = array($currenDate, $endDate);
        $em = $this->getDoctrine()->getManager();
        $data = $em->getRepository('App:LicenseDevice')
                ->getLicenseToexpire($arrDates);

        return $this->render('Reports\reportExpiredDevice.html.twig', array(
                    'datas' => $data,
                    'dates' => $arrDates,
                    'menu' => 'accounts'
        ));
    }

    public function licensesSold(Request $request) {

        $params = $request->request->getIterator()->getArrayCopy();
        $arrDates = array();
        if (!isset($params['startDate']) || !isset($params['endDate'])) {
            $currenDate = new \DateTime("now");
            $startDate = clone($currenDate);
            $endDate = clone($currenDate);
            $startDate->setDate($currenDate->format("Y"), $currenDate->format("m"), "01");
        } else {
            $startDate = new \DateTime($params['startDate']);
            $endDate = new \DateTime($params['endDate']);
        }
        $em = $this->getDoctrine()->getManager();

        $startDate->setTime(00, 00, 00);
        $endDate->setTime(23, 59, 59);

        $arrDates = array("startDate" => $startDate, "endDate" => $endDate);
        $data = $em->getRepository('App:LicensesSold')
                ->getLicensesSold($arrDates);

        return $this->render('Reports\licensesSold.html.twig', array(
                    'entities' => $data,
                    'dates' => $arrDates,
                    'menu' => 'reports',
        ));
    }

}
