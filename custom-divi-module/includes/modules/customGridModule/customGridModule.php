<?php
class CCDM_CustomGridModule extends ET_Builder_Module
	// class CCDM_CustomGridModule extends ET_Builder_Module_Type_PostBased
{

	public $slug = 'ccdm_grid_module';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => '',
		'author' => 'Diwan',
		'author_uri' => '',
	);

	public function init()
	{
		// add_action('et_builder_ready', array($thigis, 'cpmoutputDynamicStyles'));

		$this->name = esc_html__('Post carousel', 'cpm-cdm');
		$this->plural = esc_html__('Post carousel', 'cpm-cdm');
		// $this->slug = 'ccdm_grid_module';
		// $this->vb_support = 'on';
		// $this->main_css_element = '%%order_class%% .ccdm_pb_post';
	}


	public function get_selectedterms($taxonomy = null)
	{

		if (is_null($taxonomy)) {
			$categories = get_terms();
		} else {
			$categories = get_terms(
				'category',
				array(
					'orderby' => 'count',
					'hide_empty' => 0,
				)
			);
		}

		foreach ($categories as $key => $value) {
			$output[$value->slug] = esc_html__($value->name, 'cpm-cdm');
		}

		return $output;
	}

	public static function get_posttype()
	{

		$list = array();
		$args = array(
			'public' => true,
		);
		$post_types = get_post_types($args, 'objects');
		$list = wp_list_pluck($post_types, 'label', 'name');

		return $list;
	}

	public static function get_taxonomies($taxonomy_type)
	{

		$lists = array();
		$taxonomy_objects = get_object_taxonomies($taxonomy_type, 'objects');
		if (!empty($taxonomy_objects)) {
			foreach ($taxonomy_objects as $values) {
				$lists[$values->name] = ucwords($values->label);
			}
		}

		return $lists;
	}

	public static function get_lists($taxonomies_slug)
	{

		$lists = array();
		$args = array(
			'taxonomy' => $taxonomies_slug,
			'hide_empty' => true,
		);
		$taxonomies = get_terms($args);
		if (!empty($taxonomies)) {
			foreach ($taxonomies as $values) {
				$lists[$values->term_id] = ucwords($values->name);
			}
		}

		return $lists;
	}

	private static $computed_depends_on = array();

	protected static function _get_taxonomies_fields()
	{

		$fields = array();
		foreach (self::get_posttype() as $posttype_slug => $posttype) {

			$fields['taxonomy_' . $posttype_slug] = array(
				'label' => esc_html__('Taxonomy', 'dita-divi-taxonomy'),
				'description' => esc_html__('Choose the type of taxonomies you want to display.', 'dita-divi-taxonomy'),
				'type' => 'select',
				'option_category' => 'configuration',
				'show_if' => array(
					'post_type' => $posttype_slug,
					// 'use_nearby_loop' => 'off',
					// 'use_relationship_posts' => 'off',
				),
				'options' => self::get_taxonomies($posttype_slug),
				'default' => key(self::get_taxonomies($posttype_slug)),
				'toggle_slug' => 'main_content',
				'computed_affects' => array(
					'__posts',
				),
			);
			array_push(self::$computed_depends_on, 'taxonomy_' . $posttype_slug);
			foreach (self::get_taxonomies($posttype_slug) as $taxonomies_slug => $taxonomy) {

				$fields['include_categories_' . $taxonomies_slug] = array(
					'label' => esc_html__('Included Taxonomy Terms', 'dita-divi-taxonomy'),
					'description' => esc_html__('Choose which taxonomies you would like to include in the feed.', 'dita-divi-taxonomy'),
					'type' => 'categories',
					'option_category' => 'configuration',
					'show_if' => array(
						'post_type' => $posttype_slug,
						'taxonomy_' . $posttype_slug => $taxonomies_slug,
						// 'use_nearby_loop' => 'off',
						// 'use_relationship_posts' => 'off',
					),
					// 'meta_categories' => array(
					// 	'current' => esc_html__('Current Category', 'cpm-cdm'),
					// ),
					'renderer_options' => array(
						'use_terms' => true,
						'term_name' => $taxonomies_slug,
					),
					'options' => self::get_lists($taxonomies_slug),
					'toggle_slug' => 'main_content',
					'computed_affects' => array(
						'__posts',
					),
				);
				array_push(self::$computed_depends_on, 'include_categories_' . $taxonomies_slug);
			}
		}

		return $fields;
	}

	public function get_fields()
	{

		$post_related_fields = array_merge(
			array(
				//header fields
				'heading' => array(
					'label' => esc_html__('Heading', 'cpm-cdm'),
					'type' => 'text',
					'option_category' => 'basic_option',
					'description' => esc_html__('Input your desired heading here.', 'cpm-cdm'),
					'toggle_slug' => 'main_content',
				),

				'view_all_text' => array(
					'label' => esc_html__('View All', 'cpm-cdm'),
					'type' => 'text',
					'option_category' => 'basic_option',
					'description' => esc_html__('Input additional text here.', 'cpm-cdm'),
					'toggle_slug' => 'main_content',
				),
				'view_all_text_link' => array(
					'label' => esc_html__('View All Text Link', 'cpm-cdm'),
					// 'type'            => 'url',
					'type' => 'text',
					'option_category' => 'basic_option',
					'description' => esc_html__('Input additional text link here.', 'cpm-cdm'),
					'toggle_slug' => 'main_content',
				),
				//post type fields
				'post_type' => array(
					'label' => esc_html__('Post Type', 'cpm-cdm'),
					'type' => 'select',
					'option_category' => 'configuration',
					'options' => $this->get_posttype(),
					'default' => key($this->get_posttype()),
					'description' => esc_html__('Choose posts of which post type you would like to display.', 'cpm-cdm'),
					'computed_affects' => array(
						'__posts',
					),
					'toggle_slug' => 'main_content',
					// 'show_if' => array(
					// 	'use_current_loop' => 'off',
					// ),
				)
			),
			self::_get_taxonomies_fields()
		);

		$fields = array(

			'layout_style' => array(
				'label' => et_builder_i18n('Layout'),
				'type' => 'select',
				'option_category' => 'layout',
				'options' => array(
					'0' => esc_html__('Vertical', 'cpm-cdm'),
					'1' => esc_html__('Horizontal', 'cpm-cdm'),
				),
				'description' => esc_html__('Toggle between the various blog layout types.', 'cpm-cdm'),
				'computed_affects' => array(
					'__posts',
				),
				'tab_slug' => 'advanced',
				'toggle_slug' => 'layout',
				'default_on_front' => '0',
			),
			'grid_count' => array(
				'label' => et_builder_i18n('Total Column'),
				'type' => 'select',
				'option_category' => 'layout',
				'options' => array(
					'2' => esc_html__('2 Column', 'cpm-cdm'),
					'3' => esc_html__('3 Column', 'cpm-cdm'),
					'4' => esc_html__('4 Column', 'cpm-cdm'),

				),

				'description' => esc_html__('Toggle between the various blog column counts.', 'cpm-cdm'),
				'computed_affects' => array(
					'__posts',
				),
				'tab_slug' => 'advanced',
				'toggle_slug' => 'layout',
				'default_on_front' => '2',
			),
			
			'posts_number' => array(
				'label' => esc_html__('Post Count', 'cpm-cdm'),
				'type' => 'text',
				'option_category' => 'configuration',
				'description' => esc_html__('Choose how much posts you would like to display per page.', 'cpm-cdm'),
				'computed_affects' => array(
					'__posts',
				),
				'toggle_slug' => 'main_content',
				'default' => 10,
			),
			
			'show_thumbnail' => array(
				'label' => esc_html__('Show Featured Image', 'cpm-cdm'),
				'type' => 'yes_no_button',
				'option_category' => 'configuration',
				'options' => array(
					'on' => et_builder_i18n('Yes'),
					'off' => et_builder_i18n('No'),
				),
				'description' => esc_html__('This will turn thumbnails on and off.', 'cpm-cdm'),
				'computed_affects' => array(
					'__posts',
				),
				'toggle_slug' => 'elements',
				'default_on_front' => 'on',
				'mobile_options' => true,
				'hover' => 'tabs',
			),
			'show_metadata' => array(
				'label' => esc_html__('Show Meta Data', 'cpm-cdm'),
				'type' => 'yes_no_button',
				'option_category' => 'configuration',
				'options' => array(
					'on' => et_builder_i18n('Yes'),
					'off' => et_builder_i18n('No'),
				),
				'description' => esc_html__('This will display meta information on and off.', 'cpm-cdm'),
				'computed_affects' => array(
					'__posts',
				),
				'toggle_slug' => 'elements',
				'default_on_front' => 'on',
				'mobile_options' => true,
				'hover' => 'tabs',
			),
			'ajax_lazyload' => array(
				'label' => esc_html__('Lazy Load', 'cpm-cdm'),
				'type' => 'yes_no_button',
				'option_category' => 'configuration',
				'options' => array(
					'on' => et_builder_i18n('Yes'),
					'off' => et_builder_i18n('No'),
				),
				'description' => esc_html__('This will turn lazyload on and off. It only works with vertical layout', 'cpm-cdm'),
				'computed_affects' => array(
					'__posts',
				),
				'toggle_slug' => 'elements',
				'default_on_front' => 'on',
				'mobile_options' => true,
				'hover' => 'tabs',
			),

			'excerpt_length' => array(
				'label' => esc_html__('Excerpt Length', 'cpm-cdm'),
				'description' => esc_html__('Define the length of automatically generated excerpts. Leave blank for default ( 270 ) ', 'cpm-cdm'),
				'type' => 'text',
				'default' => '270',
				'computed_affects' => array(
					'__posts',
				),
				'depends_show_if' => 'off',
				'toggle_slug' => 'main_content',
				'option_category' => 'configuration',
			),
			'read_more_btn_text' => array(
				'label' => esc_html__('Read More', 'cpm-cdm'),
				'type' => 'text',
				'option_category' => 'basic_option',
				'description' => esc_html__('Text for Read More button.', 'cpm-cdm'),
				'toggle_slug' => 'main_content',
				'default' => esc_html__('Read More', 'cpm-cdm'),
			),
			'show_more' => array(
				'label' => esc_html__('Show Read More Button', 'cpm-cdm'),
				'type' => 'yes_no_button',
				'option_category' => 'configuration',
				'options' => array(
					'off' => et_builder_i18n('No'),
					'on' => et_builder_i18n('Yes'),
				),
				'depends_show_if' => 'off',
				'description' => esc_html__('Here you can define whether to show "read more" link after the excerpts or not.', 'cpm-cdm'),
				'computed_affects' => array(
					'__posts',
				),
				'toggle_slug' => 'elements',
				'default_on_front' => 'on',
				'mobile_options' => true,
				'hover' => 'tabs',
			),
			'book_btn_text' => array(
				'label' => esc_html__('Book', 'cpm-cdm'),
				'type' => 'text',
				'option_category' => 'basic_option',
				'description' => esc_html__('Text for book button.', 'cpm-cdm'),
				'toggle_slug' => 'main_content',
				'default' => esc_html__('Book', 'cpm-cdm'),
			),
			'show_book_btn' => array(
				'label' => esc_html__('Show Book Button', 'cpm-cdm'),
				'type' => 'yes_no_button',
				'option_category' => 'configuration',
				'options' => array(
					'off' => et_builder_i18n('No'),
					'on' => et_builder_i18n('Yes'),
				),
				'depends_show_if' => 'off',
				'description' => esc_html__('Here you can define whether to show "Book" link after the excerpts or not.', 'cpm-cdm'),
				'computed_affects' => array(
					'__posts',
				),
				'toggle_slug' => 'elements',
				'default_on_front' => 'on',
				'mobile_options' => true,
				'hover' => 'tabs',
			),
			
			'show_excerpt' => array(
				'label' => esc_html__('Show Excerpt', 'cpm-cdm'),
				'description' => esc_html__('Turn excerpt on and off.', 'cpm-cdm'),
				'type' => 'yes_no_button',
				'options' => array(
					'on' => et_builder_i18n('Yes'),
					'off' => et_builder_i18n('No'),
				),
				'default_on_front' => 'on',
				'computed_affects' => array(
					'__posts',
				),
				'depends_show_if' => 'off',
				'toggle_slug' => 'elements',
				'option_category' => 'configuration',
				'mobile_options' => true,
				'hover' => 'tabs',
			),
			
			'offset_number' => array(
				'label' => esc_html__('Post Offset Number', 'cpm-cdm'),
				'type' => 'text',
				'option_category' => 'configuration',
				'description' => esc_html__('Choose how many posts you would like to skip. These posts will not be shown in the feed.', 'cpm-cdm'),
				'toggle_slug' => 'main_content',
				'computed_affects' => array(
					'__posts',
				),
				'default' => 0,
			),
			
			'__posts' => array(
				'type' => 'computed',
				'computed_callback' => array('ET_Builder_Module_CustomBlog', 'get_blog_posts'),
				'computed_depends_on' => array_merge(
					self::$computed_depends_on,
					array(
						
						'post_type',
						'layout_style',
						'grid_count',
						'posts_number',
						'include_categories',
						'show_thumbnail',
						'show_content',
						'show_more',
						'show_excerpt',
						'use_manual_excerpt',
						'excerpt_length',
						'offset_number',
						'__page',
					)
				),
			),
			'__page' => array(
				'type' => 'computed',
				'computed_callback' => array('ET_Builder_Module_CustomBlog', 'get_blog_posts'),
				'computed_affects' => array(
					'__posts',
				),
			),
		);

		return array_merge($post_related_fields, $fields);
	}

	
	public function render($attrs, $content = null, $render_slug)
	{
		$heading = isset($this->props['heading']) ? esc_html($this->props['heading']) : '';
		$textViewAll = isset($this->props['view_all_text']) ? esc_html($this->props['view_all_text']) : '';
		$urlViewAlllink = isset($this->props['view_all_text_link']) ? esc_html($this->props['view_all_text_link']) : '#';

		$post_type = isset($this->props['post_type']) ? $this->props['post_type'] : 'post';
		$taxonomy = isset($this->props['taxonomy_' . $post_type]) ? $this->props['taxonomy_' . $post_type] : 'category';
		$include_categories = isset($this->props['include_categories_' . $taxonomy]) ? explode(',', $this->props['include_categories_' . $taxonomy]) : array('0');
		$posts_number = $this->props['posts_number'];
		$show_images = $this->props['show_thumbnail'];
		$read_more = $this->props['show_more'];
		$show_excerpt = $this->props['show_excerpt'];
		$excerpt_length = $this->props['excerpt_length'];
		$offset_number = $this->props['offset_number'];
		$layout_style1 = $this->props['layout_style'];
		$read_more_btn_text = $this->props['read_more_btn_text'];

		// sw code starts
		$ajax_lazyload = $this->props['ajax_lazyload'];
		// sw code ends

		if ($layout_style1 == '0' || $layout_style1 == 'Vertical') {
			// vertical class
			$layout_style = '';
		} elseif ($layout_style1 == '1' || $layout_style1 == 'Horizontal') {
			// horizantal class
			$layout_style = '_slider-horizontal';
		} else {
			// default vertical class
			$layout_style = '';
		}
		$grid_number = $this->props['grid_count'];

		$args = array(
			'post_type' => $post_type,
			'posts_per_page' => $posts_number,

		);

		// if multiple taxonomies do this
		// if ($taxonomies && $include_categories) {
		//     // Add tax_query to the args
		//     $args['tax_query'] = array(
		//         'relation' => 'OR',
		//     );
		//     foreach ($taxonomies as $taxonomy) {
		//         $args['tax_query'][] = array(
		//             'taxonomy' => $taxonomy,
		//             'field'    => 'slug',  // You can use 'id', 'slug', or 'name'
		//             'terms'    => $include_categories,  // Replace with the attribute variable
		//         );
		//     }
		// }

		if (!empty($include_categories) && is_array($include_categories) && $include_categories[0] !== '') {
			$args['tax_query'] = array(
				array(
					'taxonomy' => $taxonomy,
					'field' => 'id',  // You can use 'id', 'slug', or 'name'
					'terms' => $include_categories,  // Use the entire array as terms
				),
			);
		}

		if ($offset_number > "0") {
			$args['offset'] = $offset_number;
		}

		$query = new WP_Query($args);

		ob_start();
		$i = 2;
		$styles = '';
		$unique_id = uniqid();

		foreach ($include_categories as $term_id) {
			$term = get_term($term_id, $taxonomy);

			if (!is_wp_error($term)) {
				$styles .= 'input[type="radio"]:nth-of-type(' . $i . '):checked ~ ._slider-container ._slider:not(.' . $term->slug . '-' . $unique_id . '),';
				$i++;
			}
		}
		echo '<style>' . rtrim($styles, ',') . '{ display: none; }</style>';
		?>
		<div class="carousle-sections-container">
			<div class="carousel-heading-wrapper">
				<?php echo '<h1>' . $heading . '</h1>'; ?>
				<?php echo '<h5><a href="' . $urlViewAlllink . '" class="flex gap-8">' . $textViewAll . '
                <i class="fa-solid fa-arrow-right"></i></a>
                </h5>'; ?>
			</div>
			<input type="radio" name="filter" id="all" checked><label for="all">
				<?php echo $textViewAll; ?>
			</label>
			<?php
			if (!empty($include_categories) && $include_categories[0] !== '') {
				foreach ($include_categories as $term) {
					$term = get_term($term, $taxonomy);
					if (!is_wp_error($term)) {
						echo '<input type="radio" name="filter" id="' . $term->slug . '-' . $unique_id . '"><label for="' . $term->slug . '-' . $unique_id . '">' . $term->name . '</label>';
					}
				}
			}
			?>

			<div
				class="_slider-container _slider-container-<?php echo $grid_number; ?>-col <?php echo $layout_style; ?>  _slider-tabs">
				<?php
				if ($query->have_posts()) {
					while ($query->have_posts()) {
						$query->the_post();

						$terms = get_the_terms(get_the_ID(), $taxonomy);
						if ($terms && !is_wp_error($terms)) {
							$term_slugs = array();
							foreach ($terms as $term) {

								$term_slug = $term->slug;
								$term_slug = $term_slug . '-' . $unique_id;
								$term_slugs[] = $term_slug;
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
									<li><img src="<?php echo get_the_post_thumbnail_url(); ?>"></li>
								</ul>
							<?php } ?>
							<div class="carousel-content-wrapper">
								<h3>
									<?php the_title(); ?>
								</h3>
								<?php if ($show_excerpt) {
									echo '<p>' . $exc_desc . '</p>';
								} ?>
								
								<div class="button-wrappers">
									<?php if ($read_more == 'on') { ?>
										<a href="<?php echo get_post_permalink(); ?>" class="slider-more-btn">
										<?php echo $read_more_btn_text; ?>
										</a>
									<?php } ?>
								</div>
							</div>
						</div>
						<?php
					}

					if ($ajax_lazyload == 'on' && ($layout_style1 == '0' || $layout_style1 == 'Vertical')) {
						// if there is more than 1 page of posts – display the button
						if ($query->max_num_pages > 1):
							echo '<div class="cpm_load_more"
							 data-layout="' . $layout_style . '"
							 data-gridnum = "' . $grid_number . '"
							 data-showimage="' . $show_images . '"
							 data-showexc="' . $show_excerpt . '"
							 data-exclenth="' . $excerpt_length . '"
							 data-showreadmore="' . $read_more . '" 
							 data-args="' . esc_attr(json_encode($args)) . '"
							 data-max-page="' . $query->max_num_pages . '" 
							 data-taxonomy ="' . $taxonomy . '"
							 data-current-page="1">
							 <img src="' . plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'assets/img/loader.svg">
							 </div>';
						endif;
					}
					wp_reset_postdata();
				} else {
					esc_html_e('No Posts Found', 'cpm-cdm');
				}
				?>
			</div>

		</div>

		<?php

		return ob_get_clean();

		// return $formatted_string;
	}
}

new CCDM_CustomGridModule;

