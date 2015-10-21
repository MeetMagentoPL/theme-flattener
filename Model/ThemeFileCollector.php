<?php


namespace MeetMagentoPL\ThemeFlattener\Model;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use MeetMagentoPL\ThemeFlattener\Exception\UnableToLocateThemeDirectoryException;

class ThemeFileCollector implements ThemeFileCollectorInterface
{
    /**
     * @var ComponentRegistrarInterface
     */
    private $themeRegistrar;

    public function __construct(ComponentRegistrarInterface $themeRegistrar)
    {
        $this->themeRegistrar = $themeRegistrar;
    }

    /**
     * @param string $area
     * @param string $theme
     * @return string
     */
    public function getThemeDirectoryPath($area, $theme)
    {
        $componentName = $area . '/' . str_replace('_', '/', $theme);
        return $this->themeRegistrar->getPath(ComponentRegistrar::THEME, $componentName);
    }

    /**
     * @param string $area
     * @param string $theme
     * @return string[]
     */
    public function getCssSourceFiles($area, $theme)
    {
        $themeDirPath = $this->getThemeDirectoryPath($area, $theme);
        if (!$themeDirPath) {
            $message = sprintf('Unable to locate the theme directory for area "%s" and theme "%s"', $area, $theme);
            throw new UnableToLocateThemeDirectoryException($message);
        }

        $collectAllCssSourceFilesFromTheme = $this->collectAllCssSourceFilesFromTheme($themeDirPath);
        return $collectAllCssSourceFilesFromTheme;
    }

    /**
     * @param string $themePath
     * @return string[]
     */
    private function getCssSourceFileDirectories($themePath)
    {
        $sourceDirectoryList = array_merge([$themePath . '/web/css/source'], glob($themePath . '/*/web/css/source'));
        return array_filter($sourceDirectoryList, 'is_dir');
    }

    /**
     * @param string $themeDirPath
     * @return string[]
     */
    private function collectAllCssSourceFilesFromTheme($themeDirPath)
    {
        $cssSourceFileDirectories = $this->getCssSourceFileDirectories($themeDirPath);

        return array_reduce($cssSourceFileDirectories, function ($carry, $sourceDirectory) {
            $files = array_map([$this, 'cutOffEverythingUpTheCurrentWorkingDirectory'], $this->collectCssSourceFilesFromDir(realpath($sourceDirectory)));
            return array_merge($carry, $files);
        }, []);
    }

    private function cutOffEverythingUpTheCurrentWorkingDirectory($filePath)
    {
        $cwd = getcwd();
        $result = 0 === strpos($filePath, $cwd) ?
            substr($filePath, strlen($cwd) + 1):
            $filePath;
        return $result;
    }

    /**
     * @param string $sourceDirectory
     * @return string[]
     */
    private function collectCssSourceFilesFromDir($sourceDirectory)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDirectory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        return array_map('strval', array_filter(array_values(iterator_to_array($iterator)), function (\SplFileInfo $item) {
            return $item->isFile();
        }));
    }
}
