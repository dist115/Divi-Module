<?php
/*
Plugin Name: Custom Divi Module
Plugin URI:  
Description: 
Version:     1.0.0
Author: 
Author URI:  
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: cdm
Domain Path: /languages

Custom Divi Module is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Custom Divi Module is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Cpm Custom Divi Module. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/


if (!function_exists('ccdm_hotel_public_enqueue_scripts')) {
	function ccdm_hotel_public_enqueue_scripts()
	{
		wp_enqueue_style('ccdm-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
		wp_enqueue_style('ccdm-hotel-public-style', plugin_dir_url(__FILE__) . 'assets/css/public-style.css');    // Enqueue Slick Carousel styles

		wp_enqueue_script('ccdm-hotel-public-script', plugin_dir_url(__FILE__) . 'assets/js/public-scripts.js', array('jquery'), '1.0', true);
		wp_localize_script(
			'ccdm-hotel-public-script',
			'cpmAjax',
			array(
				'ajax_url' => esc_url(admin_url('admin-ajax.php')),
				'ccdm_plugin_url' => plugins_url(),
			)
		);
	}
	add_action('wp_enqueue_scripts', 'ccdm_hotel_public_enqueue_scripts');
}


if (!function_exists('ccdm_initialize_extension')) :
	/**
	 * Creates the extension's main class instance.
	 *
	 * @since 1.0.0
	 */


	function ccdm_initialize_extension()
	{
		require_once plugin_dir_path(__FILE__) . 'includes/CpmCustomDiviModule.php';
	}
	add_action('divi_extensions_init', 'ccdm_initialize_extension');
endif;


// sw start 
/**
 * Function for lazy loading hotel data.
 *
 * @param array $args The query arguments.
 * @throws WP_Exception Exception when there is an issue with WP_Query.
 */
add_action('wp_ajax_ccdmLazyLoader', 'ccdm_hotel_lazy_loader');
add_action('wp_ajax_nopriv_ccdmLazyLoader', 'ccdm_hotel_lazy_loader');
function ccdm_hotel_lazy_loader()
{

	// $layout_style = $_POST['layout'];
	// $grid_number = $_POST['gridCount'];
	$show_images = $_POST['showImage'];
	$show_excerpt = $_POST['showExc'];
	$excerpt_length = $_POST['exclenth'];
	$read_more = $_POST['showReadMore'];
	$taxonomy =  $_POST['taxonomy'];

	$args = $_POST['query'];
	$args['paged'] = $_POST['page'] + 1; // next page of posts
	$query = new WP_Query($args); // or you can use query_posts() for custom queries

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();

			$terms = get_the_terms(get_the_ID(), $taxonomy);
			if ($terms && !is_wp_error($terms)) {
				$term_slugs = array();
				foreach ($terms as $term) {
					$term_slugs[] = $term->slug;
				}
				$all_term_slugs = implode(' ', $term_slugs);
			}

			$desc = get_the_excerpt();

			if (!empty($desc)) {
				$exc_desc = mb_substr($desc, 0, $excerpt_length) . '...';
			} else {
				$exc_desc = '';
			}

?>
			<div class="_slider <?php echo $all_term_slugs; ?>">
				<?php
				if ($show_images == 'on' && get_the_post_thumbnail_url()) {
				?>
					<ul class="_slider-1">
						<li><img src="<?php echo get_the_post_thumbnail_url(); ?>" ></li>
					</ul>
				<?php } ?>
				<div class="carousel-content-wrapper">
					<h3>
						<?php the_title(); ?>
						</h2>
						<?php if ($show_excerpt) {
							echo '<p>' . $exc_desc . '</p>';
						} ?>
						<div class="button-wrappers">
							<?php if ($read_more == 'on') { ?>
								<a href="<?php echo get_post_permalink(); ?>" class="slider-more-btn">
									<?php esc_html_e('Read More', 'cdm') ?>
								</a>
							<?php } ?>


						</div>
				</div>
			</div>
			</div>
<?php
		}
		wp_reset_postdata();
	} else {
		esc_html_e('No Posts Found', 'cpm-cdm');
	}

	wp_die();
}
