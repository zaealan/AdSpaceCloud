<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of LogAndroidEmailSentRepository
 * @author zaealan
 */
class LogAndroidEmailSentRepository extends EntityRepository {

    /**
     * Metodo para obtener el mayor numero de un log registrado en la base de
     * de datos de licensor con el fin de seguir con la numeracion
     * correspondiente para los logs
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @return type integer con el mayor numero de registro de un log almacenado
     * en la base de datos
     */
    public function findRegisteredLogCodeLicense() {

        $em = $this->getEntityManager();
        $dql = 'SELECT MAX(le.logCode)
            FROM App:LogAndroidEmailSent le';
        $consult = $em->createQuery($dql);

        return $consult->getSingleScalarResult();
    }

}
