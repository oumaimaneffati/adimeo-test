<?php

namespace Drupal\event\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Unpublish passed events
 *
 * @QueueWorker(
 *   id = "event_unpublish",
 *   title = @Translation("Unpublish events"),
 *   cron = {"time" = 60}
 * )
 */
class UnpublishEventsQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {


  /**
   * {@inheritdoc}
   */
  public function processItem($nid) {
    $node = Node::load($nid);
    $node->setUnpublished();
    $node->save();
    \Drupal::logger('cron')->notice('Event with id %id was unpublished',['%id'=>$nid]);
  }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition);
  }

}
