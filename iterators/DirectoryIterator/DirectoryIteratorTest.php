<?php
declare(strict_types=1);

namespace iterators;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Class DirectoryIteratorTest
 *
 * @package iterators
 */
class DirectoryIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * FS root
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var \DirectoryIterator
     */
    private $rootDirIterator;

    protected function setUp()
    {
        $this->root = vfsStream::setup('root', null, [
            'emptyFolder' => [],
            'foo' => 'foo content',
            'bar' => [
                'baz' => 'baz content',
                'emptyFile' => '',
            ],
        ]);

        $this->rootDirIterator = new \DirectoryIterator(vfsStream::url($this->root->getName()));
    }

    /**
     * @test
     */
    public function itIncludesSelfAndParentDirectories()
    {
        $dotDirectoriesFound = [];

        /** @var \DirectoryIterator $item */
        foreach ($this->rootDirIterator as $item) {
            if ($item->isDot()) {
                $dotDirectoriesFound[] = $item->getFilename();
            }
        }

        $expected = ['.', '..'];
        $this->assertSame($expected, $dotDirectoriesFound);
    }

    /**
     * @test
     */
    public function itIsNotRecursive()
    {
        $itemsInIterator = [];

        /** @var \DirectoryIterator $item */
        foreach ($this->rootDirIterator as $item) {
            $itemsInIterator[] = $item->getFilename();
        }

        $expected = [
            /**
             * The first two entries are a current and parent directories
             */
            '.',
            '..',
            'emptyFolder',
            'foo',
            'bar',
        ];

        $this->assertSame($expected, $itemsInIterator);
    }

    /**
     * @test
     */
    public function itIsSeekable()
    {
        $this->assertSame('.', $this->rootDirIterator->current()->getFilename());

        $this->rootDirIterator->seek(4);

        $this->assertSame('bar', $this->rootDirIterator->current()->getFilename());
    }

    /**
     * @test
     */
    public function itCanTellTheDifferenceBetweenFilesAndDirectories()
    {
        $filesFound = [];
        $directoriesFound = [];

        /** @var \DirectoryIterator $item */
        foreach ($this->rootDirIterator as $item) {
            switch (true) {
                case $item->isFile():
                    $filesFound[] = $item->getFilename();
                    break;
                case $item->isDir():
                    $directoriesFound[] = $item->getFilename();
                    break;
            }
        }

        $expectedFiles = ['foo'];
        $this->assertSame($expectedFiles, $filesFound);

        $expectedDirectories = ['.', '..', 'emptyFolder', 'bar'];
        $this->assertSame($expectedDirectories, $directoriesFound);
    }
}
