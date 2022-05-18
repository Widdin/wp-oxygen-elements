<?php

class GalleryFilter extends OxyEl {

    function init() {
		add_action( 'wp_enqueue_scripts', [$this, 'enqueue_masonry'] );
		add_action( 'wp_ajax_galleryfilter', [$this, 'gallery_filter_function'] ); 
		add_action( 'wp_ajax_nopriv_galleryfilter', [$this, 'gallery_filter_function'] );
    }

    function afterInit() {
        $this->removeApplyParamsButton();
    }

    function name() {
        return 'Gallery Filter';
    }
    
    function slug() {
        return "gallery-filter";
    }

    function icon() {
		return plugin_dir_url( __FILE__ ).basename(__FILE__, '.php').'.svg';
    }
	
    function controls() {
        $cpt_control = $this->addOptionControl(
            array(
                "type" => 'dropdown',
				"name" => 'CPT',
				"slug" => 'custom_post_type_plugin'
            )
        );
		$cpt_control->setValue(
            array(
                'ACF' => 'Advanced Custom Field',
                'META' => 'Meta Box',
				'PODS' => 'Pods'
            )
        );
		$cpt_control->setDefaultValue('ACF');
		$cpt_control->whitelist();
		$cpt_control->rebuildElementOnChange();
    }

    function defaultCSS() {
        return file_get_contents(__DIR__.'/'.basename(__FILE__, '.php').'.css');
    }

    function render($options, $defaults, $content) {
		?>
		<form action="<?php echo esc_url( site_url() ) ?>/wp-admin/admin-ajax.php" method="POST" id="filter">
		<?php		
			$posts = get_posts( array( 'post_type' => 'gallery' ) );
			
			if ($posts) {
				
				echo '<div class="radio-toolbar">';
				  
				$output = '';
				$gallery_total_size = 0;
			
				foreach($posts as $post) {   
					$gallery = array();
					
					switch ( $options['custom_post_type_plugin'] ) {
						case 'ACF':
							$gallery = $post->gallery;
							break;
							
						case 'META':
							$gallery = rwmb_meta( $field_id='gallery', array(), $post_id=$post->ID );
							break;
		
						case 'PODS':
							$pod = pods('gallery', $post->ID);
							$gallery = $pod->field('gallery');
							break;
							
						default:
							echo "Error: No CPT-option could be found.";
							break;
					}

					$gallery_total_size += sizeof($gallery);
					
					$input = '<input id="' . esc_html( $post->post_title ) . '" type="radio" class="gallery-filter" name="gallery_category" value="' . esc_html( $post->ID ) . '" />';
					$label =  '<label for="' . esc_html( $post->post_title ) . '" class="radio-filter">' . esc_html( $post->post_title ) . " (" . esc_html( sizeof( $gallery ) ). ') </label>';
					$output .= $input . $label;
				}
				
				echo '<input id="all" type="radio" class="gallery-filter" name="gallery_category" value="-1" checked />';
				echo '<label for="all">All (' . esc_html( $gallery_total_size ) . ')</label>';
			
				echo $output;
				
				echo '</div>';
			}
		?>
		<input type="hidden" name="custom_post_type_plugin" value="<?= $options['custom_post_type_plugin'] ?>">
		<input type="hidden" name="action" value="galleryfilter">
		</form>

		<div id="response" class="gallery" />
		<?php

		$this->El->inlineJS(file_get_contents(__DIR__.'/'.basename(__FILE__, '.php').'.js'));
    }
	
	function gallery_filter_function(){
		$args = array(
			'post_type' => 'gallery',
			'posts_per_page' => -1
		);

		if ( isset( $_POST['gallery_category'] ) ) {
			$args['p'] = $_POST['gallery_category'];    
		}
		
		$query = new WP_Query( $args );

		if( $query->have_posts() ) {

			$images = array();
	
			while( $query->have_posts() ) {
				$query->the_post();
				
				
				switch ( $_POST['custom_post_type_plugin'] ) {
					case 'ACF':
						$gallery = $query->post->gallery;

						if ($gallery) {
							foreach($gallery as $image) {
								array_push($images, $image);
							}
						}
						break;
						
					case 'META':
						$gallery = rwmb_meta( $field_id='gallery', array(), $post_id=$query->post->ID );

						if($gallery) {
							foreach ($gallery as $image) {
								array_push($images, $image['ID']);
							}
						}
						break;
						
					case 'PODS':
						$pod = pods('gallery', $query->post->ID);

						$gallery = $pod->field('gallery');

						if($gallery) {
							foreach ($gallery as $image) {
								array_push($images, $image['ID']);
							}
						}
						break;
						
					default:
						echo "Error: No CPT-option could be found.";
						break;
				}
			}
			
			shuffle($images);

			$output = '';
			foreach($images as $id) {
				$atts = wp_get_attachment_image_src( $id, 'full');
				$src 	= wp_get_attachment_image_url( $id , 'full');
				$srcset = wp_get_attachment_image_srcset( $id , 'full');
				$sizes 	= wp_get_attachment_image_sizes( $id , 'full');
				
				$output .= '<img style="aspect-ratio: '.esc_html( $atts[1].'/'.$atts[2] ) .';" class="lazy loading gallery-image" data-src="'. $src .'" data-srcset="' . $srcset . '" sizes="' . esc_attr( $sizes ) . '" />';
			}
			echo $output;

			wp_reset_postdata();
		} 
		else {
			echo 'No images found';
		}

		die();
	}

	function enqueue_masonry() {
		wp_enqueue_script('masonry');
	}	

}

new GalleryFilter();
