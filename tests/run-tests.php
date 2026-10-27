<?php
declare(strict_types=1);

/**
 * Zero-Dependency Test Runner for E-Voting System.
 * Executes all domain unit test suites with pure PHP (no external composer/phpunit packages needed).
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Autoload App\Tests\ namespace
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\Tests\\';
    $baseDir = __DIR__ . '/';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    $file = $baseDir . $relative;
    if (is_file($file)) {
        require_once $file;
    }
});

// Lightweight PHPUnit compatibility shim for the standalone runner.
// Use $autoload = false so Composer's autoloader (if vendor/ is present) does not
// load PHPUnit 10's TestCase, which requires constructor args and internal runner state.
if (!class_exists(\PHPUnit\Framework\TestCase::class, false)) {
    abstract class SimpleTestCase
    {
        public static int $assertionCount = 0;
        private ?string $expectedException = null;

        public function __construct(string $name = '') {}

        protected function setUp(): void {}
        protected function tearDown(): void {}

        public function expectException(string $exceptionClass): void
        {
            $this->expectedException = $exceptionClass;
        }

        public function getExpectedException(): ?string
        {
            return $this->expectedException;
        }

        public static function assertTrue(mixed $condition, string $msg = ''): void
        {
            self::$assertionCount++;
            if ($condition !== true) {
                throw new \AssertionError($msg ?: "Failed asserting that " . var_export($condition, true) . " is true.");
            }
        }

        public static function assertFalse(mixed $condition, string $msg = ''): void
        {
            self::$assertionCount++;
            if ($condition !== false) {
                throw new \AssertionError($msg ?: "Failed asserting that " . var_export($condition, true) . " is false.");
            }
        }

        public static function assertSame(mixed $expected, mixed $actual, string $msg = ''): void
        {
            self::$assertionCount++;
            if ($expected !== $actual) {
                throw new \AssertionError($msg ?: "Failed asserting that " . var_export($actual, true) . " is identical to expected " . var_export($expected, true) . ".");
            }
        }

        public static function assertEquals(mixed $expected, mixed $actual, string $msg = ''): void
        {
            self::$assertionCount++;
            if ($expected != $actual) {
                throw new \AssertionError($msg ?: "Failed asserting that " . var_export($actual, true) . " equals expected " . var_export($expected, true) . ".");
            }
        }

        public static function assertNull(mixed $actual, string $msg = ''): void
        {
            self::$assertionCount++;
            if ($actual !== null) {
                throw new \AssertionError($msg ?: "Failed asserting that " . var_export($actual, true) . " is null.");
            }
        }

        public static function assertNotNull(mixed $actual, string $msg = ''): void
        {
            self::$assertionCount++;
            if ($actual === null) {
                throw new \AssertionError($msg ?: "Failed asserting that value is not null.");
            }
        }

        public static function assertCount(int $expectedCount, mixed $countable, string $msg = ''): void
        {
            self::$assertionCount++;
            $count = is_countable($countable) ? count($countable) : 0;
            if ($count !== $expectedCount) {
                throw new \AssertionError($msg ?: "Failed asserting that count is {$count}, expected {$expectedCount}.");
            }
        }

        public static function assertNotEmpty(mixed $actual, string $msg = ''): void
        {
            self::$assertionCount++;
            if (empty($actual)) {
                throw new \AssertionError($msg ?: "Failed asserting that value is not empty.");
            }
        }

        public static function assertStringContainsString(string $needle, string $haystack, string $msg = ''): void
        {
            self::$assertionCount++;
            if (!str_contains($haystack, $needle)) {
                throw new \AssertionError($msg ?: "Failed asserting that '{$haystack}' contains '{$needle}'.");
            }
        }
    }

    class_alias(SimpleTestCase::class, \PHPUnit\Framework\TestCase::class);
}

// Discover and run test files
$testDir = __DIR__ . '/Unit';
$files = glob($testDir . '/*Test.php');

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$failures = [];

echo "\n\033[1;36m=== E-Voting System Domain Test Suite Runner ===\033[0m\n\n";
$startTimer = microtime(true);

foreach ($files as $file) {
    require_once $file;
    $className = 'App\\Tests\\Unit\\' . basename($file, '.php');
    if (!class_exists($className)) {
        continue;
    }

    $shortName = basename($file, '.php');
    echo "\033[1mSuite: {$shortName}\033[0m\n";

    $reflection = new \ReflectionClass($className);
    $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

    foreach ($methods as $method) {
        if (!str_starts_with($method->getName(), 'test')) {
            continue;
        }

        $testName = $method->getName();
        $totalTests++;

        /** @var \PHPUnit\Framework\TestCase $instance */
        $instance = new $className();
        $setUp = new \ReflectionMethod($instance, 'setUp');
        $setUp->invoke($instance);

        $thrownEx = null;
        try {
            $method->invoke($instance);
        } catch (\Throwable $e) {
            $thrownEx = $e;
        }

        $expectedEx = method_exists($instance, 'getExpectedException') ? $instance->getExpectedException() : null;

        if ($expectedEx !== null) {
            if ($thrownEx === null) {
                $failedTests++;
                echo "  \033[31m✘\033[0m {$testName}\n";
                $failures[] = [
                    'suite' => $shortName,
                    'test' => $testName,
                    'error' => "Expected exception {$expectedEx} was not thrown.",
                    'file' => $file,
                    'line' => 0
                ];
            } elseif ($thrownEx instanceof $expectedEx) {
                $passedTests++;
                echo "  \033[32m✔\033[0m {$testName} (caught expected {$expectedEx})\n";
            } else {
                $failedTests++;
                echo "  \033[31m✘\033[0m {$testName}\n";
                $failures[] = [
                    'suite' => $shortName,
                    'test' => $testName,
                    'error' => "Failed asserting that " . get_class($thrownEx) . " is an instance of {$expectedEx}: " . $thrownEx->getMessage(),
                    'file' => $thrownEx->getFile(),
                    'line' => $thrownEx->getLine()
                ];
            }
        } else {
            if ($thrownEx !== null) {
                $failedTests++;
                echo "  \033[31m✘\033[0m {$testName}\n";
                $failures[] = [
                    'suite' => $shortName,
                    'test' => $testName,
                    'error' => $thrownEx->getMessage(),
                    'file' => $thrownEx->getFile(),
                    'line' => $thrownEx->getLine()
                ];
            } else {
                $passedTests++;
                echo "  \033[32m✔\033[0m {$testName}\n";
            }
        }

        $tearDown = new \ReflectionMethod($instance, 'tearDown');
        $tearDown->invoke($instance);
    }
    echo "\n";
}

$elapsed = round((microtime(true) - $startTimer) * 1000, 2);
$assertionCount = \PHPUnit\Framework\TestCase::$assertionCount;

echo "--------------------------------------------------------\n";
if ($failedTests === 0) {
    echo "\033[1;32mOK ({$totalTests} tests, {$assertionCount} assertions, {$elapsed} ms)\033[0m\n";
    echo "All unit tests passed cleanly!\n\n";
    exit(0);
} else {
    echo "\033[1;31mFAILURES! ({$failedTests} failed of {$totalTests} tests)\033[0m\n\n";
    foreach ($failures as $f) {
        echo "\033[31m[FAIL] {$f['suite']}::{$f['test']}\033[0m\n";
        echo "  Message: {$f['error']}\n";
        echo "  Location: {$f['file']}:{$f['line']}\n\n";
    }
    exit(1);
}
