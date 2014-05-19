<?php

namespace InstagramTakipci\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;
use InstagramTakipci\Util\ErrorHandler;
use InstagramTakipci\IO\IOInterface;
use InstagramTakipci\IO\ConsoleIO;

class Application extends BaseApplication
{
    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * Constructor.
     */
    public function __construct()
    {
        error_reporting(-1);

        if (function_exists('ini_set') && extension_loaded('xdebug')) {
            ini_set('xdebug.show_exception_trace', false);
            ini_set('xdebug.scream', false);
        }

        if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
            date_default_timezone_set(@date_default_timezone_get());
        }

        $dims = $this->getTerminalDimensions();
        $this->setTerminalDimensions($dims[0], $dims[1]);

        ErrorHandler::register();

        parent::__construct('InstagramTakipci', '@package_version@');
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->io = new ConsoleIO($input, $output, $this->getHelperSet());

        if (version_compare(PHP_VERSION, '5.4', '<')) {
            $output->writeln('<warning>InstagramTakipci sadece PHP 5.4 ve üzeri versiyonlarında çalışmaktadır. Sizin kullandığınız PHP '.PHP_VERSION.' versiyonu ile sorun yaşayabilirsiniz. Güncellme yapmanız tavsiye edilir .</warning>');
        }

        if ($input->hasParameterOption('--profile')) {
            $startTime = microtime(true);
            $this->io->enableDebugging($startTime);
        }

        $result = parent::doRun($input, $output);

        if (isset($startTime)) {
            $output->writeln('<info>Bellek kullanımı: '.round(memory_get_usage() / 1024 / 1024, 2).'MB (peak: '.round(memory_get_peak_usage() / 1024 / 1024, 2).'MB), time: '.round(microtime(true) - $startTime, 2).'s');
        }

        return $result;
    }

    /**
     * @return IOInterface
     */
    public function getIO()
    {
        return $this->io;
    }

    /**
     * Initializes all the composer commands
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Command\MainCommand();
        $commands[] = new Command\UnfollowCommand();

        if ('phar:' === substr(__FILE__, 0, 5)) {
            $commands[] = new Command\UpdateCommand();
        }

        return $commands;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('--profile', null, InputOption::VALUE_NONE, 'Bellek kullanımı ve çalışma süresini göster.'));

        return $definition;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultHelperSet()
    {
        $helperSet = parent::getDefaultHelperSet();

        $helperSet->set(new DialogHelper());

        return $helperSet;
    }
}
