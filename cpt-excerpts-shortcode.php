<?php

/*
 * Plugin Name:       CPT Excerpts Shortcode
 * Plugin URI:        https://github.com/wolozo/CPT-Excerpts-Shortcode
 * GitHub Plugin URI: https://github.com/wolozo/CPT-Excerpts-Shortcode
 * Description:       A shortcode to output excerpts of Custom Post Types.
 * Version:           0.1.1
 * Author:            Wolozo
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       w_cpt-excerpts-shortcode
 * Requires WP:       4.3
 * Requires PHP:      5.3
 */

function w_cptex_enqueue_scripts() {
  wp_enqueue_style( 'w-cptex', plugin_dir_url( __FILE__ ) . 'assets/css/cpt-excerpts.css' );
}

add_action( 'wp_enqueue_scripts', 'w_cptex_enqueue_scripts' );

// Include the stuff
//include( plugin_dir_path( __FILE__ ) . 'inc/options.php' );
//include( plugin_dir_path( __FILE__ ) . 'inc/locations.php' );
//include( plugin_dir_path( __FILE__ ) . 'inc/events.php' );

if ( ! function_exists( 'w_cptex_shortcode' ) ) {
  /**
   * @todo Element priority/order
   */
  function w_cptex_shortcode() {
    add_shortcode( 'w-cptex',
      function ( $atts, $content = null ) {

        $atts = shortcode_atts( array(
                                  'id'              => null,
                                  'class'           => null,
                                  'post_type'       => 'post',
                                  'count'           => '10',
                                  'offset'          => '0',
                                  'pagination'      => '0',
                                  'static'          => '0',
                                  'meta_date'       => 'M j, Y',
                                  'title_element'   => 'h2',
                                  'featured_image'  => '1', // [1,0]
                                  'excerpt'         => '1', // '[1,0]
                                  'read_more'       => '1', // '[1,0]
                                  'read_more_class' => "",
                                  'read_more_text'  => "Read More",
                                  'author'          => '1', // '[1,0]
                                  'date'            => '1', // '[1,0]
                                  'cats'            => '1', // '[1,0]
                                  'tags'            => '1', // '[1,0]
                                ),
                                $atts,
                                'w-cptex' );

        $id    = ( '' != $atts[ 'id' ] ? 'id="' . $atts[ 'id' ] . '"' : '' );
        $class = "class='w_cptex" . $atts[ 'class' ] . "'";

        $args = array(
          'post_type'      => array( $atts[ 'post_type' ] ),
          'post_status'    => array( 'publish' ),
          'has_password'   => false,
          'posts_per_page' => (int) $atts[ 'count' ],
        );

        if ( '0' != $atts[ 'offset' ] ) {
          $args[ 'offset' ] = (int) $atts[ 'offset' ];
        }

        if ( '1' == $atts[ 'pagination' ] ) {
          $args[ 'nopaging' ] = false;

          if ( '1' == $atts[ 'static' ] ) {
            $args[ 'paged' ] = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
          } else {
            $args[ 'paged' ] = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
          }
        }

        $cptex_query = new WP_Query( $args );

        global $wp_query;
        $tmp_query = $wp_query;
        $wp_query  = null;
        $wp_query  = $cptex_query;

        if ( $cptex_query->have_posts() ) {

          $out = null;

          ob_start();
          while ( $cptex_query->have_posts() ) {
            $cptex_query->the_post();

            $post_id = get_the_ID();

            $title     = get_the_title();
            $permalink = esc_url( get_the_permalink() );

            ?>

            <article id="post-<?php the_ID() ?>" <?php post_class( 'w_cptex-article', $post_id ) ?>">

            <?php
            // Linked Thumbnail

            if ( '1' === $atts[ 'featured_image' ] ) {
              if ( has_post_thumbnail() ) { ?>
                <a href="<?php echo $permalink; ?>" class="w_cptex-anchor w_cptex-anchor--thumb">
                  <?php the_post_thumbnail(); ?>
                </a>
              <?php } else { ?>
                <img class="w_cptex_thumb-no" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mN89x8AAuEB74Y0o2cAAAAASUVORK5CYII=">

              <?php }
            } ?>

            <?php
            // Linked Title ?>
            <<?php echo $atts[ 'title_element' ] ?> class="w_cptex-entry-title entry-title">
            <a href="<?php echo $permalink; ?>"><?php the_title(); ?></a>
            </<?php echo $atts[ 'title_element' ] ?>>

            <?php
            // Post Meta
            if ( '1' === $atts[ 'author' ] || '1' === $atts[ 'date' ] || '1' === $atts[ 'cats' ] || '1' === $atts[ 'tags' ] ) {
              $sep = null; ?>
              <div class="w_cptex-post-meta post-meta">
                <?php
                // Author
                if ( '1' === $atts[ 'author' ] ) {
                  $author = get_the_author();
                  $sep    = true;
                  ?>

                  by <span class="w_cptex-author author vcard">
                    <a href="" title="Posts by <?php echo $author; ?>"><?php echo $author; ?></a> </span>
                <?php } ?>

                <?php
                // Date
                if ( '1' === $atts[ 'date' ] ) {
                  $date = get_the_date( $atts[ 'meta_date' ] );
                  echo ( $sep ) ? '<span class"w_cptex-post-meta--sep"> | </sep>' : null;
                  $sep = true;
                  ?>
                  <span class="w_cptex-published published"><?php echo $date; ?></span>
                <?php } ?>

                <?php
                // Categories
                if ( '1' === $atts[ 'cats' ] ) {
                  echo ( $sep ) ? '<span class"w_cptex-post-meta--sep"> | </sep>' : null;
                  $sep = true;
                  echo get_the_category_list();
                }

                // Tags
                if ( '1' === $atts[ 'tags' ] ) {
                  echo ( $sep ) ? '<span class"w_cptex-post-meta--sep"> | </sep>' : null;
                  $sep = true;
                  echo get_the_tag_list( '<ul class="post-tags"><li>', '</li><li>', '</li></ul>' );
                }
                ?>
              </div>
            <?php } //  Post Meta ?>

            <?php
            // Excerpt
            if ( '1' === $atts[ 'excerpt' ] ) {
              echo '<p class="w_cptex-excerpt">' . get_the_excerpt() . '</p>';
            } ?>

            <?php
            // Read More
            if ( '1' === $atts[ 'read_more' ] ) { ?>
              <div class="w_cptex-read-more">
                <a href="<?php echo $permalink ?>" class="w_cptex-more-link more-link<?php echo $atts[ 'read_more_class' ] ?>"><?php echo $atts[ 'read_more_text' ] ?></a>
              </div>
            <?php } ?>

            </article>
          <?php }
        }

        if ( '1' == $atts[ 'pagination' ] ) { ?>
          <div class="navigation">
            <p><?php posts_nav_link( ' | ', 'Previous', 'Next' ); ?></p>
          </div>
        <?php }

        $out = ob_get_contents();
        ob_end_clean();

        wp_reset_postdata();
        $wp_query = null;
        $wp_query = $tmp_query;

        return <<<HTML
<div $id $class>$out</div>
HTML;
      } );
  }
}

add_action( 'init', 'w_cptex_shortcode', 15 );