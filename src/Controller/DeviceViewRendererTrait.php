<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Error\LoaderError;

/**
 * Using this trait requires extending:
 * @see \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
 *
 * or a compatible alternative (with get and render methods).
 */
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
     * @return Response
     *
     * @throws \Throwable
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
        } catch (\Throwable $e) {
            if ($e instanceof LoaderError || $e->getPrevious() instanceof LoaderError) {
                return $this->render(str_replace('%device_view%', '', $view), $parameters, $response);
            }

            throw $e;
        }
    }

    /**
     * @see \Symfony\Bundle\FrameworkBundle\Controller\AbstractController::get
     */
    abstract protected function get(string $id): object;

    /**
     * @see \Symfony\Bundle\FrameworkBundle\Controller\AbstractController::render
     */
    abstract protected function render(string $view, array $parameters = [], Response $response = null): Response;
}
