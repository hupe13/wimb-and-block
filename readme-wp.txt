=== Block old browser versions and suspicious browsers ===

Contributors: hupe13
Tags: bad bots, ban, blocking, security, robots.txt
Tested up to: 6.9
Stable tag: 1.1
Requires at least: 6.3
Requires PHP: 8.1
License: GPLv2 or later

With the help of WhatIsMyBrowser the plugin detects old and bad browsers and denies them access. A special robots.txt denies crawling by bad bots.

== Description ==

Every time your web browser makes a request to a website, it sends a HTTP Header called the "User Agent". The User Agent string contains information about your web browser name, operating system, device type and lots of other useful bits of information.

The plugin sends with an API the User Agent string of every browser that accesses your website for the first time to <a href="https://api.whatismybrowser.com/api/v2/user_agent_parse">https://api.whatismybrowser.com/api/v2/user_agent_parse</a> to obtain following information about the User Agent:

* Software Name & Version
* Operating System Name & Version

<a href="https://developers.whatismybrowser.com/api/about/legal/">WhatIsMyBrowser.com API Terms and Conditions</a>

With this information, the plugin attempts to detect old and bad browsers and denies them access to your website.

= HowTo =

* Go to <a href="https://developers.whatismybrowser.com/api/pricing/">What is my browser?</a> and sign up to the WhatIsMyBrowser.com API for a Basic (free) Application Plan.
* You have a limit of 5000 hits / month for Parsing User Agent. That's why the plugin manages a database table.
* The user agent string of every browser that accesses your website for the first time is sent to this service, and the information is stored a table.
* Browsers are blocked if the browser and/or system are outdated:
    - Default: Chrome, Edge and Chrome based browsers < 137, Firefox browsers < 138, Safari < 18, Internet Explorer, Netscape (!)
    - Old systems are all Windows versions prior to Windows 10, MacOS prior to Catalina and Android versions < 9 (Pie).
* It will be blocked also if the "simple software string" contains "unknown" or is empty.
* You can also set up other browsers.
* Sometimes there are false positive, for example, if the browser is from Mastodon. In this case, you can exclude it from the check.
* The plugin checks whether the crawlers really originate from Google, Bing, Yandex, Apple, Mojeek, Baidu, Seznam.

= About robots.txt =

* You can configure some rewrite rules to provide a robots.txt file that can allow or deny crawling for a browser. If crawling is denied, access to your website will be blocked for that browser.

= Logging =

* The logging can be very detailed. Please check the logs and the WIMB table regularly.

## Screenshots

1. Database settings <br />![Database settings](.wordpress-org/screenshot-1.png)
2. Exclude these browsers from checking / Always block browsers with this strings <br />![Exclude these browsers from checking / Always block browsers with this strings](.wordpress-org/screenshot-2.png)
3. robots.txt settings <br />![robots.txt settings](.wordpress-org/screenshot-3.png)
4. Access attempts from the same IP address are blocked within 4 seconds (this can also happen more quickly). <br />![Access attempts from the same IP address are blocked within 4 seconds (this can also happen more quickly).](.wordpress-org/screenshot-4.png)

== Installation ==

* Install the plugin in the usual way.
* Go to Settings - WIMB and Block - and get documentation and settings options.

== Changelog ==

see <a href="https://github.com/hupe13/wimb-and-block/blob/main/CHANGELOG.md">Github</a>
