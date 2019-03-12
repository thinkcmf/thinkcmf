# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
...

## [1.3.0] - 2016-02-14
### Added
* Run tests on PHP 7 as well.
* Trait property/method annotations are accessible as part of class annotations by [@benesch].
* Allow trait introspection for annotations by [@benesch].

### Fixed
* The root namespace importing (e.g. "use PDO;") wasn't working by [@benesch].
* Tests, that trigger notices/warnings were marked as passing by [@benesch].

## [1.2.0] - 2015-04-15
### Added
* Added support for Composer.
* The namespaced data types (e.g. in "@var" or "@return" annotations) are now supported.
* Added "IAnnotationFileAware" interface to allow annotations accessing other data (e.g. namespace name) of file it was used in.
* Added Scrutinizer CI integration.
* Added Travis CI integration.
* Added support for "@type" annotation.
* Added support for "@stop" annotation to prevent from getting annotations from parent class.
* Added Gitter bagde.
* Documentation moved from Wiki to ReadTheDocs.

### Changed
* Main namespace renamed from "Mindplay\Annotation" to "mindplay\annotations".
* Annotation cache format changed. Need to delete existing cache manually.
* Improved error message, when attempt get annotations from trait/interface is made.
* Indexes in returned annotation array are now sequential.
* Improved exception messages.

### Fixed
* The "type" in "@var" annotation was always lowercased.
* The leading "\" wasn't stripped from class name given to "getClassAnnotations", "getMethodAnnotations" and "getPropertyAnnotations" methods.
* Don't attempt to get annotations from internal classes (e.g. "SplFileInfo").
* Attempt to read annotations from non-existing class properties now ends up in exception.
* Don't throw exception, when either of these annotations are encountered: "@api", "@source", "@version".
* No PHP notice is triggered, when malformed (no type) "@param" annotation is being used.
* Inline annotations (e.g. "// @codeCoverageIgnore") were processed by mistake.

### Removed
* Standard annotation stubs (e.g. "@display", "@format", "@length", etc.) were removed.
* Removed incomplete support for "@usage" annotation inheritance.

## [1.1.0] - 2012-10-10
### Changed
* Cache engine rewritten from scratch to increase performance.
* Renamed protected "Annotation::_map" method into "Annotation::map" method.
* Annotation cache format changed. Need to delete existing cache manually.
* Don't write trailing ";" to cache files.

### Removed
* Removed APC support in caching layer.

## [1.0.0] - 2012-10-03
### Added
- Initial release.

[Unreleased]: https://github.com/php-annotations/php-annotations/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/php-annotations/php-annotations/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/php-annotations/php-annotations/compare/v1.0.0...v1.1.0
[@benesch]: https://github.com/benesch
