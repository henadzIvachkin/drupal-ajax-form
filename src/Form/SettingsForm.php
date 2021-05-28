<?php

namespace Drupal\ajax_form\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Ajax form settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The array to store form values while editing.
   *
   * @var array
   */
  protected $dataArray;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ajax_form_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ajax_form.settings'];
  }

  /**
   * Get product settings row for common table with additional fields.
   *
   * @param array $product
   *   Product data array.
   *
   * @return array
   *   Form array for single row.
   */
  protected function getProductTableRow(array $product = NULL) {
    $field_row = [
      '#attributes' => [
        'class' => [
          'draggable',
          'tabledrag-root',
        ],
      ],
      'id' => [
        '#plain_text' => $product['id'],
      ],
      'product_name' => [
        '#plain_text' => !empty($product['name']) ? $product['name'] : '',
      ],
      'product_form' => $this->getProductForm($product),
      'weight' => [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $product['name']]),
        '#title_display' => 'invisible',
        '#attributes' => ['class' => ['table-sort-weight']],
        '#default_value' => !empty($product['weight']) ? $product['weight'] : 0,
      ],
    ];
    return $field_row;
  }

  /**
   * Get product form.
   *
   * @param array $product
   *   Product data array.
   *
   * @return array
   *   Form array.
   */
  protected function getProductForm(array $product) {
    $product_form = [];
    if ($product['open'] ?? FALSE) {
      $product_form['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Name'),
        '#description' => $this->t('Product name length should be more than 4 symbols.'),
        '#required' => TRUE,
        '#default_value' => !empty($product['name']) ? $product['name'] : '',
        '#element_validate' => [
          [static::class, 'validateProductName'],
        ],
      ];
      $product_form['description'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Description'),
        '#default_value' => !empty($product['description']) ? $product['description'] : '',
      ];
      $product_form['save'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
        '#submit' => ['::multistepSubmit'],
        '#ajax' => [
          'callback' => '::multistepAjax',
          'wrapper' => 'products-table-wrapper',
          'effect' => 'fade',
        ],
        '#name' => $product['id'],
        '#op' => 'save',
      ];
      $product_form['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
        '#submit' => ['::multistepSubmit'],
        '#ajax' => [
          'callback' => '::multistepAjax',
          'wrapper' => 'products-table-wrapper',
          'effect' => 'fade',
        ],
        '#name' => $product['id'],
        '#op' => 'cancel',
        '#limit_validation_errors' => [],
      ];

    }
    else {
      $product_form['settings_edit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Edit'),
        '#submit' => ['::multistepSubmit'],
        '#ajax' => [
          'callback' => '::multistepAjax',
          'wrapper' => 'products-table-wrapper',
          'effect' => 'fade',
        ],
        '#name' => $product['id'],
        '#op' => 'edit',
        '#prefix' => '<div class="field-plugin-settings-edit-wrapper">',
        '#suffix' => '</div>',
        '#limit_validation_errors' => [],
      ];
      $product_form['summary'] = [
        '#type' => 'inline_template',
        '#template' => '<div class="field-plugin-summary">{{ summary|safe_join("<br />") }}</div>',
        '#context' => [
          'summary' => [
            $product['name'],
            $product['description'],
          ],
        ],
      ];
    }
    $product_form['delete_button'] = [
      '#type' => 'button',
      '#value' => $this->t('Delete item'),
      '#name' => $product['id'],
      '#attributes' => [
        'id' => $product['id'],
        'class' => ['show-delete-dialog'],
      ],
    ];
    $product_form['delete_dialog'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'visually-hidden',
          'delete-dialog-container',
        ],
        'id' => 'delete-dialog-container-' . $product['id'],
      ],
      'label' => [
        '#type' => 'label',
        '#title' => $this->t('Are you sure?'),
      ],
      'delete' => [
        '#type' => 'submit',
        '#value' => $this->t('Delete'),
        '#submit' => ['::multistepSubmit'],
        '#ajax' => [
          'callback' => '::multistepAjax',
          'wrapper' => 'products-table-wrapper',
          'effect' => 'fade',
        ],
        '#name' => $product['id'],
        '#op' => 'delete',
        '#limit_validation_errors' => [],
        '#attributes' => [
          'class' => [
            'delete-product',
            'button--danger',
          ],
        ],
      ],
      'cancel_deletion' => [
        '#type' => 'button',
        '#value' => $this->t('Cancel'),
        '#attributes' => [
          'id' => 'hide-delete-dialog-' . $product['id'],
          'class' => ['cancel-delete-product'],
        ],
      ],
    ];
    return $product_form;
  }

  /**
   * Validation callback for product name field.
   */
  public static function validateProductName($element, FormStateInterface $form_state, $form) {
    if (mb_strlen($element['#value']) < 4) {
      $form_state->setError($element, t('Product name length should be more than 4 symbols.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['settings']['products'] = [
      '#type' => 'table',
      '#prefix' => '<div id="products-table-wrapper">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
      '#header' => [
        $this->t('ID'),
        $this->t('Label'),
        $this->t('Product'),
        $this->t('Weight'),
      ],
      '#empty' => $this->t('No products added yet.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
        ],
      ],
      '#attached' => [
        'library' => ['ajax_form/ajax_form'],
      ],
    ];

    if ($form_state->getTriggeringElement()) {
      $products = $this->dataArray;
    }
    else {
      $products = $this->config('ajax_form.settings')->get('products');
      $this->dataArray = $products;
    }

    if (is_array($products)) {
      foreach ($products as $product) {
        $form['settings']['products'][$product['id']] = $this->getProductTableRow($product);
      }
    }
    else {
      // Add initial product.
      $init_id = 'pid_0';
      $product = [
        'id' => $init_id,
        'name' => 'Test 1',
        'description' => 'Test description',
        'weight' => -10,
      ];
      $form['settings']['products'][$product['id']] = $this->getProductTableRow($product);
      $this->dataArray[$init_id] = $product;
    }

    $form['settings']['actions'] = [
      '#type' => 'actions',
    ];
    $form['settings']['actions']['add_product'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'products-table-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Add more ajax callback.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['settings']['products'];
  }

  /**
   * Add one ajax handler.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    // Get last ID.
    $product_keys = array_keys($this->dataArray);
    natsort($product_keys);
    $last_id = array_pop($product_keys);
    $id = intval(str_replace('pid_', '', $last_id)) + 1;
    // Update weights.
    $values = $form_state->getValue('products');
    foreach ($values as $key => $product) {
      if (isset($this->dataArray[$key])) {
        $this->dataArray[$key]['weight'] = $product['weight'];
      }
    }
    // Sort products by weight.
    uasort($this->dataArray, 'Drupal\Component\Utility\SortArray::sortByWeightElement');

    $this->dataArray['pid_' . $id] = [
      'id' => 'pid_' . $id,
      'name' => 'Product ' . $id,
      'description' => 'Product description',
      'open' => FALSE,
      'weight' => 0,
    ];
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    // Get all products.
    $products = $this->dataArray;
    if (is_array($products)) {
      foreach ($products as $key => $product) {
        // Store weight.
        $products[$key]['weight'] = $form_values['products'][$key]['weight'];
        if (isset($product['open']) && $product['open']) {
          // Update product from form state.
          $products[$key]['name'] = $form_values['products'][$key]['product_form']['name'];
          $products[$key]['description'] = $form_values['products'][$key]['product_form']['description'];
          // Collapse product edit form.
          $products[$key]['open'] = FALSE;
        }
      }
    }
    // Sort products by weight.
    uasort($products, 'Drupal\Component\Utility\SortArray::sortByWeightElement');

    $this->config('ajax_form.settings')
      ->set('products', $products)
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Ajax callback.
   */
  public function multistepAjax($form, FormStateInterface $form_state) {
    return $form['settings']['products'];
  }

  /**
   * Form submission handler for product edit buttons.
   */
  public function multistepSubmit($form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    $values = $form_state->getValues();

    switch ($op) {
      case 'edit':
        $this->dataArray[$trigger['#name']]['open'] = TRUE;
        break;

      case 'save':
        $this->dataArray[$trigger['#name']] = [
          'id' => $trigger['#name'],
          'name' => $values['products'][$trigger['#name']]['product_form']['name'],
          'description' => $values['products'][$trigger['#name']]['product_form']['description'],
          'open' => FALSE,
        ];
        $this->dataArray[$trigger['#name']]['open'] = FALSE;
        break;

      case 'delete':
        $product_id = $trigger['#name'];
        unset($this->dataArray[$product_id]);
        break;

      case 'cancel':
        $this->dataArray[$trigger['#name']]['open'] = FALSE;
        break;
    }

    $form_state->setRebuild();
  }

}
