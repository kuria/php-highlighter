Changelog
#########

3.0.3
*****

- fix whitespace output for PHP 8.3


3.0.2
*****

- support the different HTML output of PHP 8.3


3.0.1
*****

- handle invalid ``NULL`` return values from ``highlight_*()`` functions
  (when disabled by PHP configuration)


3.0.0
*****

- ``PhpHighlighter::file()`` and ``PhpHighlighter::code()`` now return
  ``null`` in case of failure


2.0.0
*****

- changed class members from protected to private
- cs fixes, added codestyle checks


1.0.0
*****

Initial release
