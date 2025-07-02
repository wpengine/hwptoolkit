<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Unauthorized access!' );
}

// Register new GraphQL types and field for the Sitemap types
add_action( 'graphql_register_types', function() {

    // Register new object type for sitemapTypes
	register_graphql_object_type(
		'SitemapType',
		[
			'description' => 'Sitemap type',
			'fields'      => [
				'type' => [
					'type'        => 'String',
					'description' => 'Main type (posts, taxonomies, users)'
				],
				'subType' => [
					'type'        => 'String',
					'description' => 'Subtype (post, page, category, etc.)'
				],
				'pages'  => [
					'type'        => 'Integer',
					'description' => 'Total page count'
				]
			],
		]
	);

	// Register a new GraphQL field
	// More info: https://www.wpgraphql.com/recipes/register-object-and-field-for-custom-list-of-users
	register_graphql_field( 'RootQuery', 'sitemapTypes', [
		'type' => [ 'list_of' => 'sitemapType' ],
		'description' => 'List of supported sitemap types',
		'resolve' => function() {
			$sitemap_providers = wp_get_sitemap_providers();

			function get_sitemap_types($type, $provider) {
				if (isset($provider[$type])) {
					$entries = $provider[$type]->get_sitemap_type_data();

					foreach ($entries as $key => &$entry) {
						$entry['subType'] = $entry['name'];
						$entry['type'] = $type;

						// Use type as a name if the name is empty (for users)
						if (empty($entry['name'])) {
							$entry['subType'] = $type;
						}

						// If there are no pages, remove the entry
						if($entry["pages"] === 0) {
							unset($entries[$key]);
						}
					}

					return $entries;
				}

				// Return an empty array if the provider doesn't exist
				return array();
			}

			$posts = get_sitemap_types('posts', $sitemap_providers);
			$taxonomies = get_sitemap_types('taxonomies', $sitemap_providers);
			$users = get_sitemap_types('users', $sitemap_providers);

			return array_merge($users, $taxonomies, $posts);
		}
	]);
});