<?php

namespace Drupal\ajax_form\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Ajax form routes.
 */
class AjaxFormController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
