<?php
/**
 * Goal Hierarchy Class Unit Tests
 *
 * @package    TIJA_PMS
 * @subpackage Tests
 * @version    1.0.0
 */

require_once '../../../php/includes.php';
require_once '../../../php/classes/goalhierarchy.php';

class GoalHierarchyTest {

    private $DBConn;

    public function __construct($DBConn) {
        $this->DBConn = $DBConn;
    }

    /**
     * Test closure table building
     */
    public function testBuildClosureTable() {
        echo "Testing closure table building...\n";

        try {
            // Test with a sample entity
            $result = GoalHierarchy::buildClosureTable(1, 'Administrative', $this->DBConn);
            if ($result) {
                echo "✓ Closure table built successfully\n";
                return true;
            } else {
                echo "✗ Closure table build failed\n";
                return false;
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Test descendant retrieval
     */
    public function testGetDescendants() {
        echo "Testing descendant retrieval...\n";

        try {
            $descendants = GoalHierarchy::getDescendants(1, null, 'Administrative', $this->DBConn);
            if ($descendants !== false) {
                echo "✓ Retrieved " . count($descendants) . " descendants\n";
                return true;
            } else {
                echo "✗ Descendant retrieval failed\n";
                return false;
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Test ancestor retrieval
     */
    public function testGetAncestors() {
        echo "Testing ancestor retrieval...\n";

        try {
            $ancestors = GoalHierarchy::getAncestors(1, 'Administrative', $this->DBConn);
            if ($ancestors !== false) {
                echo "✓ Retrieved " . count($ancestors) . " ancestors\n";
                return true;
            } else {
                echo "✗ Ancestor retrieval failed\n";
                return false;
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "=== Goal Hierarchy Class Unit Tests ===\n\n";

        $this->testBuildClosureTable();
        echo "\n";

        $this->testGetDescendants();
        echo "\n";

        $this->testGetAncestors();
        echo "\n";

        echo "=== Tests Complete ===\n";
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    $base = '../../../';
    set_include_path($base);
    include 'php/includes.php';

    $test = new GoalHierarchyTest($DBConn);
    $test->runAllTests();
}

