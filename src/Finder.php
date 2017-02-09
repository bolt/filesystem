<?php

namespace Bolt\Filesystem;

use Bolt\Filesystem\Exception\InvalidArgumentException;
use Bolt\Filesystem\Exception\LogicException;
use Bolt\Filesystem\Iterator\EnsureHandlerIterator;
use Symfony\Component\Finder as Symfony;
use Traversable;

/**
 * Finder allows to build rules to find files and directories.
 *
 * It is a thin wrapper around several specialized iterator classes.
 *
 * All rules may be invoked several times.
 *
 * All methods return the current Finder object to allow easy chaining:
 *
 * $finder = $filesystem->find()->files()->name('*.php');
 *
 * This Finder differs from Symfony's finder in a few ways:
 *  - Items returned while iterating are instances of {@see File} or {@see Directory}, instead of {@see \SplFileInfo}.
 *  - Adapter methods have bee removed since Symfony is removing them in 3.0.
 *  - A few methods have been left out since they don't make sense with our abstraction.
 *      - sortBy{Accessed,Changed,Modified}Time() - Merged into one sortByTime method.
 *      - followLinks() - Abstraction does not understand symlinks.
 *      - ignoreUnreadableDirs() - Implied.
 * - toArray() method has been added for easy chaining.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Carson Full <carsonfull@gmail.com>
 */
class Finder implements \IteratorAggregate, \Countable
{
    const IGNORE_VCS_FILES = 1;
    const IGNORE_DOT_FILES = 2;

    /** @var FilesystemInterface */
    private $filesystem;

    /** @var int */
    private $mode = 0;
    /** @var string[] */
    private $names = [];
    /** @var string[] */
    private $notNames = [];
    /** @var string[] */
    private $exclude = [];
    /** @var callable[] */
    private $filters = [];
    /** @var Symfony\Comparator\NumberComparator[] */
    private $depths = [];
    /** @var Symfony\Comparator\NumberComparator[] */
    private $sizes = [];
    /** @var int|callable|false */
    private $sort = false;
    /** @var int */
    private $ignore = 0;
    /** @var string[] */
    private $dirs = [];
    /** @var Symfony\Comparator\Comparator[] */
    private $dates = [];
    /** @var \Iterator[] */
    private $iterators = [];
    /** @var string[] */
    private $contains = [];
    /** @var string[] */
    private $notContains = [];
    /** @var string[] */
    private $paths = [];
    /** @var string[] */
    private $notPaths = [];

    private static $vcsPatterns = ['.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg'];

    /**
     * Constructor.
     *
     * @param FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->ignore = static::IGNORE_VCS_FILES | static::IGNORE_DOT_FILES;
    }

    /**
     * Restricts the matching to directories only.
     *
     * @return Finder The current Finder instance
     */
    public function directories()
    {
        $this->mode = Symfony\Iterator\FileTypeFilterIterator::ONLY_DIRECTORIES;

        return $this;
    }

    /**
     * Restricts the matching to files only.
     *
     * @return Finder The current Finder instance
     */
    public function files()
    {
        $this->mode = Symfony\Iterator\FileTypeFilterIterator::ONLY_FILES;

        return $this;
    }

    /**
     * Adds tests for the directory depth.
     *
     * Usage:
     *
     *   $finder->depth('> 1') // the Finder will start matching at level 1.
     *   $finder->depth('< 3') // the Finder will descend at most 3 levels of directories below the starting point.
     *
     * @param int $level The depth level expression
     *
     * @return Finder The current Finder instance
     *
     * @see DepthRangeFilterIterator
     * @see NumberComparator
     */
    public function depth($level)
    {
        $this->depths[] = new Symfony\Comparator\NumberComparator($level);

        return $this;
    }

    /**
     * Adds tests for file dates (last modified).
     *
     * The date must be something that strtotime() is able to parse:
     *
     *   $finder->date('since yesterday');
     *   $finder->date('until 2 days ago');
     *   $finder->date('> now - 2 hours');
     *   $finder->date('>= 2005-10-15');
     *
     * @param string $date A date range string
     *
     * @return Finder The current Finder instance
     *
     * @see strtotime
     * @see DateRangeFilterIterator
     * @see DateComparator
     */
    public function date($date)
    {
        $this->dates[] = new Symfony\Comparator\DateComparator($date);

        return $this;
    }

    /**
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     * $finder->name('*.php')
     * $finder->name('/\.php$/') // same as above
     * $finder->name('test.php')
     *
     * @param string $pattern A pattern (a regexp, a glob, or a string)
     *
     * @return Finder The current Finder instance
     *
     * @see FilenameFilterIterator
     */
    public function name($pattern)
    {
        $this->names[] = $pattern;

        return $this;
    }

    /**
     * Adds rules that files must not match.
     *
     * @param string $pattern A pattern (a regexp, a glob, or a string)
     *
     * @return Finder The current Finder instance
     *
     * @see FilenameFilterIterator
     */
    public function notName($pattern)
    {
        $this->notNames[] = $pattern;

        return $this;
    }

    /**
     * Adds tests that file contents must match.
     *
     * Strings or PCRE patterns can be used:
     *
     * $finder->contains('Lorem ipsum')
     * $finder->contains('/Lorem ipsum/i')
     *
     * @param string $pattern A pattern (string or regexp)
     *
     * @return Finder The current Finder instance
     *
     * @see FileContentFilterIterator
     */
    public function contains($pattern)
    {
        $this->contains[] = $pattern;

        return $this;
    }

    /**
     * Adds tests that file contents must not match.
     *
     * Strings or PCRE patterns can be used:
     *
     * $finder->notContains('Lorem ipsum')
     * $finder->notContains('/Lorem ipsum/i')
     *
     * @param string $pattern A pattern (string or regexp)
     *
     * @return Finder The current Finder instance
     *
     * @see FileContentFilterIterator
     */
    public function notContains($pattern)
    {
        $this->notContains[] = $pattern;

        return $this;
    }

    /**
     * Adds rules that filenames must match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * $finder->path('some/special/dir')
     * $finder->path('/some\/special\/dir/') // same as above
     *
     * Use only / as dirname separator.
     *
     * @param string $pattern A pattern (a regexp or a string)
     *
     * @return Finder The current Finder instance
     *
     * @see FilenameFilterIterator
     */
    public function path($pattern)
    {
        $this->paths[] = $pattern;

        return $this;
    }

    /**
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * $finder->notPath('some/special/dir')
     * $finder->notPath('/some\/special\/dir/') // same as above
     *
     * Use only / as dirname separator.
     *
     * @param string $pattern A pattern (a regexp or a string)
     *
     * @return Finder The current Finder instance
     *
     * @see FilenameFilterIterator
     */
    public function notPath($pattern)
    {
        $this->notPaths[] = $pattern;

        return $this;
    }

    /**
     * Adds tests for file sizes.
     *
     * $finder->size('> 10K');
     * $finder->size('<= 1Ki');
     * $finder->size(4);
     *
     * @param string $size A size range string
     *
     * @return Finder The current Finder instance
     *
     * @see SizeRangeFilterIterator
     * @see NumberComparator
     */
    public function size($size)
    {
        $this->sizes[] = new Symfony\Comparator\NumberComparator($size);

        return $this;
    }

    /**
     * Excludes directories.
     *
     * @param string|array $dirs A directory path or an array of directories
     *
     * @return Finder The current Finder instance
     *
     * @see ExcludeDirectoryFilterIterator
     */
    public function exclude($dirs)
    {
        $this->exclude = array_merge($this->exclude, (array) $dirs);

        return $this;
    }

    /**
     * Excludes "hidden" directories and files (starting with a dot).
     *
     * @param bool $ignoreDotFiles Whether to exclude "hidden" files or not
     *
     * @return Finder The current Finder instance
     *
     * @see ExcludeDirectoryFilterIterator
     */
    public function ignoreDotFiles($ignoreDotFiles = true)
    {
        if ($ignoreDotFiles) {
            $this->ignore |= static::IGNORE_DOT_FILES;
        } else {
            $this->ignore &= ~static::IGNORE_DOT_FILES;
        }

        return $this;
    }

    /**
     * Forces the finder to ignore version control directories.
     *
     * @param bool $ignoreVCS Whether to exclude VCS files or not
     *
     * @return Finder The current Finder instance
     *
     * @see ExcludeDirectoryFilterIterator
     */
    public function ignoreVCS($ignoreVCS = true)
    {
        if ($ignoreVCS) {
            $this->ignore |= static::IGNORE_VCS_FILES;
        } else {
            $this->ignore &= ~static::IGNORE_VCS_FILES;
        }

        return $this;
    }

    /**
     * Adds VCS patterns.
     *
     * @see ignoreVCS()
     *
     * @param string|string[] $pattern VCS patterns to ignore
     */
    public static function addVCSPattern($pattern)
    {
        foreach ((array) $pattern as $p) {
            self::$vcsPatterns[] = $p;
        }

        self::$vcsPatterns = array_unique(self::$vcsPatterns);
    }

    /**
     * Sorts files and directories by an anonymous function.
     *
     * The anonymous function receives two File/Directory instances to compare.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @param callable $closure An anonymous function
     *
     * @return Finder The current Finder instance
     *
     * @see SortableIterator
     */
    public function sort(callable $closure)
    {
        $this->sort = $closure;

        return $this;
    }

    /**
     * Sorts files and directories by name.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return Finder The current Finder instance
     *
     * @see SortableIterator
     */
    public function sortByName()
    {
        $this->sort = Iterator\SortableIterator::SORT_BY_NAME;

        return $this;
    }

    /**
     * Sorts files and directories by type (directories before files), then by name.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return Finder The current Finder instance
     *
     * @see SortableIterator
     */
    public function sortByType()
    {
        $this->sort = Iterator\SortableIterator::SORT_BY_TYPE;

        return $this;
    }

    /**
     * Sorts files and directories by the last modified time.
     *
     * This is the last time the actual contents of the file were last modified.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return Finder The current Finder instance
     *
     * @see SortableIterator
     */
    public function sortByTime()
    {
        $this->sort = Iterator\SortableIterator::SORT_BY_TIME;

        return $this;
    }

    /**
     * Filters the iterator with an anonymous function.
     *
     * The anonymous function receives a File/Directory and must return false
     * to remove it.
     *
     * @param callable $closure An anonymous function
     *
     * @return Finder The current Finder instance
     *
     * @see CustomFilterIterator
     */
    public function filter(callable $closure)
    {
        $this->filters[] = $closure;

        return $this;
    }

    /**
     * Searches files and directories which match defined rules.
     *
     * @param string|array $dirs A directory path or an array of directories
     *
     * @throws InvalidArgumentException if one of the directories does not exist
     *
     * @return Finder The current Finder instance
     */
    public function in($dirs)
    {
        $resolvedDirs = [];

        foreach ((array) $dirs as $dir) {
            if ($this->filesystem->has($dir)) {
                $resolvedDirs[] = $dir;
                continue;
            }

            $it = new Iterator\GlobIterator($this->filesystem, $dir);

            $good = false;
            foreach ($it as $item) {
                $good = true;
                if ($item->isDir()) {
                    $resolvedDirs[] = $item->getPath();
                }
            }
            if (!$good) {
                throw new InvalidArgumentException(sprintf('The "%s" directory does not exist.', $dir));
            }
        }

        $this->dirs = array_merge($this->dirs, $resolvedDirs);

        return $this;
    }

    /**
     * Appends an existing set of files/directories to the finder. The items need to be
     * HandlerInterface objects or string paths that exist.
     *
     * @param array|Traversable $iterator
     *
     * @throws InvalidArgumentException When the given argument is not iterable.
     *
     * @return Finder The finder
     */
    public function append($iterator)
    {
        if (!$iterator instanceof Traversable && !is_array($iterator)) {
            throw new InvalidArgumentException('Finder::append() must be given an iterable object.');
        }

        $this->iterators[] = new EnsureHandlerIterator($this->filesystem, $iterator);

        return $this;
    }

    /**
     * Returns an Iterator for the current Finder configuration.
     *
     * This method implements the IteratorAggregate interface.
     *
     * @throws LogicException if the in() method has not been called
     *
     * @return \Iterator An iterator
     */
    public function getIterator()
    {
        if (0 === count($this->dirs) && 0 === count($this->iterators)) {
            /*
             * Shortcut for root path.
             *
             * Since this is a filesystem abstraction we assume that filesystem
             * adapters are created with an appropriate root path, which Symfony
             * can't assume for native filesystem.
             *
             * This won't work for composite filesystems so we try/catch.
             */
            try {
                $this->in('');
            } catch (InvalidArgumentException $e) {
                throw new LogicException('You must call one of in() or append() methods before iterating over a Finder with a composite filesystem.');
            }
        }

        if (1 === count($this->dirs) && 0 === count($this->iterators)) {
            return $this->searchInDirectory($this->dirs[0]);
        }

        $iterator = new Iterator\AppendIterator();
        foreach ($this->dirs as $dir) {
            $iterator->append($this->searchInDirectory($dir));
        }

        foreach ($this->iterators as $it) {
            $iterator->append($it);
        }

        return $iterator;
    }

    /**
     * Returns the results collected by the iterators.
     *
     * @return array
     */
    public function toArray()
    {
        return iterator_to_array($this->getIterator());
    }

    /**
     * Counts all the results collected by the iterators.
     *
     * @return int
     */
    public function count()
    {
        return iterator_count($this->getIterator());
    }

    /**
     * Compile all options into an iterator.
     *
     * @param string $dir
     *
     * @return \Iterator
     */
    private function searchInDirectory($dir)
    {
        if (static::IGNORE_VCS_FILES === (static::IGNORE_VCS_FILES & $this->ignore)) {
            $this->exclude = array_merge($this->exclude, self::$vcsPatterns);
        }

        if (static::IGNORE_DOT_FILES === (static::IGNORE_DOT_FILES & $this->ignore)) {
            $this->notPaths[] = '#(^|/)\..+(/|$)#';
        }

        $minDepth = 0;
        $maxDepth = PHP_INT_MAX;

        foreach ($this->depths as $comparator) {
            /** @var int $target */
            $target = $comparator->getTarget();
            switch ($comparator->getOperator()) {
                case '>':
                    $minDepth = $target + 1;
                    break;
                case '>=':
                    $minDepth = $target;
                    break;
                case '<':
                    $maxDepth = $target - 1;
                    break;
                case '<=':
                    $maxDepth = $target;
                    break;
                default:
                    $minDepth = $maxDepth = $target;
            }
        }

        $iterator = new Iterator\RecursiveDirectoryIterator($this->filesystem, $dir);

        if ($this->exclude) {
            $iterator = new Iterator\ExcludeDirectoryFilterIterator($iterator, $this->exclude);
        }

        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

        if ($minDepth > 0 || $maxDepth < PHP_INT_MAX) {
            $iterator = new Symfony\Iterator\DepthRangeFilterIterator($iterator, $minDepth, $maxDepth);
        }

        if ($this->mode) {
            $iterator = new Symfony\Iterator\FileTypeFilterIterator($iterator, $this->mode);
        }

        if ($this->names || $this->notNames) {
            $iterator = new Symfony\Iterator\FilenameFilterIterator($iterator, $this->names, $this->notNames);
        }

        /*
         * Deviation from Symfony:
         * Moved this iterator above the iterators below for performance.
         * This iterator and the filter iterators above can filter based on implicit data.
         * All filter iterators below need extra data; since that is slower,
         * the less they have to iterate, the better.
         */
        if ($this->paths || $this->notPaths) {
            $iterator = new Iterator\PathFilterIterator($iterator, $this->paths, $this->notPaths);
        }

        if ($this->contains || $this->notContains) {
            $iterator = new Iterator\FileContentFilterIterator($iterator, $this->contains, $this->notContains);
        }

        if ($this->sizes) {
            $iterator = new Symfony\Iterator\SizeRangeFilterIterator($iterator, $this->sizes);
        }

        if ($this->dates) {
            $iterator = new Iterator\DateRangeFilterIterator($iterator, $this->dates);
        }

        if ($this->filters) {
            $iterator = new Symfony\Iterator\CustomFilterIterator($iterator, $this->filters);
        }

        if ($this->sort) {
            $iteratorAggregate = new Iterator\SortableIterator($iterator, $this->sort);
            $iterator = $iteratorAggregate->getIterator();
        }

        return $iterator;
    }
}
