<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Replaces special tokens in the response for debugging,
 * performance monitoring, etc.
 *
 * This should be the last item run in most cases.
 *
 * todo: figure out how to replace tokens on commands?
 */
final class ResponseTokensListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -1000],
        ];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();
        $benchmark = (int)((microtime(true) - $request->server->get('REQUEST_TIME_FLOAT')) * 1000);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        /*
         * create an unquoted html attribute/json friendly etag value.
         * This is only used for debugging/validation so it's OKAY that it doesn't
         * match exactly to the ETAG present in the response headers.
         */
        $content = $response->getContent();
        $etag = str_replace('"', '', $response->getEtag() ?: md5($content));
        $tokens = [
            '%BENCHMARK%'     => $benchmark,
            '%ETAG%'          => $etag,
            '%TIMESTAMP%'     => $now->getTimestamp(),
            '%TIMESTAMP_ISO%' => $now->format('Y-m-d\TH:i:s.u\Z'),
        ];

        $count = 0;
        $content = str_replace(array_keys($tokens), array_values($tokens), $content, $count);

        if (!$count) {
            return;
        }

        $response->setContent($content);
    }
}
