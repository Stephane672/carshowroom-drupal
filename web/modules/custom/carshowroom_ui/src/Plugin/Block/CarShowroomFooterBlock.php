<?php

namespace Drupal\carshowroom_ui\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\TranslatableMarkup;

#[Block(
  id: 'carshowroom_footer',
  admin_label: new TranslatableMarkup('Car Showroom footer'),
  category: new TranslatableMarkup('Car Showroom'),
)]
class CarShowroomFooterBlock extends BlockBase {

  public function defaultConfiguration() {
    return [
      'facebook' => 'https://www.facebook.com/',
      'instagram' => 'https://www.instagram.com/julian___ls/',
      'linkedin' => 'https://www.linkedin.com/',
      'copyright' => 'Car Showroom Mauritius. All rights reserved.',
    ] + parent::defaultConfiguration();
  }

  public function blockForm($form, FormStateInterface $form_state) {
    foreach (['facebook' => 'Facebook URL', 'instagram' => 'Instagram URL', 'linkedin' => 'LinkedIn URL', 'copyright' => 'Copyright text'] as $key => $label) {
      $form[$key] = ['#type' => 'textfield', '#title' => $label, '#default_value' => $this->configuration[$key], '#maxlength' => 255];
    }
    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    foreach (['facebook', 'instagram', 'linkedin', 'copyright'] as $key) {
      $this->configuration[$key] = $form_state->getValue($key);
    }
  }

  public function build() {
    $c = $this->configuration;
    $u = fn($k) => Html::escape((string) $c[$k]);
    $icons = '<a class="cs-social" href="' . $u('facebook') . '" aria-label="Facebook" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><text x="12" y="19" text-anchor="middle" font-size="20" font-weight="700" font-family="Arial,sans-serif" fill="currentColor">f</text></svg></a>'
      . '<a class="cs-social" href="' . $u('instagram') . '" aria-label="Instagram" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r="1" fill="currentColor" stroke="none"/></svg></a>'
      . '<a class="cs-social" href="' . $u('linkedin') . '" aria-label="LinkedIn" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><text x="12" y="17" text-anchor="middle" font-size="12" font-weight="700" font-family="Arial,sans-serif" fill="currentColor">in</text></svg></a>';
    $links = '<a href="/user/login">Admin</a>';
    $html = '<footer class="cs-footer"><div class="cs-footer-top"><div class="cs-socials">' . $icons . '</div><nav class="cs-footer-nav" aria-label="Footer">' . $links . '</nav></div>'
      . '<div class="cs-footer-bottom">&copy; ' . date('Y') . ' ' . Html::escape((string) $c['copyright']) . '</div></footer>';
    return [
      '#markup' => Markup::create($html),
      '#cache' => ['max-age' => 86400],
    ];
  }

}
