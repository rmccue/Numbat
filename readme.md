Numbat - A really little CMS
============================

Hi there, and welcome to Numbat! Numbat is a really, really small CMS where
items are identified by one thing only... their URL. It is also completely
media independent, as you can use the view system to create almost any type.

Numbat also features a built-in administration, but it is extremely lite by
design.

Numbat is licensed under the
[BSD license](http://www.opensource.org/licenses/bsd-license.php) (also in
license.txt).


Getting Started
---------------
The first step to using Numbat is to create a configuration file. For a start,
copy `app/config.sample.php` to `app/config.php` and edit the database
details and the base URL.


Structure
---------
Numbat is based around items. Items are simply a way of grouping data, and
each item has only one identifying piece of information: the URL.

Data is stored for each item. Each piece of data has a pointer to its parent
item, a name, a value and a type. Datatypes control how data is displayed in
the administration panel, and how the data is stored in the database. Data is
expected to be stored in plain text in the database, as datatypes themselves
have no control of the output on the frontend.

Numbat comes with two default datatypes, the Text datatype, and the HTML
datatype. Text is used for any one-liner text, such as the document's title,
while HTML can be used for anything, usually body content. Numbat also has one
internal datatype, View. This datatype must exist for every item, and can only
be used once for the "view" row. The value of this row determines which view
is loaded by the Controller.

Views control the output of an item, and are supplied with all the data
relating to the current item. Views can be anything, and do not have to output
HTML.


Writing Views
-------------
The "default" view acts as a base for other views, and supplies several handy
functions, listed below. All of these methods can be changed to your heart's
content.

The `config` method returns the configuration instance. This can be used to
output your baseurl for portability, but could also be used along with custom
entries in the configuration to switch between "production" and "development"
output.

The `get` method returns the value from the row whose name is specified by the
first parameter to the method. For example, to get the "title" row's value,
one could use `$this->get('title')` in the view.

The `output` method simply outputs the result of `get` and is purely a
convienience method.

The `getType` method returns the datatype of the row whose name is specified
by the first parameter. It is up to the view to decide what to do with this.


Shortcodes
----------
If the secondary parameter of `get` is set to true, the view will parse the
value of the row for a "shortcode". Shortcodes are small instructions for the
view, and are replaced with their corresponding values. They are written in
the form `{x.y}`, where `x` specifies the type of data. This is either
`config` or `req`. `{config.y}` would be replaced by the configuration setting
for "y". `{req.y}` would be replaced by the request variable "y". The request
variables available are:

- "page" - The current requested page ID (e.g. `some/page`)
- "url" - The requested URL (e.g. `http://example.com/some/page`)
- "time" - The time the page started loading in seconds (as a float)
- "error" - Whether the current page is an error.

Any shortcodes which do not have a prefix will be taken as a row name, and
will be replaced by the corresponding value. `{y}` would be replaced by the
value stored for the "y" row.


Naming Structure
----------------
The names of classes within Numbat use a special naming scheme which you must
adhere to when writing your application.

Views must live in the `views/` subdirectory. The filename is based on the
class name. For the View "Foo", the filename would be "foo.php", and the class
would be named "View_Foo". All view class names must be prefixed with "View_".

Datatypes must live in the `datatypes/` subdirectory. The filename is based on
the class name. For the Datatype "Foo", the filename would be "foo.php", and
the class would be named "Data_Foo". All view class names must be prefixed
with "Data_".


Overriding
----------
The internal structure of Numbat is written to enable you to override any
class. Simply copy the file you wish to overload into the corresponding
directory in your app directory.

For example, to override the Views class, you would copy
`numbat/classes/views.php` to `app/classes/views.php`


URL Rewriting
-------------
Numbat is designed to be able to work without the need for `mod_rewrite`. If
your server does not have `mod_rewrite` enabled, you can simply add
`/index.php` to your baseurl in the configuration.

Note: Your server needs to have pathinfo enabled for this to work. If neither
`mod_rewrite` nor pathinfo are enabled, you will not be able to use Numbat.


Multiple Applications
---------------------
Numbat is designed to be used for more than one application. The easiest way
to do this is to copy the `app` directory and the `.htaccess` and `index.php`
files to a new directory. You will need to change the NUMBAT_PATH in index.php
to the **local directory** Numbat resides in.