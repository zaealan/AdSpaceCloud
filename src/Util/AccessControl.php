<?php

namespace App\Util;

use App\Entity\User;
use App\Entity\BellNotificationSeenBy;
//use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Component\Security\Core\Security;
//use Symfony\Component\DependencyInjection\ContainerInterface;

class AccessControl {

    const SESSION_LOST = 1;
    const ACCESS_DENIED = 2;
    const ACCESS_GRANTED = 3;

    protected $em;
    protected $container;
    protected $security;
    protected $session;

    /**
     * Este es el constructor de la clase AccessControl
     * el cual recibe las instancias necesarias para hacer conexion
     * con la base de datos y el componente de seguridad de symfony
     * @author Aealan Z - Kijho Technologies <lrobledo@kijho.com>
     * @since 1.0 12/03/2014
     * @param \Doctrine\ORM\EntityManagerInterface entityManager
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct($entityManager, $container, $security, $session) {
        $this->em = $entityManager;
        $this->container = $container;
        $this->security = $security;
        $this->session = $session;
    }

    /**
     * Esta funcion permite saber si un usuario tiene acceso a un modulo en especifico
     * @param string $slug slug que identifica el modulo a consultar
     * @return boolean true si tiene acceso, false si no lo tiene
     */
    public function checkAccessModule($slug, $request = null) {
        //parametros de busqueda para el modulo
        $search['only_active'] = true;
        $search['slug'] = $slug;

        if (!$this->security->getToken()) {
            return self::SESSION_LOST;
        }

        //obtenemos el usuario logueado
        $userCompany = $this->security->getToken()->getUser();

        //validamos si no ha perdido sesion el token actual
        if (!($userCompany instanceof User)) {
            return self::SESSION_LOST;
        }

        if ($request) {
            $referer = $request->getUri();

            if (strpos($referer, 'readedNoty=')) {
                $theNotyIdArray = explode('readedNoty=', $referer);

                $newSeenNoty = $this->em->getRepository('App:BellNotificationSeenBy')->find($theNotyIdArray[1]);
                if ($newSeenNoty) {
                    $actualUnmodifiedDate = Util::getCurrentDate();

                    $newSeenNoty->setDateSeen($actualUnmodifiedDate);
                    $newSeenNoty->setNotificationStatus(BellNotificationSeenBy::STATUS_NOTIFIED);

                    $this->em->persist($newSeenNoty);
                    $this->em->flush();
                }
            }
        }

        $modulesUserCompany = $this->em->getRepository('App:UserModules')
                ->findUserModulesToEdition($userCompany->getId(), $search);
        if ($modulesUserCompany) {
            return self::ACCESS_GRANTED;
        }
        return self::ACCESS_DENIED;
    }

}
