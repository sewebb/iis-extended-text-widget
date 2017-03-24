<?php
/**
 * Widget with headline, text, URL and image upload functionality.
 */
class ExtendedText extends WP_Widget {
	function __construct() {
		parent::__construct( false, 'Text (extended)', array( 'description' => 'Utökad text widget med möjlighet till bild och länk' ) );
		add_action( 'admin_print_scripts-widgets.php', array( $this, 'extended_text_widget_scripts' ), 11 );
	}

	function widget( $args, $instance ) {
		extract( $args );

		if ( ! empty( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'] );
		}

		$image = wp_get_attachment_image_src( $instance['imgsrc'], 'large' );

		if ( ! empty( $instance['body'] ) ) {
			$text = apply_filters( 'the_content', $instance['body'] );
		}
		?>

		<div class="small-4 columns">
			<section class="text">
				<?php if ( $instance['link'] ) : ?><a href="<?php echo $instance['link']; ?>"><?php endif; ?>
				<?php if ( $instance['imgsrc'] ) : ?><img src="<?php echo $image[0]; ?>"><?php endif; ?>
				<?php if ( $instance['link'] ) : ?></a><?php endif; ?>
				<?php if ( ! empty( $instance['title'] ) ) : ?><h2 class="headline"><?php echo $title; ?></h2><?php endif; ?>
				<?php if ( $text ) echo $text; ?>
				<?php if ( $text && $instance['link'] ) : ?><a href="<?php echo $instance['link']; ?>" class="read-more">Läs mer</a><?php endif; ?>
			</section>
		</div>

		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']   = strip_tags( $new_instance['title'] );
		$instance['body']    = $new_instance['body'];
		$instance['imgsrc']  = $new_instance['imgsrc'];
		$instance['link']    = $new_instance['link'];
		return $instance;
	}

	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance['title'] );
		} else {
			$title = '';
		}

		if ( $instance['imgsrc'] ) {
			$image = wp_get_attachment_image_src( $instance['imgsrc'], 'large' );
			$image = $image[0];
		}
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Rubrik</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<img src="<?php echo $instance['imgsrc']; ?>" id="img-post-input-<?php echo $this->number; ?>" style="max-width: 100%;">

		<p>
			<input type="hidden" name="<?php echo $this->get_field_name( 'imgsrc' ); ?>" id="post-input-<?php echo $this->number; ?>" class="custom-upload-image" value="<?php echo $instance['imgsrc']; ?>">
			<input type="button" class="custom_upload_image_button button" id="upload_image_button" value="Välj bild">
			<a href="#" class="custom_clear_image_button">Ta bort bild</a>
		</p>

		<p><textarea class="widefat" rows="5" cols="20" name="<?php echo $this->get_field_name( 'body' ); ?>"><?php echo $instance['body']; ?></textarea></p>

		<div class="set-link">
			<label for="<?php echo $this->get_field_id( 'link' ); ?>"><?php _e( 'Add Link' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'link' ); ?>" name="<?php echo $this->get_field_name( 'link' ); ?>" value="<?php echo $instance['link']; ?>">

			<p class="howto">Exempel: <code>https://www.iis.se</code> — glöm inte <code>https://</code></p>

			<div class="internal-link-selector">
				<?php 
					$show_internal = '1' == get_user_setting( 'wplink', '0' );
					$internal_links = self::get_internal_links();
				?>
				<p class="howto toggle-arrow <?php if ( $show_internal ) echo 'toggle-arrow-active'; ?>">
					<?php _e( 'Or link to existing content' ); ?>
				</p>

				<div class="results">
					<div class="query-results">
						<ul>
							<?php foreach( $internal_links as $link ): ?>
								<li class="<?php echo $link['class']; ?>">
									<input type="hidden" class="item-permalink" rel="<?php echo $this->get_field_id( 'link' ); ?>" value="<?php echo $link['permalink']; ?>">
									<span class="item-info"><?php echo $link['info']; ?></span>
									<span class="item-title"><?php echo $link['title']; ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<?php
	}
	
	function get_internal_links() {
		$pts = get_post_types( array( 'public' => true ), 'resource' );
		$pt_names = array_keys( $pts );
	
		$query = array(
			'post_type' => $pt_names,
			'suppress_filters' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'post_status' => 'publish',
			'order' => 'DESC',
			'orderby' => 'post_date',
			'posts_per_page' => -1,
		);
	
		// Do main query.
		$get_posts = new WP_Query;
		$posts = $get_posts->query( $query );
		// Check if any posts were found.
		if ( ! $get_posts->post_count )
			return false;
	
		// Build results.
		$results = array();
		$i = 1;
		foreach ( $posts as $post ) {
			if ( 'post' == $post->post_type )
				$info = mysql2date( __( 'Y/m/d' ), $post->post_date );
			else
				$info = $pts[ $post->post_type ]->labels->singular_name;
	
			if ($post->post_type != 'testimonial') {
				$results[] = array(
					'ID' => $post->ID,
					'title' => trim( esc_html( strip_tags( get_the_title( $post ) ) ) ),
					'permalink' => get_permalink( $post->ID ),
					'info' => $info,
					'class' => ($i % 2) ? 'alternate' : ''
				);
			}
			
			$i++;
		}
		
		return $results;
	}
	
	function extended_text_widget_scripts() {
		wp_enqueue_media();
		wp_enqueue_script( 'extended_text_widget', plugins_url( '/assets/extended-text-widget.js', dirname(__FILE__) ), array( 'jquery', 'media-upload', 'thickbox' ) );
		wp_enqueue_style( 'extended_text_widget_style', plugins_url( '/assets/extended-text-widget.css', dirname(__FILE__) ) );
	}
}

function register_extended_text() {
	register_widget( 'ExtendedText' );
}

add_action( 'widgets_init', 'register_extended_text' );
