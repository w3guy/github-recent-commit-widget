<?php

/*
Plugin Name: GitHub Recent Commit WordPress Widget
Plugin URI: http://designmodo.com
Description: Display recent commits made to a project on GitHub
Version: 1.0
Author: Agbonghama Collins
Author URI: http://w3guy.com
License: GPL2
*/


/**
 * Adds Github_Recent_Commit widget.
 */
class Github_Recent_Commit extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'github_widget', // Base ID
			__( 'Github Recent Commit', 'text_domain' ), // Name
			array( 'description' => __( 'Display recent commits made to a Github project', 'text_domain' ), )
		);
	}


	function get_commit_feed( $slug, $item_number ) {

		include_once( ABSPATH . WPINC . '/feed.php' );

		// Get a SimplePie feed object from the specified feed source.
		$rss = fetch_feed( 'https://github.com/' . $slug . '/commits/master.atom' );

		if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly

			// Figure out how many total items there are, but limit it to 5.
		{
			$maxitems = $rss->get_item_quantity( $item_number );

			// Build an array of all the items, starting with element 0 (first element).
			$rss_items = $rss->get_items( 0, $maxitems );
		}

		endif;
		?>

		<ul>
		<?php if ( $maxitems == 0 ) : ?>
			<li><?php _e( 'No items', 'my-text-domain' ); ?></li>
		<?php else : ?>
			<?php // Loop through each feed item and display each item as a hyperlink. ?>
			<?php foreach ( $rss_items as $item ) : ?>
				<li>
					<a href="<?php echo esc_url( $item->get_permalink() ); ?>"
					   title="<?php printf( 'Posted %s', $item->get_date( 'j F Y | g:i a' ) ); ?>">
						<?php echo esc_html( $item->get_title() ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		<?php endif;  ?>
		</ul>
	<?php
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$title       = apply_filters( 'widget_title', $instance['title'] );
		$slug        = $instance['project_slug'];
		$item_number = $instance['item_number'];

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// call get_commit_feed() to output the feed content
		$this->get_commit_feed( $slug, $item_number );

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'Github Commits', 'text_domain' );
		}

		$project_slug = isset( $instance['project_slug'] ) ? $instance['project_slug'] : '';

		$item_number = isset( $instance['item_number'] ) ? $instance['item_number'] : 5;

		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
			       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
			       value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'project_slug' ); ?>"><?php _e( 'Project slug:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'project_slug' ); ?>"
			       name="<?php echo $this->get_field_name( 'project_slug' ); ?>" type="text"
			       value="<?php echo esc_attr( $project_slug ); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'item_number' ); ?>"><?php _e( 'Item number:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'item_number' ); ?>"
			       name="<?php echo $this->get_field_name( 'item_number' ); ?>" type="number"
			       value="<?php echo esc_attr( $item_number ); ?>">
		</p>

	<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                 = array();
		$instance['title']        = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['project_slug'] = ( ! empty( $new_instance['project_slug'] ) ) ? strip_tags( $new_instance['project_slug'] ) : '';
		$instance['item_number']  = ( ! empty( $new_instance['item_number'] ) ) ? strip_tags( $new_instance['item_number'] ) : '';

		return $instance;
	}

}


// register Github_Recent_Commit widget
function register_github_recent_commit() {
	register_widget( 'Github_Recent_Commit' );
}

add_action( 'widgets_init', 'register_github_recent_commit' );