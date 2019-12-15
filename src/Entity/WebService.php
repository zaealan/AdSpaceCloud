<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Esta entidad permite administrar la informacion del servicio web para level tanto mobil como web
 * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
 * @author Luis Fernando <lgranados@kijho.com> 28/03/2015
 * @ORM\Table("web_service")
 * @ORM\Entity(repositoryClass="App\Entity\WebServiceRepository")
 */
class WebService {

    //constante para identificar a la compañia de subastas para quien servira la app movil
    const DEFAULT_PHYSIC_AUCTION = 1;
    const CONTENT_TYPE_STRUCTURE = 'application/json';
    const CONTENT_TYPE_TEXT = 'text/plain';
    //constante para el numero de metros a la redonda para la restriccion del bidder badge
    const AUCTION_RADIUS_METERS = 400;
    //constante para el numero de resultados por defecto en los listado de la app
    const DEFAULT_NUMBER_RESULTS = 10;
    //pimienta para las codificaciones en sha1
    const SECURITY_PEPPER = 'level_licensor_web_mobil_kijho_salt';
    const BASE_64_ITERATIONS = 12;
    const REQUEST_METHOD_GET = 'GET';
    const REQUEST_METHOD_POST = 'POST';
    const REQUEST_METHOD_PUT = 'PUT';
    const REQUEST_METHOD_DELETE = 'DELETE';
    //constantes para los codigos internos utilizados
    const CODE_LOGOUT_SUCCESS = 30;
    const CODE_COULD_NOT_AUTHENTICATE = 32;
    const CODE_PAGE_NOT_EXIST = 34;
    const CODE_ACCOUNT_SUSPENDED = 64;
    const CODE_INVALID_SECURITY_HASH = 88;
    const CODE_INVALID_UID = 91;
    const CODE_INVALID_API_KEY = 89;
    const CODE_OBJECT_NOT_FOUND = 104;
    const CODE_INTERNAL_ERROR = 131;
    const CODE_UNAUTHORIZED = 179;
    const CODE_WRONG_ARGUMENTS = 185;
    const CODE_NOT_ALLOWED_METHOD = 190;
    const CODE_SUCCESS = 200;
    const CODE_OK_CREATED = 201;
    const CODE_PARTIAL_SUCCESS = 206;
    const CODE_SUCCESS_NOT_MODIFIED = 304;
    const CODE_HOST_NOT_CONNECT = 2003;
    //constantes para los codigos de sincronizacion
    //bloque usado en el ws de peticion de sync de arriba a abajo
    const PROCESSING_UNNOTIFIED_WEB_DATA = 13;
    const FORWARDING_UNNOTIFIED_WEB_PUSH = 23;
    const SERVER_WAITING_FOR_ANDROID_WEB = 33;
    const NOTHING_TO_WEB_SYNCHRONIZE = 43;
    const SERVER_ALREADY_WORKING_WEB_SYNC = 53;
    //bloque usado en el ws de respuesta de la sync de arriba a abajo pedido por boton
    const WEB_DATA_SENDED_TO_ANDROID = 63;
    const ANOTHER_SYNC_IN_PROCESS = 73;
    const SYNC_ALREADY_IN_PROCESS = 83;
    const SYNC_RESPONSE_PROCESSED = 93;
    const WED_SYNC_NOT_ANSWERED = 95;
    const MAXIMUM_WAIT_TIME_REACHED = 103;
    const SYNC_DONE_SUCCESSFULLY = 113;
    //constantes para los codigos http utilizados
    const HTTP_CODE_SUCCESS = 200;
    const HTTP_CODE_BAD_REQUEST = 400;
    const HTTP_CODE_UNAUTHORIZED = 401;
    const HTTP_CODE_PAYMENT_REQUIRED = 402;
    const HTTP_CODE_FORBIDDEN = 403;
    const HTTP_CODE_NOT_FOUND = 404;
    const CODE_ERR_NOT_FOUND = 404;
    const HTTP_CODE_TIMEOUT = 408;
    const CODE_ERR_CONFLICT = 409;
    const CODE_ERR_UNPROCESSABLE = 422;
    const HTTP_CODE_INTERNAL_ERROR = 500;
    const HTTP_CODE_SERVICE_UNAVAILABLE = 503;
    //constantes para los niveles de profundidad de la informacion entregada en los JSON
    const DETAIL_LEVEL_LOW = 1;
    const DETAIL_LEVEL_MEDIUM = 2;
    const DETAIL_LEVEL_FULL = 3;
    //constantes para las respuestas del ws de transacciones con giftcards
    //
    //'GiftCard registed and activated successfully!';
    const GIFTCARD_WS_STATUS_NEW_GIFTCARD_SUCCESS = 171;
    //'Error in transaction!';
    const GIFTCARD_WS_STATUS_TRANSACTION_ERROR = 18;
    //'Error registering GiftCardLog!';
    const GIFTCARD_WS_STATUS_GIFTCARDLOG_ERROR = 19;
    //'Transaction successfully!';
    const GIFTCARD_WS_STATUS_TRANSACTION_SUCCESS = 172;
    //'Recharge successfully!';
    const GIFTCARD_WS_STATUS_RECHARGE_SUCCESS = 173;
    //'Recharge canceled successfully!';
    const GIFTCARD_WS_STATUS_REFUND_RECHARGE_SUCCESS = 181;
    //'Refund successfully!';
    const GIFTCARD_WS_STATUS_ADJUST_SUCCESS = 182;
    //'Refund successfully!';
    const GIFTCARD_WS_STATUS_REFUND_SUCCESS = 174;
    //'Devolution successfully!';
    const GIFTCARD_WS_STATUS_DEVOLUTION_SUCCESS = 175;
    // 'Cancelation successfully!';
    const GIFTCARD_WS_STATUS_CANCELATION_SUCCESS = 176;
    //'Information request!';
    const GIFTCARD_WS_STATUS_INFORMATION_SUCCESS = 177;
    //'GiftCard Disabled!';
    const GIFTCARD_WS_STATUS_DISABLED_SUCCESS = 178;
    //'GiftCard Enabled!';
    const GIFTCARD_WS_STATUS_ENABLED_SUCCESS = 179;
    //'Unknown Transaction!';
    const GIFTCARD_WS_STATUS_UNKNOWN_TRANSACTION = 180;
    //'Giftcard not found!';
    const GIFTCARD_WS_STATUS_GIFTCARDNOTFOUND_ERROR = 20;
    //'Giftcard of another franchise!';
    const GIFTCARD_WS_STATUS_GIFTCARD_OTHER_FRANCHISE_ERROR = 21;
    //'GiftCard already registed!';
    const GIFTCARD_WS_STATUS_GIFTCARD_ALREADY_REGISTED_ERROR = 22;
    //'GiftCard transaction not found!';
    const GIFTCARD_WS_STATUS_GIFTCARD_TRANSACTION_NOT_FOUND_ERROR = 23;
    //'GiftCard log not found!';
    const GIFTCARD_WS_STATUS_GIFTCARD_LOG_NOT_FOUND_ERROR = 24;
    //'Error in recharge cancelation!';
    const GIFTCARD_WS_STATUS_GIFTCARD_RECHARGE_CANCELATION_ERROR = 25;
    //constantes para las respuestas del ws de transacciones con giftcards
    //
    //'Error registering GiftCardLog!';
    const GIFTCARD_VALIDATION_WS_NEW_GIFTCARD = 20;
    //'Error in transaction!';
    const GIFTCARD_VALIDATION_WS_OLD_GIFTCARD = 40;
    //constantes para DETERMINAR LA RUTA DONDE ESTAN LOS ARCHIVOS DE STRUCTURA Y VERSION APK
    const DIR_ANDROID_DATA = '/uploads/android/data/';
    const DIR_ANDROID_VERSIONS = '/uploads/android/versions/';

    /**
     *  @ORM\Id
     *  @ORM\Column(name="id", type="integer")
     *  @ORM\GeneratedValue
     */
    protected $id;

    /**
     * Esta funcion permite obtener en modo texto el tipo de codigo http solicitado
     * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
     * @param integer $code el codigo http
     * @return string descripcion del codigo http
     */
    public static function getHttpCodeDescription($code) {
        $codeDescription = '';

        switch ($code) {
            case self::HTTP_CODE_SUCCESS:
                $codeDescription = 'Success';
                break;
            case self::HTTP_CODE_BAD_REQUEST:
                $codeDescription = 'Bad Request';
                break;
            case self::HTTP_CODE_UNAUTHORIZED:
                $codeDescription = 'Unauthorized';
                break;
            case self::HTTP_CODE_FORBIDDEN:
                $codeDescription = 'Forbidden';
                break;
            case self::HTTP_CODE_NOT_FOUND:
                $codeDescription = 'Not Found';
                break;
            case self::HTTP_CODE_INTERNAL_ERROR:
                $codeDescription = 'Internal Server Error';
                break;
            case self::HTTP_CODE_SERVICE_UNAVAILABLE:
                $codeDescription = 'Service Unavailable';
                break;
            default:
                break;
        }
        return $codeDescription;
    }

    /**
     * Esta funcion permite obtener en modo texto el tipo de codigo interno solicitado
     * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
     * @param integer $code el codigo interno
     * @return string descripcion del codigo interno
     */
    public static function getCodeDescription($code) {
        $codeDescription = '';

        switch ($code) {
            case self::CODE_COULD_NOT_AUTHENTICATE:
                $codeDescription = 'Could not authenticate you';
                break;
            case self::CODE_PAGE_NOT_EXIST:
                $codeDescription = 'Sorry, that page does not exist';
                break;
            case self::CODE_ACCOUNT_SUSPENDED:
                $codeDescription = 'Your account is suspended and is not permitted to access this feature';
                break;
            case self::CODE_INVALID_SECURITY_HASH:
                $codeDescription = 'Invalid security code';
                break;
            case self::CODE_INVALID_API_KEY:
                $codeDescription = 'Invalid or expired api key';
                break;
            case self::CODE_OBJECT_NOT_FOUND:
                $codeDescription = 'Object not found';
                break;
            case self::CODE_INTERNAL_ERROR:
                $codeDescription = 'Internal error';
                break;
            case self::CODE_UNAUTHORIZED:
                $codeDescription = 'Sorry, you are not authorized to see this feature';
                break;
            case self::CODE_WRONG_ARGUMENTS:
                $codeDescription = 'Wrong Arguments';
                break;
            case self::CODE_NOT_ALLOWED_METHOD:
                $codeDescription = 'Not Allowed Method';
                break;
            case self::CODE_HOST_NOT_CONNECT:
                $codeDescription = 'Can not connect to MySQL server';
                break;
            default:
                break;
        }
        return $codeDescription;
    }

}
