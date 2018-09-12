<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  PathExtractorTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Tests;

use Hrafn\Router\Contracts\PathExtractorInterface;
use Hrafn\Router\Parser\RegexPathExtractor;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * PathExtractorTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class PathExtractorTest extends TestCase {
    /** @var PathExtractorInterface */
    private $extractor;

    protected function setUp() {
        parent::setUp();

        $this->extractor = new RegexPathExtractor(new NullLogger());
    }

    public function testExtractPath() {
        $path = '/test/path/without/parameters';
        $out  = $this->extractor->getUriParts($path);

        foreach (['test', 'path', 'without', 'parameters'] as $partName) {
            $value = $out->dequeue();
            if ($value !== $partName) {
                $this->fail(sprintf('Expected value "%s" did not match "%s".', $partName, $value));
            }
        }
        $this->assertTrue(true);
    }

    public function testExtractPathWithParameter() {
        $path = '/test/path/with/{some}/parameters/{?right}';
        $out  = $this->extractor->getUriParts($path);

        foreach (['test', 'path', 'with', '%PARAM%', 'parameters', '%PARAM%'] as $partName) {
            $value = $out->dequeue();
            if ($value !== $partName) {
                $this->fail(sprintf('Expected value "%s" did not match "%s".', $partName, $value));
            }
        }
        $this->assertTrue(true);
    }

    public function testTrailingSlash() {
        $path = '/test/path/without/parameters/';
        $out  = $this->extractor->getUriParts($path);

        foreach (['test', 'path', 'without', 'parameters'] as $partName) {
            $value = $out->dequeue();
            if ($value !== $partName) {
                $this->fail(sprintf('Expected value "%s" did not match "%s".', $partName, $value));
            }
        }
        $this->assertTrue(true);
    }

    public function testTrailingSlashWithParameter() {
        $path = '/test/path/with/{some}/parameters/{?right}/';
        $out  = $this->extractor->getUriParts($path);

        foreach (['test', 'path', 'with', '%PARAM%', 'parameters', '%PARAM%'] as $partName) {
            $value = $out->dequeue();
            if ($value !== $partName) {
                $this->fail(sprintf('Expected value "%s" did not match "%s".', $partName, $value));
            }
        }
        $this->assertTrue(true);
    }

    public function testOmittedSlash() {
        $path = 'test/path/without/parameters';
        $out  = $this->extractor->getUriParts($path);

        foreach (['test', 'path', 'without', 'parameters'] as $partName) {
            $value = $out->dequeue();
            if ($value !== $partName) {
                $this->fail(sprintf('Expected value "%s" did not match "%s".', $partName, $value));
            }
        }
        $this->assertTrue(true);
    }

}
