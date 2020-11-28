# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

- `Added` for new features.
- `Changed` for changes in existing functionality.
- `Deprecated` for soon-to-be removed features.
- `Removed` for now removed features.
- `Fixed` for any bug fixes.
- `Security` in case of vulnerabilities

## [1.1.3] - 2020.11.27

### Added

- Added optional default value to return with `getBody` method.

## [1.1.2] - 2020.11.17

### Fixed

- Updated `_curlProcessResponse` method in `ClientParent` class to more reliably extract the response body.

## [1.1.1] - 2020.11.10

### Fixed

- Fixed `getBody()` not returning an array when `$json_encode = true`

## [1.1.0] - 2020.09.04

### Added

- Added support for `CONNECT`, `OPTIONS` and `TRACE` request methods.

## [1.0.0] - 2020.08.12

### Added

- Initial release.