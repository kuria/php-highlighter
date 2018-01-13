PHP highlighter
###############

PHP code highlighter.

.. contents::


Features
********

- highlighting files or strings
- highlighting specific line ranges
- marking an active line
- produces an ordered list (``<ol>``) with corresponding line numbers


Requirements
************

- PHP 7.1+


Usage
*****

Highlighting code
=================

- ``PhpHighlighter::file()`` - highlight a PHP file
- ``PhpHighlighter::code()`` - highlight a string of PHP code

.. code:: php

   <?php

   use Kuria\PhpHighlighter\PhpHighlighter;

   $php = <<<'PHP'
   <?php

   echo "Hello world!";

   PHP;

   echo PhpHighlighter::code($php);

Output:

.. code:: html

   <ol>
   <li><span style="color: #0000BB">&lt;?php</span></li>
   <li></li>
   <li><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"Hello&nbsp;world!"</span><span style="color: #007700">;</span></li>
   <li></li>
   </ol>


Marking an active line
======================

An active line can be specified using the second argument.

The active line will have a ``class="active"`` attribute.

.. code:: php

   <?php

   echo PhpHighlighter::code($php, 3);


Specifying line range
=====================

A line range can be specified using the third argument.

Example line ranges:

- ``NULL`` - highlight all lines
- ``[20, 30]`` - highlight lines from 20 to 30 (absolute)
- ``[-5, 5]`` - highlight 5 lines around the active line (requires active line)
- ``[0, 0]`` - highlight the active line only (requires active line)

.. code:: php

   <?php

   echo PhpHighlighter::code($php, 3, [-1, 1]);
