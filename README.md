# Time Report CLI

Based on [laravel-zero](http://laravel-zero.com/).

## Requirements

* PHP >= 7.1.3
* ext-curl
* ext-dom
* ext-fileinfo
* ext-json
* ext-libxml
* ext-mbstring
* ext-Phar
* ext-posix
* ext-tokenizer
* ext-xml
* ext-xmlwriter

## Supported Systems

* [Lemberg CodebaseHq](https://www.codebasehq.com/)
* [Treeline Jira](https://www.atlassian.com/software/jira)

## Installation

1. clone this repository

2. Install composer dependencies

    ```
    $ composer install
    ```

3. Copy example **env** file and add your credential

    ```
    $ cp .env.example .env
    ```

## Simple Usage

1. Create default CSV files by all available systems 

    ```
    $ ./time-report-cli report make-stubs
    ```

2. Rename `dummy-project-name.csv` to `real-project-name.csv`
    * required by [Lemberg CodebaseHq](https://www.codebasehq.com/)
    
3. fill **CSV** file

    ```
    minutes(integer);tiket number;work log description
    60;123;Cool work log
    ```
    **Important!** CSV files do not contain headers.

4. calculate work logs sum 

    ```
    $ ./time-report-cli report
    ```
    
5. send all time to systems by projects 

    ```
    $ ./time-report-cli send
    ```
    
    After successful sending all data will be annulled.


## Additional info

```
./time-report-cli report --help
```
