<?php

namespace Drupal\carshowroom_ui\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\TranslatableMarkup;

#[Block(
  id: 'carshowroom_superhighlight',
  admin_label: new TranslatableMarkup('Super Highlight carousel'),
  category: new TranslatableMarkup('Car Showroom'),
)]
class SuperHighlightBlock extends BlockBase {

  public function defaultConfiguration() {
    return ['interval' => 6, 'max_slides' => 8] + parent::defaultConfiguration();
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form['interval'] = [
      '#type' => 'number',
      '#title' => $this->t('Seconds between slides (0 = no autoplay)'),
      '#min' => 0,
      '#max' => 60,
      '#default_value' => $this->configuration['interval'],
    ];
    $form['max_slides'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum number of slides'),
      '#min' => 1,
      '#max' => 20,
      '#default_value' => $this->configuration['max_slides'],
      '#description' => $this->t('Add, edit, reorder or unpublish slides under Content > Add content > Super Highlight slide.'),
    ];
    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['interval'] = (int) $form_state->getValue('interval');
    $this->configuration['max_slides'] = (int) $form_state->getValue('max_slides');
  }

  public function build() {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'superhighlight_slide')
      ->condition('status', 1)
      ->sort('field_slide_weight', 'ASC')
      ->sort('nid', 'ASC')
      ->range(0, max(1, (int) $this->configuration['max_slides']))
      ->execute();
    $build = ['#cache' => ['tags' => ['node_list:superhighlight_slide'], 'contexts' => ['user.permissions']]];
    if (!$ids) {
      return $build;
    }
    $slides = '';
    $dots = '';
    $i = 0;
    foreach ($storage->loadMultiple($ids) as $node) {
      $build['#cache']['tags'] = array_merge($build['#cache']['tags'], $node->getCacheTags());
      $title = Html::escape($node->label());
      $sub = Html::escape((string) $node->get('field_slide_subtitle')->value);
      $btn = Html::escape((string) $node->get('field_slide_button_text')->value);
      $href = '';
      if (!$node->get('field_slide_link')->isEmpty()) {
        $href = Html::escape($node->get('field_slide_link')->first()->getUrl()->toString());
      }
      $style = '';
      if (!$node->get('field_slide_image')->isEmpty() && ($file = $node->get('field_slide_image')->entity)) {
        $url = \Drupal::service('file_url_generator')->generateString($file->getFileUri());
        $style = ' style="background-image:url(\'' . Html::escape($url) . '\')"';
        $build['#cache']['tags'] = array_merge($build['#cache']['tags'], $file->getCacheTags());
      }
      $active = $i === 0 ? ' is-active' : '';
      $slides .= '<div class="sh-slide sh-slide--' . ($i % 4) . $active . '"' . $style . '><div class="sh-overlay"></div><div class="sh-content">';
      if ($sub !== '') {
        $slides .= '<p class="sh-subtitle">' . $sub . '</p>';
      }
      $slides .= '<h2 class="sh-title">' . $title . '</h2>';
      if ($btn !== '' && $href !== '') {
        $slides .= '<a class="sh-btn" href="' . $href . '">' . $btn . '</a>';
      }
      $slides .= '</div></div>';
      $dots .= '<button type="button" class="sh-dot' . $active . '" aria-label="Slide ' . ($i + 1) . '"></button>';
      $i++;
    }
    $html = '<section class="superhighlight" aria-roledescription="carousel" aria-label="Super Highlight" data-interval="' . (int) $this->configuration['interval'] . '">'
      . '<div class="sh-track">' . $slides . '</div>'
      . '<button type="button" class="sh-arrow sh-prev" aria-label="Previous slide">&#10094;</button>'
      . '<button type="button" class="sh-arrow sh-next" aria-label="Next slide">&#10095;</button>'
      . '<div class="sh-dots">' . $dots . '</div></section>';
    $build['#markup'] = Markup::create($html);
    return $build;
  }

}
