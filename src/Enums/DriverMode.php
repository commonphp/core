<?php

/**
 * Support Class for DriverManager Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\DriverManager
 */

namespace CommonPHP\Core\Enums;

/**
 * Different operation modes for the Driver Manager
 */
enum DriverMode
{
    /** Each time a driver is called a new instance is created */
    case Unmanaged;

    /** A single instance exists for each different driver class */
    case Managed;

    /** The first class referenced is the only class to ever be returned */
    case Service;
}