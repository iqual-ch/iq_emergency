<?php

namespace Drupal\iq_emergency\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\redirect\RedirectChecker;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Redirect subscriber for controller requests.
 */
class EmergencyRedirectRequestSubscriber implements EventSubscriberInterface {

    /**
     * @var \Drupal\redirect\RedirectChecker
     */
    protected $redirectChecker;

    /**
     * The path matcher.
     *
     * @var \Drupal\Core\Path\PathMatcherInterface
     */
    protected $pathMatcher;

    /**
     * Redirect configuration.
     *
     * @var \Drupal\Core\Config\Config
     */
    protected  $redirectConfig;

    /**
     * Constructs a \Drupal\redirect\EventSubscriber\RedirectRequestSubscriber object.
     *
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
     *   The config factory.
     * @param \Drupal\redirect\RedirectChecker $redirect_checker
     *   The redirect checker service.
     * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
     *   The path matcher.
     */
    public function __construct(ConfigFactoryInterface $config_factory, RedirectChecker $redirect_checker, PathMatcherInterface $path_matcher) {
        $this->redirectConfig = $config_factory->get('iq_emergency.settings');
        $this->redirectChecker = $redirect_checker;
        $this->pathMatcher = $path_matcher;
    }

    /**
     * Handles the emergency redirect if any found.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     *   The event to process.
     */
    public function onKernelRequestCheckEmergencyRedirect(GetResponseEvent $event) {
        $request = clone $event->getRequest();
        $path = $request->getPathInfo();
        $host = $request->getHost();
        $protocol = $request->getScheme() . '://';
        $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$this->redirectConfig->get('emergency_page'));
        $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
        if (!$this->redirectChecker->canRedirect($request) || strpos($path, $alias) > 0) {
            return;
        }
        $emergency_mode = $this->redirectConfig->get('emergency_mode');
        if ($emergency_mode && !(strpos($path, '/admin/') > 0)) {
            // Use the default status code from Redirect.
            $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$this->redirectConfig->get('emergency_page'));
            $response = new TrustedRedirectResponse(
                $protocol . $host . '/' . $lang . $alias,
                301
            );
            $event->setResponse($response);
            return;
        }
    }

    /**
     * Prior to set the response it check if we can redirect.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     *   The event object.
     * @param \Drupal\Core\Url $url
     *   The Url where we want to redirect.
     */
    protected function setResponse(GetResponseEvent $event, Url $url) {
        $request = $event->getRequest();

        parse_str($request->getQueryString(), $query);
        $url->setOption('query', $query);
        $url->setAbsolute(TRUE);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        // This needs to run before RouterListener::onKernelRequest(), which has
        // a priority of 32 and
        // RedirectRequestSubscriber::onKernelRequestCheckRedirect(), which has
        // a priority of 33. Otherwise, that aborts the request if no matching
        // route is found.
        $events[KernelEvents::REQUEST][] = ['onKernelRequestCheckEmergencyRedirect', 34];
        return $events;
    }

}
