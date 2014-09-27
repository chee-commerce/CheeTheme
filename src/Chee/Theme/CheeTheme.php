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
     * @param $app Illuminate\Foundation\Application
     * @param $config Illuminate\Config\Repository
     * @param $files Illuminate\Filesystem\Filesystem
     */
    public function __construct(Application $app, Repository $config, Filesystem $files)
    {
        parent::__construct($app, $config, $files);

        $this->configFile = '/theme.json';
    }

    public function includes()
    {
        $themes = ThemeModel::all();
        foreach ($themes as $theme)
        {
            $globalInclude = $this->getConfig('include');
            foreach ($globalInclude as $file)
            {
                $path = $this->getModuleDirectory($theme->name).'/'.$file;
                if ($this->files->exists($path)) require $path;
            }
        }
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
            if (!$this->findOrFalse('name', $name))
            {
                $theme = new ThemeModel;
                $theme->name = $this->def($name, 'name');
                $theme->save();
                $this->setPositions($name);
                return true;
            }
        }
        return false;
    }

    /**
     * Dective theme and build assets
     * @param $name string
     * @param $order int order of theme
     * @return bool
     */
    public function deactive($name)
    {
        if ($theme = $this->findOrFalse('name', $name))
        {
            ThemeModel::find($theme -> id)->delete();
            $this->removePositions($name);
        }
        return false;
    }

    /**
     * Get all list themes
     * @param $actives model|null
     * @return array
     */
    public function getListAllThemes($inactives = false)
    {
        if (!$this->files->exists($this->path))
        {
            $this->files->makeDirectory($this->path, 0755, true);
        }

        $directories = $this->files->directories($this->path);
        $themes = array();
        foreach($directories as $directory)
        {
            if ($this->checkRequires($directory))
            {
                if ($inactives)
                {
                    if (!$this->findOrFalse('name', basename($directory)))
                    {
                        array_push($themes, basename($directory));
                    }
                }
                else array_push($themes, basename($directory));
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
     * Get list of active themes
     * @return array
     */
    public function getListInactiveThemes()
    {
        return $this->getListAllThemes('inactive');
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
            else
            {
                $themeModel = $this->findOrFalse('name', $themeName);
                if($themeModel) $themes[$themeName]['active'] = 1;
                else $themes[$themeName]['active'] = 0;
            }

            $themes[$themeName]['name'] = $this->def($themeName, 'name');
            $themes[$themeName]['icon'] = null === $this->def($themeName, 'icon') ? null : $this->getConfig('assets').'/'.$themeName.'/'.$this->def($themeName, 'icon');
            $themes[$themeName]['description'] = $this->def($themeName, 'description');
            $themes[$themeName]['author'] = $this->def($themeName, 'author');
            $themes[$themeName]['website'] = $this->def($themeName, 'website');
            $themes[$themeName]['version'] = $this->def($themeName, 'version');

        }
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
        //Move extracted theme to themes path
        if (!$this->files->copyDirectory($archivePath, $this->path.'/'.$themeName))
        {
            $this->errors['themeInit']['move'] = 'Can not move files.';
            return false;
        }

        $this->buildAssets($themeName);

        return true;
    }

    /**
     * Set positions of theme
     * @param $themeName
     * @return void
     */
    public function setPositions($themeName)
    {
        $positions = $this->def($themeName, 'positions');
        foreach ($positions as $pos)
        {
            if (isset($pos['name']))
            {
                $pos['name'] = $this->slugify($pos['name']);
                if ($pos['name'])
                {
                    $position = new ThemePosition;
                    $position->name = $pos['name'];
                    if (isset($pos['description']))
                        $position->description = $pos['description'];
                    $position->theme_name = $themeName;
                    $position->save();
                }
            }
        }
    }

    /**
     * Remove positions of theme
     * @param $themeName
     * @return void
     */
    public function removePositions($themeName)
    {
        $positions = ThemePosition::where('theme_name', $themeName)->delete();
    }

    public function slugify($text)
    {
      $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
      $text = trim($text, '-');
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
      $text = strtolower($text);
      $text = preg_replace('~[^-\w]+~', '', $text);
      if (empty($text))
      {
        return false;
      }
      return $text;
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
