magniffer
=========

An extendable, XPath driven, static code analysis tool for Magento, built on the top of PHP-Parser library.

List of Detected Issues
=======================

* SQL Queries Within a Loop
* Not Limiting Collection Load Result
* Empty Class
* Empty Method
* Expression is Always True
* Empty Password in Configuration File
* Handling Overly Broad Event
* Use of Global Event
* Configuration Not in adminhtml.xml.


Installation
============

With [Composer](http://getcomposer.org/):

```
composer require --dev magento-ecg/magniffer
```

Basic Usage
============

```
mgf /path/to/files
```


Extending
=========

It's easy to create a custom inspection by declaring a message, xpath and inspector in YAML file. For example:

```
message   : Empty Class
xpath     : //node:Stmt_Class[count(subNode:stmts/scalar:array/*) = 0]/subNode:name/scalar:string
inspector : php
```
