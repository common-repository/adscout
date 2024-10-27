<?php
defined('ABSPATH') or die('No script kiddies please!');

class AdScout_WooCommerce
{

    public static function product_data()
    {

        if (!is_product()) {
            return;
        }

        global $product;

        $category = $product->get_category_ids();
        $category_listing = [];
        foreach ($category as $cat) {
            $category = get_term_by('id', $cat, 'product_cat');
            $category_listing[] = $category->name;
        }

        $currency = get_woocommerce_currency();
        $img = wp_get_attachment_url($product->get_image_id());
        $price = $product->get_price();
        $sale_price = $product->get_sale_price();
        $sku = $product->get_sku();
        $id = $product->get_id();
        $title = $product->get_title();

        $data = [
            'category' => $category_listing,
            'currency' => $currency,
            'img' => $img,
            'price' => $price,
            'sale_price' => $sale_price,
            'sku' => $sku,
            'id' => $id,
            'title' => $title
        ];

        $plugin = new AdScout();

        wp_enqueue_script( $plugin->get_plugin_name() . '-public-product' , $plugin->adscout_plugin_root_url . 'public/js/adscout-public-product-data.js', array(),  $plugin->get_version(), array(
            'strategy' => 'defer',
            'in_footer' => true,
        ));
        wp_localize_script($plugin->get_plugin_name() . '-public-product' , 'adscout_product_object', $data);


    }


    /**
     * Generate a feed of products and save it to a CSV file.
     * @return array
     *
     * @since 2.0.0
     */
    public static function generate_product_feed(): array
    {

        $batch_size = 750; //Lower the number in case of server timeouts

        $upload_dir = wp_upload_dir();
        $feed_dir = trailingslashit($upload_dir['basedir']) . 'as-product-feed';

        if (!file_exists($feed_dir)) {
            wp_mkdir_p($feed_dir);
        }

        // Delete the feed file if it exists
        $feed_file = $feed_dir . '/product-feed.csv';
        $feed_file_url = $upload_dir ['baseurl'] . '/as-product-feed/product-feed.csv';
        if (file_exists($feed_file)) {
            wp_delete_file($feed_file);
        }

        // Count the number of products
        $total_products = wp_count_posts('product')->publish;

        // Loop through products in batches
        for ($offset = 0; $offset < $total_products; $offset += $batch_size) {
            $args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => $batch_size,
                'offset' => $offset,
                'no_found_rows' => true,
            );

            $products_query = new WP_Query($args);

            if ($products_query->have_posts()) {

                global $wp_filesystem;
                if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ) {
                    $creds = request_filesystem_credentials( site_url() );
                    wp_filesystem( $creds );
                }

                if (!$wp_filesystem->exists($feed_file)) {
                    $wp_filesystem->put_contents($feed_file, '\xEF\xBB\xBF', 0664);
                    $wp_filesystem->put_contents($feed_file, 'product_id,title,link,price,sale_price,image,sku,category' . PHP_EOL  , 0664);
                }

                while ($products_query->have_posts()) {
                    $products_query->the_post();
                    $product = wc_get_product(get_the_ID());

                    // Get product categories
                    if ($product->get_meta('item_group_id') > 0) {
                        $cat_obj = get_the_terms($product->get_meta('item_group_id'), 'product_cat');
                    } else {
                        $cat_obj = get_the_terms($product->get_id(), 'product_cat');
                    }


                    $category_hierarchy = array(); // This will store the category hierarchy strings

                    if ($cat_obj) {
                        foreach ($cat_obj as $value) {
                            $category_names = array(); // Store category names and ancestors

                            // Get ancestors of the current category
                            $ancestors = get_ancestors($value->term_id, 'product_cat');

                            if (!empty($ancestors)) {
                                foreach ($ancestors as $ancestor_id) {
                                    $ancestor = get_term($ancestor_id, 'product_cat');
                                    array_unshift($category_names, $ancestor->name); // Add ancestor to the beginning
                                }
                            }
                            // Get the current category name
                            $category_names[] = $value->name;

                            // Combine the category names and ancestors with ' > ' separator
                            $category_hierarchy[] = implode(' > ', $category_names);
                        }
                    }

                    // Implode the $category_hierarchy array with commas
                    $categories = implode(', ', $category_hierarchy) ;

                    // Now, $final_category_hierarchy contains the desired category hierarchy for the product, separated by commas


                    // Include variations if it's a variable product
                    if ($product->is_type('variable')) {
                        $variations = $product->get_available_variations();
                        foreach ($variations as $variation) {
                            $variation_product = wc_get_product($variation['variation_id']);
                            $parent = wc_get_product($variation_product->get_parent_id());

                            $image = get_the_post_thumbnail_url($variation['variation_id'], 'full') ? get_the_post_thumbnail_url($variation['variation_id'], 'full') : get_the_post_thumbnail_url($parent->get_id(), 'full');

                            // Prepare variation data
                            $variation_data = array(
                                $variation_product->get_id(),
                                '"' . $variation_product->get_title() . '"',
                                get_permalink($variation['variation_id']),
                                $variation_product->get_regular_price(),
                                $variation_product->get_sale_price(),
                                $image,
                                $variation_product->get_sku(),
                                '"' . $categories . '"' . PHP_EOL,
                            );

                            $import_data = $wp_filesystem->get_contents($feed_file);
                            $import_data .= implode(',', $variation_data);

                            $wp_filesystem->put_contents($feed_file,$import_data,0664);
                        }
                    } else {

                        $data = array(
                            get_the_ID(),
                            '"' . get_the_title() . '"',
                            get_permalink(),
                            $product->get_regular_price(),
                            $product->get_sale_price(),
                            get_the_post_thumbnail_url(get_the_ID(), 'full'),
                            $product->get_sku(),
                            '"' . $categories . '"' . PHP_EOL,
                        );

                        $import_data = $wp_filesystem->get_contents($feed_file);
                        $import_data .= implode(',', $data);

                        $wp_filesystem->put_contents($feed_file,$import_data,0664);

                    }
                }

                wp_reset_postdata();
            }
        }
        $current_time = current_time('timestamp');
        $options = (new AdScout_Options());

        $options::set_option('as_feed_last_updated', gmdate('d.m.Y H:i:s', $current_time));
        $options::set_option('as_feed_last_updated_timestamp', $current_time);
        $options::set_option('as_feed_file_path', $feed_file);
        $options::set_option('as_feed_url', $feed_file_url);

        return array(
            'as_feed_last_updated' => $options::get_option('as_feed_last_updated'),
            'as_feed_url' => $options::get_option('as_feed_url'),
        );

    }

}