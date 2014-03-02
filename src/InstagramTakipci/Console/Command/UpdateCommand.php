<?php

namespace InstagramTakipci\Console\Command;

use Herrera\Phar\Update\Manager;
use Symfony\Component\Console\Input\InputOption;
use Herrera\Json\Exception\FileException;
use Herrera\Phar\Update\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    const MANIFEST_FILE = 'http://kobayakawa.github.io/fuzzy-octo-batman/manifest.json';

    protected function configure()
    {
        $this
            ->setName('update')
            ->setDescription('Uygulamayı en son versiyona günceller')
            ->addOption('major', null, InputOption::VALUE_NONE, 'Büyük versiyon değişikliklerine izin ver')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Güncellemeler aranıyor...');

        try {
            $manager = new Manager(Manifest::loadFile(self::MANIFEST_FILE));
        } catch (FileException $e) {
            $output->writeln('<error>Güncellemeleri ararken bir soun oluştu. daha sonra tekrar deneyin</error>');

            return 1;
        }

        $currentVersion = $this->getApplication()->getVersion();
        $allowMajor = $input->getOption('major');

        if ($manager->update($currentVersion, $allowMajor)) {
            $output->writeln('<info>En son versiyona güncellendi</info>');
        } else {
            $output->writeln('<comment>Zaten güncel</comment>');
        }
    }
}