<?php

/**
 * This file is used to markup the variable add to cart.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-variation-swatches/variable.php.
 *
 * Available global vars:
 *
 * @var $attributes
 * @var $available_variations
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Variation_Swatches/Templates
 * @version     1.6.2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $product;

$form_classes = xt_woo_variation_swatches()->frontend()->get_form_classes();
$wrap_classes = xt_woo_variation_swatches()->frontend()->get_wrap_classes();

$is_single_product = xt_woovs_is_single_product();
$catalog_mode = false;

if (!$is_single_product) {

    $catalog_mode = xt_woovs_type_option_bool('catalog_mode', false);

    if ($catalog_mode) {

        $catalog_attributes = xt_woovs_type_option('catalog_mode_attributes');
        $catalog_attributes = XT_Framework_Customizer_Helpers::repeater_fields_string_to_array($catalog_attributes);

        $catalog_custom_attributes = xt_woovs_type_option('catalog_mode_custom_attributes', array());
        $catalog_custom_attributes = XT_Framework_Customizer_Helpers::repeater_fields_string_to_array($catalog_custom_attributes);

        $found_catalog_attribute = null;
        $first_attribute_name = null;

        foreach ($attributes as $attribute_name => $options) {

            if (empty($first_attribute_name)) {
                $first_attribute_name = $attribute_name;
            }

            if ((xt_woovs_search_attributes($catalog_attributes, 'attribute', $attribute_name) !== null || xt_woovs_search_attributes($catalog_custom_attributes, 'attribute', $attribute_name) !== null)) {
                $found_catalog_attribute = $attribute_name;
                break;
            }
        }

        if (empty($found_catalog_attribute) && !empty($first_attribute_name)) {
            $found_catalog_attribute = $first_attribute_name;
        }
    }
}

$attribute_keys = array_keys($attributes);
$variations_json = wp_json_encode($available_variations);

do_action('woocommerce_before_add_to_cart_form');

$form_action = xt_woovs_is_single_product() ? $product->get_permalink() : "";
?>

<form class="variations_form cart <?php echo esc_attr($form_classes); ?>" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $form_action)); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint($product->get_id()); ?>" data-product_variations="<?php echo function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true); ?>">

    <?php do_action('woocommerce_before_variations_form'); ?>

    <?php
    if (empty($available_variations) && false !== $available_variations) : ?>
        <p class="stock out-of-stock"><?php esc_html_e('This product is currently out of stock and unavailable.', 'woocommerce'); ?></p>
    <?php else : ?>

        <div class="<?php echo esc_attr($wrap_classes); ?>">
            <?php if ($is_single_product) : ?>

                <table class="variations" cellspacing="0">
                    <tbody>
                        <?php foreach ($attributes as $attribute_name => $options) : ?>
                            <?php
                            $key = strtolower($attribute_name);
                            $default_option = !empty($selected_attributes[$key]) ? $selected_attributes[$key] : '';
                            ?>
                            <td data-attribute="<?php echo esc_attr(sanitize_title($attribute_name)); ?>">
                                <div class="label xt-swatches">
                                    <label for="<?php echo esc_attr(sanitize_title($attribute_name)); ?>">
                                        <?php echo wc_attribute_label($attribute_name); // WPCS: XSS ok. 
                                        ?><span>:</span>
                                    </label>
                                    <?php if (!empty($default_option)) : ?>
                                        <span class="xt_woovs-attribute-value"><?php echo esc_html($default_option); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="value">
                                    <?php
                                    wc_dropdown_variation_attribute_options(array(
                                        'options' => $options,
                                        'attribute' => $attribute_name,
                                        'product' => $product,
                                        'is_single' => true
                                    ));
                                    echo end($attribute_keys) === $attribute_name ? wp_kses_post(apply_filters('woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__('Clear', 'woocommerce') . '</a>')) : '';
                                    ?>
                                </div>
                            </td>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php else : ?>

                <div class="variations">
                    <?php
                    foreach ($attributes as $attribute_name => $options) :

                        wc_dropdown_variation_attribute_options(array(
                            'options' => $options,
                            'attribute' => $attribute_name,
                            'product' => $product,
                            'is_single' => false,
                            'catalog_mode' => $catalog_mode,
                            'catalog_mode_skipped_attribute' => $catalog_mode && ($attribute_name !== $found_catalog_attribute)
                        ));

                        echo end($attribute_keys) === $attribute_name ? wp_kses_post(apply_filters('woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__('Clear', 'woocommerce') . '</a>')) : '';

                    endforeach;
                    ?>
                </div>

            <?php endif; ?>
        </div>
        <?php if (!$catalog_mode) : ?>
            <div class="single_variation_wrap">
                <?php
                /**
                 * Hook: woocommerce_before_single_variation.
                 */
                do_action('woocommerce_before_single_variation');

                /**
                 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
                 *
                 * @since 2.4.0
                 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
                 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
                 */

                if ($is_single_product) {

                    do_action('woocommerce_single_variation');
                } else {

                    $on_demand_enabled = xt_woo_variation_swatches()->frontend()->on_demand_enabled();

                    $button_classes = implode(
                        ' ',
                        array_filter(
                            array(
                                'button',
                                'product_type_' . $product->get_type(),
                                $product->is_purchasable() && $product->is_in_stock() ? 'single_add_to_cart_button' : '',
                                $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                                $product->supports('ajax_add_to_cart') && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
                            )
                        )
                    );

                ?>
                    <div class="woocommerce-variation single_variation" style="display: none;"></div>
                    <div class="woocommerce-variation-add-to-cart xt_woovs-variation-add-to-cart variations_button">

                        <?php
                        if (xt_woovs_option_bool('archives_show_quantity_field')) {

                            $quantity_display = xt_woovs_option('archives_quantity_field_display', 'block');
                            $class = 'xt_woovs-quantity-wrap-' . $quantity_display;

                            $quantity_field = woocommerce_quantity_input(
                                array(
                                    'min_value' => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
                                    'max_value' => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
                                    'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
                                ),
                                null,
                                false
                            );

                            if (!empty($quantity_field)) {

                                echo '<div class="xt_woovs-quantity-wrap ' . esc_attr($class) . '">';

                                do_action('woocommerce_before_add_to_cart_quantity');

                                echo ($quantity_field);

                                do_action('woocommerce_after_add_to_cart_quantity');

                                echo '</div>';
                            }
                        }
                        ?>

                        <?php
                        if (!$on_demand_enabled) {
                            do_action('xt_woovs_before_add_to_cart_button');
                        }
                        ?>
                        <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="<?php echo esc_attr($button_classes); ?>"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>
                        <?php
                        if (!$on_demand_enabled) {
                            do_action('xt_woovs_after_add_to_cart_button');
                        }
                        ?>

                        <input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
                        <input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
                        <input type="hidden" name="variation_id" class="variation_id" value="0" />
                    </div>

                <?php
                }

                /**
                 * Hook: woocommerce_after_single_variation.
                 */
                do_action('woocommerce_after_single_variation');
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php do_action('woocommerce_after_variations_form'); ?>
</form>

<?php
do_action('woocommerce_after_add_to_cart_form');
