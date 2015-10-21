<?php


namespace MeetMagentoPL\Falkowskifier\Test\Unit\Util;

use MeetMagentoPL\Falkowskifier\Util\RelativeFileSystemPathBuilder;

class RelativeFileSystemPathBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getRelativePath
     * @param string $basePath
     * @param string $path
     * @param string $expected
     */
    public function testRelativePathIsReturned($basePath, $path, $expected)
    {
        $this->assertSame($expected, RelativeFileSystemPathBuilder::build($basePath, $path));
    }

    /**
     * @return array[]
     */
    public function getRelativePath()
    {
        return [
            'path within bp' => ['/base/path', '/base/path/file', 'file'],
            'path within bp, bp with /' => ['/base/path/', '/base/path/file', 'file'],
            'path within bp, path with /' => ['/base/path/', '/base/path/file/', 'file/'],

            'path eq bp, with /' => ['/base/path/', '/base/path/', ''],
            'path eq bp, no /' => ['/base/path', '/base/path', ''],
            'path eq bp, path no /' => ['/base/path/', '/base/path', ''],
            'path eq bp, path with /' => ['/base/path', '/base/path/', ''],

            'path relative, path no /' => ['/base/path', 'relative/path', 'relative/path'],
            'path relative, path no /, bp with /' => ['/base/path/', 'relative/file', 'relative/file'],
            'path relative, path with /' => ['/base/path', 'relative/file/', 'relative/file/'],
            'path relative, path with /, bp with /' => ['/base/path/', 'relative/file/', 'relative/file/'],

            'bp is root' => ['/', '/path/to/file', 'path/to/file'],
            'path is root' => ['/base/path/dir', '/', '../../../'],

            'path is parent of bp' => ['/base/path', '/base', '..'],
            'path is parent of bp, path with /' => ['/base/path', '/base/', '../'],
            'path is grandparent of bp' => ['/base/path/dir', '/base', '../..'],
            'path is grandparent of bp, path with /' => ['/base/path/dir', '/base/', '../../'],

            'path one up one down' => ['/base/path/dir', '/base/path/another-dir', '../another-dir'],
            'path one up one down, path has /' => ['/base/path/dir', '/base/path/another-dir/', '../another-dir/'],

            'no shared parent' => ['/one/dir/path', '/another/dir/path', '../../../another/dir/path'],
            
            
            'no shared parent,  both relative' => ['relative/path/dir', 'another/relative/path/to/fileB', '../../../another/relative/path/to/fileB']
        ];
    }
}
