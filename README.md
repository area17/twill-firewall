# Firewall Twill Capsule

This Twill Capsule is intended to enable developers to configure an application internal firewall. 

![screenshot 1](docs/screenshot01.png)

![screenshot 2](docs/screenshot02.png)

## Domains

You add as many domains as you need and configure different strategies for each. Once you enable the `all domains (*)` entry, the same configuration will be used for all, and all other domains will be hidden.

## Features

- "Allow" strategy: only allow access to the listed IP addresses 
- "Block" strategy: block all listed IP addresses
- Detects and blocks automatically attacks
- Automatically add block attacker IP addresses to the block list
- Redirection instead of blocking. Allow displaying a "Coming soon" page to external users, while giving access to the team. 

## Middleware

A middleware is automatically added to all `web` routes, but you can configure this behaviour or even disable it to configure your middleware yourself:  

``` php
'middleware' => [
    'automatic' => true,

    'groups' => ['web'],

    'class' => \A17\TwillFirewall\Http\Middleware::class,
],
```

## Using authentication

If you have authorization on your app (as you have on Twill), you can exempt logged in users from the rate limit, so they don't risk getting blocked.

## Detecting attacks

If enabled it will rate limit requests (per minute) by the IP address. After a minute is passed the offending client can get back to do more requests. Enabling "Automatically add blocked IP addresses to block list" makes a block permanent, it adds the "attacker" IP address to the block list.

Make sure you set a too low rate, so you don't risk blocking Gooble Bot and other search engine crawlers out there. 

## Request time overhead and middleware

On our benchmarks (using [ApacheBench](https://httpd.apache.org/docs/2.4/programs/ab.html) to perform 500 requests with 10 concurrent clients) we detected the firewall adds around 4ms extra time on each request, adding the middleware to the end of the list. If you don't need authentication, you can configure the `middleware.method` to `prepend`, it will execute before all other middleware and block requests as soon as possible. You can also disable automatic middleware injection and configure it yourself.

## Installing

### Require the Composer package:

``` bash
composer require area17/twill-firewall
```

### Configuration

#### Publishing the main config file

``` bash
php artisan vendor:publish --provider="A17\TwillFirewall\ServiceProvider"
```

#### Sections

To improve DX when adding new features to this package, we split [the config file in several sections](/src/config).  

You can list sections available to be merged into the published config file using this command:

```bash
php artisan twill-firewall:config:list-sections
```

Which should give you an output like:

```
+----------------+--------------------------------------------------------+
| Section        | Merge command                                          |
+----------------+--------------------------------------------------------+
| attacks        | php artisan twill-firewall:config:merge attacks        |
| cache          | php artisan twill-firewall:config:merge cache          |
| database-login | php artisan twill-firewall:config:merge database-login |
| inputs         | php artisan twill-firewall:config:merge inputs         |
| keys           | php artisan twill-firewall:config:merge keys           |
| middleware     | php artisan twill-firewall:config:merge middleware     |
| package        | php artisan twill-firewall:config:merge package        |
| responses      | php artisan twill-firewall:config:merge responses      |
| routes         | php artisan twill-firewall:config:merge routes         |
+----------------+--------------------------------------------------------+
```

And you can merge a section using this command: 

```
php artisan twill-firewall:config:merge <section-name>
```

### Load Capsule helpers by adding calling the loader to your AppServiceProvider:

``` php
/**
 * Register any application services.
 *
 * @return void
 */
public function register()
{
    \A17\TwillFirewall\Services\Helpers::load();
}
```

#### .env 

Enable it on your `.env` file: 

```dotenv
TWILL_FIREWALL_ENABLED=true
```

## Contribute

Please contribute to this project by submitting pull requests.
