<?php

namespace Drupal\carshowroom_ui\EventSubscriber;

use Drupal\Core\Database\Connection;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Restores uploaded images from the database once per container boot.
 *
 * Render's free-tier containers have no persistent disk: every redeploy (a
 * new git push, or the platform cycling the free-tier instance) starts from
 * a brand-new filesystem that only contains whatever was committed to Git.
 * Any image a visitor or editor uploaded through the site after the last
 * deploy only ever existed on the old container's disk, so it was lost.
 *
 * carshowroom_ui backs up every uploaded public file's bytes into the
 * carshowroom_dbfiles table (see hook_file_insert() in carshowroom_ui.module),
 * which lives in the external, persistent MySQL database on Clever Cloud,
 * not on the container. This subscriber writes those bytes back out to the
 * local filesystem the first time a new container handles a request, so
 * uploaded images reappear automatically after every deploy or restart.
 */
class DbFileRestoreSubscriber implements EventSubscriberInterface {

  public function __construct(
    protected FileSystemInterface $fileSystem,
    protected Connection $database,
  ) {}

  public static function getSubscribedEvents(): array {
    // The priority doesn't matter much: this only touches the filesystem,
    // not the response, so running it early just keeps later code (image
    // rendering, etc.) from ever seeing a missing file during this request.
    return [KernelEvents::REQUEST => ['onRequest', 300]];
  }

  public function onRequest(RequestEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }
    // Only do this once per container lifetime. A marker file on the
    // container's own disk (stable request-to-request, but wiped along with
    // everything else on the next redeploy) tells us whether this
    // particular container has already run the restore.
    $marker = $this->fileSystem->getTempDirectory() . '/carshowroom_dbfiles_restored';
    if (file_exists($marker)) {
      return;
    }
    $this->restoreMissingFiles();
    @touch($marker);
  }

  protected function restoreMissingFiles(): void {
    if (!$this->database->schema()->tableExists('carshowroom_dbfiles')) {
      return;
    }
    $result = $this->database->select('carshowroom_dbfiles', 'f')
      ->fields('f', ['uri', 'data'])
      ->execute();
    foreach ($result as $row) {
      $real_path = $this->fileSystem->realpath($row->uri);
      if (!$real_path) {
        continue;
      }
      if (is_file($real_path)) {
        // Already present (e.g. an image that was committed to Git) -
        // leave it alone.
        continue;
      }
      $dir = dirname($real_path);
      if (!is_dir($dir)) {
        $this->fileSystem->mkdir($dir, NULL, TRUE);
      }
      file_put_contents($real_path, $row->data);
    }
  }

}
