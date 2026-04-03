# Contributing to Smart IoT Cloud

Thank you for your interest in contributing to Smart IoT Cloud! This guide will help you get started.

## Getting Started

1. Fork the repository
2. Clone your fork:

```bash
git clone https://github.com/your-username/smart-iot.git
cd smart-iot
```

3. Set up the project:

```bash
composer setup
```

4. Create a feature branch:

```bash
git checkout -b feature/your-feature-name
```

## Development Workflow

### Running the App

```bash
composer dev
```

### Code Quality

Before submitting a pull request, run all quality checks:

```bash
composer check
```

This runs formatting (Pint), static analysis (Larastan), and tests (Pest).

You can also run them individually:

```bash
composer fix      # Auto-format with Pint
composer stan     # Static analysis
composer pest     # Tests
```

### Writing Tests

Every change should include tests. Use Pest syntax:

```bash
php artisan make:test --pest YourFeatureTest
```

Run a specific test:

```bash
php artisan test --compact --filter=YourTestName
```

### Code Style

- Follow existing conventions in sibling files
- Use PHP 8.4+ features (constructor promotion, enums, typed properties)
- Use `$guarded` instead of `$fillable` in models
- Code is auto-formatted with Laravel Pint — run `composer fix` before committing

## Pull Requests

1. Keep PRs focused on a single change
2. Write a clear title and description
3. Ensure all checks pass (`composer check`)
4. Update or add tests for your changes
5. Reference any related issues

## Reporting Bugs

Open an issue with:

- A clear description of the bug
- Steps to reproduce
- Expected vs actual behavior
- PHP version, OS, and relevant environment details

## Feature Requests

Open an issue describing:

- The problem you're trying to solve
- Your proposed solution
- Any alternatives you considered

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
