# Twitter Push Bundle

A bundle for sending tweets as push notifications

[![Latest Stable Version](https://poser.pugx.org/mcfedr/twitterpushbundle/v/stable.png)](https://packagist.org/packages/mcfedr/twitterpushbundle)
[![License](https://poser.pugx.org/mcfedr/twitterpushbundle/license.png)](https://packagist.org/packages/mcfedr/twitterpushbundle)

## Install

### Composer

    "mcfedr/twitterpushbundle": "~1.0.1"

### AppKernel

Include the bundle in your AppKernel
You need to also load the AWSPushBundle

    public function registerBundles()
    {
        $bundles = array(
            ...
            new mcfedr\AWSPushBundle\mcfedrAWSPushBundle(),
            new mcfedr\TwitterPushBundle\mcfedrTwitterPushBundle(),

### Routing

Setup the controllers in your routing.yml

    mcfedr_twitter_push:
        resource: "@mcfedrTwitterPushBundle/Controller/"
        type:     annotation
        prefix:   /


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

You will also need to configure the AWSPushBundle, see the
[readme](https://github.com/mcfedr/awspushbundle/blob/master/README.md) for details

## Daemon

Run the daemon `./app/console mcfedr:twitter:stream`

There is a sample upstart config in the Resources/samples folder.

## Usage

Your devices should send their notification tokens to the controller

    POST /devices
    {"deviceID": "device token here", "platform": "ios"}

