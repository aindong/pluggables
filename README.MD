[![Laravel 5.1](https://img.shields.io/badge/Laravel-5.1-orange.svg?style=flat-square)](http://laravel.com)
[![Build Status](https://travis-ci.org/aindong/pluggables.svg?branch=master)](https://travis-ci.org/aindong/pluggables)
![LICENSE](https://img.shields.io/badge/license-MIT-blue.svg)

##Pluggables

A modular pattern guide/generator for creating a (more modular way) laravel application easily. It follows the FIG PSR-1, PSR-2, and PSR-4 coding standards. Pluggables supports the current version of laravel (5.1). 

##QUICK INSTALLATION

You can install this package using `composer`

`composer require aindong/pluggables=~1.0`

Once the composer operation is complete, locate your `config/app.php` and open it using your editor(vim, sublime, phpstorm, etc). 
Then add this into the service `providers` array

`Aindong\Pluggables\PluggablesServiceProvider::class,`

then scroll down to see the `facade` array and add this

`'Pluggables' => Aindong\Pluggables\Facades\Pluggable::class,`

after that, you have to set the configuration files of pluggables using
`php artisan vendor:publish` then look for the pluggables.php on the config folder.

and you can start using both the `Plugegables` console commands and facades during the development of your application, to see all the pluggables commands simply type this into your terminal
`php artisan` and check for `pluggables`. Have fun!

#####NOTES:
Development stage

#####SOON
Will add unit tests soon. For those who would like to contribute, you can always contact me @ alleo.indong@gmail.com for now, thank you!
