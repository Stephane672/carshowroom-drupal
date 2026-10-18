<?php

namespace Drupal\carshowroom_ui\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Render\Markup;

#[Block(
  id: 'carshowroom_category_cards',
  admin_label: new TranslatableMarkup('Car Category cards'),
  category: new TranslatableMarkup('Car Showroom'),
)]
class CarCategoryCardsBlock extends BlockBase {

  public function build() {
    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    $term_ids = $term_storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('vid', 'car_category')
      ->sort('weight', 'ASC')
      ->sort('name', 'ASC')
      ->execute();

    $build = [
      '#cache' => [
        'tags' => ['taxonomy_term_list:car_category', 'node_list:car'],
        'contexts' => ['user.permissions'],
      ],
    ];

    if (!$term_ids) {
      return $build;
    }

    $terms = $term_storage->loadMultiple($term_ids);
    $cards = '';

    foreach ($terms as $term) {
      $build['#cache']['tags'] = array_merge($build['#cache']['tags'], $term->getCacheTags());

      $tid = (int) $term->id();
      $name = Html::escape($term->label());
      $desc = Html::escape(trim(strip_tags((string) $term->get('description')->value)));
      $slug = strtolower((string) preg_replace('/[^a-z0-9]/i', '', trim((string) $term->label())));

      $count = (int) $node_storage->getQuery()
        ->accessCheck(TRUE)
        ->condition('type', 'car')
        ->condition('status', 1)
        ->condition('field_category_term', $tid)
        ->count()
        ->execute();

      $model_label = $count === 1 ? 'model available' : 'models available';
      $href = '/taxonomy/term/' . $tid;

      $cards .= '<a class="cs-cat-card cs-cat-card--' . Html::escape(strtolower($slug)) . '" href="' . $href . '">'
        . '<div class="cs-cat-card__img"></div>'
        . '<div class="cs-cat-card__body">';
      if ($desc !== '') {
        $cards .= '<p class="cs-cat-card__desc">' . $desc . '</p>';
      }
      $cards .= '<h3 class="cs-cat-card__name">' . $name . '</h3>'
        . '<span class="cs-cat-card__role">' . $count . ' ' . $model_label . '</span>'
        . '</div></a>';
    }

    $build['#markup'] = Markup::create('<div class="cs-cat-grid">' . $cards . '</div>');
    return $build;
  }

}
