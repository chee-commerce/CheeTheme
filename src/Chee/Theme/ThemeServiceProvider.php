<?php namespace Chee\Theme;

use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{

	/**
	* Indicates if loading of the provider is deferred.
	*
	* @var bool
	*/
	protected $defer = false;

	public function boot()
	{
		$this->package('chee/theme');

		$this->app['chee-theme']->requireFunctions();
	}

	/**
	* Register the service provider.
	*
	* @return void
	*/
	public function register()
	{
		$this->app['chee-theme'] = $this->app->share(function($app)
		{
			return new CheeTheme($app, $app['config'], $app['files']);
		});
	}

	/**
	* Get the services provided by the provider.
	*
	* @return array
	*/
	public function provides()
	{
		return array('chee-theme');
	}

	public function bootCommands()
	{
		$this->app['CheeModule.create'] = $this->app->share(function($app)
		{
			return new Commands\CreateCommand($app);
		});

		$this->app['CheeModule.buildAssets'] = $this->app->share(function($app)
		{
			return new Commands\BuildAssetsCommand($app);
		});

		$this->commands(array(
			'CheeModule.create',
			'CheeModule.buildAssets'
		));
	}

}
