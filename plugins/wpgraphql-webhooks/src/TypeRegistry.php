<?php
/**
 * Registers GraphQL types, fields, and mutations for Webhooks.
 *
 * @package WPGraphQL\Webhooks
 */

namespace WPGraphQL\Webhooks;

/**
 * Class TypeRegistry
 *
 * Manages registration of GraphQL types, queries, and mutations.
 */
class TypeRegistry {

    /**
     * Local registry of registered GraphQL type classes.
     *
     * @var string[]
     */
    public static array $registry = [];

    /**
     * Initializes type registration.
     *
     * @return void
     */
    public static function init(): void {
        do_action( 'graphql_webhooks_before_register_types' );

        $classes_to_register = array_merge(
            self::objects(),
            self::fields(),
            self::mutations()
        );

        self::register_types( $classes_to_register );

        do_action( 'graphql_webhooks_after_register_types' );
    }

    /**
     * List of GraphQL Object Type classes to register.
     *
     * @return string[]
     */
    private static function objects(): array {
        return [
            Type\Webhook::class,
        ];
    }

    /**
     * List of RootQuery field classes to register.
     *
     * @return string[]
     */
    private static function fields(): array {
        return [
            Fields\RootQuery::class,
        ];
    }

    /**
     * List of Mutation classes to register.
     *
     * @return string[]
     */
    private static function mutations(): array {
        return [
            Mutation\CreateWebhook::class,
            Mutation\DeleteWebhook::class,
            Mutation\UpdateWebhook::class,
        ];
    }

    /**
     * Registers provided GraphQL type classes and adds them to the local registry.
     *
     * @param string[] $classes_to_register Array of fully qualified class names.
     * @return void
     */
    private static function register_types( array $classes_to_register ): void {
        foreach ( $classes_to_register as $class ) {
            $class::register();
            self::$registry[] = $class;
        }
    }
}
