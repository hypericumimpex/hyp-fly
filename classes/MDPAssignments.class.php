<?php
/** 
 * Revealer add stylish hover pop-up with five animation effects for internal WordPress website links.
 * Exclusively on Envato Market: https://1.envato.market/revealer
 * 
 * @encoding     UTF-8
 * @version      1.0.2
 * @copyright    Copyright (C) 2019 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license      Envato Standard License https://1.envato.market/KYbje
 * @author       Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
 * @support      dmitry@merkulov.design
 **/

namespace Merkulov\Revealer;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

if ( ! class_exists( 'MDPAssignments' ) ) :
    
    /**
     * SINGLETON: Class used to implement Assignments tab on plugin settings page.
     *
     * @since 1.0.0
     * @author Alexandr Khmelnytsky (info@alexander.khmelnitskiy.ua)
     */
    final class MDPAssignments {
    
        /**
         * The one true MDPAssignments.
         * 
	 * @var MDPAssignments
	 * @since 1.0.0
	 **/
	private static $instance;
        
        /**
         * Sets up a new MDPAssignments instance.
         *
         * @since 1.0.0
         * @access public
         **/
        private function __construct() {
            
            /** Load JS and CSS for Backend Area. */
            $this->enqueue_backend();
            
        }
        
        /**
         * Load JS and CSS for Backend Area.
         *
         * @since 1.0.0
         * @access public
         **/
        function enqueue_backend() {
            
            /** Add admin styles. */
            add_action( 'admin_enqueue_scripts', [$this, 'add_admin_styles'] );
            
            /** Add admin javascript. */
            add_action( 'admin_enqueue_scripts', [$this, 'add_admin_scripts'] );
            
        }
        
        /**
         * Add CSS for admin area.
         *
         * @since   1.0.0
         * @return void
         **/
        public function add_admin_styles( $hook ) {
            
            $screen = get_current_screen();
            
            /** Add styles only on Settings page. */
            if ( $screen->base == "settings_page_mdp_revealer_settings" ) {
                wp_enqueue_style( 'mdp-revealer-assignments', \Revealer::$url . 'css/assignments' . \Revealer::$suffix . '.css', [], \Revealer::VERSION );
            }
            
        }
        
        /**
         * Add JS for admin area.
         *
         * @since   1.0.0
         * @return void
         **/
        public function add_admin_scripts( $hook ) {
            
            $screen = get_current_screen();
            
            /** Add styles only on Settings page. */
            if ( $screen->base == "settings_page_mdp_revealer_settings" ) {
                wp_enqueue_script( 'mdp-revealer-assignments', \Revealer::$url . 'js/assignments' . \Revealer::$suffix . '.js', ['jquery'], \Revealer::VERSION, true );
                
                /** Add code editor for Custom PHP. */
                wp_enqueue_code_editor( array( 'type' => 'application/x-httpd-php' ) );
            }
            
        }
        
        /**
         * Generate Assignments Tab.
         *
         * @since 1.0.0
         * @access public
         **/
        public function add_settings() {
            
            /** Assignment Tab. */
            register_setting( 'RevealerAssignmentsOptionsGroup', 'mdp_revealer_assignments_settings' );
            add_settings_section( 'mdp_revealer_settings_page_assignments_section', '', NULL, 'RevealerAssignmentsOptionsGroup' );
            
            /** Assignments. */
            add_settings_field( 'assignments', esc_html__( 'Assignments:', 'revealer' ), [$this, 'render_assignments'], 'RevealerAssignmentsOptionsGroup', 'mdp_revealer_settings_page_assignments_section' );
            
        }
        
        /**
         * Render Assignments field.
         * 
         * @since    1.0.0
         **/
        public function render_assignments() {

            $optval = get_option('mdp_revealer_assignments_settings');
            
            /**
            * Output options list for select
            */
            $options  = array();
            $defaults = array(
                'search'     => 'Search'
            );

            $selected = is_array($options) ? $options : array('*');

            if (count($selected) > 1 && in_array('*', $selected)) {
                $selected = array('*');
            }

            // set default options
            foreach ($defaults as $val => $label) {
                $attributes = in_array($val, $selected) ? array('value' => $val, 'selected' => 'selected') : array('value' => $val);
                $options[]  = sprintf('<option value="%s">%s</option>', $attributes["value"], $label);
            }

            // set pages
            if ($pages = get_pages()) {
                $options[] = '<optgroup label="Pages">';

                array_unshift($pages, (object) array('post_title' => 'Pages (All)'));

                foreach ($pages as $page) {
                    $val = isset($page->ID) ? 'page-' . $page->ID : 'page';
                    $attributes = in_array($val, $selected) ? array('value' => $val, 'selected' => 'selected') : array('value' => $val);
                    $options[]  = sprintf('<option value="%s">%s</option>', $attributes["value"], $page->post_title);
                }

                $options[] = '</optgroup>';
            }

            // set posts
            $options[] = '<optgroup label="Post">';
            foreach (array('home', 'single', 'archive') as $view) {
                $val = $view;
                $attributes = in_array($val, $selected) ? array('value' => $val, 'selected' => 'selected') : array('value' => $val);
                $options[] = sprintf('<option value="%s">%s (%s)</option>', $attributes["value"], 'Post', ucfirst($view));
            }
            $options[] = '</optgroup>';

            // set custom post types
            foreach (array_keys(get_post_types(array('_builtin' => false))) as $posttype) {
                $obj = get_post_type_object($posttype);
                $label = ucfirst($posttype);

                if ($obj->publicly_queryable) {
                    $options[] = '<optgroup label="' . $label.'">';

                    foreach (array('single', 'archive', 'search') as $view) {
                        $val = $posttype . '-' . $view;
                        $attributes = in_array($val, $selected) ? array('value' => $val, 'selected' => 'selected') : array('value' => $val);
                        $options[] = sprintf('<option value="%s">%s (%s)</option>', $attributes["value"], $label, ucfirst($view));
                    }

                    $options[] = '</optgroup>';
                }
            }

            // set categories
            foreach (array_keys(get_taxonomies()) as $tax) {

                if(in_array($tax, array("post_tag", "nav_menu"))) continue;

                if ($categories = get_categories(array( 'taxonomy' => $tax ))) {
                    $options[] = '<optgroup label="Categories ('.ucfirst(str_replace(array("_","-")," ",$tax)).')">';

                    foreach ($categories as $category) {
                        $val        = 'cat-' . $category->cat_ID;
                        $attributes = in_array($val, $selected) ? array('value' => $val, 'selected' => 'selected') : array('value' => $val);
                        $options[]  = sprintf('<option value="%s">%s</option>', $attributes["value"], $category->cat_name);
                    }

                    $options[] = '</optgroup>';
                }
            }
            
            // Set Default value 
            if( ! isset( $optval['mdp_revealer_assignments_field'] ) ) {
                $optval['mdp_revealer_assignments_field'] = "{|matchingMethod|:1,|WPContent|:0,|WPContentVal|:||,|homePage|:0,|menuItems|:0,|menuItemsVal|:||,|dateTime|:0,|dateTimeStart|:||,|dateTimeEnd|:||,|userRoles|:0,|userRolesVal|:||,|URL|:0,|URLVal|:||,|devices|:0,|devicesVal|:||,|PHP|:0,|PHPVal|:||}";
            }
            ?>

            <input type='hidden' class="mdp-width-1-1" id="mdp-assignInput" name='mdp_revealer_assignments_settings[mdp_revealer_assignments_field]' value='<?php echo esc_attr( $optval['mdp_revealer_assignments_field'] ); ?>'>

            <div id="mdp-assign-box">

                <div class="mdp-all-fields">
                    <div class="mdp-alert"><?php esc_html_e('By selecting the specific assignments you can limit where plugin should or shouldn\'t be published. To have it published on all pages, simply do not specify any assignments.'); ?></div>
                    <div class="mdp-panel mdp-panel-box mdp-matching-method mdp-margin">
                        <h4><?php esc_html_e('Matching Method', 'revealer'); ?></h4>
                        <p><?php esc_html_e('Should all or any assignments be matched?', 'revealer'); ?></p>
                        <div class="mdp-button-group mdp-button-group mdp-matchingMethod" data-mdp-button-radio="">
                            <button class="mdp-button mdp-button-success mdp-button-small mdp-all mdp-active"><?php esc_html_e('All', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-success mdp-button-small mdp-any"><?php esc_html_e('Any', 'revealer'); ?></button>
                        </div>
                        <p>
                            <strong><?php esc_html_e('All', 'revealer'); ?></strong><?php esc_html_e(' — Will be published if ', 'revealer'); ?><strong><?php esc_html_e('All', 'revealer'); ?></strong><?php esc_html_e(' of below assignments are matched.', 'revealer'); ?><br>
                            <strong><?php esc_html_e('Any', 'revealer'); ?></strong><?php esc_html_e(' — Will be published if ', 'revealer'); ?><strong><?php esc_html_e('Any', 'revealer'); ?></strong><?php esc_html_e(' (one or more) of below assignments are matched.', 'revealer'); ?><br>
                        </p>
                    </div>

                    <div class="mdp-panel mdp-panel-box mdp-wp-content mdp-margin">

                        <h4><?php esc_html_e( 'WordPress Content', 'revealer' ); ?></h4>

                        <div class="mdp-button-group mdp-button-group" data-mdp-button-radio="">
                            <button class="mdp-button mdp-button-primary mdp-button-small mdp-ignore mdp-active"><?php esc_html_e('Ignore', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-success mdp-button-small mdp-include"><?php esc_html_e('Include', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-danger mdp-button-small mdp-exclude"><?php esc_html_e('Exclude', 'revealer'); ?></button>
                        </div>

                        <div class="mdp-wp-content-box">
                            <p class="mdp-margin-bottom-remove mdp-margin-top">
                                <?php esc_html_e('Select on what page types or categories the assignment should be active.', 'revealer'); ?>
                            </p>
                            <select class="wp-content chosen-select" multiple="multiple">
                                <option value=""></option>
                                <?php  echo wp_kses( implode( '', $options ), ['option' => ['value' => [] ], 'optgroup' => ['label' => [] ] ] ); ?>
                            </select>
                        </div>

                    </div>

                    <div class="mdp-panel mdp-panel-box mdp-home-page mdp-margin">

                        <h4><?php esc_html_e('Home Page', 'revealer'); ?></h4>

                        <div class="mdp-button-group mdp-button-group" data-mdp-button-radio="">
                            <button class="mdp-button mdp-button-primary mdp-button-small mdp-ignore mdp-active"><?php esc_html_e('Ignore', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-success mdp-button-small mdp-include"><?php esc_html_e('Include', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-danger mdp-button-small mdp-exclude"><?php esc_html_e('Exclude', 'revealer'); ?></button>
                        </div>

                    </div>

                    <div class="mdp-panel mdp-panel-box mdp-menu-items mdp-margin">
                        <h4><?php esc_html_e('Menu Items', 'revealer'); ?></h4>
                        <div class="mdp-button-group mdp-button-group" data-mdp-button-radio="">
                            <button class="mdp-button mdp-button-primary mdp-button-small mdp-ignore mdp-active"><?php esc_html_e('Ignore', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-success mdp-button-small mdp-include"><?php esc_html_e('Include', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-danger mdp-button-small mdp-exclude"><?php esc_html_e('Exclude', 'revealer'); ?></button>
                        </div>

                        <div class="mdp-menuitems-selection">
                            <p class="mdp-margin-bottom-remove mdp-margin-top"><?php esc_html_e('Select the menu items to assign to.', 'revealer'); ?></p>
                            <select class="menuitems chosen-select" multiple="">
                                    <option value=""></option>
                                    <?php

                                    /** Get all menus */
                                    $menus = get_terms('nav_menu');
                                    foreach ($menus as $menu) {
                                        ?><optgroup label="<?php echo esc_attr( $menu->name ); ?>"><?php
                                            $navMenuItems = wp_get_nav_menu_items($menu->slug);
                                            $menuTree = $this->menu_object_tree($navMenuItems);
                                            $this->printMenuTree($menuTree, $menu->slug, 0);
                                        ?></optgroup><?php
                                    }
                                    ?>
                            </select>
                        </div>

                    </div>
                    
                    <div class="mdp-panel mdp-panel-box mdp-date-time mdp-margin">
                        
                        <h4><?php esc_html_e( 'Date & Time', 'revealer' ); ?></h4>

                        <div class="mdp-button-group mdp-button-group" data-mdp-button-radio="">
                            <button class="mdp-button mdp-button-primary mdp-button-small mdp-ignore mdp-active"><?php esc_html_e('Ignore', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-success mdp-button-small mdp-include"><?php esc_html_e('Include', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-danger mdp-button-small mdp-exclude"><?php esc_html_e('Exclude', 'revealer'); ?></button>
                        </div>

                        <div class="mdp-period-picker-box">
                            <p class="mdp-period-picker uk-margin-top">
                                <input class="mdp-period-picker-start" id="mdp-period-picker-start" type="text" value="" />
                                <input class="mdp-period-picker-end" id="mdp-period-picker-end" type="text" value="" />
                            </p>

                            <p>
                                <?php esc_html_e( 'The date and time assignments use the date/time of your servers, not that of the visitors system.', 'revealer' ); ?><br>
                                <?php esc_html_e( 'Current date/time:', 'revealer' ); ?> <strong><?php echo date( 'd.m.Y H:i' ); ?></strong>
                            </p>
                        </div>

                    </div>
                    
                    <div class="mdp-panel mdp-panel-box mdp-user-roles mdp-margin">

                        <h4><?php esc_html_e( 'User Roles', 'revealer' ); ?></h4>
                        
                        <div class="mdp-button-group mdp-button-group" data-mdp-button-radio="">
                            <button class="mdp-button mdp-button-primary mdp-button-small mdp-ignore mdp-active"><?php esc_html_e('Ignore', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-success mdp-button-small mdp-include"><?php esc_html_e('Include', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-danger mdp-button-small mdp-exclude"><?php esc_html_e('Exclude', 'revealer'); ?></button>
                        </div>

                        <div class="user-roles-box">
                            <p class="mdp-margin-remove-bottom mdp-margin-top"><?php esc_html_e( 'Select the user roles to assign to.', 'revealer' ); ?></p>
                            <select class="user-roles chosen-select" multiple="">
                                <option value=""></option>
                                <?php // Get user roles
                                $roles = get_editable_roles();
                                foreach ( $roles as $k => $role ) {
                                    ?><option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $role['name'] ); ?></option><?php
                                } ?>
                            </select>
                        </div>

                    </div>

                    <div class="mdp-panel mdp-panel-box mdp-url mdp-margin">

                        <h4><?php esc_html_e( 'URL', 'revealer' ); ?></h4>

                        <div class="mdp-button-group mdp-button-group" data-mdp-button-radio="">
                            <button class="mdp-button mdp-button-primary mdp-button-small mdp-ignore mdp-active"><?php esc_html_e('Ignore', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-success mdp-button-small mdp-include"><?php esc_html_e('Include', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-danger mdp-button-small mdp-exclude"><?php esc_html_e('Exclude', 'revealer'); ?></button>
                        </div>

                        <div class="mdp-url-box">
                            <p class="mdp-margin-bottom-remove mdp-margin-top">
                                <?php esc_html_e('Enter (part of) the URLs to match.', 'revealer'); ?><br>
                                <?php esc_html_e('Use a new line for each different match.', 'revealer'); ?>
                            </p>

                            <textarea class="mdp-url-field"></textarea>

                        </div>

                    </div>
                    
                    <div class="mdp-panel mdp-panel-box mdp-devices mdp-margin">

                        <h4><?php esc_html_e( 'Devices', 'revealer' ); ?></h4>

                        <div class="mdp-button-group mdp-button-group" data-mdp-button-radio="">
                            <button class="mdp-button mdp-button-primary mdp-button-small mdp-ignore mdp-active"><?php esc_html_e('Ignore', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-success mdp-button-small mdp-include"><?php esc_html_e('Include', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-danger mdp-button-small mdp-exclude"><?php esc_html_e('Exclude', 'revealer'); ?></button>
                        </div>

                        <div class="mdp-devices-box">
                            <p class="mdp-margin-remove-bottom mdp-margin-top"><?php esc_html_e( 'Select the devices to assign to. Keep in mind that device detection is not always 100% accurate. Users can setup their device to mimic other devices.', 'revealer' ); ?></p>
                            <select class="devices chosen-select" multiple="">
                                <option value=""></option>
                                <option value="desktop"><?php esc_html_e( 'Desktop', 'revealer' ); ?></option>
                                <option value="tablet"><?php esc_html_e( 'Tablet', 'revealer' ); ?></option>
                                <option value="mobile"><?php esc_html_e( 'Mobile', 'revealer' ); ?></option>
                            </select>
                        </div>

                    </div>

                    <div class="mdp-panel mdp-panel-box mdp-php mdp-margin">

                        <h4><?php esc_html_e( 'Custom PHP', 'revealer' ); ?></h4>
                        
                        <div class="mdp-button-group mdp-button-group" data-mdp-button-radio="">
                            <button class="mdp-button mdp-button-primary mdp-button-small mdp-ignore mdp-active"><?php esc_html_e('Ignore', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-success mdp-button-small mdp-include"><?php esc_html_e('Include', 'revealer'); ?></button>
                            <button class="mdp-button mdp-button-danger mdp-button-small mdp-exclude"><?php esc_html_e('Exclude', 'revealer'); ?></button>
                        </div>

                        <div class="mdp-php-box">
                            <p class="mdp-margin-bottom-remove mdp-margin-top">
                                <?php esc_html_e('Enter a piece of PHP code to evaluate. The code must return the value true or false.', 'revealer'); ?><br>
                                <?php esc_html_e('For instance:', 'revealer'); ?>
                            </p>
<pre>$dayofweek = date('w');
if( '5' == $dayofweek ) { // Work only on Fridays.
    $result = true;
} else {
    $result = false;
}
return $result;</pre>

                            <textarea id="mdp-php-field" name="mdp-php-field" class="mdp-php-field"></textarea>

                        </div>
                        
                    </div>

                </div>

            </div>

            <?php   
        }
        
        /**
         * Checks if a plugin should work on current page.
         * 
         * @return boolean
         * 
         * @since 1.0.0
         * @access protected
         **/
        public function display() {
            
            /** Get plugin options. */
            $options = get_option('mdp_revealer_assignments_settings');
            
            /** Assignments. */
            if( ! isset($options['mdp_revealer_assignments_field'] ) ) {
                $options['mdp_revealer_assignments_field'] = "{|matchingMethod|:1,|WPContent|:0,|WPContentVal|:||,|homePage|:0,|menuItems|:0,|menuItemsVal|:||,|dateTime|:0,|dateTimeStart|:||,|dateTimeEnd|:||,|userRoles|:0,|userRolesVal|:||,|URL|:0,|URLVal|:||,|devices|:0,|devicesVal|:||,|PHP|:0,|PHPVal|:||}";
            }
            
            /** Get assignments for plugin. */
            $assignment = json_decode( str_replace('|', '"', $options['mdp_revealer_assignments_field']) );
            
            if( ! $assignment ) { return true; } // If no settings - Show plugin Everywhere

            /** WordPress Content. */
            $wordPressContent = $this->WordPressContent( $assignment );

            /** Home Page. */
            $homePage = $this->HomePage( $assignment );

            /** Menu Items. */
            $menuItems = $this->MenuItems( $assignment );
            
            /** Date & Time */
            $dateTime = $this->DateTime( $assignment );
            
            /** User Roles. */
            $userRoles = $this->UserRoles( $assignment );

            /** URL. */
            $URL = $this->URL( $assignment );
            
            /** Devices. */
            $devices = $this->Devices( $assignment );
            
            /** Custom PHP. */
            $PHP = $this->PHP( $assignment );

            /** Matching Method. */
            $result = $this->MatchingMethod( $assignment, $wordPressContent, $homePage, $menuItems, $dateTime, $userRoles, $URL, $devices, $PHP );

            return $result;
            
        }
        
        /**
         * Plugin Assignments - Date & Time.
         * 
         * @since 1.0.0
         * @access protected
         **/
        protected function DateTime( $assignment ) {

            /** If no dateTime - ignore. */
            if ( $assignment->dateTimeStart == "" or $assignment->dateTimeEnd == "" ) {
                $result = -1;
                return $result;
            }

            $time = time();
            $s = strtotime($assignment->dateTimeStart) - $time;
            $e = strtotime($assignment->dateTimeEnd) - $time;

            switch ( $assignment->dateTime ) {
                case 0: // Ignore
                    $result = -1;
                    break;

                case 1: // Include
                    $result = FALSE;
                    if ($s <= 0 AND $e >= 0) {
                        $result = TRUE;
                    }
                    break;

                case 2: // Exclude
                    $result = TRUE;
                    if ($s <= 0 AND $e >= 0) {
                        $result = FALSE;
                    }
                    break;
            }
            
            return $result;
        }

        /**
         * Plugin assignments - WordPress Content.
         * 
         * @since 1.0.0
         * @access protected
         **/
        protected function WordPressContent($assignment) {

            $result = -1;

            switch ($assignment->WPContent) {
                case 0: // Ignore
                    $result = -1;
                    break;

                case 1: // Include
                    $result = FALSE;
                    if (!$assignment->WPContentVal) {
                        $result = -1;
                        return $result;
                    } // If no menu items - ignore

                    $query = $this->getQuery();
                    foreach ($query as $q) {
                        if (in_array($q, $assignment->WPContentVal)) {
                            $result = TRUE;
                            return $result;
                        }
                    }
                    break;

                case 2: // Exclude
                    $result = TRUE;
                    if (!$assignment->WPContentVal) {
                        $result = -1;
                        return $result;
                    } // If no menu items - ignore

                    $query = $this->getQuery();
                    foreach ($query as $q) {
                        if (in_array($q, $assignment->WPContentVal)) {
                            $result = FALSE;
                            return $result;
                        }
                    }
                    break;
            }

            return $result;
        }
        
        /**
         * Plugin assignments - Home Page.
         * 
         * @since 1.0.0
         * @access protected
         **/
        protected function HomePage($assignment) {
            switch ($assignment->homePage) {
                case 0: // Ignore
                    $result = -1;
                    break;

                case 1: // Include
                    $result = FALSE;
                    if (is_front_page()) {
                        $result = TRUE;
                    }
                    break;

                case 2: // Exclude
                    $result = TRUE;
                    if (is_front_page()) {
                        $result = FALSE;
                    }
                    break;
            }
            return $result;
        }

        /**
         * Plugin assignments - Menu Items.
         * 
         * @since 1.0.0
         * @access protected
         **/
        protected function MenuItems($assignment) {

            $result = -1;

            // If wrong input array - Ignore
            if (!is_array($assignment->menuItemsVal)) {
                $result = -1;
                return $result;
            }

            // Current URL
            $curUrl = "";
            if (!isset($_SERVER["HTTPS"]) || ($_SERVER["HTTPS"] != 'on')) {
                $curUrl = 'http://' . $_SERVER["SERVER_NAME"];
            } else {
                $curUrl = 'https://' . $_SERVER["SERVER_NAME"];
            }
            $curUrl .= $_SERVER["REQUEST_URI"];

            switch ($assignment->menuItems) {
                case 0: // Ignore
                    $result = -1;
                    break;

                case 1: // Include
                    $result = FALSE;
                    if (!$assignment->menuItemsVal) {
                        $result = -1;
                        return $result;
                    } // If no menu items - ignore

                    $menu_items_arr = array(); // Assignments menu items
                    foreach ($assignment->menuItemsVal as $val) {
                        if ($val == "") {
                            continue;
                        }
                        list($menuSlug, $menuItemID) = explode("+", $val);
                        $menu_items = wp_get_nav_menu_items($menuSlug);
                        $menu_item = wp_filter_object_list($menu_items, array('ID' => $menuItemID));
                        $menu_items_arr[] = reset($menu_item);
                    }

                    foreach ($menu_items_arr as $mItem) {
                        if ($curUrl == $mItem->url) {
                            $result = TRUE;
                            return $result;
                        }
                    }
                    break;

                case 2: // Exclude
                    $result = TRUE;
                    if (!$assignment->menuItemsVal) {
                        $result = -1;
                        return $result;
                    } // If no menu items - ignore

                    $menu_items_arr = array(); // Assignments menu items

                    foreach ($assignment->menuItemsVal as $val) {
                        list($menuSlug, $menuItemID) = explode("+", $val);
                        $menu_items = wp_get_nav_menu_items($menuSlug);
                        $menu_item = wp_filter_object_list($menu_items, array('ID' => $menuItemID));
                        $menu_items_arr[] = reset($menu_item);
                    }

                    foreach ($menu_items_arr as $mItem) {
                        if ($curUrl == $mItem->url) {
                            $result = FALSE;
                            return $result;
                        }
                    }
                    break;
            }
            
            return $result;
        }
        
        /**
         * Plugin assignments - User Roles.
         * 
         * @since 1.0.0
         * @access protected
         **/
        protected function UserRoles( $assignment ) {

            // If wrong input array - Ignore
            if (!is_array($assignment->userRolesVal)) {
                $result = -1;
                return $result;
            }
            
            include_once( ABSPATH . 'wp-includes/pluggable.php' );

            switch ($assignment->userRoles) {
                case 0: // Ignore
                    $result = -1;
                    break;

                case 1: // Include
                    $result = FALSE;
                    $user = wp_get_current_user();
                    foreach ($user->roles as $role) {
                        if (in_array($role, $assignment->userRolesVal)) {
                            $result = TRUE;
                        }
                    }
                    break;

                case 2: // Exclude
                    $result = TRUE;
                    $user = wp_get_current_user();
                    foreach ($user->roles as $role) {
                        if (in_array($role, $assignment->userRolesVal)) {
                            $result = FALSE;
                        }
                    }
                    break;
            }
            return $result;
        }
        
        /**
         * Plugin assignments - Devices.
         * 
         * @since 1.0.0
         * @access protected
         **/
        protected function Devices( $assignment ) {
            
            /** Add Mobile Detect Class. */
            require_once ( wp_normalize_path( \Revealer::$path . '/classes/MDPMobile_Detect.php' ) );
            
            $detect = new MDPMobile_Detect;

            /** Detect current user device. */
            $device = 'desktop';
            if ( $detect->isTablet() ) { $device = 'tablet'; }
            if( $detect->isMobile() && !$detect->isTablet() ) { $device = 'mobile'; }
            
            // If wrong input array - Ignore
            if ( ! is_array( $assignment->devicesVal ) ) {
                $result = -1;
                return $result;
            }

            switch ( $assignment->devices ) {
                case 0: // Ignore
                    $result = -1;
                    break;

                case 1: // Include
                    $result = FALSE;
                    if ( in_array( $device, $assignment->devicesVal ) ) {
                        $result = TRUE;
                    }
                    break;

                case 2: // Exclude
                    $result = TRUE;
                    if ( in_array( $device, $assignment->devicesVal ) ) {
                        $result = FALSE;
                    }
                    break;
            }
            
            return $result;
        }

        /**
         * Plugin assignments - URL.
         * 
         * @since 1.0.0
         * @access protected
         **/
        protected function URL($assignment) {

            // Current URL
            $curUrl = "";
            if (!isset($_SERVER["HTTPS"]) || ($_SERVER["HTTPS"] != 'on')) {
                $curUrl = 'http://' . $_SERVER["SERVER_NAME"];
            } else {
                $curUrl = 'https://' . $_SERVER["SERVER_NAME"];
            }
            $curUrl .= $_SERVER["REQUEST_URI"];

            $URLVal = (array) preg_split('/\r\n|[\r\n]/', $assignment->URLVal);
            $URLVal = array_filter($URLVal, function($value) {
                if (trim($value) != "") {
                    return $value;
                }
            });

            switch ($assignment->URL) {
                case 0: // Ignore
                    $result = -1;
                    break;

                case 1: // Include
                    $result = FALSE;
                    if (count($URLVal) == 0) {
                        $result = FALSE;
                    } // If no URLS to include - hide widget
                    foreach ($URLVal as $u) {
                        if (strpos($curUrl, $u) !== false) {
                            $result = TRUE;
                        }
                    }

                    break;

                case 2: // Exclude
                    $result = TRUE;
                    if (count($URLVal) == 0) {
                        $result = TRUE;
                    } // If no URLS to exclude - show widget
                    foreach ($URLVal as $u) {
                        if (strpos($curUrl, $u) !== false) {
                            $result = FALSE;
                        }
                    }
                    break;
            }
            
            return $result;
        }
        
        /**
         * Plugin assignments - Custom PHP.
         * 
         * @since 1.0.0
         * @access protected
         **/
        protected function PHP( $assignment ) {

            /** Replace <?php and other fix stuff. */
            $php = trim( $assignment->PHPVal );
            
            $p = substr( $php, 0, 5 );
            if ( strtolower( $p ) == '<?php' ) {
                $php = substr( $php, 5 );
            }
            
            $php = trim( $php );
            
            if ( $php == '' ) { $result = -1; }

            $php .= ';return true;';
            
            $temp_PHP_func = create_function( '', $php );
            
            /** Evaluate the script. */ 
            ob_start();
                $pass = (bool) $temp_PHP_func( '' );
                unset($temp_PHP_func);
            ob_end_clean();
            
            switch ( $assignment->PHP ) {
                case 0: // Ignore
                    $result = -1;
                    break;

                case 1: // Include
                    $result = FALSE;
                    if ( $pass ) {
                        $result = TRUE;
                    }

                    break;

                case 2: // Exclude
                    $result = TRUE;
                    if ( $pass ) {
                        $result = FALSE;
                    }
                    break;
            }
            
            return $result;
        }

        /**
         * Plugin assignments - Matching Method.
         * 
         * @since 1.0.0
         * @access protected
         **/
        protected function MatchingMethod( $assignment, $wordPressContent, $homePage, $menuItems, $dateTime, $userRoles, $URL, $devices, $PHP ) {

            $arrCond = array(); // Add condition values

            // Ignore if -1 
            if ( $wordPressContent != -1 ) {
                $arrCond[] = $wordPressContent;
            }
            
            if ( $homePage != -1 ) {
                $arrCond[] = $homePage;
            }
            
            if ( $menuItems != -1 ) {
                $arrCond[] = $menuItems;
            }
            
            if ( $dateTime != -1 ) {
                $arrCond[] = $dateTime;
            }
            
            if ( $userRoles != -1 ) { 
                $arrCond[] = $userRoles;
            }
            
            if ( $URL != -1 ) {
                $arrCond[] = $URL;
            }
            
            if ( $devices != -1 ) { 
                $arrCond[] = $devices;
            }
            
            if ( $PHP != -1 ) {
                $arrCond[] = $PHP;
            }

            if ( ! count( $arrCond ) ) {
                $arrCond[] = TRUE;
            } 
            
            // If all rules are Ignore - Show widget
            // Initialization
            $anytrue = false;
            $alltrue = true;

            // Processing
            foreach ($arrCond as $v) {
                $anytrue |= $v;
                $alltrue &= $v;
            }

            // Result
            if ($alltrue) {
                // All elements are TRUE
                $result = TRUE;
            } elseif (!$anytrue) {
                // All elements are FALSE
                $result = FALSE;
            } else {
                // Mixed values
                if ($assignment->matchingMethod == 0) { // ALL RULES
                    $result = FALSE;
                } else { // ANY OF RULES
                    $result = TRUE;
                }
            }

            return $result;
        }
        
        /**
         * Create tree view form menu items array.
         * 
         * @param type $nav_menu_items_array
         * @return type
         **/
        public function menu_object_tree($nav_menu_items_array) {
           foreach ($nav_menu_items_array as $key => $value) {
               $value->children = array();
               $nav_menu_items_array[$key] = $value;
           }

           $nav_menu_levels = array();
           $index = 0;
           if (!empty($nav_menu_items_array))
               do {
                   if ($index == 0) {
                       foreach ($nav_menu_items_array as $key => $obj) {
                           if ($obj->menu_item_parent == 0) {
                               $nav_menu_levels[$index][] = $obj;
                               unset($nav_menu_items_array[$key]);
                           }
                       }
                   } else {
                       foreach ($nav_menu_items_array as $key => $obj) {
                            if( is_array( $last_level_ids ) ){
                                if ( in_array( $obj->menu_item_parent, $last_level_ids ) ) {
                                    $nav_menu_levels[$index][] = $obj;
                                    unset($nav_menu_items_array[$key]);
                                }
                            }
                       }
                   }
                   $last_level_ids = wp_list_pluck($nav_menu_levels[$index], 'db_id');
                   $index++;
               } while (!empty($nav_menu_items_array));

           $nav_menu_levels_reverse = array_reverse($nav_menu_levels);

           $nav_menu_tree_build = array();
           $index = 0;
           if (!empty($nav_menu_levels_reverse))
               do {
                   if (count($nav_menu_levels_reverse) == 1) {
                       $nav_menu_tree_build = $nav_menu_levels_reverse;
                   }
                   $current_level = array_shift($nav_menu_levels_reverse);
                   if (isset($nav_menu_levels_reverse[$index])) {
                       $next_level = $nav_menu_levels_reverse[$index];
                       foreach ($next_level as $nkey => $nval) {
                           foreach ($current_level as $ckey => $cval) {
                               if ($nval->db_id == $cval->menu_item_parent) {
                                   $nval->children[] = $cval;
                               }
                           }
                       }
                   }
               } while (!empty($nav_menu_levels_reverse));

           $nav_menu_object_tree = $nav_menu_tree_build[0];
           return $nav_menu_object_tree;
        }       
       
        /**
         * Output options list for select.
         * 
         * @since    1.0.0
         **/
        public function printMenuTree($arr, $menuslug, $level = 0) {
            foreach ( $arr as $item ) {
                ?><option value="<?php echo esc_attr( $menuslug . "+" . $item->ID ); ?>"><?php echo esc_html( str_repeat( '-', $level ) . ' ' . $item->title . ' (' . $item->type_label . ')' ); ?></option><?php
                if ( count( $item->children ) ) {
                    $this->printMenuTree( $item->children, $menuslug, $level + 1 );
                }
            }
        }
        
        /**
         * Get current query information.
         *
         * @global \WP_Query $wp_query
         *
         * @return string[]
         **/
        public function getQuery() {
            global $wp_query;

            // create, if not set
            if ( empty($this->query ) ) {

                // init vars
                $obj = $wp_query->get_queried_object();
                $type = get_post_type();
                $query = array();

                if ( is_home() ) {
                    $query[] = 'home';
                }

                if ( is_front_page() ) {
                    $query[] = 'front_page';
                }

                if ( $type == 'post' ) {

                    if ( is_single() ) {
                        $query[] = 'single';
                    }

                    if ( is_archive() ) {
                        $query[] = 'archive';
                    }
                } else {
                    if ( is_single() ) {
                        $query[] = $type . '-single';
                    } elseif ( is_archive() ) {
                        $query[] = $type . '-archive';
                    }
                }

                if ( is_search() ) {
                    $query[] = 'search';
                }

                if ( is_page() ) {
                    $query[] = $type;
                    $query[] = $type . '-' . $obj->ID;
                }

                if ( is_category() ) {
                    $query[] = 'cat-' . $obj->term_id;
                }

                // WooCommerce
                include_once( ABSPATH.'wp-admin/includes/plugin.php' );
                if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

                    if ( is_shop() && !is_search() ) {
                        $query[] = 'page';
                        $query[] = 'page-' . wc_get_page_id( 'shop' );
                    }

                    if ( is_product_category() || is_product_tag() ) {
                        $query[] = 'cat-' . $obj->term_id;
                    }
                }

                $this->query = $query;
            }

            return $this->query;
        }
    
        /**
         * Main MDPAssignments Instance.
         *
         * Insures that only one instance of MDPAssignments exists in memory at any one time.
         *
         * @static
         * @return MDPAssignments
         * @since 1.0.0
         **/
        public static function get_instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof MDPAssignments ) ) {
                self::$instance = new MDPAssignments;
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
            _doing_it_wrong( __FUNCTION__, esc_html__( 'The whole idea of the singleton design pattern is that there is a single object therefore, we don\'t want the object to be cloned.', 'revealer' ), \Revealer::VERSION );
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
            _doing_it_wrong( __FUNCTION__, esc_html__( 'The whole idea of the singleton design pattern is that there is a single object therefore, we don\'t want the object to be unserialized.', 'revealer' ), \Revealer::VERSION );
	}
    
    } // End Class MDPAssignments.

endif; // End if class_exists check.

/** Run MDPAssignments class. */
//$MDPAssignments = MDPAssignments::get_instance();