<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Unauthorized access!' );
}

// Register new GraphQL types and field for the Sitemap entries
add_action( 'graphql_register_types', function() {

    // Register new object type for sitemapEntries
	register_graphql_object_type(
		'SitemapEntry',
		[
			'description' => 'Sitemap entry',
			'fields'      => [
				'uri' => [
					'type'        => 'String',
					'description' => 'Entry URI'
				],
				'lastmod'  => [
					'type'        => 'String',
					'description' => 'Last modified date' 
				],
				'imageLoc' => [
					'type'        => 'String',
					'description' => 'The URL of the featured image for this sitemap entry'
				]
			],
		]
	);


    // Register a new GraphQL field with args
    // More info: https://www.wpgraphql.com/2020/03/11/registering-graphql-fields-with-arguments
    register_graphql_field( 'RootQuery', 'sitemapEntries', [
		'type' => [ 'list_of' => 'sitemapEntry' ],
		'args' => [
			'type' => [
				'type' => [ 'non_null' => 'String' ], // Add required string argument
				'description' => 'Type that you have got from SitemapType',
			],
			'subType' => [
				'type' => [ 'non_null' => 'String' ], // Add required string argument
				'description' => 'Subtype that you have got from SitemapType',
			],
			'page' => [
				'type' => [ 'non_null' => 'Integer' ], // Add required integer argument
				'description' => 'Page number',
			],
		],
		'description' => 'List of sitemap entries',
		'resolve' => function( $source, $args, $context, $info) {
            $sitemap_providers = wp_get_sitemap_providers();

			if(! isset($sitemap_providers)){
                return array(); // Return empty array if the providers are not set
            }

            // Sanitize and validate the arguments
			$type = isset($args['type']) ? sanitize_text_field($args['type']) : '';
			$subType = isset($args['subType']) ? sanitize_text_field($args['subType']) : '';
			$page = isset($args['page']) ? absint($args['page']) : 1;

			$type_provider = $sitemap_providers[$type];

			if(!$type_provider) {
				return array(); // Return empty array if the provider doesn't exist
			}

			$entries = $sitemap_providers[$type]->get_url_list($page, $subType);
			
			foreach ($entries as &$entry) {
				$entry['uri'] = wp_make_link_relative( $entry['loc'] ); // Convert full permalink to relative
				unset($entry['loc']); // Remove loc as we already have uri
			}

			return $entries;
		}
	] );
});