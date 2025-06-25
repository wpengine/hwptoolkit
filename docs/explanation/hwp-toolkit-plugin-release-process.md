# Overview

This is an explanation on how a plugin is released on GitHub.

# Workflow

## High Level project structure for release process

```
.
└──  package.json # Contains scripts to update the different plugin versions
│
└── .changeset
│   └── config.json # Changeset config file
├── .github
│   └── workflows
│       ├── release-branch.yml # Creates the release branch
│       └── release-plugin.yml # Creates the GitHub release
├── plugin
│   └── hwp-previews
│       └── package.json # Required for changeset to work
│   └── wp-graphql-headless-webhooks
│       └── package.json # Required for changeset to work
├── scripts/
│   └── plugin-version
│       ├── previewsVersionPlugin.js # Updates the version of hwp-previews plugin
│       └── webhooksVersionPlugin.js # Updates the version of wp-graphql-headless-webhooks plugin
```

## 1. PR creation

1. A create PR is created for a plugin
2. This triggers the [changeset bot](https://github.com/changesets/bot) to check for a changeset.
3. A user can add a changeset manually by running `npm run changeset` or if they are a maintainer, they can do this also in GitHub
4. Once the PR is merged the following actions will take place:


## 2. PR merged to the main branch

This will trigger the release workflow [release-branch.yml](../../.github/workflows/release-branch.yml) and the workflow will do the following:

1. Check to see if a changeset exists
2. Check that a plugin is modified
3. Create a release branch for that plugin using `npm run build && changeset publish
4. Depending on what plugin is changed it will either run `npm run version:previews` or `npm run version:webhooks` which will call a script for updating the plugin version under [scripts/plugin-version](../../scripts/plugin-version/)

After this a plugin release branch is created. See <https://github.com/wpengine/hwptoolkit/pulls?q=Plugin+Release>

>[!NOTE]
> If you are adding a new plugin, you will need to add a script and also update this workflow, along with adding a package.json while updating the main package-lock.json by running `npm install`


## 3. Plugin Release Branch

The plugin release branch should contain some updates to the plugin and mainly the plugin version number.

Once approved and merged this will trigger the [release-plugin.yml](../../.github/workflows/release-plugin.yml)

This will then create a release for the plugin under [Github releases](https://github.com/wpengine/hwptoolkit/releases)
