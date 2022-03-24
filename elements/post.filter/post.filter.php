<?php

class PostFilter extends OxyEl {

    function init() {
		add_action('wp_ajax_postsfilter', [$this, 'posts_filter_function']); 
		add_action('wp_ajax_nopriv_postsfilter', [$this, 'posts_filter_function']);
    }

    function afterInit() {
        $this->removeApplyParamsButton();
    }

    function name() {
        return 'Posts Filter';
    }
    
    function slug() {
        return "post-filter";
    }

    function icon() {
		return plugin_dir_url( __FILE__ ).basename(__FILE__, '.php').'.svg';
    }
	
    function controls() {

        // Filter
        $filter_section = $this->addControlSection("filter_section", __("Filter"), "assets/icon.png", $this);
        $filter_section->typographySection(
            __("Typography"),
            '.radio-toolbar label',
            $this
        );
        $filter_section->borderSection(
            __("Borders"),
            ".radio-toolbar label",
            $this
        );
        $filter_section->addStyleControl(
            array(
                "name" => __('Background Color'),
                "selector" => ".radio-toolbar label",
                "property" => 'background-color',
                "control_type" => "colorpicker"
            )
        );

        // Filter Active
        $filter_active_section = $this->addControlSection("filter_active_section", __("Filter Active"), "assets/icon.png", $this);
        $filter_active_section->typographySection(
            __("Typography"),
            ".radio-toolbar input[type='radio']:checked+label",
            $this
        );
        $filter_active_section->borderSection(
            __("Borders"),
            ".radio-toolbar input[type='radio']:checked+label",
            $this
        );
        $filter_active_section->addStyleControl(
            array(
                "name" => __('Background Color'),
                "selector" => ".radio-toolbar input[type='radio']:checked+label",
                "property" => 'background-color',
                "control_type" => "colorpicker"
            )
        );

        // Title
        $title_section = $this->addControlSection("title_section", __("Title"), "assets/icon.png", $this);
        $title_section->typographySection(
            __("Typography"),
            '.post-title',
            $this
        );

        // Excerpt
        $excerpt_section = $this->addControlSection("excerpt_section", __("Excerpt"), "assets/icon.png", $this);
        $excerpt_section->typographySection(
            __("Typography"),
            '.post-excerpt',
            $this
        );

        // Categories
        $terms = get_terms( array( 'taxonomy' => 'category', 'orderby' => 'name' ) );
        foreach ($terms as $term) {
            $category_section = $this->addControlSection($term->slug, __("Category " . $term->name), "assets/icon.png", $this);
            $category_section->addStyleControl(
                array(
                    "name" => __('Background Color'),
                    "selector" => '.' . $term->slug . '',
                    "property" => 'background-color',
                    "control_type" => "colorpicker"
                )
            );
            $category_section->typographySection(
                __("Typography"),
                '.' . $term->slug . '',
                $this
            );
        }
        
    }

    function defaultCSS() {
        return file_get_contents(__DIR__.'/'.basename(__FILE__, '.php').'.css');
    }

    function render($options, $defaults, $content) {
		?>
		<form action="<?php echo esc_url( site_url() ) ?>/wp-admin/admin-ajax.php" method="POST" id="filter">

			<?php
			if( $terms = get_terms( array( 'taxonomy' => 'category', 'orderby' => 'name' ) ) ) {

				$total_count = 0;
				$output = "";

				echo '<div class="radio-toolbar">';

				foreach ( $terms as $term ) {
					$total_count += $term->count;

					$input = '<input id="' . esc_html( $term->name ) . '" type="radio" class="posts-filter" name="posts_category" value="' . esc_html( $term->term_id ) . '" />';
					$label =  '<label for="' . esc_html( $term->name ) . '" class="radio-filter">' . esc_html( $term->name ) . " (" . esc_html( $term->count ) . ') </label>';

					$output .= $input . $label;
				}

				echo '<input id="all" type="radio" class="posts-filter" name="posts_category" value="-1" checked />';
				echo '<label for="all">All (' . esc_html( $total_count ) . ')</label>';

				echo $output;

				echo '</select>';
				
				echo '</div>';
			}
			?>

			<input type="hidden" name="action" value="postsfilter">
		</form>

		<div id="response"/>
		<?php

		$this->El->inlineJS(file_get_contents(__DIR__.'/'.basename(__FILE__, '.php').'.js'));
    }
    
	function posts_filter_function(){
		
		$paged = 1;
		
		if( isset($_POST['paged'])) {
			$paged = $_POST['paged'];
		}
		
		$args = array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'paged' => $paged
		);
		
		// for taxonomies / categories
		if( isset( $_POST['posts_category'] ) &&  $_POST['posts_category'] != -1 )
			$args['tax_query'] = array(
			array(
				'taxonomy' => 'category',
				'field' => 'id',
				'terms' => $_POST['posts_category']
			)
		);
		
		$query = new WP_Query( $args );

		if( $query->have_posts() ) {
			
			if ( $paged == 1 ) {
				echo '<div class="posts">';
			}
			
			while ( $query->have_posts() ) {
				$query->the_post(); ?>
				
				<div class="post-container loading lazy">
					
					<?php
						$id = get_post_thumbnail_id();
						
						$attachment = wp_get_attachment_image_src( $id, 'full');
						$src 	= wp_get_attachment_image_url( $id, 'full' );
						$srcset = wp_get_attachment_image_srcset( $id, 'full' );
						$sizes 	= wp_get_attachment_image_sizes( $id, 'full' );

						echo '<img style="aspect-ratio: '.$attachment[1].'/'.$attachment[2].';" class="post-image" data-src="'. $src .'" data-srcset="' . $srcset . '" sizes="' . $sizes . '" />';
					?>

					<div class="post-content">
						
						<div class="post-categories">
							<?php 
								foreach ( ( get_the_category() ) as $category ) {
									echo '<div class="post-category ' . esc_html( $category->slug ) . '">' . esc_html( $category->cat_name ) . '</div>';
								} 
							?>
						</div>
						
						<h4 class="post-title">
							<?php esc_html( the_title() ); ?>
						</h4>
						
						<p class="post-excerpt">
							<?php echo  esc_html( get_the_excerpt() ); ?>
						</p> 

						<a class="post-link" href="<?php esc_url( the_permalink() ); ?>">
							Read more
						</a>
					
					</div>
				</div>
				<?php
			}
			
			if ( $paged == 1 ) {
				echo '</div>';
			}

			if ($query->max_num_pages > 1 && $paged < $query->max_num_pages) {
				echo '<button id="load_more" data-current-page="'. esc_html( $paged ) .'" data-next-page="'. esc_html( ($paged + 1) ) .'" data-max-page="'. esc_html( $query->max_num_pages ) .'" onClick="load_more()">Load more</button>';
			}
	
			wp_reset_postdata();
		}
		else {
			echo 'No posts found';
		}
		
		die();
	}	
}

new PostFilter();
