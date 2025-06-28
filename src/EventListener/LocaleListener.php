<?php

/**
 * Event Listener.
 */

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class LocaleListener.
 */
class LocaleListener implements EventSubscriberInterface
{
    /**
     * @var string Default locale
     */
    private string $defaultLocale;

    /**
     * Constructor.
     *
     * @param string $defaultLocale Default locale
     */
    public function __construct(string $defaultLocale = 'pl')
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Request event to set the locale.
     *
     * @param RequestEvent $event Request event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Jeśli już ustawiono _locale, nie nadpisuj
        if (!$request->attributes->has('_locale')) {
            $request->setLocale($request->getPreferredLanguage() ?? $this->defaultLocale);
        }
    }

    /**
     * Returns an array of events.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
