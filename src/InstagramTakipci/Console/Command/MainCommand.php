<?php

namespace InstagramTakipci\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use InstagramTakipci\Config;
use InstagramTakipci\Bot;

use Goutte\Client;
use Guzzle\Http\Client as GuzzleClient;

class MainCommand extends Command
{
    /**
     *
     * @var Bot
     */
    protected $bot;

    /**
     * @param Bot   $bot
     */
    public function __construct(Bot $bot = null)
    {
        $this->bot = $bot ? : new Bot();
        $this->bot->registerBuiltInSites();

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('basla')
            ->setDefinition(array(
                new InputArgument('isim', InputArgument::REQUIRED, 'Instagram kullanıcı adı'),
                new InputArgument('sifre', InputArgument::REQUIRED, 'Instagram şifresi (kimseyle paylaşılmaz)'),
                new InputOption(
                        "hashtags", "ht", InputOption::VALUE_REQUIRED, "Hashtagların okunacağı dosya yolu", "src/InstagramTakipci/data/hashtags.txt"),
                new InputOption("yorumlar", "y", InputOption::VALUE_REQUIRED, "Yorumların okunacağı dosya yolu", "src/InstagramTakipci/data/comments.txt"),
                new InputOption("low", "l", InputOption::VALUE_OPTIONAL, "Rastgele uyuma zamanının alt sınırı", 5),
                new InputOption("high", "hg", InputOption::VALUE_OPTIONAL, "Rastgele uyuma zamanının üst sınırı", 30),
            ))
            ->setDescription('@TODO:')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('isim');
        $password = $input->getArgument('sifre');

        $hashtags = $input->getOption('hashtags');
        $comments = $input->getOption('yorumlar');

        $filesystem = new Filesystem();

        if ("src/InstagramTakipci/data/hashtags.txt" == $hashtags) {
            $output->isVerbose() &&
                    $output->writeln("<comment>Hashtag dosyası bulunamadı. Varsayılan hashtaglar yüklenecek</comment>");
        }

        if ("src/InstagramTakipci/data/comments.txt" == $comments) {
            $output->isVerbose() &&
                    $output->writeln("<comment>Yorum dosyası bulunamadı. Varsayılan yorumlar yüklenecek</comment>");
        }

        if (!$filesystem->isAbsolutePath($hashtags)) {
            $hashtags = getcwd() . DIRECTORY_SEPARATOR . $hashtags;
        }

        if (!$filesystem->isAbsolutePath($comments)) {
            $comments = getcwd() . DIRECTORY_SEPARATOR . $comments;
        }

        $hashtags = array_map('trim', array_unique(file($hashtags)));
        $comments = array_map('trim', array_unique(file($comments)));

        $lowLimit = intval($input->getOption('low'));
        $highLimit = intval($input->getOption('high'));

        $config = Config::create($username, $password, $hashtags, $comments, $lowLimit, $highLimit);
        
        // Create the Guzzle and Goutte clients
        $client = new Client();
        
        $client->setClient(new GuzzleClient('', array(
            'curl.options' => array(
                CURLOPT_COOKIESESSION => true,
                CURLOPT_COOKIEJAR => getcwd() . '/cookie/cookies.txt',
                CURLOPT_COOKIEFILE => getcwd() . '/cookie/cookies.txt',
                CURLOPT_FRESH_CONNECT => true,
                CURLOPT_TIMEOUT => 0
            ),
            'request.options' => array(
                'debug' => $output->isDebug(),
            )
        )));
        
        $client->getClient()->setUserAgent(random_uagent());
        
        $this->bot->setClient($client);
        $this->bot->setConfig($config);
    }

}
