<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  PathExtractorTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Tests;

use Hrafn\Router\Contracts\PathExtractorInterface;
use Hrafn\Router\Parser\RegularExpressionExtractor;
use PHPUnit\Framework\TestCase;

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

        $this->extractor = new RegularExpressionExtractor();
    }

    public function testExtractPath() {
        $this->expectNotToPerformAssertions();
        $path = '/test/path/without/parameters';
        $out  = $this->extractor->getUriParts($path);

        foreach (['test', 'path', 'without', 'parameters'] as $partName) {
            $value = $out->dequeue();
            if ($value !== $partName) {
                $this->fail(sprintf('Expected value "%s" did not match "%s".', $partName, $value));
            }
        }
    }

    public function testExtractPathWithParameter() {
        $this->expectNotToPerformAssertions();
        $path = '/test/path/with/{some}/parameters/{?right}';
        $out  = $this->extractor->getUriParts($path);

        foreach (['test', 'path', 'with', '%PARAM%', 'parameters', '%PARAM%'] as $partName) {
            $value = $out->dequeue();
            if ($value !== $partName) {
                $this->fail(sprintf('Expected value "%s" did not match "%s".', $partName, $value));
            }
        }
    }

    public function testTrailingSlash() {
        $this->expectNotToPerformAssertions();
        $path = '/test/path/without/parameters/';
        $out  = $this->extractor->getUriParts($path);

        foreach (['test', 'path', 'without', 'parameters'] as $partName) {
            $value = $out->dequeue();
            if ($value !== $partName) {
                $this->fail(sprintf('Expected value "%s" did not match "%s".', $partName, $value));
            }
        }
    }

    public function testTrailingSlashWithParameter() {
        $this->expectNotToPerformAssertions();
        $path = '/test/path/with/{some}/parameters/{?right}/';
        $out  = $this->extractor->getUriParts($path);

        foreach (['test', 'path', 'with', '%PARAM%', 'parameters', '%PARAM%'] as $partName) {
            $value = $out->dequeue();
            if ($value !== $partName) {
                $this->fail(sprintf('Expected value "%s" did not match "%s".', $partName, $value));
            }
        }
    }

    public function testOmittedSlash() {
        $this->expectNotToPerformAssertions();
        $path = 'test/path/without/parameters';
        $out  = $this->extractor->getUriParts($path);

        foreach (['test', 'path', 'without', 'parameters'] as $partName) {
            $value = $out->dequeue();
            if ($value !== $partName) {
                $this->fail(sprintf('Expected value "%s" did not match "%s".', $partName, $value));
            }
        }
    }

}
