# Browser access control via WhatIsMyBrowser

Contributors: hupe13   
Tags: browser, agent, wimb   
Tested up to: 6.8   
Stable tag: 251017   
Requires at least: 6.5   
Requires PHP: 8.3   
License: GPLv2 or later   

Detects the browser and checks whether it is up to date. Blocks old versions and suspicious browsers.

## Description

Detects the browser and checks whether it is up to date. Blocks old versions and suspicious browsers.

## Updates

Please install [leafext-update-github](https://github.com/hupe13/leafext-update-github) to get updates and keep an eye on this repository in case I've made any mistakes.

## Functions

* There is a great tool: [What is my browser?](https://developers.whatismybrowser.com/api/). Get an API key for a Basic Application Plan.
* You have a limit of 5000 hits / month for Parsing User Agent. Thats why the plugin manages a database table.
* The user agent string of every browser accessing your website the first time is send to this service and some data will be stored in this table.
* These data are: the user agent string, a simple software string and the operating system. For example:
  - Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36
  - Chrome 140 on Windows 10
  - Windows 10
* The browser will be blocked if an old browser version or operating system is detected.
* Default: Chrome < 128, Edge < 128, Firefox < 128, Internet Explorer, Netscape (!), Opera < 83, Safari < 17
* Old systems are all Windows versions before Windows 10.
* Please inform me, if these defaults are not okay.
* You can configure other agents too.
* It will be blocked also if the "simple software string" contains "unknown" or is empty.
* If it this is not right for a browser, you can exclude it from checking.
* The plugin checks, if the crawlers are really from Google, Bing, Yandex, Apple, Mojeek, Baidu, Seznam.
* There is still some work to be done on the plugin. But it is already working.
