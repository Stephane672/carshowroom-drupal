<?php

namespace Drupal\carshowroom_ui\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountRedirectSubscriber implements EventSubscriberInterface {

  public function __construct(
    protected AccountProxyInterface $currentUser,
  ) {}

  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onRequest', 28],
    ];
  }

  public function onRequest(RequestEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }
    $request = $event->getRequest();
    $route_name = $request->attributes->get('_route');
    if ($route_name !== 'entity.user.canonical') {
      return;
    }
    if ($this->currentUser->isAnonymous()) {
      return;
    }
    $user_param = $request->attributes->get('user');
    $viewed_uid = is_object($user_param) ? $user_param->id() : $user_param;
    if ((int) $viewed_uid !== (int) $this->currentUser->id()) {
      return;
    }
    $url = Url::fromRoute('entity.user.edit_form', ['user' => $viewed_uid]);
    if (!$url->access($this->currentUser)) {
      return;
    }
    $event->setResponse(new RedirectResponse($url->toString()));
  }

}
