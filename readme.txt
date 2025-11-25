=== Block old browser versions and suspicious browsers ===

Contributors: hupe13
Tags: bad bots, ban, blocking, security, robots.txt
Tested up to: 6.9
Stable tag: 251122
Requires at least: 6.3
Requires PHP: 8.1
License: GPLv2 or later

With help of WhatIsMyBrowser the plugin detects old and suspicious agents and denies them access. A special robots.txt prevents crawling by bad bots.

== Description ==

The plugin uses WhatIsMyBrowser.com to get informations about the browser. It detects old and suspicious browsers and denies them access to your website. It provides a robots.txt file to prohibit crawling and blocks crawlers if they do so anyway.

- Get an API key from <a href="https://developers.whatismybrowser.com/api/">What is my browser?</a> for a Basic Application Plan.
- You have a limit of 5000 hits / month for Parsing User Agent. Thats why the plugin manages a database table.
- The user agent string of every browser accessing your website the first time is send to this service and some data will be stored in this table.
- Browsers will be blocked, if the browser and/or the system is an old one:<br>Default: Chrome and Chrome based browsers < 128, Firefox < 128, Internet Explorer, Netscape (!), Opera < 83, Safari < 17
- Old systems are all Windows versions before Windows 10, some MacOS and Android versions.
- It will be blocked also if the "simple software string" contains "unknown" or is empty.
- You can configure other browsers too.
- Sometimes there are false positive, for example if the browser is from Mastodon. Then you can exclude these from checking.
- The plugin checks, if the crawlers are really from Google, Bing, Yandex, Apple, Mojeek, Baidu, Seznam.

= About robots.txt =

- You can configure some rewrite rules, to provide a robots.txt to enable or to disable crawling for a browser. If crawling is disabled, access to your website will be blocked for that browser.

= Logging =

- The logging can be very verbose. Please check the logs and the WIMB table regularly.

== Installation ==

* Install the plugin in the usual way.
* Go to Settings - WIMB and Block - and get documentation and settings options.

Please install [ghu-update-puc](https://github.com/hupe13/ghu-update-puc) to get updates and keep an eye on this repository in case I've made any mistakes.
