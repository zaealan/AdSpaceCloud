<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Security\WebService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Util\Util;
use App\Util\Encryptor;
use App\Controller\ParametersNormalizerController;

/**
 * Clase principal con el esquema para control general api
 * @author Luis Enrique Robledo Lopez <lrobledo@kijho.com> 07/09/2017
 * @author Luis Fernando <lgranados@kijho.com> 07/09/2017
 * 
 */
class ApiController extends ParametersNormalizerController {

    private $meta;
//    private $enabledHost;
    protected $container;
    protected $mailer;
    protected $indexesToTranslate = ['typeDescription', 'statusDescription', 'categoryDescription', 'attentionTypeDescription'];
    public $needles = ['bin', 'png', 'blob', 'jpg', 'jpeg', 'BIN', 'PNG', 'BLOB', 'JPG', 'JPEG'];

    /**
     * constructor de la clase ApiController
     * @param ContainerInterface $container
     */
    function __construct(ContainerInterface $container, \Swift_Mailer $mailer = null) {
        $wsEntity = new WebService();
        $this->meta = $wsEntity->getMeta();
        $this->container = $container;
        $this->mailer = $mailer;
//        $this->enabledHost = $this->container->getParameter('enabled_host');
    }

    /**
     * Esta variable permite controlar el codigo http de respuesta 
     * @var integer codigo http
     */
    protected $statusCode = WebService::CODE_OK;

    public function meta(Request $request, $metaArray = null, $paginatorArray = null) {
        $uri = $request->getPathInfo();

        if ($request->getMethod() == 'GET' && !empty($request->query->all())) {
            $extraParamas = $request->query->all();

            foreach ($extraParamas as $keyValue => $value) {
                if (strpos($uri, "?")) {
                    $uri .= "&$keyValue=$value";
                } else {
                    $uri .= "?$keyValue=$value";
                }
            }
        }

        $this->meta['links']['self'] = $uri;

        if (empty($metaArray)) {
            return $this->meta;
        }

        $customMetaArray = $this->meta;
        $customMetaArray['meta']['authors'] = $metaArray;

        if (!empty($paginatorArray)) {
            $customMetaArray = $this->paginationForMeta($customMetaArray, $paginatorArray);
        }

        if (!isset($customMetaArray['meta']) || !isset($customMetaArray['meta']['copyright']) || !isset($customMetaArray['meta']['authors'])) {
            return $this->setStatusCode(WebService::HTTP_CODE_RESPONSE_BAD_FORMAT)
                            ->respondWithError('Meta data bad formated', WebService::CODE_BAD_FORMAT, $this->meta);
        }

        return $customMetaArray;
    }

    /**
     * @param type $customMetaArray
     * @param type $paginatorArray
     * @return type
     */
    protected function paginationForMeta($customMetaArray, $paginatorArray) {
        if ($paginatorArray['items_per_page'] == 'all') {
            $paginatorArray['items_per_page'] = $paginatorArray['total_items'];
        }

        $customMetaArray['pagination']['currentPage'] = (int) $paginatorArray['page'];
        $customMetaArray['pagination']['itemsPerPage'] = (int) $paginatorArray['items_per_page'];
        $customMetaArray['pagination']['totalItems'] = $paginatorArray['total_items'];

        $auxiliarItemsInCurrentPage = ($paginatorArray['total_items'] - ($paginatorArray['page'] * $paginatorArray['items_per_page']));
        if ($auxiliarItemsInCurrentPage > 0) {
            $customMetaArray['pagination']['itemsInCurrentPage'] = (int) $paginatorArray['items_per_page'];
        } else {
            $customMetaArray['pagination']['itemsInCurrentPage'] = (int) ($paginatorArray['items_per_page'] + ($paginatorArray['total_items'] - ($paginatorArray['page'] * $paginatorArray['items_per_page'])));
        }

        $auxiliarForLastPageCount = ((int) ($paginatorArray['total_items'] / $paginatorArray['items_per_page']));
        if (($paginatorArray['total_items'] - ($auxiliarForLastPageCount * $paginatorArray['items_per_page'])) == 0) {
            $auxiliarPageIncrement = 0;
        } else {
            $auxiliarPageIncrement = 1;
        }

        $customMetaArray['pagination']['firstPage'] = 1;
        $customMetaArray['pagination']['lastPage'] = ($auxiliarForLastPageCount + $auxiliarPageIncrement);
        $customMetaArray['pagination']['prevPage'] = $paginatorArray['page'] - 1;

        if ($paginatorArray['page'] + 1 > $customMetaArray['pagination']['lastPage']) {
            $customMetaArray['pagination']['nextPage'] = 0;
        } else {
            $customMetaArray['pagination']['nextPage'] = $paginatorArray['page'] + 1;
        }

        return $customMetaArray;
    }

    /**
     * @param type $needles
     * @param type $haystack
     * @return boolean
     */
    public function match($needles, $haystack) {
        foreach ($needles as $needle) {
            if (strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Esta funcion permite comvertir en formato JSON un arreglo recibido
     * como parametro, para luego construir la respuesta que se le 
     * enviara al cliente
     * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
     * @param array $array
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function respondWithArray(array $array, $headers = [], $message = 'Done') {


        $resp = new Response(json_encode($array));

        $resp->headers->set('Content-Type', 'application/json');

        $resp->headers->set('Access-Control-Allow-Origin', '*');

        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                $resp->headers->set($key, $value);
            }
        }

        return $resp;
    }

    /**
     * Permite enviar al usuario quien realiza la peticion un mensaje de error
     * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
     * @param string $message mensaje de error
     * @param integer $errorCode codigo correspondiente al error indicado
     * @return Response JSON con el mensaje de error
     */
    public function respondWithError($message, $errorCode, $metaArray = null) {
        if ($this->statusCode === WebService::CODE_OK) {
            trigger_error(
                    "You better have a really good reason for erroring on a 200...", E_USER_WARNING
            );
        }
        $metaArray['errors'] = [
            'status' => $this->statusCode,
            'message' => $message,
        ];

        return $this->respondWithArray($metaArray);
    }

    /**
     * Esta funcion permite enviar una respuesta con un mensaje simple
     * a peticiones
     * @param string $message mensaje que se quiere mandar
     * @param integer $code codigo del mensaje
     * @return type
     */
    protected function respondWithMessage($message, $code = WebService::CODE_OK, $data = null) {

        if ($data != null) {
            return $this->respondWithArray([
                        'data' => [
                            'data' => $data,
                            'code' => $code,
                            'httpCode' => $this->statusCode,
                            'message' => $message,
                        ]
            ]);
        }

        return $this->respondWithArray([
                    'data' => [
                        'code' => $code,
                        'httpCode' => $this->statusCode,
                        'message' => $message,
                    ]
        ]);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
     * @return Response
     */
    public function errorForbidden($message = 'Forbidden') {
        return $this->setStatusCode(WebService::HTTP_CODE_FORBIDDEN)->respondWithError($message, WebService::CODE_UNAUTHORIZED);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
     * @return Response
     */
    public function errorInternalError($message = 'Internal Error') {
        return $this->setStatusCode(WebService::HTTP_CODE_INTERNAL_ERROR)->respondWithError($message, WebService::CODE_INTERNAL_ERROR);
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
     * @return Response
     */
    public function errorNotFound($message = 'Resource Not Found', $metaArray = null) {
        return $this->setStatusCode(WebService::HTTP_CODE_NOT_FOUND)->respondWithError($message, WebService::CODE_OBJECT_NOT_FOUND, $metaArray);
    }

    /**
     * Generates a Response with a 401 HTTP header and a given message.
     * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
     * @return Response
     */
    public function errorUnauthorized($message = 'Unauthorized') {
        return $this->setStatusCode(WebService::HTTP_CODE_UNAUTHORIZED)->respondWithError($message, WebService::CODE_UNAUTHORIZED);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
     * @return Response
     */
    public function errorWrongArgs($message = 'Wrong Arguments') {
        return $this->setStatusCode(WebService::HTTP_CODE_BAD_REQUEST)->respondWithError($message, WebService::CODE_WRONG_ARGUMENTS);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
     * @return Response
     */
    public function errorNotAllowedMethod($message = 'Not Allowed Method') {
        return $this->setStatusCode(WebService::HTTP_CODE_BAD_REQUEST)->respondWithError($message, WebService::CODE_NOT_ALLOWED_METHOD);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     * @author Cesar Giraldo <cnaranjo@kijho.com> 09/10/2014
     * @return Response
     */
    public function errorNotConnetHost($message = 'Can not connect to server') {
        return $this->setStatusCode(WebService::CODE_HOST_NOT_CONNECT)->respondWithError($message, WebService::CODE_HOST_NOT_CONNECT);
    }

    /**
     * @return type
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * @param type $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param Request $request
     * @param type $metaArray
     * @return type
     */
    public function getMeta(Request $request = null, $metaArray = null) {
        if ($request) {
            $this->meta['links']['self'] = $request->getPathInfo();
        }

        if (empty($metaArray)) {
            return $this->meta;
        }

        $this->meta['meta']['authors'] = $metaArray;
        return $this->meta;
    }

    /**
     * @param type $request
     * @return type
     */
    public function getContentInRequest($request) {
        $auxBreakCounter = 0;
        $params = $request->getContent();
        do {
            ++$auxBreakCounter;
            $params = json_decode($params, true);
        } while (is_string($params) && $auxBreakCounter < 7);
        return $params;
    }

    /**
     * Permite obtener el start y el limit de las solicitudes de los usuarios
     * a los listados
     * @param Request $request datos de la solicitud
     * @return array[integer] un arreglo con el start y el limit
     */
    public function getStartAndLimit($request) {

        $start = (int) $request->get('start');

        $limit = (int) $request->get('limit');


        if ($limit == 0) {
            $limit = WebService::DEFAULT_NUMBER_RESULTS;
        }

        return array('start' => $start, 'limit' => $limit);
    }

    /**
     * Permite validar una serie de parámetros en el objeto request, para identificar si no llego alguno de ellos
     * @param $request
     * @param $requiredFields
     * @param $returnParams
     * @return boolean
     */
    public function validateParams(Request $request, $requiredFields, $returnParams = false) {
        $translator = $this->container->get('translator');
        $params = $this->getContentInRequest($request);
        if ($params) {
            foreach ($requiredFields as $value) {
                if (!isset($params[$value]) || $params[$value] === '') {
                    return $translator->trans($value) . " " . $translator->trans('errors_is_required');
                }
            }

            if ($returnParams) {
                return $params;
            } else {
                return true;
            }
        }
        return $translator->trans('errors_empty_request');
    }

    /**
     * Permite validar una serie de parámetros enviados por GET en la Url de la peticion
     * @param req
     * @param requiredFields
     * @return {boolean}
     */
    public function validateQueryParams($request, $requiredFields) {
        $params = $request->attributes->all();
        if ($params) {
            $totalFields = count($requiredFields);
            for ($i = 0; $i < $totalFields; $i++) {
                if (!isset($params[$requiredFields[$i]]) || $params[$requiredFields[$i]] === '') {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Permite validar si el host de la peticion se encuentra entre los
     * Hosts permitidos por el sistema
     * @return boolean
     */
//    public function isValidHost($host) {
//        if (array_search($host, $this->getEnabledHost()) !== false) {
//            return true;
//        }
//        return false;
//    }
//
//    function getEnabledHost() {
//        return $this->enabledHost;
//    }

    /**
     * Funcion que permite traducir los indices que esten definidos en
     * $indexesToTranslate para mostrar el texto correspondiente al idioma segun
     * el valor arrojado por la consulta
     * 
     * NOTA: Si requiere agregar un nuevo indice al arreglo para traducir utilize
     * la siguiente sintaxis.
     * $indexesToTranslate = ['typeDescription', 'statusDescription'];
     * Este arreglo lo encuentra en apiController
     * 
     * @param type $sUserList
     * @return type
     */
    public function filterAndTranslateArrayQueryResult($sUserList) {
        $translator = $this->container->get('translator');

        if (!isset($sUserList[0])) {
            foreach ($this->indexesToTranslate as $transIndex) {
                if (isset($sUserList[$transIndex])) {
                    $sUserList[$transIndex] = $translator->trans($sUserList[$transIndex]);
                }
            }
        } else {
            foreach ($sUserList as $k => $v) {
                foreach ($this->indexesToTranslate as $transIndex) {
                    if (isset($sUserList[$k][$transIndex])) {
                        $sUserList[$k][$transIndex] = $translator->trans($sUserList[$k][$transIndex]);
                    }
                }
            }
        }

        return $sUserList;
    }

    /**
     * Funcion para que retorna la respuesta del webservice, configurable
     * @param type $request
     * @param type $data array o string con los datos a enviar en la respuesta
     * @param type $code el codigo que tendra la respuesta
     * @return JsonResponse
     */
    public function metaResponse($request, $data, $code, $metaArray = [], $paginatorArray = []) {

        $metaResult = $this->meta($request, $metaArray, $paginatorArray);

        if (is_array($metaResult)) {
            $dataToResponse = Util::replacemtOfEspecialsCharactersPreJsonDecode($data);
            $metaResult['data'] = $dataToResponse;
            return new JsonResponse($metaResult, $code);
        } else {
            return $metaResult;
        }
    }

    /**
     * Funcion que permite verificar si un string o array de string tiene el formato UUID valido
     * para la peticion generada.
     * 
     * Este debe tener el siguiente formato: a6bccdbe-7dac-42a3-b8d1-020654cd857
     * o formato array
     * 
     *  [
     *    0 => "2ea923bb-63de-44b0-a2a6-1fee01fea58d"
     *    1 => "9c9e4a68-9eda-48d1-8c33-e574ff556f05"
     *    ..........................................
     *    ..........................................
     *    9 => "9c9e4a68-9eda-48d1-8c33-e574ff556f06"      
     *  ]
     * 
     * @param type $uuid array|string con el texto en formato uuid
     * @param type $isArray boolean que define si se debe analizar como array o solo texto
     * por defecto su valor es false
     * @return boolean
     */
    public function validateUUID($uuid, $isArray = false) {
        if ($isArray) {
            foreach ($uuid as $code) {
                if (!is_string($code) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $code) !== 1)) {
                    return false;
                }
            }
        } else {
            if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Funcion que permite verificar si un string tiene el formato UUID valido
     * para la peticion generada.
     * 
     * Este debe tener el siguiente formato: a6bccdbe-7dac-42a3-b8d1-020654cd857
     * @param type $uuid cadena de texto en formato uuid
     * @return boolean
     */
    public function validateSoftUUID($uuid, $isArray = false) {
        return Util::validateSoftUUID($uuid, $isArray);
    }

    /**
     * Validates fields in entity, using assert annotation
     *
     * @param data $data
     *
     * @return string|null
     */
    public function validate($data) {

        $validator = $this->get('validator');
        $errors = $validator->validate($data);
        if (count($errors) > 0) {
            $errorMessage = $errors[0]->getMessage();
            return $errorMessage;
        }
        return;
    }

    /**
     * Funcion que permite crear un token, mediante un parametro de tipo array
     * @param type $array
     * @return string
     */
    public function token($array) {
        $encryptData = Encryptor::safeEncrypt(json_encode($array));
        /**
         * JWT: Debido a que esto se usa para validar tokens entre distintos servidores
         * y estos pueden estar desincronizados por algunos minutos, el parametro 
         * iat, el cual jwt utiliza para determinar en que momento se creo el token
         * y validar si es valido, 
         * lo vamos a generar 10 minutos antes de la hora actual para evitar un error 
         * por desincronizacion.
         * 
         */
        $token = $this->get('lexik_jwt_authentication.encoder')
                ->encode([
            'info' => $encryptData,
            'exp' => time() + 3600, // 1 hour expiration
            "iat" => strtotime('-10 minutes') //10 minutos antes de la hora actual
        ]);

        return $token;
    }

    /**
     * Funcion que permite decodificar un token
     * @param type $token string con la informacion a decodificar
     * @return array
     */
    public function decodeToken($token) {
        $decodeToken = $this->get('lexik_jwt_authentication.encoder')
                ->decode($token);

        if ($decodeToken === false) {
            throw new \Exception('Invalid Token');
        }

        $decrypt = $decodeToken['info'];
        $safeDecrypt = Encryptor::safeDecrypt(json_encode($decrypt));
        $data = json_decode($safeDecrypt, true);

        return $data;
    }

    /**
     * Funcion para enviar el correos electronicos
     *
     * @param type $arrInfo arreglo con los datos para crear el email y su contenido 
     * estos datos serian 
     * 
     * [
     * subject=>'your_subject',
     * to=>'email to send',
     * message=>'html content',
     * emailCc=>'email cc' Este datos es opcional
     * ]
     * 
     * @param type $attachedFile para agregar un adjunto en el correo
     * @author Luis Fernando Granados 11/10/2017
     */
    public function sendEmail($arrInfo, $attachedFile = '') {
        $arrRended = [
            'message' => $arrInfo['message'],
        ];

        $body = $this->renderView('Emails\emailTemplate.html.twig', $arrRended);


        $infoMail = $this->getParameter('mailer_user');
        $infoPass = $this->getParameter('mailer_password');

        if ($this->getParameter("mailer_host") == "mailhog") {
            $encryption = null;
        } else {
            $encryption = $this->getParameter("mailer_encryption");
        }

        if ($attachedFile != '') {
            $documento = \Swift_Attachment::fromPath($attachedFile);
        }

        $transport = (new \Swift_SmtpTransport($this->getParameter("mailer_host"), $this->getParameter("mailer_port"), $encryption))
                ->setUsername($infoMail)
                ->setPassword($infoPass);

        $mailer = new \Swift_Mailer($transport);
        $message = (new \Swift_Message())
                ->setSubject($arrInfo['subject'])
                ->setFrom([$infoMail => "Open My Tab"])
                ->setTo($arrInfo['to'])
                ->setBody($body/* html body */, 'text/html');

        if (isset($arrInfo['emailCc'])) {
            $message->setCC($arrInfo['emailCc']);
        }

        if ($attachedFile != '') {
            $message->attach($documento);
        }

        return $mailer->send($message);
    }

    /**
     * @param type $arrInfo
     * @param type $attachedFile
     * @return boolean
     */
    public function sendEmailOtm($arrInfo, $attachedFile = '') {

        $arrRended = [
            'order' => $arrInfo['order']
        ];
        if (isset($arrInfo['message']['externalMenu'])) {
            $arrRended['externalMenu'] = (string) $arrInfo['message']['externalMenu'];
        }

        $body = $this->render('Emails\emailOmtOrder.html.twig', $arrRended)->getContent();

        $tituloEmail = "Open My Tab";
        $emailRest = $this->getParameter('mailer_user');
        if (isset($arrInfo['message']['restaurant']['email']) && $arrInfo['message']['restaurant']['email'] != '') {
            $emailRest = $arrInfo['message']['restaurant']['email'];
        }

        if (isset($arrRended['externalMenu']) && $arrRended['externalMenu'] == 1) {
            $tituloEmail = $arrRended['message']['restaurant']['name'];
        }

        $response = ["msn" => "sended", "response" => "__OK__"];
        $infoMail = $this->getParameter('mailer_user');
        $infoPass = $this->getParameter('mailer_password');

        if ($this->getParameter("mailer_host") == "mailhog") {
            $encryption = null;
        } else {
            $encryption = $this->getParameter("mailer_encryption");
        }

        try {
            if ($attachedFile != '') {
                $documento = \Swift_Attachment::fromPath($attachedFile);
            }

            $transport = (new \Swift_SmtpTransport($this->getParameter("mailer_host"), $this->getParameter("mailer_port"), $encryption))
                    ->setUsername($infoMail)
                    ->setPassword($infoPass);

            $mailer = new \Swift_Mailer($transport);
            $message = (new \Swift_Message())
                    ->setSubject($arrInfo['subject'])
                    ->setFrom($emailRest, $tituloEmail)
                    ->setTo($arrInfo['to'])
                    ->setBody($body/* html body */, 'text/html');
            if (isset($arrInfo['emailCc'])) {
                $message->setCC($arrInfo['emailCc']);
            }

            if ($attachedFile != '') {
                $message->attach($documento);
            }

            $resultMail = $mailer->send($message);
            $emailSuccess = true;
        } catch (\Exception $ex) {
            $emailSuccess = false;
            $response = ["msn" => "Error: " . $ex->getMessage(), "response" => "__KO__"];
        }
        $response['success'] = $emailSuccess;

        return $response;
    }

    /**
     * @param type $arrInfo
     * @param type $attachedFile
     * @return type
     */
    public function sendConfirmEmail($arrInfo, $attachedFile = '') {
        $arrRended = [
            'email' => $arrInfo['email'],
            'externalMenu' => $arrInfo['externalMenu'],
            'bgColor' => $arrInfo['externalMenuColor'],
            'url' => $arrInfo['url'],
            'name' => $arrInfo['name']
        ];

        $translator = $this->container->get('translator');
        $body = $this->render('Emails\emailOmtEmailConfirm.html.twig', $arrRended)->getContent();

        $subjet = $translator->trans('confirmation_email') . ', ' . $arrRended['name'];

        $infoMail = $this->getParameter('mailer_user');
        $infoPass = $this->getParameter('mailer_password');

        if ($this->getParameter("mailer_host") == "mailhog") {
            $encryption = null;
        } else {
            $encryption = $this->getParameter("mailer_encryption");
        }

        if ($attachedFile != '') {
            $documento = \Swift_Attachment::fromPath($attachedFile);
        }

        $transport = (new \Swift_SmtpTransport($this->getParameter("mailer_host"), $this->getParameter("mailer_port"), $encryption))
                ->setUsername($infoMail)
                ->setPassword($infoPass);

        $mailer = new \Swift_Mailer($transport);
        $message = (new \Swift_Message())
                ->setSubject($subjet)
                ->setFrom([$this->getParameter('mailer_user') => "Open My Tab"])
                ->setTo($arrRended['email'])
                ->setBody($body/* html body */, 'text/html');

        if (isset($arrInfo['emailCc'])) {
            $message->setCC($arrInfo['emailCc']);
        }

        if ($attachedFile != '') {
            $message->attach($documento);
        }

        return $mailer->send($message);
    }

    /**
     * Funcion para enviar el correos electronicos
     *
     * @param type $arrInfo arreglo con los datos para crear el email y su contenido 
     * estos datos serian 
     * 
     * [
     * subject=>'your_subject',
     * to=>'email to send',
     * message=>'html content',
     * emailCc=>'email cc' Este datos es opcional
     * ]
     * 
     * @param type $attachedFile para agregar un adjunto en el correo
     * @author Angel Andres Diaz Calle 24/07/2018
     */
    public function sendEmailPasswordRecovery($arrInfo, $attachedFile = '') {
        $arrRended = [
            'message' => $arrInfo['message'],
            'email' => $arrInfo['to'],
            'externalMenu' => $arrInfo['externalMenu'],
            'bgColor' => $arrInfo['externalMenuColor'],
            'url' => $arrInfo['url']
        ];

        $body = $this->render('Emails\emailPasswordRecovery.html.twig', $arrRended)->getContent();

        $infoMail = $this->getParameter('mailer_user');
        $infoPass = $this->getParameter('mailer_password');

        if ($this->getParameter("mailer_host") == "mailhog") {
            $encryption = null;
        } else {
            $encryption = $this->getParameter("mailer_encryption");
        }

        if ($attachedFile != '') {
            $documento = \Swift_Attachment::fromPath($attachedFile);
        }

        $transport = (new \Swift_SmtpTransport($this->getParameter("mailer_host"), $this->getParameter("mailer_port"), $encryption))
                ->setUsername($infoMail)
                ->setPassword($infoPass);

        $mailer = new \Swift_Mailer($transport);
        $message = (new \Swift_Message())
                ->setSubject($arrInfo['subject'])
                ->setFrom([$infoMail => "Open My Tab"])
                ->setTo($arrInfo['to'])
                ->setBody($body/* html body */, 'text/html');

        if (isset($arrInfo['emailCc'])) {
            $message->setCC($arrInfo['emailCc']);
        }

        if ($attachedFile != '') {
            $message->attach($documento);
        }

        return $mailer->send($message);
    }

    /**
     * Funcion generica para serializar (convertir a array) y traducir elementos 
     * si es requerido
     * @param type $object el objeto a serializar
     * @param type $translate variable boolen default false,
     * si se envia true se analizaran los elementos del arreglo luego de serializarse para ser traducidos si es el caso.
     * @author Luis Fernando Granados 09/10/2017
     * @return array|[]
     */
    public function serializerAndTranslate($object, $translate = false) {

        $serializer = $this->container->get('jms_serializer');
        if (!is_array($object)) {
            $arrayData = $object->showEverything();
            $arrayData = $serializer->toArray($arrayData);
        } else {
            $arrayData = $serializer->toArray($object);
        }

        /**
         * Pasamos el resultado a la funcion filterAndTranslateArrayQueyResult 
         * para traducir los atributos que se requieran 
         */
        if ($translate) {
            $arrayData = $this->filterAndTranslateArrayQueryResult($arrayData);
        }

        return $arrayData;
    }

    /**
     * @param type $order
     * @param type $arrayListAddTrans
     * @return array['response' => result, 'paginator' => paginatorData]
     */
    public function validateAndBuildPaginatorData($order, $arrayListAddTrans) {
        $totalItems = $arrayListAddTrans['quantity'];
        unset($arrayListAddTrans['quantity']);

        $arrayPaginationData = ['page' => $order['page'], 'items_per_page' => $order['items_per_page'], 'total_items' => $totalItems];

        return ['response' => $arrayListAddTrans, 'paginator' => $arrayPaginationData, 'totalItems' => $totalItems];
    }

    /**
     * Funcion que permite agregar el atributo path a las consultas manuales.
     * Pasando el array con los datos de la imagen
     * @param type $data array con los datos de la imagen
     * @param type $pathOnline path aws
     * @param type $pathLocal path local
     * @return array con el key path en su interior
     */
    public function addPathImage($data, $pathOnline, $pathLocal) {
        return Util::addPathImageInUtil($data, $pathOnline, $pathLocal);
    }

    public function errorResponse($request, $result) {

        $data = json_decode($result->getContent(), true);

        if (isset($data['errors'])) {
            return $this->setStatusCode($data['errors']['status'])
                            ->respondWithError($data['errors']['message']
                                    , $data['errors']['status']
                                    , $this->getMeta($request));
        }
    }

    /**
     * @param type $request
     * @param type $translated
     * @param type $responseCurl
     * @param type $restaurant
     * @param type $url
     * @param type $httpStatus
     * @param type $log
     * @return type
     */
    public function validateCurlInController($request, $translated, $responseCurl, $restaurant, $url, $httpStatus, $log, $user = null) {

        $log->addNotice('ApiController', ['validateCurlInController ', 'Entering!']);

        if ($restaurant) {
            $msg = $restaurant->getLevelDbNickname();
        } else {
            $msg = 'Pepito test!';
        }

        if ($user) {
            $msg = 'Request ArmorCard by user ' . $user->getId();
        }
        
        $auxiliarIsArrayFlag = false;
        if (is_array($responseCurl)) {
            $auxiliarIsArrayFlag = true;
            $responseCurlAuxiliar = $responseCurl;
            $responseCurl = json_encode($responseCurlAuxiliar);
        }

        if (strpos($responseCurl, '500 Internal Server Error') !== false) { //500
            $error = Util::getStringBetween($responseCurl, '<title>', '</title>');

            $emails = $this->container->getParameter('app_emails_consumers_errors');
            $emailArray['to'] = $emails[0];
            $emailArray['subject'] = '500 Server Error In curl Request For: ' . $msg;
            $emailArray['message'] = '500 Internal Server Error In curl Request: <strong>' . $error . '</strong> for: <strong>' . $msg . '</strong> in: <strong>' . $url . '</strong> WS ';
            $this->sendEmail($emailArray);

            $log->addNotice('SyncLevelCommingDataConsumer', ['500 Internal Server Error In curl Request: ', $error]);
            return $this->setStatusCode(WebService::CODE_INTERNAL_ERROR)
                            ->respondWithError($error
                                    , WebService::CODE_INTERNAL_ERROR, $this->getMeta($request));
        } elseif ($httpStatus == WebService::CODE_ERR_NOT_FOUND) { //404
            $emails = $this->container->getParameter('app_emails_consumers_errors');
            $emailArray['to'] = $emails[0];
            $emailArray['subject'] = '404 Page Not Found In curl Request To: ' . $msg;
            $emailArray['message'] = '404 Page Not Found In curl Request For: <strong>' . $msg . '</strong> in: <strong>' . $url . '</strong> WS ';
            $this->sendEmail($emailArray);


            $log->addNotice('SyncLevelCommingDataConsumer', ['404 Page Not Found In curl Request To: ', $url . ' for restaurant: ' . $msg]);
            return $this->setStatusCode(WebService::CODE_ERR_NOT_FOUND)
                            ->respondWithError($translated->trans('error_url_not_found', ['%URL%' => $url])
                                    , WebService::CODE_ERR_NOT_FOUND, $this->getMeta($request));
        } elseif (strpos($responseCurl, 'Unable to create the storage directory') !== false) { //404
            $emails = $this->container->getParameter('app_emails_consumers_errors');
            $emailArray['to'] = $emails[0];
            $emailArray['subject'] = 'Unable to create the storage directory in: ' . $msg;
            $emailArray['message'] = 'Unable to create the storage directory in:  <strong>' . $msg . '</strong> in: <strong>' . $url . '</strong> WS ';
            $this->sendEmail($emailArray);

            $log->addNotice('SyncLevelCommingDataConsumer', ['Unable to create the storage directory in: ', $url . ' for restaurant: ' . $msg]);
            return $this->setStatusCode(WebService::CODE_OBJECT_NOT_FOUND)
                            ->respondWithError($translated->trans('msg_error_create_storage')
                                    , WebService::CODE_OBJECT_NOT_FOUND, $this->getMeta($request));
        } elseif ($httpStatus == 0) { //0
            $emails = $this->container->getParameter('app_emails_consumers_errors');
            $emailArray['to'] = $emails[0];
            $emailArray['subject'] = '404 Page Not Found In curl Request To: ' . $msg;
            $emailArray['message'] = '404 Page Not Found In curl Request For: <strong>' . $msg . '</strong> in: <strong>' . $url . '</strong> WS ';
            $this->sendEmail($emailArray);


            $log->addNotice('ApiController', ['404 Page Not Found In curl Request To: ', $url . ' for: ' . $msg]);

            return $this->setStatusCode(WebService::CODE_OBJECT_NOT_FOUND)
                            ->respondWithError($translated->trans('msg_error_page_notfound_curl')
                                    , WebService::CODE_OBJECT_NOT_FOUND, $this->getMeta($request));
        }

        $log->addNotice('ApiController', ['validateCurlInController ', 'Returning!']);

        return true;
    }

    /**
     * Se encarga de enviar un correo de confirmacion de la pre orden
     * @param type $arrInfo
     * @param type $attachedFile
     * @return type
     */
    public function sendEmailOrderPreConfirm($arrInfo, $attachedFile = '') {
        $arrRended = [
            'order' => $arrInfo['order'],
        ];

        $body = $this->render('Emails\emailOmtOrderPreConfirm.html.twig', $arrRended)->getContent();

        $infoMail = $this->getParameter('mailer_user');
        $infoPass = $this->getParameter('mailer_password');

        if ($this->getParameter("mailer_host") == "mailhog") {
            $encryption = null;
        } else {
            $encryption = $this->getParameter("mailer_encryption");
        }

        if ($attachedFile != '') {
            $documento = \Swift_Attachment::fromPath($attachedFile);
        }

        $transport = (new \Swift_SmtpTransport($this->getParameter("mailer_host"), $this->getParameter("mailer_port"), $encryption))
                ->setUsername($infoMail)
                ->setPassword($infoPass);

        $mailer = new \Swift_Mailer($transport);
        $message = (new \Swift_Message())
                ->setSubject($arrInfo['subject'])
                ->setFrom([$infoMail => "Open My Tab"])
                ->setTo($arrInfo['to'])
                ->setBody($body/* html body */, 'text/html');

        if (isset($arrInfo['emailCc'])) {
            $message->setCC($arrInfo['emailCc']);
        }

        if ($attachedFile != '') {
            $message->attach($documento);
        }

        return $mailer->send($message);
    }
    
    /**
     * Se encarga de enviar un correo con el usuario y el pass del usuario creado por red social
     * @param type $arrInfo
     * @param type $attachedFile
     * @return type
     */
    public function sendDetailUser($arrInfo, $attachedFile = '') {
        $arrRended = [
            'email' => $arrInfo['email'],
            'name' => $arrInfo['name'],
            'pass' => $arrInfo['pass'],
            'externalMenu' => $arrInfo['externalMenu'],
            'bgColor' => $arrInfo['externalMenuColor']
        ];

        $translator = $this->container->get('translator');
        $body = $this->render('Emails\emailOmtDetailUser.html.twig', $arrRended)->getContent();

        $subjet = $translator->trans('user') . ', ' . $arrRended['name'];

        $infoMail = $this->getParameter('mailer_user');
        $infoPass = $this->getParameter('mailer_password');

        if ($this->getParameter("mailer_host") == "mailhog") {
            $encryption = null;
        } else {
            $encryption = $this->getParameter("mailer_encryption");
        }

        if ($attachedFile != '') {
            $documento = \Swift_Attachment::fromPath($attachedFile);
        }

        $transport = (new \Swift_SmtpTransport($this->getParameter("mailer_host"), $this->getParameter("mailer_port"), $encryption))
                ->setUsername($infoMail)
                ->setPassword($infoPass);

        $mailer = new \Swift_Mailer($transport);
        $message = (new \Swift_Message())
                ->setSubject($subjet)
                ->setFrom([$this->getParameter('mailer_user') => "Open My Tab"])
                ->setTo($arrRended['email'])
                ->setBody($body/* html body */, 'text/html');

        if (isset($arrInfo['emailCc'])) {
            $message->setCC($arrInfo['emailCc']);
        }

        if ($attachedFile != '') {
            $message->attach($documento);
        }

        return $mailer->send($message);
    }

    /**
     * Metodo para enviar una respuesta de symfony tipo json para un ajax
     *
     * @param type $responseToAjax arreglo para ser enviado como respuesta
     * @return Response respuesta tipo json generica para un ajax
     */
    public function respondJsonAjax($responseToAjax) {
        $r = new Response(json_encode($responseToAjax));
        $r->headers->set('Content-Type', 'application/json');
        return $r;
    }

}
