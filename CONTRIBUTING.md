# Contributing to hwptoolkit

Thank you for your interest in hwptoolkit. This project is maintained by a team committed to transparency, responsiveness and supporting the community. We embrace the spirit of open-source and we believe community is the key for success. We welcome all forms of engagement with the community and contributions of all levels of skill and experience.

In this guide we will discuss the different types of contribution, how you can get started and how you should proceed for each type of contribution.

## Resources

- [How we work](https://github.com/wpengine/hwptoolkit/blob/main/HOW_WE_WORK.md)
- [Project board](https://github.com/orgs/wpengine/projects/13)
- [Discord Server](https://faustjs.org/discord/)
- [Faustjs documentation](https://faustjs.org/)
- [Issues](https://github.com/wpengine/hwptoolkit/issues)
- [Discussions](https://github.com/wpengine/hwptoolkit/discussions)

## Types of contribution

You have plenty of options once you decide to contribute. You can create GitHub Issues for bugs and features or create GitHub Discussions for RFC's and support.

Before opening a GitHub Issue, consider whether your topic is best suited for a Discussion. If you're unsure whether an idea is feasible, or needs broader community input, start a discussion first. Use Discussions for brainstorming, RFCs, or general questions, while Issues should focus on actionable bug reports or feature requests.

Example:

- Use Discussions for: "Should we support X framework in the future?"
- Use Issues for: "Button in X component is not rendering correctly in Next.js 14."

> [!NOTE]  
> Only GitHub Issues appear on our Project Board.

### Report a bug or suggest a feature

You'll eventually come across a bug or a missing feature. Reporting it will not just help you to seek a solution for your project, it will help other community members to avoid potential roadblocks. It will also help us to detect issues early on and improve the hwptoolkit.

> [!CAUTION]
> Use [GitHub issues](https://github.com/wpengine/hwptoolkit/issues) for non-security related bugs only. To avoid the exposure of the potential vulnerabilities, please report security related issues to us via email at opensource@wpengine.com.

A usual bug reporting/feature suggesting workflow should look like this:

1. Look for a duplicate issue.

   If there's an issue related to your concern, leave a comment to share your perspective, instead of opening a new one.

2. Create your issue using the issue template
3. Add appropriate labels to your issue

### Help other people and engage with community

You can check the open issues, join the discussion, solve the issue and save the day for others. Don't limit yourself just with the GitHub issues. You can check [Discussions](https://github.com/wpengine/hwptoolkit/discussions) and [Discord Server](https://discord.gg/RZ7XWgF2) as well to see anyone needs your expertise. Also you can join us at our twice-monthly [headless WordPress community meetings](https://discord.gg/headless-wordpress-836253505944813629?event=1336404483013480588) to share your opinions and your experience.

### Triage issues

You can help the team and the whole community alike by triaging the open issues. You can do that by adding the missing pieces in the issue. It may need to be reproduced or verified. Clearing the roadblocks from the issue, doing the research and sharing the feedback could be as helpful as solving the issue itself.

### Pull Requests (PR)

This is a kind of contribution that puts a smile to the face of every maintainer in the project. Before starting to work on a PR, please consider these:

- Make sure to check the [issues](https://github.com/wpengine/hwptoolkit/issues) to see anything related to the change you are planning to make. Maybe it's been discussed before, maybe somebody is already working on it.
- If there's no issue related to your PR, create one and describe the problem you're facing and the solution you're proposing. Discuss it with the maintainers. If it's approved, you can start working on your PR.
- Submit a separate PR for each issue. Avoid combining multiple issues into one PR.

#### Project structure
The following directory structure helps organize the project components efficiently.

```
/
|-- packages            # Front-end packages to be published on NPM
|-- plugins             # PHP based WordPress Plugins
|-- docs                # Documentation, tutorials, explanations
|--|-- how-to           # How-to guides
|--|-- explanation      # Explanations
|--|-- tutorial         # Tutorials
|--|-- reference        # Code reference
|-- examples            # Examples demonstrating how to implement various aspects of headless WordPress
|--|-- next             # Next.js based examples
|--|-- ...              # Examples based on other front-end frameworks
```

#### Writing documentation

We are using the approach of [Diátaxis](https://diataxis.fr/) for our documentation. Diátaxis provides four different forms of documentation:

- [How-to guides](https://diataxis.fr/how-to-guides/)
- [Explanation](https://diataxis.fr/explanation/)
- [Tutorials](https://diataxis.fr/tutorials/)
- [Reference](https://diataxis.fr/reference/)

These are four distinct ways of teaching people how to achieve different types of goals. It helps us to be effective in communicating our message and also helps us to be more organized.

If you're intending to contribute to the documentation, you need to determine the correct category for the message you want to convey. Please don't forget to create your docs in the appropriate folder.

#### Conventional Commits

We're using [Conventional Commits](https://www.conventionalcommits.org/) to organize our commits and pull requests. You can familiarize yourself with the different prefixes and their use cases below:

```
# build:    Changes that affect the build system or external dependencies
# chore:    Routine tasks, maintenance, or refactors that don't change functionality
# ci:       Changes to CI configuration files and scripts
# docs:     Documentation only changes
# feat:     A new feature for the user or a significant enhancement
# fix:      A bug fix
# perf:     A code change that improves performance
# refactor: A code change that neither fixes a bug nor adds a feature
# release:  Used for release PRs/commits
# revert:   Reverts a previous commit
# style:    Changes that don't affect the meaning of the code (formatting, missing semi-colons, etc)
# test:     Adding missing tests or correcting existing tests
```

#### Steps to contribute

We use the [feature branch](https://www.atlassian.com/git/tutorials/comparing-workflows/feature-branch-workflow) workflow. The workflow for a typical code change looks like:

> [!NOTE]  
> Before you get started make sure to read and understand the system requirements, prerequisites and installation steps of the specific component you're intending to contribute. You can find this info under the README file of each example or package.

1. Clone the repo: `git clone https://github.com/wpengine/hwptoolkit.git`
2. Create a new branch for your feature: `git checkout -b ＜new-branch＞`
3. Commit your changes

   - Use Conventional Commits in commit messages
   - Write commit messages explaining why changes were made, not just what

4. Clearly flag breaking changes and update relevant documentation
5. [Open a PR](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request) to main branch
   - Use Conventional Commits in your PR name
   - Follow the PR template
   - Mention the original issue in the PR
   - Indicate which parts need focused attention
6. Mention your PR in the original issue

We will try to review your PR as soon as possible. Once approved by two maintainers, you can merge your PR into `main`.

### Discussions

Use [GitHub Discussions](https://github.com/wpengine/hwptoolkit/discussions) if you want to submit PR or discuss bigger topics. There are many different categories in the Discussions section for different topics. Choose one, create a new discussion or join an existing one to share your thoughts. You can explore our ever-evolving RFCs, which serve as a beacon guiding the development of our toolkit.
