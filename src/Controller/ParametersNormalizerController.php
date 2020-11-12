<?php

namespace App\Controller;

use App\Controller\RealContainerBearerController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Argument\ServiceLocator;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of ParametersNormalizerController
 * @author aealan
 */
class ParametersNormalizerController extends RealContainerBearerController {

    /**
     * @param type $propertyName
     * @return type
     */
    public function __get($propertyName) {
        if ($propertyName == "realContainer") {
            return $this->get('real_kernel')->getContainer();
        } else {
            return $this->$propertyName;
        }
    }

    /**
     * Gets a container parameter by its name.
     *
     * @return mixed
     *
     * @final
     */
    protected function getParameter(string $name) {

        $parameters = $this->get('real_kernel')->getContainer()->getParameterBag()->all();

        if (!isset($parameters[$name])) {
            dump($name);
            die;
            throw new ServiceNotFoundException('parameter_bag', null, null, [], sprintf('The "%s::getParameter()" method is missing a parameter bag to work properly. Did you forget to register your controller as a service subscriber? This can be fixed either by using autoconfiguration or by manually wiring a "parameter_bag" in the service locator passed to the controller.', \get_class($this)));
        }

        return $parameters[$name];
    }

    /**
     * @param type $responseToAjax
     * @return Response
     */
    public function respondJsonAjax($responseToAjax) {
        $r = new Response(json_encode($responseToAjax));
        $r->headers->set('Content-Type', 'application/json');
        return $r;
    }

}
