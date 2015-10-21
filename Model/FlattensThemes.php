<?php


namespace MeetMagentoPL\Falkowskifier\Model;


use MeetMagentoPL\Falkowskifier\Exception\UnableToCreateDirectoryException;
use MeetMagentoPL\Falkowskifier\Model\ThemeFileCollectorInterface;
use MeetMagentoPL\Falkowskifier\Util\RelativeFileSystemPathBuilder;

class FlattensThemes
{
    /**
     * @var ThemeFileCollectorInterface
     */
    private $themeFileCollector;

    /**
     * @var string
     */
    private $themeDir;

    public function __construct(ThemeFileCollectorInterface $themeFileCollector)
    {
        $this->themeFileCollector = $themeFileCollector;
    }

    /**
     * @param string $area
     * @param string $theme
     */
    public function flattenToDefaultDestination($area, $theme)
    {
        $defaultDestinationDir = 'xx/' . strtolower(str_replace('_', '-', $theme)) . '-flat';
        $this->flatten($area, $theme, $defaultDestinationDir);
    }

    /**
     * @param string $area
     * @param string $theme
     * @param string $destinationDir
     */
    public function flatten($area, $theme, $destinationDir)
    {
        $this->themeDir = $this->themeFileCollector->getThemeDirectoryPath($area, $theme);
        $this->createDestinationWorkDir($destinationDir);
        foreach ($this->getThemeCssSourceFiles($area, $theme) as $cssSourceFile) {
            $this->processModuleCssSourceFiles($destinationDir, $cssSourceFile);

            $inThemePart = $this->getInThemePartOfCssSourceFile($cssSourceFile);
            if (preg_match('#^web/css/source/(.+/|)([^/]+)$#', $inThemePart, $matches)) {
                list(, $subdirs, $file) = $matches;
                $linkDir = $destinationDir . '/css';
                $linkTarget = $this->createPathToLinkTarget($linkDir, $cssSourceFile);
                symlink($linkTarget, $linkDir . '/' . $this->createLinkNameForThemeCssSourceFile($subdirs, $file));
            }
            
        }
    }

    /**
     * @param string $destinationDir
     */
    private function createDestinationWorkDir($destinationDir)
    {
        $message = sprintf('Unable to create the destination directory "%s"', $destinationDir);
        $this->ensureDirectoryExists($destinationDir, $message);
    }

    /**
     * @param string $dir
     * @param string|null $failureMessage
     */
    private function ensureDirectoryExists($dir, $failureMessage = null)
    {
        if (!file_exists($dir)) {
            @mkdir($dir, 0700, true);
            if (!file_exists($dir)) {
                throw $this->createUnableToCreateDirectoryException($dir, $failureMessage);
            }
        }
    }

    /**
     * @param string $dir
     * @param string|null $failureMessage
     * @return UnableToCreateDirectoryException
     */
    private function createUnableToCreateDirectoryException($dir, $failureMessage)
    {
        $message = is_null($failureMessage) ?
            sprintf('Unable to create the directory "%s"', $dir) :
            $failureMessage;
        return new UnableToCreateDirectoryException($message);
    }

    /**
     * @param string $area
     * @param string $theme
     * @return string[]
     */
    private function getThemeCssSourceFiles($area, $theme)
    {
        return (array) $this->themeFileCollector->getCssSourceFiles($area, $theme);
    }

    /**
     * @param string $module
     * @param string $subdirs
     * @param string $file
     * @return string
     */
    private function createLinkNameForModuleCssSourceFile($module, $subdirs, $file)
    {
        return str_replace('__', '_', $module . '_' . $this->createLinkNameForThemeCssSourceFile($subdirs, $file));
    }

    /**
     * @param string $subdirs
     * @param string $file
     * @return string
     */
    private function createLinkNameForThemeCssSourceFile($subdirs, $file)
    {
        $subdirReplacement = strlen($subdirs) ? str_replace('/', '_', $subdirs) . '_' : '';
        return $subdirReplacement . $file;
    }

    /**
     * @param $linkDir
     * @param $cssSource
     * @return string
     */
    private function createPathToLinkTarget($linkDir, $cssSource)
    {
        $this->ensureDirectoryExists($linkDir);
        $relativePathToTarget = RelativeFileSystemPathBuilder::build($linkDir, $cssSource);
        return $relativePathToTarget;
    }

    /**
     * @param string $destinationDir
     * @param string $cssSource
     */
    private function processModuleCssSourceFiles($destinationDir, $cssSource)
    {
        $inThemePart = $this->getInThemePartOfCssSourceFile($cssSource);
        if (preg_match('#^([^_/]+_[^_/]+)/web/css/source/(.+/|)([^/]+)$#', $inThemePart, $matches)) {
            list(, $module, $subdirs, $file) = $matches;
            $linkDir = $destinationDir . '/css';
            $linkTarget = $this->createPathToLinkTarget($linkDir, $cssSource);
            symlink($linkTarget, $linkDir . '/' . $this->createLinkNameForModuleCssSourceFile($module, $subdirs, $file));
        }
    }

    /**
     * @param string $cssSource
     * @return string
     */
    private function getInThemePartOfCssSourceFile($cssSource)
    {
        return substr($cssSource, strlen($this->themeDir) + 1);
    }
}
