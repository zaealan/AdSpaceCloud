<?php

namespace App\Security;

/**
 * Esta entidad permite administrar la informacion del servicio web.
 *
 * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
 * @author Luis Fernando <lgranados@kijho.com> 28/03/2015
 */
class WebService {

    private $meta = ['meta' => [
            'copyright' => 'Open My Tab',
            'authors' => ['Luis Fernando Granados', 'Luis Enrique Robledo Lopez'],
        ],
        'links' => [
            'self' => '',
        ],];

    /**
     * Variable para dar el tiempo de vigencia del token generado por el login
     * esta dado en segundos, 3600s = 1h.
     */
    const TIME_UNIT_TO_VALIDATE = 525600; // 1 year
    const CONTENT_TYPE_STRUCTURE = 'application/json';
    const CONTENT_TYPE_TEXT = 'text/plain';
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
    const CODE_UNAUTHORIZED = 179;
    const CODE_WRONG_ARGUMENTS = 185;
    const CODE_NOT_ALLOWED_METHOD = 190;
    const CODE_HOST_NOT_CONNECT = 2003;
    const CODE_BAD_FORMAT = 2010;
    //constantes para los codigos de sincronizacion
    //bloque usado en el ws de peticion de sync de arriba a abajo
    const PROCESSING_UNNOTIFIED_WEB_DATA = 13;
    const FORWARDING_UNNOTIFIED_WEB_PUSH = 23;
    const NOTHING_TO_WEB_SYNCHRONIZE = 43;
    const SERVER_ALREADY_WORKING_WEB_SYNC = 53;
    //bloque usado en el ws de respuesta de la sync de arriba a abajo pedido por boton
    const ANOTHER_SYNC_IN_PROCESS = 73;
    const SYNC_ALREADY_IN_PROCESS = 83;
    const SYNC_RESPONSE_PROCESSED = 93;
    const MAXIMUM_WAIT_TIME_REACHED = 103;
    // Http Success Codes
    const CODE_OK = 200;
    const CODE_OK_CREATED = 201;
    const CODE_OK_ACCEPTED = 202;
    const CODE_OK_NO_CONTENT = 204;
    // Http Error Codes
    const CODE_ERR_BAD_REQUEST = 400;
    const HTTP_CODE_BAD_REQUEST = 400;
    const CODE_ERR_FORBIDDEN = 401;
    const CODE_ERR_NOT_FOUND = 404;
    const HTTP_CODE_NOT_FOUND = 404;
    const CODE_ERR_CONFLICT = 409;
    const CODE_ERR_PRECONDITION_FAILED = 412;
    const CODE_ERR_UNPROCESSABLE = 422;
    const CODE_INTERNAL_ERROR = 500;
    const HTTP_CODE_INTERNAL_ERROR = 500;
    const HTTP_CODE_SERVICE_UNAVAILABLE = 503;
    const HTTP_CODE_SUCCESS = 200;
    const HTTP_CODE_RESPONSE_BAD_FORMAT = 505;
    //Codes Credit cards
    const CODE_CARD_EXIST = 410;
    const CODE_CARD_NOT_EXIST = 411;
    // Codes 
    const CODE_PROD_NOT_AVAILABLE = 412;

    /**
     * @return type
     */
    public function getMeta() {
        return $this->meta;
    }

    /**
     * Esta funcion permite obtener en modo texto el tipo de codigo http solicitado.
     *
     * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
     *
     * @param int $code el codigo http
     *
     * @return string descripcion del codigo http
     */
    public static function getHttpCodeDescription($code) {
        $codeDescription = '';

        switch ($code) {
            case self::CODE_OK:
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
            case self::HTTP_CODE_RESPONSE_BAD_FORMAT:
                $codeDescription = 'Respose bad format';
                break;
            default:
                break;
        }

        return $codeDescription;
    }

    /**
     * Esta funcion permite obtener en modo texto el tipo de codigo interno solicitado.
     *
     * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
     *
     * @param int $code el codigo interno
     *
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
                $codeDescription = 'Can not connect to POSTGRES server';
                break;
            case self::CODE_BAD_FORMAT:
                $codeDescription = 'Meta response bad format';
                break;
            case self::CODE_ERR_PRECONDITION_FAILED:
                $codeDescription = 'Max. number of features reached';
                break;
            default:
                break;
        }

        return $codeDescription;
    }

}
