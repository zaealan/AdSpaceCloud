<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;
use App\Entity\PushSent;

/**
 * Repositorio en donde se realizan todas las consultas relacionadas con los
 * push enviandos por licensor
 * @author Aealan Z <lrobledo@kijho.com>
 */
class PushSentRepository extends EntityRepository {

    /**
     * Funcion para encontrar los push de sincronizacion de
     * arriba a abajo no respondidos
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $license licensia a la cual se le buscara los push
     * @return type lista de push de arriba a abajo no respondidos
     */
    public function findPushNotRespondedWebSync($license) {

        // seteo de los parametros para la consulta
        $parameters = [];
        $parameters['type'] = PushSent::PUSH_TYPE_SYNC_UPDOWN;
        $parameters['status'] = PushSent::STATUS_PUSH_PENDING;
        $parameters['license'] = $license;
        $parameters['resend'] = false;

        $em = $this->getEntityManager();
        $dql = 'SELECT ps
            FROM App:PushSent ps
            WHERE ps.pushType = :type AND ps.pushStatus = :status AND ps.psLicense = :license AND (ps.pushToResend = :resend OR ps.pushToResend IS NULL)
            ORDER BY ps.sentDate ASC';
        $consult = $em->createQuery($dql);

        $consult->setParameters($parameters);
        return $consult->getResult();
    }

    /**
     * Funcion para encontrar los push no respondidos ingnorando
     * los de sincronizacion para ser reenviados por comando
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $commandRunDateT fecha en la que se corre el comando
     * @param type $pushType especificacion del tipo de push, es opcional
     * @return type arreglo con los push no respondidos segun parametros
     */
    public function findPushNotRespondedForCommand($commandRunDateT, $pushType = 0) {

        $parameters = [];
        $extraParameter = '';

        if ($pushType) {
            $parameters['type'] = $pushType;
            $extraParameter = ' AND ps.pushType = :type';
        }

        // seteo de los parametros para la consulta
        $parameters['status'] = PushSent::STATUS_PUSH_PENDING;
        $parameters['typedown'] = PushSent::PUSH_TYPE_SYNC_DOWNUP;
        $parameters['typeup'] = PushSent::PUSH_TYPE_SYNC_UPDOWN;
        $parameters['resend'] = false;
        $parameters['dateTCommand'] = $commandRunDateT;

        $em = $this->getEntityManager();
        $dql = 'SELECT ps
            FROM App:PushSent ps
            WHERE ps.pushStatus = :status AND (ps.resendDate < :dateTCommand OR ps.resendDate IS NULL) AND (ps.pushToResend = :resend OR ps.pushToResend IS NULL) AND ps.pushType <> :typedown AND ps.pushType <> :typeup ' . $extraParameter . '
            ORDER BY ps.sentDate ASC';
        $consult = $em->createQuery($dql);

        $consult->setParameters($parameters);
        return $consult->getResult();
    }

    /**
     * Funcion para encontrar los push no respondidos ingnorando
     * los de sincronizacion para ser reenviados por comando
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $commandRunDateT fecha en la que se corre el comando
     * @param type $pushType especificacion del tipo de push, es opcional
     * @return type arreglo con los push no respondidos segun parametros
     */
    public function findCurlNotRespondedForCommand($commandRunDateT) {
        $parameters = [];

        // seteo de los parametros para la consulta
        $parameters['status'] = PushSent::STATUS_CURL_FINISHED;
        $parameters['curltype'] = PushSent::PUSH_TYPE_CURL_TO_OMT;
        $parameters['dateTCommand'] = $commandRunDateT;

        $em = $this->getEntityManager();
        $dql = 'SELECT ps
            FROM App:PushSent ps
            WHERE ps.omtCurlStatus < :status AND (ps.omtCurlResendDate < :dateTCommand OR ps.omtCurlResendDate IS NULL) AND ps.omtCurlType = :curltype 
            ORDER BY ps.sentDate ASC';
        $consult = $em->createQuery($dql);

        $consult->setParameters($parameters);
        return $consult->getResult();
    }
    
    /**
     * Funcion para obtener el ultimo push de sincronizacion de abajo a arriba
     * enviado con el fin de validar la respuesta en este
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $license
     * @return type
     */
    public function findLastAskingSyncPushToLicense($license) {

        // seteo de los parametros para la consulta
        $parameters = [];
        $parameters['type'] = PushSent::PUSH_TYPE_SYNC_DOWNUP;
        $parameters['license'] = $license;

        $em = $this->getEntityManager();
        $dql = 'SELECT ps
            FROM App:PushSent ps
            WHERE ps.pushType = :type AND ps.psLicense = :license
            ORDER BY ps.id DESC';
        $consult = $em->createQuery($dql);
        $consult->setMaxResults(1);

        $consult->setParameters($parameters);
        return $consult->getResult();
    }
    
    /**
     * Funcion para obtener el ultimo push de sincronizacion de abajo a arriba
     * enviado con el fin de validar la respuesta en este
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $license
     * @return type
     */
    public function findLastLicensePushByCode($license, $code) {

        // seteo de los parametros para la consulta
        $parameters = [];
        $parameters['code'] = $code;
        $parameters['license'] = $license;

        $em = $this->getEntityManager();
        $dql = 'SELECT ps
            FROM App:PushSent ps
            WHERE ps.verificationCode = :code AND ps.psLicense = :license
            ORDER BY ps.sentDate DESC';
        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        $consult->setMaxResults(1);
        
        return $consult->getResult();
    }

    /**
     * Funcion para obtener el ultimo push de sincronizacion de abajo a arriba
     * enviado con el fin de validar la respuesta en este
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $license
     * @return type
     */
    public function findPostPushNoSyncFromInitialLoginByLicense($license, $firstPosTPush) {

        // seteo de los parametros para la consulta
        $parameters = [];
        $parameters['license'] = $license;
        $parameters['postTo'] = $firstPosTPush->getId();
        $parameters['notType1'] = PushSent::PUSH_TYPE_SYNC_DOWNUP;
        $parameters['notType2'] = PushSent::PUSH_TYPE_SYNC_UPDOWN;

        $em = $this->getEntityManager();
        $dql = 'SELECT ps
            FROM App:PushSent ps
            WHERE ps.pushType <> :notType1 AND ps.pushType <> :notType2 AND ps.psLicense = :license AND ps.id > :postTo
            ORDER BY ps.id DESC';
        $consult = $em->createQuery($dql);
        $consult->setParameters($parameters);
        return $consult->getResult();
    }

    /**
     * clearAndroidForInitialLogin set NULL android_Sync when it is not noll
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $path
     * @param type $dayLogin
     * @return type
     */
    public function respondOldPushesForLicense($pushType, $license) {
        $em = $this->getEntityManager();

        $parameters = [];
        $parameters['type'] = $pushType;
        $parameters['license'] = $license;
        $parameters['status'] = PushSent::STATUS_PUSH_PENDING;
        $parameters['newStatus'] = PushSent::STATUS_PUSH_READED;
        $parameters['now'] = new \DateTime('now');
        $parameters['notTestingPush'] = false;
        
        $whereDql = "WHERE ps.pushType = :type AND ps.pushStatus = :status AND ps.psLicense = :license AND ps.isTestingPush = :notTestingPush";

        $dql = "UPDATE App:PushSent ps "
                . "SET ps.pushStatus = :newStatus, ps.respondDate = :now "
                . $whereDql;

        $query = $em->createQuery($dql);

        $query->setParameters($parameters);

        $dqlResult = $query->getResult();
        return $dqlResult;
    }
    
    /**
     * clearAndroidForInitialLogin set NULL android_Sync when it is not noll
     * @author Aealan Z <lrobledo@kijho.com> 29/07/2016
     * @param type $path
     * @param type $dayLogin
     * @return type
     */
    public function closeUnansweredS3Pushes($license) {
        $em = $this->getEntityManager();

        $parameters = [];
        $parameters['typeS3Full'] = PushSent::PUSH_TYPE_UPLOAD_S3_BACKUP;
        $parameters['typeS3Logs'] = PushSent::PUSH_TYPE_UPLOAD_S3_LOGS;
        $parameters['typeS3Errors'] = PushSent::PUSH_TYPE_UPLOAD_S3_ERROR_LOGS;
        $parameters['license'] = $license;
        $parameters['status'] = PushSent::STATUS_PUSH_PENDING;
        $parameters['newStatus'] = PushSent::STATUS_PUSH_READED;
        $parameters['now'] = new \DateTime('now');
        $parameters['notTestingPush'] = false;
        
        $whereDql = "WHERE (ps.pushType = :typeS3Full OR ps.pushType = :typeS3Logs OR ps.pushType = :typeS3Errors) AND ps.pushStatus = :status AND ps.psLicense = :license AND ps.isTestingPush = :notTestingPush";

        $dql = "UPDATE App:PushSent ps "
                . "SET ps.pushStatus = :newStatus, ps.respondDate = :now "
                . $whereDql;

        $query = $em->createQuery($dql);

        $query->setParameters($parameters);

        $dqlResult = $query->getResult();
        return $dqlResult;
    }

    /**
     * 
     * @author Felipe Arango <aarango@uva3.com> 25/06/2019
     */
    public function searchPushSentForCleaning($license, $date  ) {
        $em = $this->getEntityManager();

        $parameters['license'] = $license;
        $parameters['datemax'] = $date;
        $parameters['notType1'] = PushSent::PUSH_TYPE_SYNC_DOWNUP;
        $parameters['notType2'] = PushSent::PUSH_TYPE_SYNC_UPDOWN;
        
        $dql = 'SELECT ps
            FROM App:PushSent ps
            WHERE ps.pushType <> :notType1 AND ps.pushType <> :notType2 AND ps.psLicense = :license AND ps.sentDate < :datemax 
            
            ORDER BY ps.id ASC';

        $query = $em->createQuery($dql);

        $query->setParameters($parameters);

        $dqlResult = $query->getResult();
        return $dqlResult;
    }

}
