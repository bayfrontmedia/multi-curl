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

## [3.0.0] - 2025.01.15

### Added

- Added `InteractsWithApi` trait.

### Changed

- Minimum PHP requirement is now `^8.2`.
- Moved all exceptions to the `Bayfront\MultiCurl\Exceptions` namespace.
- Updated documentation.
 
## [2.2.1] - 2024.12.23

### Added

- Tested up to PHP v8.4.

## [2.2.0] - 2024.12.16

### Added

- Added memory limit parameter for `download` method.

### Changed

- General code cleanup.

## [2.1.0] - 2024.04.03

### Removed

- Removed `ClientException` being thrown in class constructors if the cURL PHP extension is not loaded.

## [2.0.0] - 2023.01.26

### Added

- Added support for PHP 8.

## [1.1.4] - 2021.03.19

### Fixed

- Fixed bug in `get` method not encoding URLs properly.

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