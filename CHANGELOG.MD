# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Introduced the `Orkestra\App::decorate()` method, enabling service decoration during container resolution.
- Enhanced container operations `call`, `get`, `has` and `decorate` to verify application boot state.

### Changed

- Modified `Orkestra\AppContainerTrait` to built the container during the application boot process.