# Twitter Push Bundle

A bundle for sending tweets as push notifications

## Install

Include the bundle in your AppKernal
You need to also load the AWSPushBundle

    new mcfedr\AWSPushBundle\mcfedrAWSPushBundle(),
    new mcfedr\TwitterPushBundle\mcfedrTwitterPushBundle(),

## Config

This is sample configuration, to add to your config.yml

    mcfedr_twitter_push:
        twitter:
            consumer_key: 'my consumer key'
            consumer_secret: 'my consumer secret'
            token: 'my token'
            token_secret: 'my token secret'
        userid: "twitter id that you want to follow"

You can find user id at [idfromuser.com](http://idfromuser.com)

You will also need to configure the AWSPushBundle, see the
[readme](https://github.com/mcfedr/awspushbundle/blob/master/README.md) for details

## Daemon

Run the daemon `./app/console mcfedr:twitter:stream`

There is a sample upstart config in the Resources/samples folder.
