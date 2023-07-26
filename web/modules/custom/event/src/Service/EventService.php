<?php

namespace Drupal\event\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

class EventService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entity_type_manager;

  /**
   * EventService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->entity_type_manager = $entity_type_manager;
  }

  /**
   * @param $current_event_id
   * @param $taxonomy_id
   * @param $number_events
   *
   * @return array
   */
  public function getRelatedEventsIds(
    $current_event_id,
    $taxonomy_id,
    $number_events
  ): array {
    $related_events = [];
    $related_events = $this->getNotPassedEventsByTaxonomy($current_event_id,
      $number_events,
      $taxonomy_id, TRUE);
    if (($remain_number = $number_events - count($related_events)) < 0) {
      $related_events[] = $this->getNotPassedEventsByTaxonomy($current_event_id,
        $remain_number,
        $taxonomy_id, FALSE);
    }

    return $related_events;
  }

  /**
   * @param $current_event_id
   * @param $taxonomy_id
   * @param $number_events
   *
   * @return array|array[]
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getRelatedEvents(
    $current_event_id,
    $taxonomy_id,
    $number_events
  ) {
    $related_events_id = $this->getRelatedEventsIds($current_event_id,
      $taxonomy_id, $number_events);
    if (empty($related_events_id)) {
      return [];
    }
    $events = $this->entity_type_manager
      ->getStorage('node')
      ->loadMultiple($related_events_id);

    return array_map(function ($event) {
      return [
        'title' => $event->getTitle(),
        'event_type' => $this->getEventTypeName($event),
      ];
    }, $events);
  }

  /**
   * @param $event
   *
   * @return string|void
   */
  public function getEventTypeName($event) {
    if ($event instanceof \Drupal\node\NodeInterface && $event->bundle() === 'event') {
      if ($event->get('field_event_type')->isEmpty()) {
        return '';
      }

      return $event->get('field_event_type')
        ->referencedEntities()[0]->label();
    }
  }

  /**
   * @param $number_events
   * @param $id
   *
   * @return array|int
   */
  public function getNotPassedEventsByTaxonomy(
    $current_event_id,
    $number_events,
    $taxonomy_id,
    $same = TRUE
  ) {
    $now = $this->getNowDate();
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'event')
      ->condition('nid', $current_event_id, '<>')
      ->condition('status', 1)
      ->condition('field_event_type.target_id', $taxonomy_id,
        ($same ? '=' : '<>'))
      ->condition('field_date_range.end_value', $now, '>')
      ->accessCheck(FALSE)
      ->sort('field_date_range.value', 'desc')
      ->range(0, $number_events);
    return $query->execute();
  }

  /**
   * @return string
   */
  public function getNowDate(): string {
    $now = new DrupalDateTime();
    $now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    return $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
  }

  /**
   * @return array|int
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getNodesToUnpublish() {
    $query = $this->entity_type_manager->getStorage('node')->getQuery();
    $query->condition('status', 1);
    $query->condition('type', 'event');
    $query->accessCheck(FALSE);
    $query->condition('field_date_range.end_value', $this->getNowDate(), '<');

    return $query->execute();
  }

}