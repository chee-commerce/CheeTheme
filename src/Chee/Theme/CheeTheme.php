<?php namespace Chee\Theme;

use Illuminate\Foundation\Application;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Chee\Pclzip\Pclzip;
use Chee\Module\CheeModule;

/**
 * CheeModule for manage module
 * @author Chee
 */
 class CheeTheme extends CheeModule
 {
    /**
     * IoC
     * @var Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Config
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Files
     * @var Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Path of themes
     * @var string
     */
    protected $path;


    /**
     * Initialize class
     */
    public function __construct(Application $app, Repository $config, Filesystem $files)
    {
        parent::__construct($app, $config, $files);

        $this->configFile = '/theme.json';
    }

    /**
     * Active theme and build assets
     * @param $name string
     * @return bool
     */
    public function active($name)
    {
        if ($this->moduleExists($name) && $this->checkRequires($this->getModuleDirectory($name)))
        {
            $theme = new ThemeModel;
            $theme->name = $this->def($name, 'name');
            $theme->save();
            return true;
        }
        return false;
    }

    /**
     * Get all list themes
     * @return array
     */
    public function getListAllThemes()
    {
        $directories = $this->files->directories($this->path);
        $themes = array();
        foreach($directories as $directory)
        {
            if ($this->checkRequires($directory))
            {
                array_push($themes, basename($directory));
            }
        }
        return $this->getListThemes($themes);
    }

    /**
     * Get list of active themes
     * @return array
     */
    public function getListActiveThemes()
    {
        $themes = ThemeModel::all();
        return $this->getListThemes($themes, true);
    }

    /**
     * Get list themes
     * @param $themesModel array
     * @return array
     */
    protected function getListThemes($themesList, $isModel = false)
    {
        $themes = array();
        foreach ($themesList as $themeName)
        {
            if($isModel) $themeName = $themeName->name;
            $themes[$themeName]['name'] = $this->def($themeName, 'name');
            $themes[$themeName]['icon'] = null === $this->def($themeName, 'icon') ? null : $this->getConfig('assets').'/'.$themeName.'/'.$this->def($themeName, 'icon');
            $themes[$themeName]['description'] = $this->def($themeName, 'description');
            $themes[$themeName]['author'] = $this->def($themeName, 'author');
            $themes[$themeName]['website'] = $this->def($themeName, 'website');
            $themes[$themeName]['version'] = $this->def($themeName, 'version');
        }
        dd($themes);
        return $themes;
    }

    /**
     * Initialize zip theme for install
     * @param $archivePath string path
     * @param $themeName string
     * @return bool
     */
    protected function moduleInit($archivePath, $themeName)
    {
        //Move extracted theme to themes pathd
        if (!$this->files->copyDirectory($archivePath, $this->path.'/'.$themeName))
        {
            $this->errors['themeInit']['move'] = 'Can not move files.';
            return false;
        }

        $this->buildAssets($themeName);

        return true;
    }

    /**
     * Update theme
    * @param $archive string path of zip
    * @param $moduleName string
    * @return bool
    */
    protected function update($archivePath, $themeName)
    {
        parent::update($archivePath, $themeName);
        $this->buildAssets($themeName);
    }

    /**
     * Delete theme and remove assets and module files
     * @param $name string
     * @return boolean
     */
    public function delete($name)
    {
        if ($this->moduleExists($name))
        {
            $this->removeAssets($name);

            $themePath = $this->getModuleDirectory($name);

            $this->files->deleteDirectory($themePath);
            if ($this->files->exists($themePath))
            {
                $this->errors['delete']['forbidden']['theme'] = $themePath;
            }

            $theme = $this->findOrFalse('name', $name);
            if ($theme)
            {
                $theme->delete();
            }
        }
    }

    /**
     * Remove assets of a theme
     * @param $themeName string
     * @return bool
     */
    public function removeAssets($themeName)
    {
        $assets = $this->getAssetDirectory($themeName);

        $this->files->deleteDirectory($assets);
        if ($this->files->exists($assets))
        {
            $this->errors['delete']['forbidden']['assets'] = $assets;
        }
    }

    /**
     * Find one record from model
     * @param $field string
     * @param $name string
     * @return object|false
     */
    public function findOrFalse($field, $name) {
        $theme = ThemeModel::where($field, $name)->first();
        return !is_null($theme) ? $theme : false;
    }

    /**
     * Update record module in database
     * @param $moduleName string
     * @return void
     */
    protected function updateRecordModule($moduleName)
    {
        return true;
    }

    /**
     * Check if module exists
     * @param $name string
     * @return bool
     */
    public function moduleExists($name)
    {
        $themePath = $this->files->exists($this->path.'/'.$name);
        if (!$themePath) return false;
        return true;
    }

    /**
     * Get configuration module
     * @param $item string
     * @param $default null|mixed
     * @return mixed
     */
    protected function getConfig($item, $default = null)
    {
        return $this->config->get('theme::'.$item, $default);
    }
 }
