<?php namespace Chee\Theme\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Foundation\Application;
use Illuminate\Console\Command;

class BuildThemeCommand extends \Chee\Module\Commands\AbstractCommand
{
    /**
     * Name of the command
     * @var string
     */
    protected $name = 'CheeTheme:buildTheme';

    /**
     * Command description
     * @var string
     */
    protected $description = 'Regenerate positions, positions value and image sizes';

    public function fire()
    {
        $name = studly_case(substr($this->argument('name'), strpos($this->argument('name'), '=') + 1));

        if (empty($name))
        {
            $this->error('Please write module name');
            exit;
        }

        $build = $this->app['chee-theme']->updateRegisteredModule($name);
        if ($build)
        {
            $this->info('Regenerated positions, position values and image sizes');
        }
        else
        {
            $this->error('Error on build theme');
        }
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('name', InputArgument::REQUIRED, 'the name of module.')
        );
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }
}
