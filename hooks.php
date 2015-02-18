<?php if (!defined('FW')) die('Forbidden');

/**
 * Just for removing FW_Theme_Menu_Walker set in the previous
 * filter when the fallback menu is in action.
 * @param array $args
 * @return array
 * @internal
 */
function _filter_fw_ext_mega_menu_wp_page_menu_args($args) {
	if ($args['walker'] instanceof FW_Theme_Menu_Walker) {
		$args['walker'] = '';
	}

	return $args;
}
add_filter('wp_page_menu_args', '_filter_fw_ext_mega_menu_wp_page_menu_args');

/**
 * @param array $args
 * @return array
 * @internal
 */
function _filter_fw_ext_mega_menu_wp_nav_menu_args($args) {
	// nav-menu-template.php L271
	// $args['menu'] = ...

	// nav-menu-template.php L363
	// $args['menu_id'] = 'xxx-menu-id';
	// $args['menu_class'] = 'xxx-menu-class';

	// nav-menu-template.php L311
	// $args['container'] = 'xxx-container'; // should be in apply_filters('wp_nav_menu_container_allowedtags')
	// $args['container_id'] = 'xxx-container-id';
	// $args['container_class'] = 'xxx-container-class';

	// nav-menu-template.php L151
	// $args['before'] = 'xxx-before';
	// $args['after'] = 'xxx-after';
	// $args['link_before'] = 'xxx-link-before';
	// $args['link_after'] = 'xxx-link-after';

	// nav-menu-template.php L405
	// $args['items_wrap'] = '<ul id="%1$s" class="%2$s">%3$s</ul>';

	// FIXME Just for making https://github.com/ThemeFuse/Unyson/issues/79 workable.
	if ($args['theme_location'] == 'primary') {
		$args['walker'] = new FW_Theme_Menu_Walker();
	}

	return $args;
}
add_filter('wp_nav_menu_args', '_filter_fw_ext_mega_menu_wp_nav_menu_args');

/**
 * @param [WP_Post] $sorted_menu_items
 * @param $args
 * @return array
 * @internal
 */
function _filter_fw_ext_mega_menu_wp_nav_menu_objects($sorted_menu_items, $args) {
	// <li id="menu-item-1234" class="menu-item menu-item-type-post_type ... mega-menu">
	//     ....
	// </li>

	$mega_menu = array();
	foreach ($sorted_menu_items as $item) {
		if ($item->menu_item_parent == 0 && fw_ext_mega_menu_get_meta($item, 'enabled')) {
			$mega_menu[$item->ID] = true;
		}
	}

	foreach ($sorted_menu_items as $item) {
		if (isset($mega_menu[$item->ID])) {
			$item->classes[] = 'menu-item-has-mega-menu';
		}
		if (isset($mega_menu[$item->menu_item_parent])) {
			$item->classes[] = 'mega-menu-col';
		}
		if (fw_ext_mega_menu_get_meta($item, 'icon')) {
			$item->classes[] = 'menu-item-has-icon';
		}
	}

	return $sorted_menu_items;
}
add_filter('wp_nav_menu_objects', '_filter_fw_ext_mega_menu_wp_nav_menu_objects', 10, 2);

/**
 * nav-menu-template.php L141
 * Walker_Nav_Menu::start_el
 *
 * @param $attr
 * @param $item
 * @param $args
 * @return mixed
 * @internal
 */
function _filter_fw_ext_mega_menu_nav_menu_link_attributes($attr, $item, $args) {
	// item_output = {{before}}<a {{ attr }}>{{ link_before }}{% the_title %}{{ link_after }}</a>{{ after }}

	return $attr;
}
add_filter('nav_menu_link_attributes', '_filter_fw_ext_mega_menu_nav_menu_link_attributes', 10, 3);

/**
 * nav-menu-template.php L174
 * Walker_Nav_Menu::start_el
 *
 * @param $item_output
 * @param $item
 * @param $depth
 * @param $args
 * @return string
 * @internal
 */
function _filter_fw_ext_mega_menu_walker_nav_menu_start_el($item_output, $item, $depth, $args) {
	// <li>
	//     {{ item_output }}
	//     <div>{{ item.description }}</div>
	//     <div class="mega-menu">
	//         <ul class="sub-menu"></ul>
	//     </div>
	// </li>

	if ($depth > 0 && fw_ext_mega_menu_get_meta($item, 'title-off')) {
		$item_output = '';
	}

	// Note that raw description is stored in post_content field.
	if ($depth > 0 && trim($item->post_content)) {
		$item_output .= '<div>' . $item->post_content . '</div>';
	}

	return $item_output;
}
add_filter('walker_nav_menu_start_el', '_filter_fw_ext_mega_menu_walker_nav_menu_start_el', 10, 4);