<?php
/**
 * Created by mcfedr on 26/11/14 08:40
 */

namespace Mcfedr\TwitterPushBundle\Service;

use Doctrine\Common\Cache\ArrayCache;
use mcfedr\AWSPushBundle\Message\Message;

class TweetPusherTest extends \PHPUnit_Framework_TestCase
{
    public function testPushTweet()
    {
        $messagesMock = $this->getMock('Mcfedr\AwsPushBundle\Service\Messages', ['broadcast'], [] , '', false);
        $messagesMock->expects($this->once())
            ->method('broadcast')
            ->with(
                $this->isInstanceOf('Mcfedr\AwsPushBundle\Message\Message')
            );
        $service = new TweetPusher($messagesMock);
        $service->pushTweet($this->getTweet());
    }

    public function testPushTweetTopic()
    {
        $arn = 'topic';
        $messagesMock = $this->getMock('Mcfedr\AwsPushBundle\Service\Messages', ['send'], [] , '', false);
        $messagesMock->expects($this->once())
            ->method('send')
            ->with(
                $this->isInstanceOf('Mcfedr\AwsPushBundle\Message\Message'),
                $this->equalTo($arn)
            );
        $service = new TweetPusher($messagesMock, $arn);
        $service->pushTweet($this->getTweet());
    }

    /**
     * @expectedException \Mcfedr\TwitterPushBundle\Exception\MaxPushesPerHourException
     */
    public function testPushTweetRate()
    {
        $arn = 'topic';
        $messagesMock = $this->getMock('Mcfedr\AwsPushBundle\Service\Messages', ['send'], [] , '', false);
        $messagesMock->expects($this->exactly(5))
            ->method('send')
            ->with(
                $this->isInstanceOf('Mcfedr\AwsPushBundle\Message\Message'),
                $this->equalTo($arn)
            );

        $service = new TweetPusher($messagesMock, $arn, null, null, 5, new ArrayCache());
        for ($i = 0; $i < 6; $i++) {
            $service->pushTweet($this->getTweet());
        }
    }

    public function testGetMessageForTweet()
    {
        $tweet = $this->getTweet();
        $service = new TweetPusher($this->getMock('Mcfedr\AwsPushBundle\Service\Messages', [], [], '', false));
        /** @var Message $m */
        $m = $this->callMethod($service, 'getMessageForTweet', [
            $tweet
        ]);
        $this->assertInstanceOf('\mcfedr\AWSPushBundle\Message\Message', $m);
        $this->assertEquals("Лев Рубинштейн: Пока люди разговаривают, они не воюют", $m->getText());
        $this->assertEquals("http://t.co/5pmwVqKXNy", $m->getCustom()['u']);
    }

    private  function callMethod($obj, $name, array $args) {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    private function getTweet() {
        return json_decode(<<<TWEET
{
            "created_at": "Sat Apr 12 08:11:31 +0000 2014",
            "id": 454894570166550500,
            "id_str": "454894570166550528",
            "text": "Лев Рубинштейн: Пока люди разговаривают, они не воюют http://t.co/5pmwVqKXNy",
            "source": "web",
            "truncated": false,
            "in_reply_to_status_id": null,
            "in_reply_to_status_id_str": null,
            "in_reply_to_user_id": null,
            "in_reply_to_user_id_str": null,
            "in_reply_to_screen_name": null,
            "user": {
                "id": 1454734730,
                "id_str": "1454734730",
                "name": "Hromadske.TV",
                "screen_name": "HromadskeTV",
                "location": "Ukraine",
                "url": "http://hromadske.tv/",
                "description": "Для нас українське громадське мовлення – це соціальна місія, громадянська позиція. Взяти участь може кожен – матеріально, технічно, організаційно, волонтерськи",
                "protected": false,
                "followers_count": 130661,
                "friends_count": 189,
                "listed_count": 769,
                "created_at": "Fri May 24 17:12:06 +0000 2013",
                "favourites_count": 2,
                "utc_offset": 10800,
                "time_zone": "Kyiv",
                "geo_enabled": false,
                "verified": false,
                "statuses_count": 7273,
                "lang": "uk",
                "contributors_enabled": false,
                "is_translator": false,
                "is_translation_enabled": false,
                "profile_background_color": "C0DEED",
                "profile_background_image_url": "http://abs.twimg.com/images/themes/theme1/bg.png",
                "profile_background_image_url_https": "https://abs.twimg.com/images/themes/theme1/bg.png",
                "profile_background_tile": false,
                "profile_image_url": "http://pbs.twimg.com/profile_images/344513261568054516/810536cc89a0ee2948247a4c571c1007_normal.png",
                "profile_image_url_https": "https://pbs.twimg.com/profile_images/344513261568054516/810536cc89a0ee2948247a4c571c1007_normal.png",
                "profile_banner_url": "https://pbs.twimg.com/profile_banners/1454734730/1371033370",
                "profile_link_color": "0084B4",
                "profile_sidebar_border_color": "C0DEED",
                "profile_sidebar_fill_color": "DDEEF6",
                "profile_text_color": "333333",
                "profile_use_background_image": true,
                "default_profile": true,
                "default_profile_image": false,
                "following": null,
                "follow_request_sent": null,
                "notifications": null
            },
            "geo": null,
            "coordinates": null,
            "place": null,
            "contributors": null,
            "retweet_count": 9,
            "favorite_count": 2,
            "entities": {
                "hashtags": [],
                "symbols": [],
                "urls": [
                    {
                        "url": "http://t.co/5pmwVqKXNy",
                        "expanded_url": "http://goo.gl/AOGtkf",
                        "display_url": "goo.gl/AOGtkf",
                        "indices": [
                            54,
                            76
                        ]
                    }
                ],
                "user_mentions": []
            },
            "favorited": false,
            "retweeted": false,
            "possibly_sensitive": false,
            "lang": "ru"
        }
TWEET
            , true);
    }
}
