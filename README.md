# HaploMvc

A simple and lightweight PHP MVC development framework.

## Key Features

   * User/SEO friendly URLs based on regex pattern matching
   * Basic built in protection for XSS and helpers to prevent CSRF
   * PHP templating
   * Caching (file or memcached)
   * Simple DB layer (wrapper to PDO)
   * Support for localisation

## License

BSD License (see LICENSE for conditions of use).
HaploEscaper uses the Zend Escaper library which is licensed under the Zend Framework license (http://framework.zend.com/license)

## Change Log

### 0.0.12 (24th October 2013)

Added optional slim action class and general tidy up of code.

### 0.0.11 (23rd October 2013)

Updated SQL builder to support where conditions in brackets, added paged_array method to DB class and updated other interfaces.

### 0.0.10 (17th October 2013)

Some more restructuring, hooked up db and builder to app.

### 0.0.9 (16th October 2013)

Added basic Active Record class.

### 0.0.8 (15th October 2013)

Restructured framework files, split out provider specific DB code into driver files, added initial code for query builder (unfinished).

### 0.0.7 (10th October 2013)

Implemented dry run switch to enable outputting of generated queries rather than running.

### 0.0.6 (10th October 2013)

Added additional methods to DB Query Builder.

### 0.0.5 (10th October 2013)

Added basics of DB Query Builder and support code to DB class.

### 0.0.4 (6th October 2013)

Added initial tests, fixed validation issues and updated loader.

### 0.0.3 (5th October 2013)

Updated setup checker.

### 0.0.2 (5th October 2013)

Add defaults for translation options.

### 0.0.1 (5th October 2013)

Initial release. Based on an earlier version of the same framework but completely rewritten to use namespaces and to ensure looser coupling between components.
