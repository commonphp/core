<?php

/**
 * Support Class for Debugger Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Debugger
 */

namespace CommonPHP\Core\Enums;

/**
 * Different levels of severity used by the debugger
 */
enum DebuggerSeverity
{
    /** This is a fatal event */
    case Error;

    /** Non-fatal event that describes a future error */
    case Warning;

    /** Non-fatal event that could indicate an error, but could be normal */
    case Notice;

    /** Debugging message for later review */
    case Debug;

    /** General information message, for later review */
    case Info;

    /** A security-based event, could be fatal */
    case Security;

    /** An application-specific event, could be fatal */
    case Application;
}