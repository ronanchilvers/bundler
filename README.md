# Bundler

A simple bundler to concatenate and version static files, optionally writing correct HTML tags.

## Goals
- [ ] Simple to use
- [ ] No dependencies
- [ ] Support CSS and JS
- [ ] Support HTML tag generation
- [ ] Support versioning (cache busting)
- [ ] Support multiple bundles
- [ ] Facilitate extending to add extra features such as minification

## Usage

- Configure bundler
```yaml
---
```
- Add bundler to your templates
```html

```

## Development & Testing

This project uses PHPUnit. A basic test harness is already configured.

### Install dependencies

```
composer install
```

### Run the test suite

```
composer test
```

### Additional scripts

```
composer test:dox        # Readable "spec" style output
composer test:coverage   # Generates HTML coverage in build/coverage/html
composer test:ci         # Clover + JUnit logs in build/logs/
```

### Writing tests

* Place unit tests under tests/Unit
* Name test files with the suffix Test.php (e.g. BundlerTest.php)
* Use namespaces under Tests\Unit (e.g. namespace Tests\Unit;)
* Each test method should be public and start with test, or use #[Test] attributes.

An example test exists at tests/Unit/ExampleTest.php covering the Example class in src/Example.php. Feel free to delete these once you begin implementing real functionality.

### Code coverage

Coverage reports are written to build/. Add build/ to your .gitignore if you commit coverage locally:

```
echo "build/" >> .gitignore
```

### Continuous Integration

Use the test:ci script in CI workflows to produce Clover (coverage) and JUnit (test result) artifacts for reporting.
