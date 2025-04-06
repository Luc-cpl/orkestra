---
sidebar_position: 1
---

# Contribution Guidelines

We're excited that you're interested in contributing to Orkestra! This document outlines the process and guidelines for contributing to the project.

## How to Contribute

### Reporting Bugs

If you find a bug, please report it by creating an issue on our [GitHub issue tracker](https://github.com/Luc-cpl/orkestra/issues). When filing a bug report, please include:

1. A clear and descriptive title
2. Detailed steps to reproduce the bug
3. Expected behavior vs. actual behavior
4. Orkestra version, PHP version, and OS
5. Any relevant code snippets or error messages

### Suggesting Enhancements

Feature requests are welcome! Please submit them as issues on our GitHub repository with the "enhancement" label. Provide as much detail as possible:

1. A clear and descriptive title
2. A detailed description of the proposed feature
3. Explanation of why this feature would be useful to Orkestra users
4. Any examples or mockups that might help clarify the request

### Pull Requests

Here's how to submit a pull request:

1. Fork the repository
2. Create a new branch from `main` (`git checkout -b feature/your-feature-name`)
3. Make your changes
4. Write or update tests for your changes
5. Run the test suite to ensure all tests pass
6. Commit your changes with a clear commit message
7. Push your branch to your fork
8. Open a pull request against the `main` branch of the Orkestra repository

#### Pull Request Guidelines

- Keep PRs focused on a single feature or bug fix
- Follow the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Update documentation if needed
- Add tests for new features or bug fixes
- Reference any related issues in your PR description

## Development Setup

### Requirements

- PHP 8.2 or higher
- Composer

### Local Development Environment

1. Fork and clone the repository:

```bash
git clone https://github.com/YOUR-USERNAME/orkestra.git
cd orkestra
```

2. Install dependencies:

```bash
composer install
```

3. Run the test suite:

```bash
composer pest
composer phpstan
```

## Coding Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use descriptive variable and method names
- Write self-documenting code with clear intent
- Add DocBlocks to all methods and classes
- Keep classes focused on a single responsibility

### PHP CS Fixer

We use Laravel Pint to maintain coding standards. Run it before submitting your PR:

composer pint
```

## Testing

All new features and bug fixes should include tests. We aim for high test coverage and use PHPUnit for testing.

### Writing Tests

- Place tests in the `tests` directory
- Mirror the namespace structure of the `src` directory
- Name test classes with the `Test` suffix
- Prefix test methods with `test`
- Focus on testing behavior, not implementation details

To run tests:

```bash
vendor/bin/phpunit
```

## Documentation

If your changes introduce new features or modify existing functionality, please update the documentation accordingly.

Our documentation uses Markdown and is located in the `website/docs` directory. For major documentation changes, please open a separate pull request.

## Release Process

Orkestra follows [Semantic Versioning](https://semver.org/):

- MAJOR versions for incompatible API changes
- MINOR versions for new functionality in a backward-compatible manner
- PATCH versions for backward-compatible bug fixes

## Getting Help

If you have questions about contributing to Orkestra, please:

- Join our [GitHub Discussions](https://github.com/Luc-cpl/orkestra/discussions)
- Ask in the relevant GitHub issue
- Contact the maintainers directly

## Thank You!

Your contributions make Orkestra better for everyone. We appreciate your time and effort! 