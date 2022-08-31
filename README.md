# CommonPHP Core Library

_Version 1.0_

CommonPHP is an open-source web application framework built for use in future commercial projects. The concept stemmed from the use of other popular web frameworks, but is built to conform to a different way of thought and code.

We're going to jump topics before diving further into the purpose of this library. To skip to the purpose, go to the [More information](#markdown-header-more-information) section 

## Installation

Installation can either be done by downloading this project and including the individual files, however, it was intended to use composer to install this library

```bash
composer install commonphptlm/core 
``` 

## Usage

The assumption is made that this project is being used from composer. If that's not the case, all PHP files in the library (namely the ones intended to be used) need to be manually included.

```php
include('vendor/autoload.php');
$injector = new \CommonPHP\Core\Injector();
```

Once the injector is created, that single object should either exist in your primary application class or be passed between various classes in your application. Any class that you intend to use as a Service, or want to be able to inject dependencies into, should be created from this instance.

### Services

Service classes, used with the Injector component, are singleton enforced classes meaning that only a single instance of the class can exist.

```php

function serviceFilesystemExample(\CommonPHP\Core\Filesystem $filesystem): bool
{
    // Returns true
    return $filesystem->hasNamespace('log');
}

function standaloneFilesystemExample(\CommonPHP\Core\Filesystem $filesystem): bool
{
    // Returns false
    return $filesystem->hasNamespace('log');
}

/** @var \CommonPHP\Core\ServiceManager $serviceManager */
$serviceManager = $injector->instantiate(\CommonPHP\Core\ServiceManager::class);

/** @var \CommonPHP\Core\Filesystem $filesystem */
$filesystem = $serviceManager->getService(\CommonPHP\Core\Filesystem::class);

$filesystem->addNamespace('log', '/var/log');

$injector->call('serviceFilesystemExample');

standaloneFilesystemExample(new Filesystem());
```

As you can see in the example, the call made to ```serviceFileSystem``` will return true because of the Injector providing that Filesystem object. Alternatively, ```standaloneFilesystemExample``` returns false because a fresh instance of the Filesystem component was created for that function.

### Custom Injectors

As mentioned later, one of the goals of this frameworks is modularity. For that reason, most things can be customized, including having a custom injector.

```php
class MyCustomInjector implements \CommonPHP\Core\Contracts\InjectorContract
{
    public function check(string $typeName, bool $isBuiltin): bool 
    {
        return $typeName === MyCustomClass::class;    
    }
    
    public function get(string $typeName, string $signature): object
    {
        return new MyCustomClass();    
    }
}
```

While this is a very simple example of a custom injector, the ```check``` method is used to test the type to see if it matches this injector. If true, ```get``` will return an instance of the type. The Core Library includes three builtin Injectors:
- ConfigurableInjector: Injects classes that have the Configurable attribute and automatically loads any applicable settings
- CoreInjector: Automatically injects any of service libraries that exist in the Core library
- ServiceInjector: automatically inject any class that has the ```#[Service]``` Attribute

## More Information

Just to note, this isn't to say that other frameworks are bad, quite the contrary. Other application frameworks have some definitively powerful features, but, depending on the purpose, they can be slightly too complex for different types of projects

There are three primary goals for the development and future of CommonPHP:

- **Modernization**: The (hopeful) plan is to keep the framework as in-line as possible with current PHP and mysql functionality. While this (sometimes) may not be possible, as to maintain backward compatibility, the plan would be to potentially fork the project (or increase the major version) as major releases of underlying platforms are deployed.
- **Modular**: Other than the Core library, the plan is to split out major components of the framework into their own separate library. This is so that a smaller project, that may not need 80% of the framework, only loads the 20% that is required.
- **Easy to Use**: As mentioned previously, the purpose of this framework is to conform to a different way of thought and code. The hope here is that the end-product for CommonPHP will result in a collection of commonly used PHP classes, that are easy to understand and use, without having to rely intimately on the documentation or code builders to build a simple or complex application 

## The Core Library

This specific library, the "Core" library, is the base library in the suite (and the first to be released). This library contains all the conceivable components that would be used in every dependent library and application. These components include:

- **Arrayifier**: The Arrayifier component provides a simple way to convert between objects and arrays by utilizing PHP attributes and custom methods
- **Configuration**: Provide the ability to read, write and check for configuration files as well as reading and writing directly into and from class objects
- **Debugger**: Provides extremely basic debugging and logging functionality, including rotating logs when they reach 1MB
- **DriverManager**: Allows classes to implement driver functionality into themselves, enabling the use of custom attributes and enforcing contracts.
- **Filesystem**: Simple filesystem mechanics so that objects can access the filesystem, as well as namespaces (aka virtual paths), without having to determine the root (or other) paths individually between classes
- **Injector**: Provides dependency injection functionality for instantiation and invocation of class methods and functions, with the ability to create class aliases for contract injection
- **Inspector**: Extends upon the built-in PHP reflection to accomplish frequent calls without repeating code
- **ServiceManager**: Enforces a singleton-type pattern on marked classes (as long as they're instantiated from the Injector component) so that only a single instance of such a class would be created and shared
- **Validator**: Allows for validation of properties on a provided class.

## More documentation and Contributions

Honestly, we just kind of wanted to get this framework out there for review. This is the start of our contribution to the open-source world, so we hope it's a worthy contribution! 

That being said, we're still working on writing the documentation for this library, and the rest of the framework. Feel free to review this library and pull requests are always welcome! For major changes, please open an issue first to discuss what you'd like to change. Additionally, please update any tests where applicable.

## License
[GPL 3.0](https://opensource.org/licenses/gpl-3.0.html)
