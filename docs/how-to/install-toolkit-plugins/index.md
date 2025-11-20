---
title: "Installing HWP Toolkit Plugins with Composer"
description: "A guide on how to install any HWP Toolkit plugin using Composer, which is the recommended way for modern WordPress development workflows."
---

You can install any HWP Toolkit plugin using Composer, which is the recommended way for modern WordPress development workflows.

You can also install them manually from our [Releases](https://github.com/wpengine/hwptoolkit/releases) page.

## Requirements

- Composer 1.x or 2.x
- WordPress 6.0+
- PHP 7.4+

>[!NOTE]
> WPGraphQL Logging Plugin requires PHP 8.1.2 in order to work.

### Available Plugins

- [wpengine/hwp-previews](https://github.com/wpengine/hwptoolkit/tree/main/plugins/hwp-previews#readme)
- [wpengine/wpgraphql-webhooks](https://github.com/wpengine/hwptoolkit/tree/main/plugins/wpgraphql-webhooks#readme)
- [wpengine/wpgraphql-debug-extensions](https://github.com/wpengine/hwptoolkit/tree/main/plugins/wpgraphql-debug-extensions#readme)
- [wpengine/wpgraphql-logging](https://github.com/wpengine/hwptoolkit/tree/main/plugins/wpgraphql-logging#readme)

## Quick Start: Example `composer.json`

Copy and use this example as your `composer.json` for a typical WordPress project using HWP Toolkit plugins:

```json
{
  "name": "wpengine/headless-wordpress-toolkit-composer-test",
  "description": "Headless WordPress Toolkit Composer Test",
  "repositories": [
    {
      "type": "composer",
      "url": "https://raw.githubusercontent.com/wpengine/hwptoolkit/main/plugins/composer-packages.json"
    }
  ],
  "require": {
    "wpengine/hwp-previews": "*",
    "wpengine/wpgraphql-webhooks": "*",
    "wpengine/wpgraphql-debug-extensions": "*",
    "wpengine/wpgraphql-logging": "*"
  },
  "config": {
    "allow-plugins": {
      "composer/installers": true
    }
  }
}
```

## Installation Steps

1. **Copy the above JSON into your project's `composer.json` file.**

2. **Install dependencies:**

   ```bash
   composer install
   ```

3. **Activate the plugin(s) in WordPress**

   Go to the WordPress admin and activate the plugin(s) as usual.

---

## Updating

To update to the latest version:

```bash
composer update wpengine/hwp-previews wpengine/wpgraphql-webhooks wpengine/wpgraphql-debug-extensions wpengine/wpgraphql-logging
```

---

## Troubleshooting

- If you see an error about missing `composer/installers`, run `composer require composer/installers`.
- If the plugin does not appear in your plugins list, check your `installer-paths` and that you are in the correct WordPress directory.
- Ensure the `repositories` section is present and correct in your `composer.json`.

---

## Contributing

If you feel like something is missing or you want to add documentation, we encourage you to contribute! Please check out our [Contributing Guide](https://github.com/wpengine/hwptoolkit/blob/main/CONTRIBUTING.md) for more details.
