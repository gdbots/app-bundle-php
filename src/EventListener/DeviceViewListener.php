<?php
declare(strict_types = 1);

namespace Gdbots\Bundle\AppBundle\EventListener;

use Gdbots\Bundle\AppBundle\DeviceViewResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DeviceViewListener implements EventSubscriberInterface
{
    /** @var DeviceViewResolver */
    protected $deviceViewResolver;

    /** @var string */
    protected $deviceView;

    /** @var bool */
    protected $hasInvalidCookie = false;

    /**
     * @param DeviceViewResolver $deviceViewResolver
     */
    public function __construct(DeviceViewResolver $deviceViewResolver)
    {
        $this->deviceViewResolver = $deviceViewResolver;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $request->attributes->set('device_view', $this->getDeviceView($request));
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
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
            $response->headers->setCookie(new Cookie('device_view', $this->deviceView));
        }
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    protected function getDeviceView(Request $request)
    {
        if (null === $this->deviceView) {
            $envValue = $request->server->get('DEVICE_VIEW');
            $this->deviceView = $this->deviceViewResolver->resolve($envValue);
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
