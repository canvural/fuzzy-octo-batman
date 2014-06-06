<?php

namespace InstagramTakipci\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use InstagramTakipci\Config;
use InstagramTakipci\Bot;

class UnfollowCommand extends Command
{
    private $clientIds = array(
        "104087a4e94c467b83b19a7f315c6f43",
        "ff71a6212d124eb6a648a240c681e27b",
        "4123f73c6aad4f3b903369ccb353ecde",
        "9420651ad6064ce196bf4ff008ec2032",
        "ab48a254b33b467f8634331184c6550a",
        "9fd51ae9c0014b2293be80bfae3692de",
        "8e7165923b2845adb1b8f36f3ba4be8b",
        "713da7bc385e4d7f9e01a73ad2845ae5",
        "010b62cc4e344146bd73437f9b634587",
        "c02d1c473e53485d946a1c44d3daf8d2"
    );

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('unfollow')
            ->setDefinition(array(
                new InputArgument('isim', InputArgument::REQUIRED, 'Instagram kullanıcı adı'),
                new InputArgument('sifre', InputArgument::REQUIRED, 'Instagram şifresi (kimseyle paylaşılmaz)'),
            ))
            ->setDescription('Sizi takip etmeyen, takipçilerinizi siler')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $username = $input->getArgument('isim');
        $password = $input->getArgument('sifre');

        $config = Config::create($username, $password);

        // Set up the bot
        $bot = new Bot($this->getIO(), $config);
        $bot->setRandomSite();

        // First, login to the site
        $result = $bot->login();
        $this->checkForLoginErrors($result);

        // Get the user id
        $userId = $bot->getUserIdByName($username);
        if (!$userId) {
            $output->writeln("Kullanıcı bulunamadı!");

            return FALSE;
        }

        $following = array();
        $followers = array();

        $followerUrl = sprintf(
            "https://api.instagram.com/v1/users/%d/follows?client_id=%s",
            $userId,
            $this->clientIds[array_rand($this->clientIds)]
        );

        do {
            $response = $bot->getClient()->get($followerUrl)->json();
            $following = array_merge($following, $response['data']);
            $followerUrl = $response['pagination']['next_url'];
        } while($response['pagination']['next_url']);

        $followingUrl = sprintf(
            "https://api.instagram.com/v1/users/%d/followed-by?client_id=%s",
            $userId,
             $this->clientIds[array_rand($this->clientIds)]
        );

        do {
            $response = $bot->getClient()->get($followingUrl)->json();
            $followers = array_merge($followers, $response['data']);
            $followingUrl = $response['pagination']['next_url'];
        } while($response['pagination']['next_url']);

        $unfollow = array_values(array_diff(array_column($following, 'id'), array_column($followers, 'id')));

        foreach ($unfollow as $value) {
            $jsonResponse = $bot->unfollow($value);

            if ($jsonResponse->status == "OK") {
                // Write info about like
                $output->isVerbose() &&
                    $output->writeln("<info>INFO: $value sizi takip etmiyordu!</info>");
            } else {
                // TODO: Log the request for investigating the error
                $output->isVerbose() &&
                    $output->writeln("<error>HATA: {$jsonResponse->message} </error>\n");
            }
            sleep(rand(10, 20));
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
