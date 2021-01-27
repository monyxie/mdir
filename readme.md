# MDir

MDir lets you easily browse and view your markdown files.

## Features
* Browser and view your markdown files in your browser.
* Plain directory structure and plain markdown files, no database needed.
* Github Flavored Markdown.
* [Mermaid](https://mermaid-js.github.io/) diagrams support.

## Getting started

You will need to have PHP >= 7.1 installed on your machine. Then follow these steps:
1. Clone this repo to your local machine.
2. Run `composer install' to install the dependencies.
3. Modify the config file `config/app.php` to suit your needs.
4. Start the web server. There are 3 ways you can do this:
    * With the built-in web server.
        1. Run the command `php react-server.php`.
    * With [Roadrunner](https://github.com/spiral/roadrunner). The steps are:
        1. Download and install the roadrunner binary.
        2. Run `rr serve`, where `rr` is the roadrunner binary.
    * Setup an old-fashioned LAMP/LNMP website (you can skip the M part, because MDir doesn't need MySQL to function). Please refer to guides on the internet on how to do this.
5. Open `http://localhost:5000/` in your browser.

## Todo
- [ ] Figure out how to make anchor links and Mermaid renders work better together

## Credits

This project is based on the following works:

- [Parsedown](https://github.com/erusev/parsedown) - Markdown parser.
- [Commonmark](https://commonmark.thephpleague.com/) - Markdown parser.
- [Symfony](https://symfony.com/) - Infrastructural libraries.
- [Pure](https://purecss.io/) - CSS library.
- [github-markdown-css](https://github.com/sindresorhus/github-markdown-css) - CSS for markdown.
- [Mermaid](https://mermaid-js.github.io/) - Diagrams support.
