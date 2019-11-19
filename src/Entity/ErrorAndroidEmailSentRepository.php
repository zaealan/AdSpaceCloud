<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of ErrorAndroidEmailSentRepository
 * @author zaealan
 */
class ErrorAndroidEmailSentRepository extends EntityRepository {

    /**
     * Metodo que se encarga de encontrar registro de la entidad
     * ErrorAndroidEmailSent con el mayo valor de codigo de error para que asi
     * la aplicacion tenga conocimiento de la numeracion siguiente para los
     * futuros registros
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @return type integer con el numero del codigo de error mas grande
     * registrado en la base de datos de licensor
     */
    public function findRegisteredErrorCodeLicense() {

        $em = $this->getEntityManager();
        $dql = 'SELECT MAX(ee.errorCode)
            FROM App:ErrorAndroidEmailSent ee';
        $consult = $em->createQuery($dql);

        return $consult->getSingleScalarResult();
    }

}
