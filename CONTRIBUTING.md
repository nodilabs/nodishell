# Contributing to NodiShell

Thank you for your interest in contributing to NodiShell! 🎉 This document provides guidelines and instructions for contributing to this project.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Contributing Guidelines](#contributing-guidelines)
- [Pull Request Process](#pull-request-process)
- [Conventional Commits](#conventional-commits)
- [Code Style & Standards](#code-style--standards)
- [Testing](#testing)
- [Documentation](#documentation)
- [Issue Reporting](#issue-reporting)
- [Feature Requests](#feature-requests)

## Code of Conduct

By participating in this project, you agree to abide by our code of conduct. Please be respectful, inclusive, and constructive in all interactions.

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Laravel 10.x, 11.x, or 12.x
- Git

### Fork and Clone

1. Fork the repository on GitHub
2. Clone your fork locally:
   ```bash
   git clone https://github.com/YOUR_USERNAME/nodishell.git
   cd nodishell
   ```

## Development Setup

### 1. Install Dependencies

```bash
composer install
```

### 2. Set Up Testing Environment

```bash
# Copy the test environment file if needed
cp .env.example .env.testing

# Run the test suite to ensure everything works
composer test
```

### 3. Development Commands

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis
composer analyse

# Format code
composer format

# Prepare package
composer prepare
```

## Contributing Guidelines

### Types of Contributions

We welcome the following types of contributions:

- 🐛 **Bug fixes**
- ✨ **New features**
- 📚 **Documentation improvements**
- 🧪 **Test improvements**
- 🎨 **Code style improvements**
- ⚡ **Performance improvements**
- 🔧 **Infrastructure improvements**

### Before You Start

1. **Check existing issues** - Look for existing issues or discussions
2. **Open an issue** - For new features or significant changes, open an issue first
3. **Discuss your approach** - Get feedback before starting work

## Pull Request Process

### 1. Branch Naming

Create descriptive branch names using one of these prefixes:

```
feat/description-of-feature
fix/description-of-bug-fix
docs/description-of-documentation-change
test/description-of-test-addition
refactor/description-of-refactor
perf/performance-improvement-description
chore/maintenance-task-description
```

**Examples:**
```
feat/add-script-search-functionality
fix/category-discovery-null-pointer
docs/update-installation-guide
test/add-system-check-tests
```

### 2. Making Changes

1. **Create a new branch** from `main`:
   ```bash
   git checkout -b feat/your-feature-name
   ```

2. **Make your changes** following our code standards

3. **Write tests** for new functionality

4. **Update documentation** if needed

5. **Run the test suite**:
   ```bash
   composer test
   composer analyse
   composer format
   ```

### 3. Submitting Your PR

1. **Push your branch**:
   ```bash
   git push origin feat/your-feature-name
   ```

2. **Create a Pull Request** with:
   - Clear title following conventional commits
   - Detailed description of changes
   - Reference to any related issues
   - Screenshots (if UI changes)
   - Checklist completion

### 4. PR Requirements

✅ **Required Checklist:**
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Tests added for new functionality
- [ ] All tests pass locally
- [ ] PHPStan analysis passes
- [ ] Documentation updated (if needed)
- [ ] Conventional commit format used
- [ ] No merge conflicts with main branch

## Conventional Commits

We follow the [Conventional Commits](https://www.conventionalcommits.org/) specification for all commit messages.

### Format

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Types

- **feat**: A new feature
- **fix**: A bug fix
- **docs**: Documentation only changes
- **style**: Changes that do not affect code meaning (formatting, etc.)
- **refactor**: Code change that neither fixes a bug nor adds a feature
- **perf**: Performance improvement
- **test**: Adding missing tests or correcting existing tests
- **chore**: Changes to build process, auxiliary tools, or libraries

### Scopes (Optional)

Use scopes to specify which part of the codebase is affected:

- `commands` - Generator commands or NodiShell command
- `services` - Service classes
- `discovery` - Discovery services
- `checks` - System check functionality
- `config` - Configuration files
- `tests` - Test-related changes
- `docs` - Documentation changes

### Examples

```bash
# Good commit messages
feat(commands): add script generation with custom templates
fix(discovery): resolve null pointer in category discovery
docs(readme): update installation instructions for Laravel 11
test(services): add comprehensive tests for autocomplete service
refactor(checks): simplify system check validation logic
perf(discovery): optimize script loading performance

# Bad commit messages
Added new feature
Fixed bug
Update docs
```

### Breaking Changes

For breaking changes, add a `!` after the type/scope and include `BREAKING CHANGE:` in the footer:

```
feat(commands)!: remove deprecated script generation options

BREAKING CHANGE: The --legacy flag has been removed from script generation commands.
Use the new --template option instead.
```

## Code Style & Standards

### PHP Standards

- Follow **PSR-12** coding standards
- Use **strict types** declarations: `declare(strict_types=1);`
- Use **type hints** for all method parameters and return types
- Write **self-documenting code** with clear variable and method names

### Laravel Conventions

- Follow Laravel naming conventions
- Use Laravel's helper functions where appropriate
- Leverage Laravel's service container for dependency injection
- Follow Laravel package development best practices

### Code Formatting

We use Laravel Pint for code formatting:

```bash
# Format all code
composer format

# Check formatting without fixing
vendor/bin/pint --test
```

### Static Analysis

We use PHPStan (via Larastan) for static analysis:

```bash
# Run static analysis
composer analyse

# Run with verbose output
vendor/bin/phpstan analyse --verbose
```

## Testing

### Test Requirements

- **Write tests** for all new functionality
- **Maintain or improve** code coverage
- **Use Pest PHP** testing framework
- **Follow AAA pattern** (Arrange, Act, Assert)

### Test Types

1. **Unit Tests** - Test individual classes and methods
2. **Feature Tests** - Test complete workflows
3. **Architecture Tests** - Ensure architectural constraints

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run specific test file
vendor/bin/pest tests/Unit/Services/CategoryDiscoveryServiceTest.php

# Run tests matching pattern
vendor/bin/pest --filter="CategoryDiscovery"
```

### Test Structure

```php
<?php

use NodiLabs\NodiShell\Services\CategoryDiscoveryService;

describe('CategoryDiscoveryService', function () {
    beforeEach(function () {
        $this->service = new CategoryDiscoveryService();
    });

    it('can discover categories from configured path', function () {
        // Arrange
        $expectedCategories = ['Database', 'Testing'];
        
        // Act
        $categories = $this->service->discover();
        
        // Assert
        expect($categories)->toBeArray()
            ->and($categories)->toHaveCount(2);
    });
});
```

## Documentation

### Documentation Requirements

- Update `README.md` for new features
- Add inline code comments for complex logic
- Include PHPDoc blocks for all public methods
- Update configuration documentation when needed

### PHPDoc Standards

```php
/**
 * Discover and load all available script categories.
 *
 * @param  string  $path  The path to search for categories
 * @return Collection<int, CategoryInterface>  Collection of discovered categories
 * 
 * @throws DirectoryNotFoundException When the categories path doesn't exist
 */
public function discover(string $path): Collection
{
    // Implementation
}
```

## Issue Reporting

### Before Reporting

1. **Search existing issues** to avoid duplicates
2. **Check documentation** to ensure it's not expected behavior
3. **Update to latest version** to see if issue persists

### Bug Report Template

Use our bug report template and include:

- **Environment details** (PHP version, Laravel version, OS)
- **Steps to reproduce** the issue
- **Expected behavior** vs actual behavior
- **Code samples** or configuration that triggers the issue
- **Error messages** or stack traces

### Labels

We use these labels for issues:
- `bug` - Something isn't working
- `enhancement` - New feature or request
- `documentation` - Improvements or additions to documentation
- `good first issue` - Good for newcomers
- `help wanted` - Extra attention is needed

## Feature Requests

### Before Requesting

1. **Check existing feature requests** to avoid duplicates
2. **Consider if it fits** the project's scope and goals
3. **Think about implementation** and potential challenges

### Feature Request Template

Include:
- **Problem description** - What problem does this solve?
- **Proposed solution** - How should it work?
- **Alternatives considered** - What other solutions did you consider?
- **Additional context** - Screenshots, examples, etc.

## Getting Help

- **GitHub Discussions** - For questions and general discussion
- **GitHub Issues** - For bug reports and feature requests
- **Email** - Contact ivan@nodifyit.com for private matters

## Recognition

Contributors will be recognized in:
- GitHub contributors list
- Release notes for significant contributions
- Project documentation

Thank you for contributing to NodiShell! 🚀 
