<?php

/**
 * @file
 * template.php
 */

/* Enable jquery.ui tabs functionality */
drupal_add_library('system', 'ui.tabs');

/**
 * Overrides theme_menu_link().
 */
function medicago_menu_link(array $variables) {
  $element = $variables['element'];
  $sub_menu = '';

  $options = !empty($element['#localized_options']) ? $element['#localized_options'] : array();

  // Check plain title if "html" is not set, otherwise, filter for XSS attacks.
  $title = empty($options['html']) ? check_plain($element['#title']) : filter_xss_admin($element['#title']);

  // Ensure "html" is now enabled so l() doesn't double encode. This is now
  // safe to do since both check_plain() and filter_xss_admin() encode HTML
  // entities. See: https://www.drupal.org/node/2854978
  $options['html'] = TRUE;

  $href = $element['#href'];
  $attributes = !empty($element['#attributes']) ? $element['#attributes'] : array();

  if ($element['#below']) {
    // Prevent dropdown functions from being added to management menu so it
    // does not affect the navbar module.
    if (($element['#original_link']['menu_name'] == 'management') && (module_exists('navbar'))) {
      $sub_menu = drupal_render($element['#below']);
    }
    elseif ((!empty($element['#original_link']['depth'])) && ($element['#original_link']['depth'] >= 1)) {
      // Add our own wrapper.
      unset($element['#below']['#theme_wrappers']);
      $sub_menu = '<ul class="dropdown-menu">' . drupal_render($element['#below']) . '</ul>';

      // Generate as standard dropdown.
      $title .= ' <span class="caret"></span>';
      $attributes['class'][] = 'dropdown';

      // Set dropdown trigger element to # to prevent inadvertant page loading
      // when a submenu link is clicked.
      $options['attributes']['data-target'] = '#';
      $options['attributes']['class'][] = 'dropdown-toggle';
      $options['attributes']['data-toggle'] = 'dropdown';
    }
  }

  return '<li' . drupal_attributes($attributes) . '>' . l($title, $href, $options) . $sub_menu . "</li>\n";
}


/**
 * @ingroup tripal_feature
 */

function medicago_theme_tripal_feature_preprocess_tripal_feature_base(&$variables) {
  // we want to provide a new variable that contains the matched features.
  $feature = $variables['node']->feature;

  // get the featureloc src features
  $options = array(
    'return_array' => 1,
    'include_fk' => array(
      'srcfeature_id' => array(
        'type_id' => 1
      ),
    ),
  );
    $feature_loc = 0;
  $feature = chado_expand_var($feature, 'table', 'featureloc', $options);

  // because there are two foriegn keys in the featureloc table with the feature table
  // we have to access the records for each by specifying the field name after the table name:
  $ffeaturelocs = $feature->featureloc->feature_id;

  // now extract the sequences
  $featureloc_sequences = tripal_feature_load_featureloc_sequences($feature->feature_id, $ffeaturelocs);
  $feature->featureloc_sequences = $featureloc_sequences;


}

?>
