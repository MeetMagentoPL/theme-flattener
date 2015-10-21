<?php


namespace MeetMagentoPL\Falkowskifier;

interface ThemeFileCollectorInterface
{
    /**
     * @param string $area
     * @param string $theme
     * @return string[]
     */
    public function getCssSourceFiles($area, $theme);

    /**
     * @param string $area
     * @param string $theme
     * @return string
     */
    public function getThemeDirectoryPath($area, $theme);
}
