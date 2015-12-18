# Create an sqlite DB containing PHP function documentation

A work-in-progress app for creating an sqlite PHP function database using a checkout of the PHP documentation SVN repository.

This is a really early doors version - written in a rush for use in a seperate project - which I've decided to open source because - well, why not? :-)

There's a lot more work to do, so if you come across any issues/limitations then there's a good chance I already know about it and plan to fix/improve it in a later release.  

Also, expect the DB layout to change - adding multiple language support will almost definitely require this. There's also a few hacky workarounds in the code that I had to 
add in a hurry to get the DB generated quickly - expect them to be fixed in a later version.

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "chrismou/phpdocs-to-db": "dev-master"
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

Then take the phpdoc_skeleton.db file in the build/ directory and create a copy in the same directory, calling it phpdoc.db (DB creation will be automated in a future release). 

Now open a terminal, get into the project root and run the following command:

```
php run.php phpdocdb:create
```

##s Future

* Automatic DB creation (currently requires the included sqlite skeleton db)
* Support for auto checkout/update using the PHP SVN module
* Support for language selection (or creation of a multi-language DB)
* Support for multiple DB providers
* Option to select whether to create a multi-table relational DB or a single-table DB (ie, for if filesize is an issue)
** Including being able to specify whether to include full variable definitions, etc

## License

Released under the MIT License. See [LICENSE](LICENSE.md)s