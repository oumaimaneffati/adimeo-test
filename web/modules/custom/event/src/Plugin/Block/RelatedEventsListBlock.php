<?php

namespace Drupal\event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\event\Service\EventService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Related Events' block.
 *
 * @Block(
 *   id = "related_events_list_block",
 *   admin_label = @Translation("Related Events Block"),
 * )
 */
class RelatedEventsListBlock extends BlockBase implements
  ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\event\Service\EventService
   */
  public $event_service;

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\event\Service\EventService $event_service
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EventService $event_service
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->event_service = $event_service;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param $event_service
   *
   * @return static
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event.event_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['events_number'] = [
      '#type' => 'number',
      '#title' => $this->t('Events number'),
      '#description' => $this->t('Number of events to display in the block '),
      '#default_value' => isset($config['events_number']) ? $config['events_number'] : 3,
      '#required' => TRUE,
    ];


    return $form;
  }

  /**
   * @return array|void
   */
  public function build() {
    $config = $this->getConfiguration();
    $events_number = $config['events_number'];
    $current_event = \Drupal::routeMatch()->getParameter('node');;
    if ($current_event instanceof \Drupal\node\NodeInterface) {
      $event_type_id = $current_event->get('field_event_type')
        ->getValue()[0]['target_id'];
      $related_events = $this->event_service->getRelatedEvents($current_event->id(),
        $event_type_id, $events_number);

      return [
        '#theme' => 'block_related_events',
        '#events' => $related_events,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['events_number'] = $values['events_number'];
  }
}