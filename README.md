# Import PHP docs into an sqlite DB

A work-in-progress app for creating an sqlite DB from the official PHP docs, using a checkout of their SVN repository.

This is a really early doors version, written in a rush for use in a seperate project, which I've decided to open source because - well, why not? :-)

There's a lot more work to do, so if you come across any issues/limitations then there's a good chance I already know about it and plan to fix/improve it in a later release.  
Also, expect the DB layout to change - adding multiple language support will almost definitely require this. There's also a few hacky workarounds in the code that I had to 
add in a hurry to get the DB generated quickly - expect them to be fixed in a later version.

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "chrismou/phpdocs-to-sqlite": "dev-master"
    }
}
```

Next you need to grab a copy of the PHP docs from their SVN repository.  As I've not added full language support yet, you need to choose what language you want to use now.

For this example, you'll need subversion installed. To set yourself up to create an English language PHP doc DB, get into the project root and run the following command:

```
svn checkout http://svn.php.net/repository/phpdoc/en/trunk/ data/
```

Expect this to take a while, as you're grabbing 150+ MB of XML files. You can switch it to use other languages by switching "en" in the URL (open 
[http://svn.php.net/repository/phpdoc] in a browser to see the full list of supported languages).

Now open a terminal, get into the project root and run the following command:

```
php run.php phpdocdb:create
```

# Future

* Support for auto checkout/update using the PHP SVN module
* Support for language selection (or creation of a multi-language DB)
* Option to select whether to create a multi-table relational DB or a single-table DB (ie, for if filesize is an issue)
** Including being able to specify whether to include full variable definitions, etc