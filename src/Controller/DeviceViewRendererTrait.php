<?php
declare(strict_types = 1);

namespace Gdbots\Bundle\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait DeviceViewRendererTrait
{
    /**
     * Renders a view that is device view specific first, if found, otherwise the default/shared view.
     * This calls the FrameworkBundle Controller "render" method under the hood.
     *
     * @param string   $view       The view name (with %device_view% string which will be replaced with current value)
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A response instance
     *
     * @return Response A Response instance
     *
     * @throws \Exception
     */
    protected function renderUsingDeviceView(string $view, array $parameters = [], ?Response $response = null): Response
    {
        /** @var Request $request */
        $request = $this->get('request_stack')->getCurrentRequest();
        $deviceView = $request ? $request->attributes->get('device_view') : null;

        if (null === $deviceView || false === strpos($view, '%device_view%')) {
            return $this->render(str_replace('%device_view%', '', $view), $parameters, $response);
        }

        try {
            return $this->render(str_replace('%device_view%', '.' . $deviceView, $view), $parameters, $response);
        } catch (\Exception $e) {
            if (!$e->getPrevious() instanceof \Twig_Error_Loader) {
                throw $e;
            }

            return $this->render(str_replace('%device_view%', '', $view), $parameters, $response);
        }
    }

    /**
     * @param string $id
     *
     * @return object
     */
    abstract protected function get($id);

    /**
     * @param string   $view
     * @param array    $parameters
     * @param Response $response
     *
     * @return Response
     */
    abstract protected function render($view, array $parameters = [], Response $response = null);
}
