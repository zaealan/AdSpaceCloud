<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of NotificationInLicensorBellRepository
 * @author aealan
 */
class NotificationInLicensorBellRepository extends EntityRepository {

    /**
     * Funcion encargada de obtener las licencias segun un parametro
     * de busqueda de tipo estado
     * @author Aealan Z <lrobledo@kijho.com> 28/06/2019
     * @param type $validationDate
     * @return type
     */
    public function searchForBellNotificationsToValidate($validationDate) {
        $textParameters = '1 ';
        $parameters = [];
        if ($validationDate) {
            $validationDate->modify('-1 day');
            $parameters['isIterativeNoty'] = true;
            $textParameters = "(nlb.pickedtByValidatingCronDate IS NULL OR (nlb.pickedtByValidatingCronDate < '" . $validationDate->format('Y-m-d H:i:s') . "' AND nlb.iterativeNotification = :isIterativeNoty))";
        }

        $em = $this->getEntityManager();
        $dql = "SELECT nlb "
                . "FROM App:NotificationInLicensorBell nlb "
                . "LEFT JOIN App:BellNotificationSeenBy bns WITH nlb.id = bns.seenNotification "
                . "WHERE $textParameters ";

        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        return $consult->getResult();
    }

}
