<?php namespace Chee\Theme\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Foundation\Application;

class ListCommand extends \Chee\Module\Commands\AbstractCommand
{
    /**
     * Name of the command
     * @var string
     */
    protected $name = 'CheeTheme';

    /**
     * Command description
     * @var string
     */
    protected $description = 'List of CheeTheme commands';

    /**
     * Echo a list of commands in CheeModule
     */
    public function fire()
    {
        $this->info('CheeTheme Commands:');
        $this->info('--------------------------------------------------------------------------------------------------------------------------------');
        $this->info('| CheeTheme:create         | Create a new theme for development.  | eg: php artisan CheeTheme:create name=themeName            |');
        $this->info('--------------------------------------------------------------------------------------------------------------------------------');
        $this->info('| CheeTheme:buildAssets    | Move assets directory to public.     | eg: php artisan CheeTheme:buildAssets name=moduleTheme     |');
        $this->info('--------------------------------------------------------------------------------------------------------------------------------');
        $this->info('| CheeTheme:buildTheme     | Regenerate positions, positionValues | eg: php artisan CheeTheme:buildTheme name=moduleTheme      |');
        $this->info('|                          | and image sizes                      |                                                            |');
        $this->info('--------------------------------------------------------------------------------------------------------------------------------');
        $this->info('');
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return array(

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
