<?php

/**
 * Support Class for Filesystem Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Filesystem
 */

namespace CommonPHP\Core\Enums;

/**
 * File access modes
 */
enum FileMode
{
    /** Not relevant to the action */
    case None;

    /** File is meant to be read */
    case Read;

    /** File is meant to be written */
    case Write;

    /** File may be read or written */
    case ReadWrite;
}