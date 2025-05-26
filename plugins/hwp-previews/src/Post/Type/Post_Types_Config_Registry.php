<?php

declare( strict_types=1 );

namespace HWP\Previews\Post\Type;

use HWP\Previews\Admin\Settings\Helper\Settings_Helper;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;

/**
 * Class Post_Types_Config_Registry.
 */
class Post_Types_Config_Registry {

	protected static ?Post_Types_Config_Interface $instance = null;

	public static function get_post_type_config(): Post_Types_Config_Interface {
		$instance = self::$instance;

		if ( $instance instanceof Post_Types_Config_Interface ) {
			return $instance;
		}

		$instance = new Post_Types_Config( new Post_Type_Inspector() );
		$instance->set_post_types( Settings_Helper::get_instance()->post_types_enabled() );
		$instance       = apply_filters( 'hwp_preview_get_post_types_config', $instance );
		self::$instance = $instance;

		return $instance;
	}

}
