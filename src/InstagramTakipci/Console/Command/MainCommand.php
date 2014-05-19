<?php

namespace InstagramTakipci\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use InstagramTakipci\Config;
use InstagramTakipci\Bot;

class MainCommand extends Command
{
    /**
     *
     * @var Bot
     */
    protected $bot;

    protected function configure()
    {
        $this
            ->setName('basla')
            ->setDefinition(array(
                new InputArgument('isim', InputArgument::REQUIRED, 'Instagram kullanıcı adı'),
                new InputArgument('sifre', InputArgument::REQUIRED, 'Instagram şifresi (kimseyle paylaşılmaz)'),
                new InputOption("hashtags", "ht", InputOption::VALUE_REQUIRED, "Hashtagların okunacağı dosya yolu", "src/InstagramTakipci/data/hashtags.txt"),
                new InputOption("yorumlar", "y", InputOption::VALUE_REQUIRED, "Yorumların okunacağı dosya yolu", "src/InstagramTakipci/data/comments.txt"),
                new InputOption("low", "l", InputOption::VALUE_OPTIONAL, "Rastgele uyuma zamanının alt sınırı", 5),
                new InputOption("high", "hg", InputOption::VALUE_OPTIONAL, "Rastgele uyuma zamanının üst sınırı", 30),
            ))
            ->setDescription('@TODO:')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

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

        // Set up the bot
        $bot = new Bot($this->getIO(), $config);
        $bot->setRandomSite();

        // Finish timestamp
        $finishTime = strtotime("+6 hour");

        // THE MAIN THINGS
        // First, login to the site
        $result = $bot->login();
        $this->checkForLoginErrors($result);

        // After successfull login, this is our main loop
        while (time() <= $finishTime) {
            $bot->doALL();
            $output->writeln("INFO: Sleeping for 10 minutes!");
            gc_collect_cycles();
            sleep(60 * 10);
        }
    }

    private function checkForLoginErrors($result)
    {
        if (!$result['error']) {
            return;
        }

        if ("FORBIDDEN" == $result['error']['message']) {
            $this->output->writeln("<error>Bir limite takıldınız. 10 dakika beklenecek.\n
                Ayrıca captcha kontrolüne takılmış olabilirsiniz.</error>");
            $this->output->isDebug() &&
                    $this->output->writeln("<error>" . $result['error']['info'] . " FORBIDDEN hatası</error>");
            time_sleep_until (strtotime ("+10 minutes"));
        } else {
            // TODO: log $result
            var_dump($result);
            $this->output->writeln("<error>Kullanıcı adı ve parolayı kontrol edip tekrar deneyin.\n
                Bu mesajı birden fazla defa gördüyseniz Instagram'da veya webstagram'da geçici bir sorun olmuş olabilir.\n
                Daha sonra tekrar deneyin.</error>");
            exit;
        }
    }

}
