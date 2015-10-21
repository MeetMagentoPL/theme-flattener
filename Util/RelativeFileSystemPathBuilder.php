<?php


namespace MeetMagentoPL\ThemeFlattener\Util;

class RelativeFileSystemPathBuilder
{
    /**
     * @param string $basePath
     * @param string $path
     * @return string
     */
    public static function build($basePath, $path)
    {
        if (0 === strpos($path, $basePath) || $basePath === $path . '/') {
            return ltrim(substr($path, strlen($basePath)), '/');
        }

        if (! self::isRelativePath($basePath) && self::isRelativePath($path)) {
            return $path;
        }

        return self::buildRelativePath($basePath, $path);
    }

    /**
     * @param string $basePath
     * @param string $path
     * @return string
     */
    private static function buildRelativePath($basePath, $path)
    {
        $pathParts = explode('/', rtrim($path, '/'));
        $basePathParts = explode('/', rtrim($basePath, '/'));
        $commonDirCount = self::getCountOfSharedDirectories($basePathParts, $pathParts);
        $downPath = self::buildDownPortionOfRelativePath($commonDirCount, $basePathParts);
        $upPath = self::buildUpPortionOfRelativePath($commonDirCount, $pathParts);

        return $downPath . $upPath . (substr($path, - 1) === '/' ? '/' : '');
    }

    /**
     * @param string[] $basePathParts
     * @param string[] $pathParts
     * @return int
     */
    private static function getCountOfSharedDirectories(array $basePathParts, array $pathParts)
    {
        $commonPartCount = 0;
        for ($max = min(count($pathParts), count($basePathParts)); $commonPartCount < $max; $commonPartCount ++) {
            if ($pathParts[$commonPartCount] !== $basePathParts[$commonPartCount]) {
                break;
            }
        }

        return $commonPartCount;
    }

    /**
     * @param int $commonDirCount
     * @param string[] $basePathParts
     * @return string
     */
    private static function buildDownPortionOfRelativePath($commonDirCount, array $basePathParts)
    {
        $numDown = count(array_slice($basePathParts, $commonDirCount));
        return implode('/', array_fill(0, $numDown, '..'));
    }

    /**
     * @param int $commonDirCount
     * @param string[] $pathParts
     * @return string
     */
    private static function buildUpPortionOfRelativePath($commonDirCount, array $pathParts)
    {
        if ($commonDirCount === count($pathParts)) {
            return '';
        }

        return '/' . implode('/', array_slice($pathParts, $commonDirCount));
    }

    /**
     * @param string $path
     * @return bool
     */
    private static function isRelativePath($path)
    {
        return substr($path, 0, 1) !== '/';
    }
}
