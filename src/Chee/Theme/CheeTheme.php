<?php namespace Chee\Theme;

use Illuminate\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Chee\Theme\Models\ThemePosition;
use Illuminate\Config\Repository;
use Chee\Theme\Models\ThemeModel;
use Chee\Theme\Models\ImageSize;
use Chee\Module\CheeModule;
use Chee\Pclzip\Pclzip;

/**
 * CheeModule for manage module
 *
 * @author Chee
 */

 class CheeTheme extends CheeModule
 {
    /**
     * IoC
     *
     * @var Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Config
     *
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Files
     *
     * @var Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Path of themes
     *
     * @var string
     */
    protected $path;

    /**
     * Array of Themes
     *
     * @var Illuminate\Config\Repository
     */
    protected $themes = array();


    /**
     * Initialize class
     *
     * @param $app Illuminate\Foundation\Application
     * @param $config Illuminate\Config\Repository
     * @param $files Illuminate\Filesystem\Filesystem
     */
    public function __construct(Application $app, Repository $config, Filesystem $files)
    {
        parent::__construct($app, $config, $files);

        $this->configFile = '/theme.json';
    }

    /**
     * Get all module from database and initialize
     *
     * @return void
     */
    public function start()
    {
        $themes = self::getListAllThemes();
        foreach($themes as $themeName => $attr)
        {
            if ($attr['active'] == 1)
            {
                $path = $this->getModuleDirectory($themeName);
                if ($path)
                {
                    $this->themes[$themeName] = new Theme($this->app, $themeName, $path);
                }
            }
        }
    }

    /**
     * Register modules with Moduel class
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->themes as $theme)
        {
            $theme->register();
        }
        $themes = ThemeModel::where('active_theme_is_enabled', 1)->get();
        foreach ($themes as $theme)
        {
            if ($theme->active_theme_is_enabled)
            {
                $this->app['events']->fire('themes.enable.'.$theme->active_theme_name, null);
                $theme->active_theme_is_enabled = 0;
            }
            $theme->save();
        }
    }

    /**
     * Active theme and build assets
     *
     * @param $name string
     * @return bool
     */
    public function active($name)
    {
        $cheeCommerceRequire = $this->def($name, $this->systemName);
        if (!$this->CheeCommerceCompliancy($cheeCommerceRequire))
        {
            return false;
        }

        if ($this->moduleExists($name) && $this->checkRequires($this->getModuleDirectory($name)))
        {
            if (!$this->findOrFalse('active_theme_name', $name))
            {
                $theme = new ThemeModel;
                $theme->active_theme_name = $this->def($name, 'name');
                $theme->active_theme_is_enabled = 1;
                $theme->save();
                return true;
            }
        }
        return false;
    }

    /**
     * Force active theme and build assets
     *
     * @param $name string
     * @return bool
     */
    public function forceActive($name)
    {
        if ($this->moduleExists($name) && $this->checkRequires($this->getModuleDirectory($name)))
        {
            if (!$this->findOrFalse('active_theme_name', $name))
            {
                $theme = new ThemeModel;
                $theme->active_theme_name = $this->def($name, 'name');
                $theme->active_theme_is_enabled = 1;
                $theme->save();
                return true;
            }
        }
        return false;
    }

    /**
     * Dective theme and build assets
     *
     * @param $name string
     * @param $order int order of theme
     * @return bool
     */
    public function deactive($name)
    {
        if ($theme = $this->findOrFalse('active_theme_name', $name))
        {
            ThemeModel::find($theme->active_theme_id)->delete();
            $this->app['events']->fire('themes.disable.'.$name, null);
        }
        return false;
    }

    /**
     * Get all list themes
     *
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
                    if (!$this->findOrFalse('active_theme_name', basename($directory)))
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
     *
     * @return array
     */
    public function getListActiveThemes()
    {
        $themes = ThemeModel::orderBy('active_theme_order', 'asc')->get();
        return $this->getListThemes($themes, true);
    }

    /**
     * Get list of active themes
     *
     * @return array
     */
    public function getListInactiveThemes()
    {
        return $this->getListAllThemes('inactive');
    }

    /**
     * Get details of a theme
     *
     * @param $name string
     * @return array|false
     */
    public function getTheme($name)
    {
        $theme = array();
        $theme['name'] = $this->def($name, 'name');
        $theme['icon'] = null === $this->def($name, 'icon') ? null : $this->getConfig('assets').'/'.$name.'/'.$this->def($name, 'icon');
        $theme['description'] = $this->def($name, 'description');
        $theme['author'] = $this->def($name, 'author');
        $theme['website'] = $this->def($name, 'website');
        $theme['version'] = $this->def($name, 'version');
        return $theme;
    }

    /**
     * Get list themes
     *
     * @param $themesModel array
     * @return array
     */
    protected function getListThemes($themesList, $isModel = false)
    {
        $themes = array();
        foreach ($themesList as $themeName)
        {
            if($isModel)
            {
                $themes[$themeName->active_theme_name]['id'] = $themeName->active_theme_id;
                $themeName = $themeName->active_theme_name;
            }
            else
            {
                $themeModel = $this->findOrFalse('active_theme_name', $themeName);
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
     *
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
        $this->setPositions($themeName);

        $this->removeImageSizes($themeName);
        $this->setImageSizes($themeName);

        return true;
    }

    /**
     * Update module
     *
     * @param $archive string path of zip
     * @param $moduleName string
     * @return bool
     */
    protected function update($archivePath, $themeName)
    {
        if (parent::update($archivePath, $themeName))
        {
            $this->removePositions($themeName);
            $this->setPositions($themeName);

            $this->removeImageSizes($themeName);
            $this->setImageSizes($themeName);
        }
    }

    /**
     * Set positions of theme
     *
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
                $pos['name'] = $this->app['str']->slug($pos['name']);
                if ($pos['name'])
                {
                    $position = new ThemePosition;
                    $position->theme_position_name = $pos['name'];
                    if (isset($pos['description']))
                        $position->theme_position_description = $pos['description'];
                    $position->active_themes_name = $themeName;
                    $position->save();
                }
            }
        }
    }

    /**
     * Set image sizes of theme
     *
     * @param $themeName
     * @return void
     */
    public function setImageSizes($themeName)
    {
        $imagesSizes = $this->def($themeName, 'imageSizes');
        $imagesSizesBag = array();

        $imageTypes = array("products" => 0, "categories" => 0, "manufacturers" => 0, "suppliers" => 0);
        foreach ($imagesSizes as $size)
        {
            $types = $imageTypes;
            foreach ($size['usage'] as $type)
            {
                $types[$type] = 1;
            }

            $imagesSizesBag[] = array(
                "image_size_name" => $size['name'],
                "image_size_width" => (int) $size['width'],
                "image_size_height" => (int) $size['height'],
                "image_size_quality" => (int) $size['quality'],
                "image_size_usage" => (string) json_encode($types)
            );
        }
        ImageSize::insert($imagesSizesBag);
    }

    /**
     * Remove image sizes where name equal to current image size theme
     *
     * @param $themeName
     * @return void
     */
    public function removeImageSizes($themeName)
    {
        $imagesSizes = $this->def($themeName, 'imageSizes');
        foreach ($imagesSizes as $size)
            ImageSize::where('image_size_name', $size['name'])->delete();
    }

    /**
     * Remove positions of theme
     *
     * @param $themeName
     * @return void
     */
    public function removePositions($themeName)
    {
        $positions = ThemePosition::where('active_themes_name', $themeName)->delete();
    }

    /**
     * Delete theme and remove assets and module files
     *
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

            $theme = $this->findOrFalse('active_theme_name', $name);
            if ($theme)
            {
                $theme->delete();
            }
            $this->removePositions($name);
        }
    }

    /**
     * Find one record from model
     *
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
     *
     * @param $moduleName string
     * @return void
     */
    protected function updateRecordModule($moduleName)
    {
        return true;
    }

    /**
     * Check if module exists
     *
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
     *
     * @param $item string
     * @param $default null|mixed
     * @return mixed
     */
    protected function getConfig($item, $default = null)
    {
        return $this->config->get('theme::'.$item, $default);
    }
 }
