# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2024-05-11

### Changed

- Binds the `middleware.sources` in container instead of add a new configuration definition.
- Updates `env` configuration definition to specify `testing` as a valid value.

### Fixed

- Makes the `app:config:list` command to show the correct required values.