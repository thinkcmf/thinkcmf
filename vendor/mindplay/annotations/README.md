# php-annotations

[![Join the chat at https://gitter.im/php-annotations/php-annotations](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/php-annotations/php-annotations?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![CI](https://github.com/php-annotations/php-annotations/actions/workflows/tests.yml/badge.svg)](https://github.com/php-annotations/php-annotations/actions/workflows/tests.yml)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/php-annotations/php-annotations/badges/quality-score.png?s=41628593655dae3740c3a64f172438430ee26b84)](https://scrutinizer-ci.com/g/php-annotations/php-annotations/)
[![Code Coverage](https://scrutinizer-ci.com/g/php-annotations/php-annotations/badges/coverage.png?s=dbea8860e011cdb7b5352b48c25259ca950fe2c6)](https://scrutinizer-ci.com/g/php-annotations/php-annotations/)

[![Latest Stable Version](https://poser.pugx.org/mindplay/annotations/v/stable.svg)](https://packagist.org/packages/mindplay/annotations) [![Total Downloads](https://poser.pugx.org/mindplay/annotations/downloads.svg)](https://packagist.org/packages/mindplay/annotations) [![Latest Unstable Version](https://poser.pugx.org/mindplay/annotations/v/unstable.svg)](https://packagist.org/packages/mindplay/annotations) [![License](https://poser.pugx.org/mindplay/annotations/license.svg)](https://packagist.org/packages/mindplay/annotations)

Source-code annotations for PHP.

Copyright (C) 2011-2015 Rasmus Schultz <rasmus@mindplay.dk>

https://github.com/php-annotations/php-annotations

For documentation and updates, please visit the project Wiki:

http://php-annotations.readthedocs.org/


## Project Structure

The files in this project are organized as follows:

```
php-annotations         This README and the LGPL license
  /src
    /annotations        The core of the library itself
      /standard         Standard library of annotation classes
  /demo                 Browser-based example/demonstration
  /docs                 Documentation files (http://php-annotations.readthedocs.org/en/latest/)
  /test                 Unit tests for the core of the library
    /test.php           Test suite runner
    /annotations        Fixture Annotation types
    /lib                Unit test library
    /runtime            Run-time cache folder used for tests
    /suite              Test cases
```

The "mindplay" folder is the only folder required for the annotation
framework itself - other folders contain demonstration code, tests, etc.

To run the test suite, run "php-annotations/test/test.php" from a
browser - a summary of the test-results will be displayed on the page.


## Code Style

Largely PSR-2 compliant:

https://raw.github.com/php-fig/fig-standards/master/accepted/PSR-2-coding-style-guide.md


## License

http://www.gnu.org/licenses/lgpl-3.0.txt

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License as
published by the Free Software Foundation; either version 3 of
the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses>.

Additional permission under GNU GPL version 3 section 7

If you modify this Program, or any covered work, by linking or
combining it with php-annotations (or a modified version of that
library), containing parts covered by the terms of the LGPL, the
licensors of this Program grant you additional permission to convey
the resulting work.
