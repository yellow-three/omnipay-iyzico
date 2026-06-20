# Contributing to Omnipay Iyzico

Thank you for considering contributing to Omnipay Iyzico! This document provides guidelines and instructions for contributing.

## Code of Conduct

By participating, you are expected to uphold a respectful and inclusive environment.

## How to Contribute

### Reporting Bugs

- Search existing [issues](https://github.com/yellow-three/omnipay-iyzico/issues) first
- Open a new issue with a clear title and description
- Include PHP version, package version, and iyzico API environment (sandbox/production)
- Provide minimal reproduction code if possible

### Suggesting Features

- Open an issue describing the feature and its use case
- Explain why it would be useful for the iyzico integration

### Submitting Pull Requests

1. Fork the repository
2. Create a feature branch from `main`
3. Make your changes
4. Add or update tests
5. Ensure all tests pass
6. Submit a pull request

## Development Setup

```bash
git clone https://github.com/yellow-three/omnipay-iyzico.git
cd omnipay-iyzico
composer install
```

### Running Tests

```bash
vendor/bin/phpunit
```

### Code Standards

- Follow PSR-12 coding style
- Use strict types: `declare(strict_types=1)`
- Add PHPDoc blocks for public methods
- Keep methods focused and short

### Commit Messages

- Use imperative mood: "Add feature" not "Added feature"
- Keep subject line under 72 characters
- Reference related issues with `#issue-number`

## Adding New iyzico API Methods

1. Create a new Request class in `src/Message/`
2. Extend `AbstractRequest`
3. Implement `getData()` and `sendData()`
4. Add the gateway method in `src/Gateway.php`
5. Add corresponding tests in `tests/`

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
