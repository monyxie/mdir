# MDir

A Markdown viewer.

## Setup

1. Clone this repo to your local machine.
2. Run `composer install' to install the dependencies.
3. Change the config file `config/app.php` to suit your needs. See the config section for more info.

## Running the server
There are 3 ways you can run the server. In all cases you will need to have PHP 7.1 or above installed first.

* As a typical PHP website. Instructions may vary depending on which web server you choose. But generally you'll need to:
    1. Setting up a webserver of your choice (Nginx or Apache will do).
    2. In the web server's configuration, setup up a PHP website and point the document root to `/path/to/mdir/public`. You will need to refer to the web server's documentation for guides on how to do this.
    3. Start the web server.
* With [Roadrunner](https://github.com/spiral/roadrunner). The steps are:
    1. Download and install the roadrunner binary.
    2. Run `rr serve`, where `rr` is the roadrunner binary.
    3. Optionally, you could make the above command into a service. One way to do this on Linux is with systemd.
* With MDir's built-in web server. This is the simplest way of running the server. You just run `php react-server.php` and that's it.


## Credits
- [Parsedown](https://github.com/erusev/parsedown) - Markdown parser.
- [Symfony](https://symfony.com/) - Infrastructural libraries.
- [Pure](https://purecss.io/) - CSS library.
- [github-markdown-css](https://github.com/sindresorhus/github-markdown-css) - CSS for markdown.
