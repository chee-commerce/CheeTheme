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

	/**
	* Bootstrap the application events.
	*
	* @return void
	*/
	public function boot()
	{
		$this->package('chee/theme');

		$this->app['chee-theme']->includes();
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

}
