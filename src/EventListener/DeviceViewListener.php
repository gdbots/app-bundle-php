<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle\EventListener;

use Gdbots\Bundle\AppBundle\DeviceViewResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class DeviceViewListener implements EventSubscriberInterface
{
    /** @var DeviceViewResolver */
    private $resolver;

    /** @var string */
    private $deviceView;

    /** @var bool */
    private $hasInvalidCookie = false;

    /**
     * @param DeviceViewResolver $resolver
     */
    public function __construct(DeviceViewResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();
        $request->attributes->set('device_view', $this->getDeviceView($request));
        $viewerCountry = strtoupper(trim($request->server->get('VIEWER_COUNTRY', '')));
        if (!empty($viewerCountry)) {
            $request->attributes->set('viewer_country', $viewerCountry);
        }
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();

        if ($this->hasInvalidCookie) {
            $response->headers->removeCookie('device_view');
        }

        if ($request->query->has('device_view')) {
            $response->headers->setCookie(new Cookie(
                'device_view',
                $this->deviceView,
                0,
                '/',
                null,
                true,
                true,
                false,
                Cookie::SAMESITE_STRICT
            ));
        }
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getDeviceView(Request $request): string
    {
        if (null === $this->deviceView) {
            $envValue = $request->server->get('DEVICE_VIEW');
            $this->deviceView = $this->resolver->resolve($envValue);
            if ($envValue !== $this->deviceView) {
                $this->hasInvalidCookie = true;
                $request->cookies->remove('device_view');
                $request->query->remove('device_view');
            }
        }

        return $this->deviceView;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST  => ['onKernelRequest', 10000],
            KernelEvents::RESPONSE => ['onKernelResponse', 10000],
        ];
    }
}
