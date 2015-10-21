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
     * @param string $destinationDir
     */
    public function flatten($area, $theme, $destinationDir)
    {
        $this->themeDir = RelativeFileSystemPathBuilder::build(
            getcwd(),
            $this->themeFileCollector->getThemeDirectoryPath($area, $theme)
        );
        $this->createDestinationWorkDir($destinationDir);
        foreach ($this->getThemeCssSourceFiles($area, $theme) as $cssSourceFile) {
            $this->processModuleCssSourceFiles($destinationDir, $cssSourceFile);
            $this->processThemeCssSourceFiles($destinationDir, $cssSourceFile);
            
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
        $linkName = $module . '_' . $this->createLinkNameForThemeCssSourceFile($subdirs, $file);
        return $this->removeDoubleUnderscores($linkName);
    }

    /**
     * @param string $subdirs
     * @param string $file
     * @return string
     */
    private function createLinkNameForThemeCssSourceFile($subdirs, $file)
    {
        $subdirReplacement = strlen($subdirs) ? str_replace('/', '_', $subdirs) : '';
        return $this->removeDoubleUnderscores($subdirReplacement . $file);
    }

    /**
     * @param $linkDir
     * @param $cssSource
     * @return string
     */
    private function createPathToLinkTarget($linkDir, $cssSource)
    {
        $this->ensureDirectoryExists($linkDir);
        return RelativeFileSystemPathBuilder::build($linkDir, $cssSource);
    }

    /**
     * @param string $destinationDir
     * @param string $cssSourceFile
     */
    private function processModuleCssSourceFiles($destinationDir, $cssSourceFile)
    {
        $inThemePart = $this->getInThemePartOfCssSourceFile($cssSourceFile);
        if (preg_match('#^([^_/]+_[^_/]+)/web/css/source/(.+/|)([^/]+)$#', $inThemePart, $matches)) {
            list(, $module, $subdirs, $file) = $matches;
            $linkName = $this->createLinkNameForModuleCssSourceFile($module, $subdirs, $file);

            $this->createSymlink($destinationDir, $linkName, $cssSourceFile);
        }
    }

    /**
     * @param string $destinationDir
     * @param string $cssSourceFile
     */
    private function processThemeCssSourceFiles($destinationDir, $cssSourceFile)
    {
        $inThemePart = $this->getInThemePartOfCssSourceFile($cssSourceFile);
        if (preg_match('#^web/css/source/(.+/|)([^/]+)$#', $inThemePart, $matches)) {
            list(, $subdirs, $file) = $matches;
            $linkName = $this->createLinkNameForThemeCssSourceFile($subdirs, $file);

            $this->createSymlink($destinationDir, $linkName, $cssSourceFile);
        }
    }

    /**
     * @param string $cssSource
     * @return string
     */
    private function getInThemePartOfCssSourceFile($cssSource)
    {
        $themeDir = $this->themeDir;
        $result = substr($cssSource, strlen($themeDir) + 1);
        return $result;
    }

    /**
     * @param string $str
     * @return string
     */
    private function removeDoubleUnderscores($str)
    {
        return str_replace('__', '_', $str);
    }

    /**
     * @param string $destinationDir
     * @param string $linkName
     * @param string $cssSource
     */
    private function createSymlink($destinationDir, $linkName, $cssSource)
    {
        $linkDir = $destinationDir . '/css';
        $linkTarget = $this->createPathToLinkTarget($linkDir, $cssSource);
        $linkFilePath = $linkDir . '/' . $linkName;
        if (file_exists($linkFilePath)) {
            unlink($linkFilePath);
        }
        symlink($linkTarget, $linkFilePath);
    }
}
