<?php

namespace Factory\Requirements\Helpers;

class FactoryHelpers
{
    function echo_style($style, $message)
    {
        // ANSI color codes
        $styles = array(
            'reset' => "\033[0m",
            'red' => "\033[31m",
            'green' => "\033[32m",
            'yellow' => "\033[33m",
            'error' => "\033[37;41m",
            'success' => "\033[37;42m",
            'title' => "\033[34m",
        );
        $supports = $this->has_color_support();

        echo ($supports ? $styles[$style] : '') . $message . ($supports ? $styles['reset'] : '');
    }


    function has_color_support()
    {
        static $support;

        if (null === $support) {

            if ('Hyper' === getenv('TERM_PROGRAM')) {
                return $support = true;
            }

            if (DIRECTORY_SEPARATOR === '\\') {
                return $support = (function_exists('sapi_windows_vt100_support')
                        && @sapi_windows_vt100_support(STDOUT))
                    || false !== getenv('ANSICON')
                    || 'ON' === getenv('ConEmuANSI')
                    || 'xterm' === getenv('TERM');
            }

            if (function_exists('stream_isatty')) {
                return $support = @stream_isatty(STDOUT);
            }

            if (function_exists('posix_isatty')) {
                return $support = @posix_isatty(STDOUT);
            }

            $stat = @fstat(STDOUT);
            // Check if formatted mode is S_IFCHR
            return $support = ($stat ? 0020000 === ($stat['mode'] & 0170000) : false);
        }

            return $support;
    }

    function echo_title($title, $style = null) {
        $style = $style ?: 'title';

        echo PHP_EOL;
        $this->echo_style($style, $title . PHP_EOL);
        $this->echo_style($style, str_repeat('-', strlen($title)) . PHP_EOL);
        echo PHP_EOL;
    }

    function echo_block($style, $title, $message)
    {
        $message = ' ' . trim($message) . ' ';
        $width = strlen($message);

        echo PHP_EOL . PHP_EOL;

        $this->echo_style($style, str_repeat(' ', $width));
        echo PHP_EOL;
        $this->echo_style($style, str_pad(' [' . $title . ']', $width, ' ', STR_PAD_RIGHT));
        echo PHP_EOL;
        $this->echo_style($style, $message);
        echo PHP_EOL;
        $this->echo_style($style, str_repeat(' ', $width));
        echo PHP_EOL;
    }
}

