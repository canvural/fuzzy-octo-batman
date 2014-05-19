<?php

namespace InstagramTakipci\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use InstagramTakipci\Console\Application;
use InstagramTakipci\IO\IOInterface;
use InstagramTakipci\IO\NullIO;

class Command extends BaseCommand
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @return IOInterface
     */
    public function getIO()
    {
        if (null === $this->io) {
            $application = $this->getApplication();
            if ($application instanceof Application) {
                /* @var $application    Application */
                $this->io = $application->getIO();
            } else {
                $this->io = new NullIO();
            }
        }

        return $this->io;
    }

    /**
     * @param IOInterface $io
     */
    public function setIO(IOInterface $io)
    {
        $this->io = $io;
    }
}
