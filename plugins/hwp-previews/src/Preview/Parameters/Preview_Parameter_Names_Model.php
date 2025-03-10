<?php

declare( strict_types=1 );

namespace HWP\Previews\Preview\Parameters;

use HWP\Previews\Shared\Model;

/**
 * If something is empty it will not be used in the preview URL.
 */
class Preview_Parameter_Names_Model extends Model {

	/**
	 * Is required, like ?preview=true.
	 *
	 * @var string
	 */
	public string $preview = 'preview';

	/**
	 * Like ?token=1234567890 or ?secret=1234567890, and etc.
	 *
	 * @var string
	 */
	public string $token;
	public string $post_slug;
	public string $post_id;
	public string $post_type;
	public string $post_uri;
	public string $graphql_single;


	public function __construct( array $names ) {
		if ( ! empty( $names['preview'] ) ) {
			$this->preview = (string) $names['preview'];
		}

		$this->token          = (string) ( $names['token'] ?? '' ); // can be `token` by default.
		$this->post_slug      = (string) ( $names['post_slug'] ?? '' ); // can be `slug` by default.
		$this->post_id        = (string) ( $names['post_id'] ?? '' ); // can be `p` by default.
		$this->post_type      = (string) ( $names['post_type'] ?? '' ); // can be `type` by default.
		$this->post_uri       = (string) ( $names['post_uri'] ?? '' ); // can be `uri` by default.
		$this->graphql_single = (string) ( $names['graphql_single'] ?? '' ); // can be `gql` by default.
	}

}