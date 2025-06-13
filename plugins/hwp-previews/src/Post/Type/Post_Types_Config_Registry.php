<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Type;

use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use HWP\Previews\Preview\Helper\Settings_Helper;

/**
 * Class Post_Types_Config_Registry.
 */
class Post_Types_Config_Registry {
	/**
	 * The instance of the post types config.
	 *
	 * @var \HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface|null
	 */
	protected static ?Post_Types_Config_Interface $instance = null;

	/**
	 * Get the instance of the post types config.
	 */
	public static function get_post_type_config(): Post_Types_Config_Interface {
		$instance = self::$instance;

		if ( $instance instanceof Post_Types_Config_Interface ) {
			return $instance;
		}

		$instance = new Post_Types_Config( new Post_Type_Inspector() );
		$instance->set_post_types( Settings_Helper::get_instance()->post_types_enabled() );
		$instance       = apply_filters( 'hwp_previews_get_post_types_config', $instance );
		self::$instance = $instance;

		return $instance;
	}
}
