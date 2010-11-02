<?php

/**
 * CommandLine Arguments Parser Facility
 *
 *
 *
 * Rules / Conventions derived from »Program Argument Syntax Conventions« (see http://www.cs.utah.edu/dept/old/texinfo/glibc-manual-0.02/library_22.html#SEC387)
 * (POSIX-1) Arguments are options if they begin with a hyphen delimiter (`-').
 * (POSIX-2) Multiple options may follow a hyphen delimiter in a single token if the options do not take arguments. Thus, `-abc' is equivalent to `-a -b -c'.
 * (POSIX-3) Option names are single alphanumeric characters (as for isalnum; see section Classification of Characters).
 * (POSIX-4) Certain options require an argument. For example, the `-o' command of the ld command requires an argument--an output file name.
 * (POSIX-5) An option and its argument may or may not appear as separate tokens. (In other words, the whitespace separating them is optional.) Thus, `-o foo' and `-ofoo' are equivalent.
 * (POSIX-6) Options typically precede other non-option arguments.
 * (POSIX-7) The argument `--' terminates all options; any following arguments are treated as non-option arguments, even if they begin with a hyphen.
 * (POSIX-8) A token consisting of a single hyphen character is interpreted as an ordinary non-option argument. By convention, it is used to specify input from or output to the standard input and output streams.
 * (POSIX-9) Options may be supplied in any order, or appear multiple times. The interpretation is left up to the particular application program.
 * (GNU-1) Long options consist of `--' followed by a name made of alphanumeric characters and dashes. Option names are typically one to three words long, with hyphens to separate words. Users can abbreviate the option names as long as the abbreviations are unique. 
 * (GNU-2) To specify an argument for a long option, write `--name=value'. This syntax enables a long option to accept an argument that is itself optional. 
 * (CLI-1) a long option name must be at least 2 characters long to not interfere with short option names when leading hyphens are stripped
 *
 * ClappArgumentParser complies with all rules but does not enforce (POSIX-6)
 *
 * @author Rodney Rehm
 * @package Clapp
 */

?>