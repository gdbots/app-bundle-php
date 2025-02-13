<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle\Controller;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Error\LoaderError;

/**
 * Using this trait requires extending:
 * @see \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
 *
 * or a compatible alternative (with container and render methods).
 */
trait DeviceViewRendererTrait
{
    protected ContainerInterface $container;

    /**
     * Renders a view that is device view specific first, if found, otherwise the default/shared view.
     * This calls the FrameworkBundle Controller "render" method under the hood.
     *
     * @param string        $view       The view name (with %device_view% string which will be replaced with current value)
     * @param array         $parameters An array of parameters to pass to the view
     * @param Response|null $response   A response instance
     *
     * @return Response
     *
     */
    protected function renderUsingDeviceView(string $view, array $parameters = [], ?Response $response = null): Response
    {
        /** @var Request $request */
        $request = $this->get('request_stack')->getCurrentRequest();
        $deviceView = $request?->attributes->get('device_view');

        if (null === $deviceView || !str_contains($view, '%device_view%')) {
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

    protected function get(string $id): object
    {
        return $this->container->get($id);
    }

    /**
     * @see \Symfony\Bundle\FrameworkBundle\Controller\AbstractController::render
     */
    abstract protected function render(string $view, array $parameters = [], ?Response $response = null): Response;
}
