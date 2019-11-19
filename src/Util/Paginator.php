<?php

namespace App\Util;

/**
 * Paginator class
 * @author Aealan Z - Kijho Technologies <lrobledo@kijho.com>
 * @since 1.0 26/03/2015
 */
class Paginator {

    const IMAGE_ARROW_UP = 'up_arrow.gif';
    const IMAGE_ARROW_DOWN = 'down_arrow.gif';
    const REQUEST_TYPE_ARRAY = 'array';
    const REQUEST_TYPE_REQUEST = 'request';

    /**
     * Esta funcion permite filtrar de un arreglo los parametros de busqueda u ordenamiento
     * con los indices indicados en otro arreglo u objeto request, metodo utilizado para los paginadores
     * @author Aealan Z - Kijho Technologies <lrobledo@kijho.com>
     * @since 1.0 26/03/2015
     * @param array[string] $index arreglo con las claves que contiene el arreglo de parametros
     * @param \Symfony\Component\HttpFoundation\Request $request peticion $_POST, array[string] parametros sin filtrar
     * @return array[string] arreglo filtrado
     */
    public static function filterParameters($index, $parametersRequest, $type, $castToInteger = null) {
        $arrayFiltered = array();
        foreach ($index as $item) {
            if ($type == self::REQUEST_TYPE_ARRAY) {
                if (isset($parametersRequest[$item]) && $parametersRequest[$item] != '') {
                    if (!is_array($parametersRequest[$item])) {
                        $filterItem = preg_replace('@[ ]{2,}@', ' ', trim($parametersRequest[$item]));
                    }
                }
                if ($castToInteger) {
                    $filterItem = (int) $filterItem;
                }
                if (isset($filterItem) && $filterItem != '') {
                    $arrayFiltered[$item] = $filterItem;
                    $filterItem = '';
                }
            } elseif ($type == self::REQUEST_TYPE_REQUEST) {
                $filterItem = preg_replace('@[ ]{2,}@', ' ', trim($parametersRequest->query->get($item)));

                if ($castToInteger) {
                    $filterItem = (int) $filterItem;
                }
                if (isset($filterItem) && $filterItem != '') {
                    $arrayFiltered[$item] = $filterItem;
                    $filterItem = '';
                }
            }
        }

        return $arrayFiltered;
    }

    /**
     * Esta funcion permite construir una url, correspondiente a una peticion $_GET a partir
     * de un arreglo de parametros cualquiera.
     * @author Aealan Z - Kijho Technologies <lrobledo@kijho.com>
     * @since 1.0 26/03/2015
     * @param array[string] $index indices del arreglo de parametros
     * @param array[string] $search arreglo de parametros
     * @return string cadena de texto con la url correspondiente
     */
    public static function getUrlFromParameters($index, $search) {
        $url = '';
        foreach ($index as $item) {
            if (isset($search[$item]) && $search[$item] != '') {
                if (!is_array($search[$item])) {
                    $url = $url . '&' . $item . "=" . $search[$item];
                }
            }
        }
        return $url;
    }

    /**
     * Esta funcion prmite construir un arreglo el cual contiene los datos para realizar
     * las tareas de ordenamiento en un listado cualquiera
     * @author Aealan Z - Kijho Technologies <lrobledo@kijho.com>
     * @since 1.0 26/03/2015
     * @param array[string] $index indices del arreglo de parametros
     * @param array[string] $order arreglo de parametros
     * @return array[string url, array[order, image]] elementos para el ordemaniento
     */
    public static function getUrlOrderFromParameters($index, $order, $container = '') {
        $orderBy = array();
        $orderBy['url'] = null;
        foreach ($index as $item) {
            $orderBy[$item] = array();
            $orderBy[$item]['order'] = null;
            $orderBy[$item]['image'] = null;

            if (isset($order[$item]) && $order[$item] != '') {
                $orderBy[$item]['order'] = $order[$item] + 1;
                $orderBy['url'] = $orderBy['url'] . '&' . $item . "=" . $order[$item];

                if ($orderBy[$item]['order'] % 2) {
                    $orderBy[$item]['image'] = self::IMAGE_ARROW_UP;

                    if ($container) {
                        $imagePath = $container->getParameter('level_licensor_scheme') . '://' . $container->getParameter('level_licensor_host') . '/bundles/levellicensor/images/' . self::IMAGE_ARROW_UP;
                        $orderBy[$item]['htmlImage'] = '<img src="' . $imagePath . '" />';
                    }
                } else {
                    $orderBy[$item]['image'] = self::IMAGE_ARROW_DOWN;

                    if ($container) {
                        $imagePath = $container->getParameter('level_licensor_scheme') . '://' . $container->getParameter('level_licensor_host') . '/bundles/levellicensor/images/' . self::IMAGE_ARROW_DOWN;
                        $orderBy[$item]['htmlImage'] = '<img src="' . $imagePath . '" />';
                    }
                }
            } else {
                $orderBy[$item]['order'] = 1;
                $orderBy[$item]['image'] = '';
            }
        }
        return $orderBy;
    }

    /**
     * Esta funcion permite setear en session los arreglos de busqueda y ordenamiento
     * de listados definidos en la aplicacion.
     * @author Aealan Z - Kijho Technologies <lrobledo@kijho.com>
     * @since 1.0 26/03/2015 
     * @param Request $request solicitud del cliente
     * @param string $searchName identificador del listado 
     * @param array[string] $search parametros de busqueda
     * @param array[string] $order parametros de ordenamiento
     */
    public static function setLastSearchOnSession($request, $searchName, $search, $order) {

        $page = (int) $request->get('page');
        $itemsPerPage = (int) $request->get('itemsPerPage');

        if ($page > 0 || $itemsPerPage) {
            $search['page'] = $page;
            $search['itemsPerPage'] = $itemsPerPage;
        }

        $session = $request->getSession();
        if (!empty($search) || !empty($order)) {
            $lastSearch = array($search, $order);
            $session->set($searchName, $lastSearch);
        }
    }

    /**
     * Esta funcion permite obtener un arreglo de busqueda y ordenamiento almacenado 
     * en session y retornarlo como otro arreglo plano
     * @author Aealan Z - Kijho Technologies <lrobledo@kijho.com>
     * @since 1.0 26/03/2015 
     * @param Request $request solicitud del usuario
     * @param string $searchName clave del arreglo en session
     * @return array[string] arreglo reestructurado
     */
    public static function getLastSearchOnSession($request, $searchName) {
        $session = $request->getSession();

        $fullSearch = array();
        $lastSearch = $session->get($searchName);
        if (!empty($lastSearch)) {

            if (isset($lastSearch[0]) && isset($lastSearch[1])) {
                $fullSearch = array_merge($lastSearch[0], $lastSearch[1]);
            }
        }
        return $fullSearch;
    }

}
