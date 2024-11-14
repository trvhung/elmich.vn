<?php

defined('ABSPATH') || exit;

get_header('shop');
?>
<?php
$term = get_queried_object();
$parent = $term->parent;
$term_id = $term->term_id;

// Custom order child cat 
$custom_orders = array(
	17 => array(32, 33, 34, 121, 35, 31, 30),
	39 => array(46, 40, 51, 52, 44, 49, 56, 41, 43)
);

// Lấy term_id của term hiện tại (ví dụ: 17 hoặc 39)
$current_term_id = $term->term_id;

// Kiểm tra xem term_id hiện tại có thứ tự mong muốn không
if (isset($custom_orders[$current_term_id])) {
	// Lấy thứ tự mong muốn cho term_id hiện tại
	$desired_order = $custom_orders[$current_term_id];

	// Lấy childrens cho term_id hiện tại
	$childrens = get_terms($term->taxonomy, array(
		'parent' => $term->term_id,
		'hide_empty' => false,
	));

	// Tạo một mảng để ánh xạ term_id với vị trí trong mảng $desired_order
	$term_order_mapping = array_flip($desired_order);

	// Sắp xếp $childrens dựa trên thứ tự đã chỉ định
	usort($childrens, function ($a, $b) use ($term_order_mapping) {
		$order_a = isset($term_order_mapping[$a->term_id]) ? $term_order_mapping[$a->term_id] : PHP_INT_MAX;
		$order_b = isset($term_order_mapping[$b->term_id]) ? $term_order_mapping[$b->term_id] : PHP_INT_MAX;

		return $order_a - $order_b;
	});

	// Bây giờ $childrens sẽ được sắp xếp theo thứ tự mong muốn
} else {
	// Nếu không có thứ tự mong muốn, thực hiện lấy childrens mặc định
	$childrens = get_terms($term->taxonomy, array(
		'parent' => $term->term_id,
		'hide_empty' => false,
	));
}
// Kiểm tra nếu đang ở trong category ID 60
$is_category_60 = $term_id == 60;

// Nếu đang ở trong category ID 60, thêm child category ID 104 vào danh sách
if ($is_category_60) {
	$child_104 = get_term(104, $term->taxonomy);
	if ($child_104 && !in_array($child_104, $childrens)) {
		array_unshift($childrens, $child_104);
	}
}
$is_category_131 = $term_id == 131;
// Nếu đang ở trong category ID 131, thêm child category ID 104 vào danh sách
if ($is_category_131) {
	$child_99 = get_term(99, $term->taxonomy);
	if ($child_99 && !in_array($child_99, $childrens)) {
		array_unshift($childrens, $child_99);
	}
}
// Kiểm tra nếu đang ở trong category ID 198
$is_category_203 = $term_id == 203;

// Nếu đang ở trong category ID 198, thêm các child category vào danh sách
if ($is_category_203) {
	// Mảng chứa danh sách các child category ID
	$child_category_ids = array(188, 153, 41, 42, 43, 47, 164, 201, 52, 120, 34, 56, 53, 82, 51, 46, 99, 182);

	// Duyệt qua mảng child category IDs và thêm vào danh sách nếu chưa có
	foreach ($child_category_ids as $child_id) {
		$child_term = get_term($child_id, $term->taxonomy);

		if ($child_term && !in_array($child_term, $childrens)) {
			array_unshift($childrens, $child_term);
		}
	}
}

$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$baseUrl = explode("?", $url);

$filter_tag = '';
if (isset($_GET['filter_tag']) && !empty($_GET['filter_tag'])) {
	$filter_tag = $_GET['filter_tag'];
}

// end custom order child cate
// end custom order child cate
?>

<div class="product-category">
	<?php
		get_template_part('components/banner');
		get_template_part('components/breadcrumb2');
	?>
	<div class="container">
		<div class="elmich-head-page">
			<h1 class="elmich_title_cat">
				<?php woocommerce_page_title(); ?>
			</h1>
			<div class="filter-tag">
				<div class="item-tag <?php if ($filter_tag == 'hang-moi') {echo 'active';} ?>">
					<a href="<?php echo $baseUrl[0] ?>?filter_tag=hang-moi">Hàng mới</a>
				</div>
				<div class="item-tag <?php if ($filter_tag == 'hang-duoc-yeu-thich') {echo 'active';} ?>">
					<a href="<?php echo $baseUrl[0] ?>?filter_tag=hang-duoc-yeu-thich">Được yêu thích</a>
				</div>
				<div class="item-tag <?php if ($filter_tag == 'pho-bien') {echo 'active';} ?>">
					<a href="<?php echo $baseUrl[0] ?>?filter_tag=pho-bien"> Phổ biến</a>
				</div>
				<div class="item-tag <?php if ($filter_tag == 'giao-nhanh') {echo 'active';} ?>">
					<a href="<?php echo $baseUrl[0] ?>?filter_tag=giao-nhanh">Giao nhanh</a>
				</div>
			</div>
		</div>
	</div>

	<?php if ($childrens && !is_search() && ($parent == 0)) { ?>
		<section class="elmich_cat_page">
			<div class="container">
				<div class="bic_prod__list">
					<?php foreach ($childrens as $child) {
						$child_title = $child->name;
						$child_slug = $child->slug;
	
						global $post;
	
						if (isset($_GET['filter_tag']) && !empty($_GET['filter_tag'])) {
							$args = array(
								'post_type' => 'product',
								'post_status' => 'publish',
								'posts_per_page' => 8,
								'orderby' => 'desc',
								'relation' => 'AND',
								'tax_query' => array(
									array(
										'taxonomy' => 'product_cat',
										'field' => 'id',
										'terms' => $child->term_id,
										'operator' => 'IN'
									),
									array(			
										'taxonomy' => 'product_tag',
										'field'    => 'slug',
										'terms'    => $_GET['filter_tag'],
										'operator' => 'IN'
									)
								),
							);
						} else {
							$args = array(
								'post_type' => 'product',
								'post_status' => 'publish',
								'posts_per_page' => 8,
								'orderby' => 'desc',
								'tax_query' => array(
									array(
										'taxonomy' => 'product_cat',
										'field' => 'id',
										'terms' => $child->term_id
									)
								),
							);
						}
						
						$related_items = new WP_Query($args);
						if ($related_items->have_posts()) : ?>
							<div class="elmich_child_cat">
								<h2 class="elmich_child_title">
									<a href="<?php echo $child_slug; ?>">
										<?php echo $child_title; ?>
									</a>
								</h2>
								<div class="row elmich_product_tem">
									<?php while ($related_items->have_posts()) :
										$related_items->the_post();
									?>
										<div class="bic_p__item elmich_product_grid col-lg-3 col-md-4 col-6">
											<?php wc_get_template_part('content', 'product2'); ?>
										</div>
									<?php endwhile; ?>
								</div>
								<div class="view-more">
									<a href="<?php echo $child_slug; ?>">
										Xem thêm
										<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
											<g clip-path="url(#clip0_33_723)">
												<path d="M1.3335 7.99992C1.3335 11.6819 4.31816 14.6666 8.00016 14.6666C11.6822 14.6666 14.6668 11.6819 14.6668 7.99992C14.6668 4.31792 11.6822 1.33325 8.00016 1.33325" stroke="#E1081B" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M12 8C12 5.791 10.209 4 8 4C5.791 4 4 5.791 4 8C4 10.209 5.791 12 8 12" stroke="#E1081B" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
											</g>
											<defs>
												<clipPath id="clip0_33_723">
													<rect width="16" height="16" fill="white"/>
												</clipPath>
											</defs>
										</svg>
									</a>
								</div>
							</div>
							<?php wp_reset_postdata(); ?>
						<?php endif;
						?>
					<?php } ?>
				</div>
			</div>
		</section>
	<?php } else { ?>
		<section class="elmich_cat_page product_group">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<?php if (woocommerce_product_loop()) { ?>
							<div class="row elmich_product_tem" id="gini-archive_post">
								<?php if (wc_get_loop_prop('total')) {
									while (have_posts()) {
										the_post(); ?>
										<div class="bic_p__item elmich_product_grid col-6 col-md-4 col-lg-3 gini_archive_template">
											<?php wc_get_template_part('content', 'product2'); ?>
										</div>
								<?php }
								} ?>
							</div>
							<div class="d-none">
								<?php the_posts_navigation(); ?>
							</div>
						<?php } else {
							/**
							 * Hook: woocommerce_no_products_found.
							 *
							 * @hooked wc_no_products_found - 10
							 */
							do_action('woocommerce_no_products_found');
						}
						?>
						<div class="page-load-status ">
							<div class="loader-ellips infinite-scroll-request text-center">
								<span class="loader-ellips__dot"></span>
								<span class="loader-ellips__dot"></span>
								<span class="loader-ellips__dot"></span>
								<span class="loader-ellips__dot"></span>
							</div>
							<p class="infinite-scroll-last"></p>
							<p class="infinite-scroll-error"></p>
						</div>
						<div class="text-center view-more-button_section d-none">
							<button class="view-more-button post-loadmore">
								<?php _e('Xem thêm', 'gnws') ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</section>
	<?php } ?>
<!-- 	<?php echo do_shortcode("[elementor-template id='50178']"); ?> -->
</div>
<?php get_footer('shop');
