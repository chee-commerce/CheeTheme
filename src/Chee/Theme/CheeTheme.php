<?php namespace Chee\Theme;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Chee\Theme\Models\ThemePosition;
use Chee\Theme\Models\PositionView;
use Illuminate\Config\Repository;
use Chee\Theme\Models\ThemeModel;
use Chee\Theme\Models\ModuleView;
use Chee\Theme\Models\ImageSize;
use Chee\Theme\Models\Module;
use Chee\Module\CheeModule;

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
     * @param Illuminate\Foundation\Application $app
     * @param Illuminate\Config\Repository $config
     * @param Illuminate\Filesystem\Filesystem $files
     * @return void
     */
    public function __construct(Application $app, Repository $config, Filesystem $files)
    {
        parent::__construct($app, $config, $files);
    }

    /**
     * Get all module from database and initialize
     *
     * @return void
     */
    public function start()
    {
        $theme = $this->getActiveTheme();
        if ($theme)
        {
            $path = $this->getModuleDirectory($theme->theme_name);
            if ($path)
                $this->themes[$theme->theme_name] = new Theme($this->app, $theme->theme_name, $path);
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
    }

    /**
     * Active theme and build assets
     *
     * @param string $themeName
     * @return bool
     */
    public function active($themeName)
    {
        if ($this->moduleExists($themeName))
        {
            $this->app['db']->table('themes')->update(array('theme_active' => 0));
            ThemeModel::where('theme_name', $themeName)->update(array('theme_active' => 1));
            return true;
        }
        return false;
    }

    /**
     * Dective theme and build assets
     *
     * @param string $themeName
     * @return bool
     */
    public function deactive($themeName)
    {
        if ($this->moduleExists($themeName))
        {
            ThemeModel::where('theme_name', $themeName)->update('theme_active', 0);
            return true;
        }
        return false;
    }

    /**
     * Get all list modules
     *
     * @return array
     */
    public function getListAllModules()
    {
        return $this->getListAllThemes();
    }

    /**
     * Get all list themes
     *
     * @param bool $withActiveTheme
     * @return array
     */
    public function getListAllThemes($withActiveTheme = true)
    {
        if ($withActiveTheme)
            $themes = ThemeModel::all();
        else
        {
            $activeTheme = $this->getActiveTheme();
            if (isset($activeTheme->theme_name))
                $themes = ThemeModel::where('theme_name', '<>', $activeTheme->theme_name)->get();
            else
                $themes = ThemeModel::all();
        }

        return $this->makeListThemes($themes);
    }

    /**
     * Get details of a theme
     *
     * @param string $themeName
     * @return array|false
     */
    public function getTheme($themeName)
    {
        $themes = ThemeModel::where('theme_name', $themeName)->get();
        $themes = $this->makeListThemes($themes);
        if (isset($themes[$themeName]))
            return $themes[$themeName];
        return false;
    }

    /**
     * Get list themes
     *
     * @param Illuminate\Database\Eloquent\Collection $themesBag
     * @return array
     */
    protected function makeListThemes(Collection $themesBag)
    {
        $themes = array();
        foreach ($themesBag as $theme)
        {
            $themeName = $theme->theme_name;

            $themes[$themeName]['id'] = $theme->theme_id;
            $themes[$themeName]['name'] = $themeName;
            $themes[$themeName]['icon'] = null === $this->def($themeName, 'icon') ? null : $this->getConfig('assets').'/'.$themeName.'/'.$this->def($themeName, 'icon');
            $themes[$themeName]['description'] = $this->def($themeName, 'description');
            $themes[$themeName]['author'] = $this->def($themeName, 'author');
            $themes[$themeName]['website'] = $this->def($themeName, 'website');
            $themes[$themeName]['version'] = $this->def($themeName, 'version');

        }
        return $themes;
    }

    /**
    * Get details of active theme
    *
    * @param bool $list get detail of active theme?
    * @return Illuminate\Database\Eloquent\Collection|false
    */
    public function getActiveTheme($list = false)
    {
        $theme = ThemeModel::where('theme_active', 1)->first();
        if ($theme)
            if ($list)
                return $this->getTheme($theme->theme_name);
            else
                return $theme;

        return false;
    }

    /**
    * Get theme is active or not
    *
    * @param string $themeName
    * @return bool
    */
    public function isActiveTheme($themeName)
    {
        $theme = $this->findOrFalse('theme_active', 1);
        if ($theme)
            return $themeName == $theme->theme_name;

        return false;
    }

    /**
     * Register module
     *
     * @param string $themeName
     * @return bool
     */
    protected function registerModule($themeName)
    {
        $theme = new ThemeModel;
        $theme->theme_name = $themeName;
        $theme->save();

        $this->setPositions($themeName, $theme->theme_id);

        $this->setPositionsValue($themeName, $theme->theme_id);

        $this->setImageSizes($themeName);

        return true;
    }

    /**
     * Update module
     *
     * @param string $themeName
     * @return bool
     */
    public function updateRegisteredModule($themeName)
    {
        $theme = $this->findOrFalse('theme_name', $themeName);
        if ($theme)
        {
            $this->setPositions($themeName, $theme->theme_id);
            $this->setPositionsValue($themeName, $theme->theme_id);
            $this->setImageSizes($themeName);
            return true;
        }
        else
        {
            return false;
        }

    }

    /**
     * Set positions of theme
     *
     * @param string $themeName
     * @param int $themeId id of theme in themes table
     * @return void
     */
    public function setPositions($themeName, $themeId)
    {
        $positions = $this->def($themeName, 'positions', false, array());
        if (!is_array($positions)) return false;

        $positionsName = array_column($positions, 'name');

        if (count($positionsName) == 0)
            return;

        //Delete unused positions in theme update
        ThemePosition::where('themes_theme_id', $themeId)->whereNotIn('theme_position_name', $positionsName)->delete();

        //Not register positions who before registered in theme update
        $registeredPositions = ThemePosition::where('themes_theme_id', $themeId)
                                                ->whereIn('theme_position_name', $positionsName)
                                                ->get(array('theme_position_name'))
                                                ->keyBy('theme_position_name');

        foreach ($positions as $pos)
        {
            if (isset($pos['name']) && !isset($registeredPositions[$pos['name']]))
            {
                $position = new ThemePosition;
                $position->theme_position_name = $pos['name'];
                $position->theme_position_description = @$pos['description'];
                $position->themes_theme_id = $themeId;
                $position->save();
            }
        }
    }

    /**
     * Set value of positions
     *
     * @param string $themeName
     * @param int $themeId
     * @return void
     */
    protected function setPositionsValue($themeName, $themeId)
    {
        $pvBag = array();

        $pValues = $this->def($themeName, 'positionsValue', false, array());
        if (!is_array($pValues) || count($pValues) == 0) return false;

        $positionsName = array_keys($pValues);
        $positionsExists = ThemePosition::where('themes_theme_id', $themeId)->whereIn('theme_position_name', $positionsName)->get()->keyBy('theme_position_name')->toArray();
        foreach ($pValues as $position => $values)
        {
            if (!is_array($values))
                continue;

            //Do not insert values of not registered position
            if (!isset($positionsExists[$position]))
                continue;

            $positionId = $positionsExists[$position]['theme_position_id'];
            foreach ($values as $value)
            {
                if (!is_array($value) || !isset($value['moduleName']) || !isset($value['viewName']))
                    continue;

                $module = Module::where('module_name', $value['moduleName'])->first();
                if (is_null($module))
                    continue;

                $moduleView = ModuleView::where('modules_module_id', $module->module_id)->where('module_view_name', $value['viewName'])->first(array('module_view_id'));
                if (is_null($moduleView))
                    continue;

                //Check position value not registered before
                $registeredPV = PositionView::where('module_views_module_view_id', $moduleView->module_view_id)->where('theme_positions_theme_position_id', $positionId)->count();

                if ($registeredPV == 0)
                {
                    $pv = array();
                    $pv['module_views_module_view_id'] = $moduleView->module_view_id;
                    $pv['theme_positions_theme_position_id'] = $positionId;
                    $pv['position_view_status'] = isset($value['status']) ? 1 : 0;
                    $pv['position_view_order'] = (int) @$value['order'];
                    array_push($pvBag, $pv);
                }
            }
        }
        if (count($pvBag) > 0)
            PositionView::insert($pvBag);
    }

    /**
     * Set image sizes of theme
     *
     * @param string $themeName
     * @return mixed
     */
    public function setImageSizes($themeName)
    {
        $imagesSizes = $this->def($themeName, 'imageSizes', false, array());
        if (!is_array($imagesSizes)) return false;

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
                'image_size_name' => $size['name'],
                'image_size_width' => (int) $size['width'],
                'image_size_height' => (int) $size['height'],
                'image_size_quality' => (int) $size['quality'],
                'image_size_usage' => (string) json_encode($types),
                'deleted_at' => ''
            );
        }

        foreach ($imagesSizesBag as $size)
        {
            $imageSize = ImageSize::withTrashed()->where('image_size_name', $size['image_size_name'])->first();
            if ($imageSize)
            {
                $imageSize->image_size_width = $size['image_size_width'];
                $imageSize->image_size_height = $size['image_size_height'];
                $imageSize->image_size_quality = $size['image_size_quality'];
                $imageSize->image_size_usage = $size['image_size_usage'];
                $imageSize->deleted_at = null;
                $imageSize->save();
            }
            else
                ImageSize::insert($size);
        }
    }

    /**
     * Remove image sizes where name equal to current image size theme
     *
     * @param string $themeName
     * @return void
     */
    public function removeImageSizes($themeName)
    {
        $imagesSizes = $this->def($themeName, 'imageSizes');
        if (is_array($imagesSizes))
            foreach ($imagesSizes as $size)
                ImageSize::where('image_size_name', $size['name'])->delete();
    }

    /**
     * Remove positions of theme
     *
     * @param int $themeId id of theme in themes table
     * @return void
     */
    public function removePositions($themeId)
    {
        $positions = ThemePosition::where('themes_theme_id', $themeId)->delete();
    }

    /**
     * Delete theme and remove assets and module files
     *
     * @param string $themeName
     * @return bool
     */
    public function delete($themeName)
    {
        $theme = $this->findOrFalse('theme_name', $themeName);
        if ($theme)
        {
            if ($theme)
            {
                $this->removeImageSizes($themeName);
                $this->removePositions($theme->theme_id);
                $theme->delete();
            }

            $this->removeAssets($themeName);

            $themePath = $this->getModuleDirectory($themeName);
            $this->files->deleteDirectory($themePath);
            if ($this->files->exists($themePath))
            {
                $this->errors->add('delete_files', 'Unable to delete files in: '.$themePath);
            }

            return true;
        }
        return false;
    }

    /**
     * Find one record from model
     *
     * @param string $field
     * @param string $name
     * @return object|false
     */
    public function findOrFalse($field, $name) {
        $theme = ThemeModel::where($field, $name)->first();
        return !is_null($theme) ? $theme : false;
    }

    /**
     * Check if theme exists
     *
     * @param string $themeName
     * @param bool $returnId
     * @return bool
     */
    public function moduleExists($themeName, $returnId = false)
    {
        $theme = $this->findOrFalse('theme_name', $themeName);
        $themePath = $this->files->exists($this->path.'/'.$themeName);
        if (!$themePath || !$theme) return false;

        if ($returnId)
            return $theme->theme_id;
        else
            return true;
    }

    /**
    * Check if module exists
    *
    * @param string $moduleName
    * @return bool
    */
    public function parentModuleExists($moduleName)
    {
        return parent::moduleExists($moduleName);
    }

    /**
     * Get assets path of specific module
     *
     * @param string|null $themeName name of theme
     * @return string
     */
    public function getAssetDirectory($themeName = null)
    {
        if ($themeName)
            return public_path().'/'.$this->getConfig('assets').'/'.$themeName;

        return public_path().'/'.$this->getConfig('assets').'/';
    }

    /**
     * Get configuration module
     *
     * @param string $item
     * @param null|mixed $default
     * @return mixed
     */
    protected function getConfig($item, $default = null)
    {
        return $this->config->get('theme::'.$item, $default);
    }
 }
