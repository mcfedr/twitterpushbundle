# Twitter Push Bundle

A bundle for sending tweets as push notifications

[![Latest Stable Version](https://poser.pugx.org/mcfedr/twitterpushbundle/v/stable.png)](https://packagist.org/packages/mcfedr/twitterpushbundle)
[![License](https://poser.pugx.org/mcfedr/twitterpushbundle/license.png)](https://packagist.org/packages/mcfedr/twitterpushbundle)
[![Build Status](https://travis-ci.org/mcfedr/twitterpushbundle.svg?branch=master)](https://travis-ci.org/mcfedr/twitterpushbundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/f20e6ab9-aa4c-4b5c-9af1-f3691c5b1bf9/mini.png)](https://insight.sensiolabs.com/projects/f20e6ab9-aa4c-4b5c-9af1-f3691c5b1bf9)

## Install

### Composer

    php composer.phar require mcfedr/twitterpushbundle

### AppKernel

Include the bundle in your AppKernel
You need to also load the AWSPushBundle

    public function registerBundles()
    {
        $bundles = array(
            ...
            new Mcfedr\AwsPushBundle\McfedrAWSPushBundle(),
            new Mcfedr\TwitterPushBundle\McfedrTwitterPushBundle(),

## Config

This is sample configuration, to add to your config.yml

    mcfedr_twitter_push:
        twitter:
            consumer_key: 'my consumer key'
            consumer_secret: 'my consumer secret'
            token: 'my token'
            token_secret: 'my token secret'
        userid: "twitter id that you want to follow"

You can find userid at [idfromuser.com](http://idfromuser.com). You can also use a comma separated list if you want to
follow multiple users

You will also need to configure the AwsPushBundle, see the
[README](https://github.com/mcfedr/awspushbundle/blob/master/README.md) for details

## Daemon

Run the daemon `./app/console mcfedr:twitter:stream --env=prod --no-debug`

There is a sample supervisord config in the supervisord folder.
