terminus-courses
====================

Simple Symfony Console Application For syncing student Pantheon environments with
instructors environment.


License
-------

Copyright (c) 2013 Miguel Angel Gabriel (magabriel@gmail.com)

This extension is licensed under the GPL V2 license. See `LICENSE.txt`.

Description
-----------

Uses Pantheon Multidev

Requires [Pantheon CLI](https://github.com/pantheon-systems/cli)

Forked from: https://github.com/magabriel/symfony-cli-skeleton

Available commands
------------------
###help
###list
###student-create   
Create Environments
###student-del
Delete  Student Environments
###student-git-push
Push to Git Students
###student-push
Push to Students Environments

Customization
-------------
  
  

Build
-----

Execute `php build.php` and a `phar` file will be automagically generated inside the `build/` project subdirectory. You can rename or/and copy this `phar` file to wherever you want and use it to execute you shinny CLI application.
   
phar.readonly = 0 must be set in your php.ini file.

About versions
--------------

File `config/build.yml` stores the *current* application version string that is shown when it is run. The version string is incremented *after* each build following a naming pattern that can be found in the same file and that can be easily customized to suit your needs.
 
 

