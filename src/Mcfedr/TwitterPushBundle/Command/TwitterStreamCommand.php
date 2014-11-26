<?php
namespace Mcfedr\TwitterPushBundle\Command;

use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\StreamInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use Mcfedr\TwitterPushBundle\Service\TweetPusher;

class TwitterStreamCommand extends Command
{
    const BLOCK_SIZE = 1;
    /**
     * @var Client
     */
    private $client;

    /**
     * @var TweetPusher
     */
    private $pusher;

    /**
     * @var string
     */
    private $userid;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Client $client
     * @param TweetPusher $pusher
     * @param string $userid
     * @param LoggerInterface $logger
     */
    public function __construct(Client $client, TweetPusher $pusher, $userid, LoggerInterface $logger)
    {
        parent::__construct();

        $this->client = $client;
        $this->pusher = $pusher;
        $this->userid = $userid;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('mcfedr:twitter:stream')
            ->setDescription('Check for new tweets using a stream and push them');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info("Opening twitter stream to follow user {$this->userid}");

        /** @var Response $response */
        $response = $this->client->post('statuses/filter.json', [
            'body' => [
                'follow' => $this->userid
            ],
            'stream' => true
        ]);

        /** @var StreamInterface $stream */
        $stream = $response->getBody();

        // Read until the stream is closed
        $this->logger->info('Reading stream');
        $line = '';
        while (!$stream->eof()) {
            $line .= $stream->read(static::BLOCK_SIZE);
            while (strstr($line, "\r\n") !== false) {
                list($json, $line) = explode("\r\n", $line, 2);
                $this->logger->debug('Got a line', ['line' => $json]);
                if (trim($json) == '') {
                    $this->logger->debug('Keep alive');
                    continue;
                }
                $data = json_decode($json, true);
                if (isset($data['text'])) {
                    $this->logger->info('Received tweet', ['tweet' => $data]);
                    //Filter replies and retweets
                    if ($data['user']['id_str'] == $this->userid) {
                        try {
                            $this->pusher->pushTweet($data);
                            $this->logger->notice(
                                'Sent tweet',
                                [
                                    'TweetId' => $data['id_str']
                                ]
                            );
                        } catch (\Exception $e) {
                            $this->logger->error(
                                "Failed to push",
                                [
                                    'TweetId' => $data['id_str'],
                                    'Exception' => $e
                                ]
                            );
                        }
                    } else {
                        $this->logger->info('Ignored tweet', ['TweetId' => $data['id_str']]);
                    }
                } else {
                    $this->logger->debug(
                        'Other message',
                        [
                            'message' => $data
                        ]
                    );
                }
            }
        }

        $this->logger->info('Stream finished');
    }
}
