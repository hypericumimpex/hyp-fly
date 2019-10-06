<?php
/**
 * Plugin Name: Revealer
 * Plugin URI: https://github.com/hypericumimpex/hyp-fly/
 * Description: Most effective way to protect your online content from being copy.
 * Author: Merkulove
 * Version: 1.0.2
 * Author URI: https://github.com/hypericumimpex/
 **/

/** 
 * Revealer add stylish hover pop-up with five animation effects for internal WordPress website links.
 * Exclusively on Envato Market: https://1.envato.market/revealer
 * 
 * @encoding     UTF-8
 * @version      1.0.2
 * @copyright    Copyright (C) 2019 Merkulove ( https://github.com/hypericumimpex/ ). All rights reserved.
 * @license      Envato Standard License https://github.com/hypericumimpex/hyp-fly
 * @author       Hypericum
 * @support      suport@hypericum.com
 **/

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

if ( ! class_exists( 'Revealer' ) ) :
    
    /**
     * SINGLETON: Core class used to implement a Revealer plugin.
     *
     * This is used to define internationalization, admin-specific hooks, and
     * public-facing site hooks.
     *
     * @since 1.0.0
     */
    final class Revealer {
    
        /** Plugin version.
         *
         * @var Constant
         * @since 1.0.0
         **/
        const VERSION = '1.0.2';
        
        /**
         * Revealer Plugin settings.
         * 
	 * @var array()
	 * @since 1.0.0
	 **/
        public $options = [];
        
        /**
         * Helpers objects.
         * 
	 * @var array()
	 * @since 1.0.0
	 **/
        public $helpers = [];
        
        /**
         * Use minified libraries if SCRIPT_DEBUG is turned off.
         * 
	 * @since 1.0.0
	 **/
        public static $suffix = '';
        
        /**
         * URL (with trailing slash) to plugin folder.
         * 
	 * @var string
	 * @since 1.0.0
	 **/
        public static $url = '';
        
        /**
         * PATH to plugin folder.
         * 
	 * @var string
	 * @since 1.0.0
	 **/
        public static $path = '';
        
        /**
         * Plugin base name.
         * 
	 * @var string
	 * @since 1.0.0
	 **/
        public static $basename = '';
        
        /**
         * The one true Revealer.
         * 
	 * @var Revealer
	 * @since 1.0.0
	 **/
	private static $instance;
        
        /**
         * Sets up a new plugin instance.
         *
         * @since 1.0.0
         * @access public
         **/
        private function __construct() {

            /** Initialize main variables. */
            $this->init();
            
            /** Add plugin settings page. */
            $this->add_settings_page();

            /** Load JS and CSS for Backend Area. */
            $this->enqueue_backend();
            
            /** Load JS and CSS for Fronend Area. */
            $this->enqueue_fronend();
            
            /** Loads plugin helpers. */
            $this->load_helpers();

        }
        
        /**
         * Load JS and CSS for Backend Area.
         *
         * @since 1.0.0
         * @access public
         **/
        function enqueue_backend() {
            
            /** Add admin styles. */
            add_action( 'admin_enqueue_scripts', [$this, 'load_admin_styles'] );
            
            /** Add admin javascript. */
            add_action( 'admin_enqueue_scripts', [$this, 'load_admin_scripts'] );
            
        }
        
        /**
         * Load JS and CSS for Fronend Area.
         *
         * @since 1.0.0
         * @access public
         **/
        function enqueue_fronend() {
            
            /** Add plugin styles. */
            add_action( 'wp_enqueue_scripts', [$this, 'enqueue_styles'] );
            
            /** Add plugin script. */
            add_action( 'wp_enqueue_scripts', [$this, 'load_scripts'] );
            
        }
        
        /**
         * Add plugin styles.
         *
         * @since   1.0.0
         * @return void
         **/
        public function enqueue_styles() {

            /** CSS. */
            $bg_color = $this->options['bgcolor'];
            $text_color = $this->options['text_color'];
            $css = "
                .tippy-tooltip,
                .tippy-tooltip .tippy-backdrop {
                    background-color: {$bg_color} !important;
                    color: {$text_color};
                }
                
                .tippy-tooltip #mdp-preloader circle { fill: {$text_color}; }
                .tippy-tooltip #mdp-preloader stop { stop-color: {$text_color}; }

                .tippy-tooltip h3,
                .tippy-tooltip .mdp-excerpt,
                .tippy-tooltip .mdp-excerpt p { color: {$text_color}; }
                    
                .tippy-tooltip[x-placement^='top'] .tippy-arrow { border-top-color: {$bg_color}; }
                .tippy-tooltip[x-placement^='bottom'] .tippy-arrow { border-bottom-color: {$bg_color}; }
                .tippy-tooltip[x-placement^='left'] .tippy-arrow { border-left-color: {$bg_color}; }
                .tippy-tooltip[x-placement^='right'] .tippy-arrow { border-right-color: {$bg_color}; }    
                .tippy-roundarrow path { fill: {$bg_color}; }
            ";
            
            wp_enqueue_style( 'mdp-revealer', self::$url . 'css/revealer' . self::$suffix . '.css', [], self::VERSION );
            wp_add_inline_style( 'mdp-revealer', $css );
        }

        /**
         * Initialize main variables.
         *
         * @since 1.0.0
         * @access public
         **/
        public function init() {
            
            /** Gets the plugin URL (with trailing slash). */
            self::$url = plugin_dir_url( __FILE__ );
            
            /** Gets the plugin PATH. */
            self::$path = plugin_dir_path( __FILE__ );
            
            /** Use minified libraries if SCRIPT_DEBUG is turned off. */
            self::$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
            
            /** Set plugin basename. */
            self::$basename = plugin_basename( __FILE__ );
            
            /** Load translation. */
            add_action( 'plugins_loaded', [$this, 'load_textdomain'] );
            
            /** Get plugin settings. */
            $this->get_options();
            
            /** Ajax on the Viewer-Facing Side. */
            add_action( 'wp_ajax_get_page_by_url', array( $this, 'get_page_by_url' ) );
            add_action( 'wp_ajax_nopriv_get_page_by_url', array( $this, 'get_page_by_url' ) );
            
            /** Allow DIVI in AJAX. */
            add_filter( 'et_builder_load_actions', [$this, 'divi_builder_load_actions'] );
        }
        
        /**
         * Add ajax action to the array of allowed actions
         * to ensure the DIVI builder modules are loaded for ajax callback.
         *
         * @since 1.0.0
         * @access public
         **/
        public function divi_builder_load_actions() {
            $actions[] = 'get_page_by_url';

            return $actions;
        }
        
        /**
         * Ajax front-end action hook here.
         *
         * @since 1.0.0
         * @access public
         **/
        public function get_page_by_url() {
            
            /** Get Target URL. */
            if ( ! isset( $_REQUEST['url'] ) ) { return; }
            $url = filter_var( $_REQUEST['url'], FILTER_SANITIZE_URL );
            
            /** Get Post ID. */
            $post_id = url_to_postid( $url );
            if ( ! $post_id ) { return; }

            $post = get_post( $post_id );
            
            /** Post Title. */
            $title = $post->post_title;

            /** Post Author. */
            $authorID = $post->post_author;
            $author = get_the_author_meta( 'display_name', $authorID );

            /** Post Category. */
            $categories = get_the_category( $post_id );
            $category = '';
            foreach( $categories as $key => $cat ){
                $category .= ( count( $categories ) > 1 && count( $categories ) > $key + 1  ) ? $cat->cat_name . ', ' : $cat->cat_name;
            }

            /** Post Comments */
            $comments = $post->comment_count;

            /** Post Excerpt. */
            if ( has_excerpt( $post->ID ) ) {
                $excerpt = get_the_excerpt( $post->ID );
            } else {

                /** Make sure all vc shortcodes are loaded. */
                if ( method_exists( 'WPBMap', 'addAllMappedShortcodes' ) ) {
                    WPBMap::addAllMappedShortcodes();
                }

                /** Current post Content. */
                $excerpt = apply_filters( 'the_content', $post->post_content );
            }
            
            $excerpt = wp_trim_words( $excerpt, $this->options['excerpt_length'], ' ...' );
            $excerpt = strip_tags( $excerpt, '<strong><b>' );
            $excerpt = preg_replace( "/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', $excerpt );
            
            /** Image. */
            if ( $this->options['image_position'] == 'without-image' ) {
                $src = '';
            } else {
                $src = get_the_post_thumbnail_url( $post->ID, [320, 180] );
            }
            
            /** Image Position. */
            $image_position_class = '';
            
            if ( in_array( $this->options['image_position'], ['without-image', 'top', 'bottom', 'left', 'right']) ) {
                $image_position_class = $this->options['image_position'];
                
            } elseif ( $this->options['image_position'] == 'top-bottom' ) {
                $classes = ['top', 'bottom'];
                $image_position_class = $classes[array_rand( $classes )];

            } elseif ( $this->options['image_position'] == 'top-left' ) {
                $classes = ['top', 'left'];
                $image_position_class = $classes[array_rand( $classes )];
                
            } elseif ( $this->options['image_position'] == 'top-right' ) {
                $classes = ['top', 'right'];
                $image_position_class = $classes[array_rand( $classes )];
                
            } elseif ( $this->options['image_position'] == 'bottom-left' ) {
                $classes = ['bottom', 'left'];
                $image_position_class = $classes[array_rand( $classes )];
                
            } elseif ( $this->options['image_position'] == 'bottom-right' ) {
                $classes = ['bottom', 'right'];
                $image_position_class = $classes[array_rand( $classes )];
                
            } elseif ( $this->options['image_position'] == 'random' ) {
                $classes = ['top', 'bottom', 'left', 'right'];
                $image_position_class = $classes[array_rand( $classes )];
            }
            
            /** Popup content. */
            ob_start();
            ?>
            <div class="mdp-revealer-box <?php echo esc_attr( $image_position_class ); ?>">
                <?php if ( $src ) : ?>
                    <a href="<?php echo esc_url( $url ); ?>" class="mdp-image">
                        <img src="<?php echo esc_attr( $src ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
                    </a>
                <?php endif; ?>
                
                <a class="mdp-content" href="<?php echo esc_url( $url ); ?>">

                    <?php if ( $this->options['author_position'] == 'top' || $this->options['category_position'] == 'top' || $this->options['comments_position'] == 'top' ) : ?>
                        <div class="mdp-header">
                            <p>
                                <?php if ( $this->options['author_position'] == 'top' ) : ?>
                                    <span class="mdp-author"><?php echo esc_attr( $author ); ?></span>
                                <?php endif; ?>
                                <?php if ( $this->options['category_position'] == 'top' && count( $categories ) > 0 ) : ?>
                                    <span class="mdp-category"><?php echo esc_attr( $category ); ?></span>
                                <?php endif; ?>
                                <?php if ( $this->options['comments_position'] == 'top' ) : ?>
                                    <span class="mdp-comments"><?php echo esc_attr( $comments ); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ( $title ) : ?>
                        <h4><?php esc_html_e( $title ); ?></h4>
                    <?php endif; ?>

                    <?php if ( $excerpt ) : ?>
                        <div class="mdp-excerpt"><p><?php echo wp_kses_post( $excerpt ); ?></p></div>
                    <?php endif; ?>

                    <?php if ( $this->options['author_position'] == 'bottom' || $this->options['category_position'] == 'bottom' || $this->options['comments_position'] == 'bottom' ) : ?>
                        <div class="mdp-footer">
                            <p>
                                <?php if ( $this->options['author_position'] == 'bottom' ) : ?>
                                    <span class="mdp-author"><?php echo esc_attr( $author ); ?></span>
                                <?php endif; ?>
                                <?php if ( $this->options['category_position'] == 'bottom' && count( $categories ) > 0 ) : ?>
                                    <span class="mdp-category"><?php echo esc_attr( $category ); ?></span>
                                <?php endif; ?>
                                <?php if ( $this->options['comments_position'] == 'bottom' ) : ?>
                                    <span class="mdp-comments"><?php echo esc_attr( $comments ); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>

                </a>
            </div>
            <?php
            $html = ob_get_clean();
            
            
            /** 
             * Return Result.
             * $html escaped above.
             **/
            echo json_encode( [ 'status' => 1, 'html' => $html ] ); 
            die();
        }
        
        /**
         * Add plugin settings page.
         *
         * @since 1.0.0
         * @access public
         **/
        public function add_settings_page() {
            
            add_action( 'admin_menu', [$this, 'add_admin_menu'] );
            add_action( 'admin_init', [$this, 'settings_init'] );
            
        }

        /**
         * Loads plugin helpers, it is something like mini plugins.
         *
         * @since 1.0.0
         * @access public
         **/
        public function load_helpers() {
            
            /** Add Plugin Helper Class. */
            require_once ( wp_normalize_path( self::$path . '/classes/MDPHelper.class.php' ) );
            /** Run MDPHelper class. */
            $this->helpers['MDPHelper'] = Merkulov\Revealer\MDPHelper::get_instance();
            
            /** Add Assignments Tab on Settings Page. */
            require_once ( wp_normalize_path( self::$path . '/classes/MDPAssignments.class.php' ) );
            /** Run MDPAssignments class. */
            $this->helpers['MDPAssignments'] = Merkulov\Revealer\MDPAssignments::get_instance();
            
        }
        
        /**
         * Register the JavaScript for the public-facing side of the site.
         *
         * @since   1.0.0
         * @return void
         **/
        public function load_scripts() {
            
            /** Checks if plugin should work on this page. */
            if( $this->helpers['MDPAssignments']->display() ) {
                
                wp_enqueue_script( 'popper', self::$url . 'js/popper.min.js', [], self::VERSION, true );
                wp_enqueue_script( 'tippy', self::$url . 'js/tippy.min.js', [], self::VERSION, true );
                wp_enqueue_script( 'mdp-revealer', self::$url . 'js/revealer' . self::$suffix . '.js', ['popper', 'tippy'], self::VERSION, true );
                wp_localize_script( 'mdp-revealer', 'mdp_revealer', 
                    /** We use mdp_ prefix to avoid conflicts in JS. */
                    [ 
                        'ajaxurl' => admin_url( 'admin-ajax.php' ),
                        'selector' => $this->options['selector'], // Selector.
                        'theme' => $this->options['style'], // Style.
                        'animation' => $this->options['animation'], // Animation.
                        'maxWidth' => $this->options['size'], // Pop-up Size.
                        'arrow' => ( $this->options['arrow'] == 'without-arrow' ) ? FALSE : TRUE, // Arrow.
                        'arrowType' => $this->options['arrow'] // Arrow style.
                    ]
                );
                
            }
            
        }

        /**
         * Add admin menu for plugin settings.
         *
         * @since 1.0.0
         * @access public
         **/
        public function add_admin_menu() {
            add_submenu_page(
                'options-general.php',
                esc_html__( 'Revealer Settings', 'revealer' ),
                esc_html__( 'Revealer', 'revealer' ),
                'manage_options',
                'mdp_revealer_settings',
                [$this, 'options_page']
            );
        }

        /**
         * Plugin Settings Page.
         *
         * @since 1.0.0
         * @access public
         **/
        public function options_page() {
            
            /** User rights check. */
            if ( ! current_user_can('manage_options' ) ) { return; }?>

            <div class="wrap">
                <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
                <p><?php esc_html_e( 'Revealer add stylish hover pop-up with five animation effects for internal WordPress website links.', 'revealer' ); ?></p>
                
                <?php 
                $tab = 'general';
                if ( isset ( $_GET['tab'] ) ) {
                    $tab = $_GET['tab'];
                }
                
                /** Render Tabs Headers. */
                $this->render_tabs( $tab );
                
                /** Render Tabs Body. */
                ?>
                <form action='options.php' method='post'>
                    <?php

                    /** General Tab. */
                    if( $tab == 'general' ) {
                        settings_fields( 'RevealerOptionsGroup' );
                        do_settings_sections( 'RevealerOptionsGroup' );

                    /** Assignments Tab. */
                    } elseif ( $tab == 'assignments' ) {
                        settings_fields( 'RevealerAssignmentsOptionsGroup' );
                        do_settings_sections( 'RevealerAssignmentsOptionsGroup' );
                    }

                    submit_button(); 

                    ?>
                </form>
                    
            </div>
            
            <?php
        }
        
        /**
         * Render Tabs Headers.
         *
         * @since 1.0.0
         * @access public
         **/
        public function render_tabs( $current = 'general' ) {
            
            /** Tabs array. */
            $tabs = 
            [ 
                'general' => esc_html__( 'General', 'revealer' ), 
                'assignments' => esc_html__( 'Assignments', 'revealer' ) 
            ];

            /** Render Tabs. */
            ?><div class="nav-tab-wrapper"><?php
            foreach ( $tabs as $tab => $name ) {
                $class = ( $tab == $current ) ? ' nav-tab-active' : '';
                ?><a class="nav-tab <?php echo esc_attr( $class ); ?>" href="?page=mdp_revealer_settings&tab=<?php echo esc_attr( $tab ); ?>"><?php echo esc_html( $name ); ?></a><?php
            }
            ?></div><?php
        }

        /**
         * Generate Settings Page.
         *
         * @since 1.0.0
         * @access public
         **/
        public function settings_init() {
            
            /** General Tab. */
            register_setting( 'RevealerOptionsGroup', 'mdp_revealer_settings' );
            add_settings_section( 'mdp_revealer_settings_page_general_section', '', NULL, 'RevealerOptionsGroup' );
            
            /** Selector. */
            add_settings_field( 'selector', esc_html__( 'Selector:', 'revealer' ), [$this, 'render_selector'], 'RevealerOptionsGroup', 'mdp_revealer_settings_page_general_section' );

            /** Style. */
            add_settings_field( 'style', esc_html__( 'Style:', 'revealer' ), [$this, 'render_style'], 'RevealerOptionsGroup', 'mdp_revealer_settings_page_general_section' );

            /** Arrow. */
            add_settings_field( 'arrow', esc_html__( 'Arrow:', 'revealer' ), array( $this, 'render_arrow' ), 'RevealerOptionsGroup', 'mdp_revealer_settings_page_general_section' );
            
            /** Animation. */
            add_settings_field( 'animation', esc_html__( 'Animation:', 'revealer' ), [$this, 'render_animation'], 'RevealerOptionsGroup', 'mdp_revealer_settings_page_general_section' );

            /** Pop-up Size. */
            add_settings_field( 'size', esc_html__( 'Pop-up Size:', 'revealer' ), array( $this, 'render_size' ), 'RevealerOptionsGroup', 'mdp_revealer_settings_page_general_section' );
            
            /** Image position. */
            add_settings_field( 'image_position', esc_html__( 'Image position:', 'revealer' ), [$this, 'render_image_position'], 'RevealerOptionsGroup', 'mdp_revealer_settings_page_general_section' );

            /** Author position. */
            add_settings_field( 'author_position', esc_html__( 'Author position:', 'revealer' ), [$this, 'render_author_position'], 'RevealerOptionsGroup', 'mdp_revealer_settings_page_general_section' );

            /** Categories position. */
            add_settings_field( 'category_position', esc_html__( 'Categories position:', 'revealer' ), [$this, 'render_category_position'], 'RevealerOptionsGroup', 'mdp_revealer_settings_page_general_section' );

            /** Comments position. */
            add_settings_field( 'comments_position', esc_html__( 'Comments position:', 'revealer' ), [$this, 'render_comments_position'], 'RevealerOptionsGroup', 'mdp_revealer_settings_page_general_section' );

            /** Excerpt Length. */
            add_settings_field( 'excerpt_length', esc_html__( 'Excerpt Length:', 'revealer' ), array( $this, 'render_excerpt_length' ), 'RevealerOptionsGroup', 'mdp_revealer_settings_page_general_section' );            
            
            /** Background color. */
            add_settings_field( 'bgcolor', esc_html__( 'Background Color:', 'revealer' ), [$this, 'render_bgcolor'], 'RevealerOptionsGroup', 'mdp_revealer_settings_page_general_section' );

            /** Text color. */
            add_settings_field( 'text_color', esc_html__( 'Text Color:', 'revealer' ), [$this, 'render_text_color'], 'RevealerOptionsGroup', 'mdp_revealer_settings_page_general_section' );
            
            /** Create Assignments Tab. */
            $this->helpers['MDPAssignments']->add_settings();
            
        }
        
        /**
         * Image position field.
         *
         * @since 1.0.0
         * @access public
         **/
        public function render_image_position() {

            ?>
            <select name="mdp_revealer_settings[image_position]" class="regular-text">
                <option value="without-image" <?php if ( $this->options['image_position'] == 'without-image' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Without Image', 'revealer' ); ?></option>
                <option value="top" <?php if ( $this->options['image_position'] == 'top' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Top', 'revealer' ); ?></option>
                <option value="bottom" <?php if ( $this->options['image_position'] == 'bottom' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Bottom', 'revealer' ); ?></option>
                <option value="left" <?php if ( $this->options['image_position'] == 'left' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Left', 'revealer' ); ?></option>
                <option value="right" <?php if ( $this->options['image_position'] == 'right' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Right', 'revealer' ); ?></option>
                <option value="top-bottom" <?php if ( $this->options['image_position'] == 'top-bottom' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Top or Bottom', 'revealer' ); ?></option>
                <option value="top-left" <?php if ( $this->options['image_position'] == 'top-left' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Top or Left', 'revealer' ); ?></option>
                <option value="top-right" <?php if ( $this->options['image_position'] == 'top-right' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Top or Right', 'revealer' ); ?></option>
                <option value="bottom-left" <?php if ( $this->options['image_position'] == 'bottom-left' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Bottom or Left', 'revealer' ); ?></option>
                <option value="bottom-right" <?php if ( $this->options['image_position'] == 'bottom-right' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Bottom or Right', 'revealer' ); ?></option>
                <option value="random" <?php if ( $this->options['image_position'] == 'random' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Random', 'revealer' ); ?></option>
            </select>
            <?php
            
        }

        /**
         * Author position field.
         *
         * @since 1.0.0
         * @access public
         **/
        public function render_author_position() {

            ?>
            <select name="mdp_revealer_settings[author_position]" class="regular-text">
                <option value="without-author" <?php if ( $this->options['author_position'] == 'without-author' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Hide author', 'revealer' ); ?></option>
                <option value="top" <?php if ( $this->options['author_position'] == 'top' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Top', 'revealer' ); ?></option>
                <option value="bottom" <?php if ( $this->options['author_position'] == 'bottom' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Bottom', 'revealer' ); ?></option>
            </select>
            <?php

        }

        /**
         * Category position field.
         *
         * @since 1.0.0
         * @access public
         **/
        public function render_category_position() {

            ?>
            <select name="mdp_revealer_settings[category_position]" class="regular-text">
                <option value="without-category" <?php if ( $this->options['category_position'] == 'without-category' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Hide categories', 'revealer' ); ?></option>
                <option value="top" <?php if ( $this->options['category_position'] == 'top' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Top', 'revealer' ); ?></option>
                <option value="bottom" <?php if ( $this->options['category_position'] == 'bottom' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Bottom', 'revealer' ); ?></option>
            </select>
            <?php

        }

        /**
         * Comments position field.
         *
         * @since 1.0.0
         * @access public
         **/
        public function render_comments_position() {

            ?>
            <select name="mdp_revealer_settings[comments_position]" class="regular-text">
                <option value="without-comments" <?php if ( $this->options['comments_position'] == 'without-comments' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Hide comments', 'revealer' ); ?></option>
                <option value="top" <?php if ( $this->options['comments_position'] == 'top' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Top', 'revealer' ); ?></option>
                <option value="bottom" <?php if ( $this->options['comments_position'] == 'bottom' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Bottom', 'revealer' ); ?></option>
            </select>
            <?php

        }
        
        /**
         * Excerpt Length field.
         *
         * @since 1.0.0
         * @access public
         **/
        public function render_excerpt_length() {
            
            ?>
            <input name="mdp_revealer_settings[excerpt_length]" class="small-text" type="number" min="0" max="500" step="1" pattern="\d*" oninput="this.value=this.value.replace(/[^0-9]/g,'');" value="<?php echo esc_attr( $this->options['excerpt_length'] ); ?>" />
            <p class="description"><?php esc_html_e( 'The length of the text in popup in words.', 'revealer' ); ?></p>
            <?php

        }
        
        /**
         * Render Size field.
         *
         * @since 1.0.0
         * @access public
         **/
        public function render_size() {
            
            ?>
            <input name="mdp_revealer_settings[size]" class="regular-text" type="text" value="<?php echo esc_attr( $this->options['size'] ); ?>" />
            <p class="description"><?php esc_html_e( 'Set the popup size in px, % or em.', 'revealer' ); ?></p>
            <?php

        }
        
        /**
         * Render Arrow field.
         *
         * @since 1.0.0
         * @access public
         **/
        public function render_arrow() {
            
            ?>
            <select name='mdp_revealer_settings[arrow]' class="regular-text">
                <option value="without-arrow" <?php if ( $this->options['arrow'] == 'without-arrow' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Without Arrow', 'revealer' ); ?></option>
                <option value="sharp" <?php if ( $this->options['arrow'] == 'sharp' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Sharp', 'revealer' ); ?></option>
                <option value="round" <?php if ( $this->options['arrow'] == 'round' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Round', 'revealer' ); ?></option>
            </select>
            <p class="description"><?php esc_html_e( 'Determines if an arrow should be added to pointing toward the reference element and the arrow type.', 'revealer' ); ?></p>
            <?php

        }
        
        /**
         * Render Selector field.
         *
         * @since 1.0.0
         * @access public
         **/
        public function render_selector() {
            
            ?>
            <input name="mdp_revealer_settings[selector]" type="text" value="<?php echo esc_attr( $this->options['selector'] ); ?>" class="regular-text">
            <p class="description"><?php esc_html_e( 'Links by selector will be processed. You can specify multiple selectors separated by commas.', 'revealer' ); ?></p>
            <?php
            
        }
        
        /**
         * Render Text Color field.
         *
         * @since 1.0.0
         * @access public
         **/
        public function render_text_color() {
            
            ?><input name="mdp_revealer_settings[text_color]" class="mdp-color-fld" data-alpha="true" type="text" value="<?php echo esc_attr( $this->options['text_color'] ); ?>" /><?php
            
        }
        
        /**
         * Render Tooltip Background Color field.
         *
         * @since 1.0.0
         * @access public
         **/
        public function render_bgcolor() {
            
            ?><input name="mdp_revealer_settings[bgcolor]" class="mdp-color-fld" data-alpha="true" type="text" value="<?php echo esc_attr( $this->options['bgcolor'] ); ?>" /><?php
            
        }
        
        /**
         * Render Animation field.
         *
         * @since 1.0.0
         * @access public
         **/
        public function render_animation() {

            ?>
            <select name='mdp_revealer_settings[animation]' class="regular-text">
                <option value="shift-away" <?php if ( $this->options['animation'] == 'shift-away' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Shift Away', 'revealer' ); ?></option>
                <option value="shift-toward" <?php if ( $this->options['animation'] == 'shift-toward' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Shift Toward', 'revealer' ); ?></option>
                <option value="fade" <?php if ( $this->options['animation'] == 'fade' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Fade', 'revealer' ); ?></option>
                <option value="scale" <?php if ( $this->options['animation'] == 'scale' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Scale', 'revealer' ); ?></option>
                <option value="perspective" <?php if ( $this->options['animation'] == 'perspective' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Perspective', 'revealer' ); ?></option>
            </select>
            <p class="description"><?php esc_html_e( 'The type of transition animation.', 'revealer' ); ?></p>
            <?php
            
        }
        
        /**
         * Render Style field.
         *
         * @since 1.0.0
         * @access public
         **/
        public function render_style() {

            ?>
            <select name='mdp_revealer_settings[style]' class="regular-text">
                <option value="revealer-rectangular" <?php if ( $this->options['style'] == 'revealer-rectangular' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Rectangle', 'revealer' ); ?></option>
                <option value="revealer-rounded" <?php if ( $this->options['style'] == 'revealer-rounded' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Rounded rectangle', 'revealer' ); ?></option>
                <option value="revealer-background" <?php if ( $this->options['style'] == 'revealer-background' ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Image in background', 'revealer' ); ?></option>
            </select>
            <?php
            
        }
        
        /**
         * Get plugin settings with default values.
         *
         * @return array
         **/
        public function get_options() {
            
            /** Options. */
            $options = get_option( 'mdp_revealer_settings' );
            
            /** Default values. */
            $defaults = 
            [
                'selector' => 'article p a', // Selector.
                'style' => 'revealer-rectangular', // Popup Style.
                'animation' => 'shift-toward', // Animation.
                'size' => '320px', // Popup Size.
                'arrow' => 'sharp', // Arrow.
                'excerpt_length' => '15', // Excerpt Length.
                'image_position' => 'top', // Image Position.
                'author_position' => 'bottom', // Author Position.
                'category_position' => 'bottom', // Author Position.
                'comments_position' => 'without-comments', // Author Position.
                'bgcolor' => '#fff', // Background color.
                'text_color' => '#242424', // Text color.
            ];

            $results = wp_parse_args( $options, $defaults );
            
            $this->options = $results;
        }

        /**
         * Loads the Revealer translated strings.
         *
         * @since 1.0.0
         * @access public
         **/
        public function load_textdomain() {
            
            load_plugin_textdomain( 'revealer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
            
        }        
        
        /**
         * Add CSS for admin area.
         *
         * @since   1.0.0
         * @return void
         **/
        public function load_admin_styles( $hook ) {
            
            /** Add styles only on setting page */
            $screen = get_current_screen();
            
            if ( $screen->base == "settings_page_mdp_revealer_settings" ) {
                
                wp_enqueue_style( 'wp-color-picker' ); // Color Picker.
                wp_enqueue_style( 'mdp-revealer-admin', self::$url . 'css/admin' . self::$suffix . '.css', [], self::VERSION );
                
            }
            
        }
        
        /**
         * Add JS for admin area.
         *
         * @since   1.0.0
         * @return void
         **/
        public function load_admin_scripts( $hook ) {
            
            /** Add styles only on setting page */
            $screen = get_current_screen();
            
            if ( $screen->base == "settings_page_mdp_revealer_settings" ) {
                
                wp_enqueue_script( 'wp-color-picker' );
                wp_enqueue_script( 'wp-color-picker-alpha', self::$url . 'js/wp-color-picker-alpha.min.js', ['wp-color-picker'], self::VERSION, true );
                wp_enqueue_script( 'mdp-revealer-admin', self::$url . 'js/admin' . self::$suffix . '.js', ['jquery'], self::VERSION, true );
                
            }
        }
        
        /**
         * Main Revealer Instance.
         *
         * Insures that only one instance of Revealer exists in memory at any one time.
         *
         * @static
         * @return Revealer
         * @since 1.0.0
         **/
        public static function get_instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Revealer ) ) {
                self::$instance = new Revealer;
            }

            return self::$instance;
        }

        /**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 **/
	public function __clone() {
            /** Cloning instances of the class is forbidden. */
            _doing_it_wrong( __FUNCTION__, esc_html__( 'The whole idea of the singleton design pattern is that there is a single object therefore, we don\'t want the object to be cloned.', 'revealer' ), self::VERSION );
	}

        /**
	 * Disable unserializing of the class.
         * 
         * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be unserialized.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 **/
	public function __wakeup() {
            /** Unserializing instances of the class is forbidden. */
            _doing_it_wrong( __FUNCTION__, esc_html__( 'The whole idea of the singleton design pattern is that there is a single object therefore, we don\'t want the object to be unserialized.', 'revealer' ), self::VERSION );
	}

    } // End Class Revealer.
endif; // End if class_exists check.

/** Run Revealer class. */
$Revealer = Revealer::get_instance();