# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Introduced the `Orkestra\App::decorate()` method, enabling service decoration during container resolution.
- Introduced the `Orkestra\App::make()` method for resolving new instances from the container.

### Changed

- Modified `Orkestra\AppContainerTrait` to built the container during the application boot process.
- Modified `Orkestra\App::get()` according psr-11 signature to only accept one argument.
- Enhanced container operations `call`, `get`, `has` and `decorate` to verify application boot state.
- Get entity factory from app container directly in tests `factory` function.

### Deprecated

- Deprecated the `Orkestra\App::singleton()` method in favor of `Orkestra\App::bind()` only.

## [1.0.1] - 2024-05-11

### Changed

- Binds the `middleware.sources` in container instead of add a new configuration definition.
- Updates `env` configuration definition to specify `testing` as a valid value.

### Fixed

- Makes the `app:config:list` command to show the correct required values.
