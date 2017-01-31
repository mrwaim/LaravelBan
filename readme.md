# Banned Keywords Checker For Laravel

This is a package to check some of the keyword being used in files inside folder. This package are developed on Laravel 5.

## Install using composer
`composer require klsandbox/banned-keywords-checker`

## How To Use
After installing via composer, publish the config simply by using publish command

`php artisan vendor:publish`

open the config for banned keyword, go to the config/banned-keywords.php , specify the keywords and the directory need to be searched.

after that, run the command to search for the banned keywords

` php artisan check:banned-keywords`

you can also specify the keyword from the terminal

` php artisan check:banned-keywords another_keyword`

add optional argument to make it strict

` php artisan check:banned-keywords --mode=strict`


## Security Vulnerabilities

If you discover a security vulnerability within this package, please send an e-mail to Ibrahim Abdul Rahim at ibrahim@klsandbox.com. 

## License

This client are open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).