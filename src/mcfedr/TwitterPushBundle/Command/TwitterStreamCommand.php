<?php
namespace Mcfedr\TwitterPushBundle\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Guzzle\Http\Client;
use Guzzle\Stream\PhpStreamRequestFactory;
use Mcfedr\TwitterPushBundle\Service\TweetPusher;

class TwitterStreamCommand extends Command
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var TweetPusher
     */
    protected $pusher;

    /**
     * @var string
     */
    protected $userid;

    /**
     * @var LoggerInterface
     */
    protected $logger;

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

        $request = $this->client->post(
            'statuses/filter.json',
            null,
            [
                'follow' => $this->userid
            ]
        );

        $factory = new PhpStreamRequestFactory();
        $stream = $factory->fromRequest($request);

        // Read until the stream is closed
        while (!$stream->feof()) {
            $this->logger->debug('Loop start');

            // Read a line from the stream
            // Normal read line seems to leave me waiting quite a bit
            //$line = $stream->readLine();

            // Whereas this seems to work just fine
            $line = '';
            $fp = $stream->getStream();
            while (false !== ($char = fgetc($fp))) {
//                $this->logger->debug('Got a char', ['char' => $char]);
                $line .= $char;
                if ($char == "\n") {
                    break;
                }
            }

            $this->logger->debug('Got a line', ['line' => $line]);

            if (trim($line) == '') {
                $this->logger->debug('Keep alive');
                continue;
            }
            $data = json_decode($line, true);
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

        $this->logger->info('Stream finished');
    }
}
