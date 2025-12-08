<?php
/**
 * Goal Class Unit Tests
 *
 * @package    TIJA_PMS
 * @subpackage Tests
 * @version    1.0.0
 */

require_once '../../../php/includes.php';
require_once '../../../php/classes/goal.php';

class GoalTest {

    private $DBConn;

    public function __construct($DBConn) {
        $this->DBConn = $DBConn;
    }

    /**
     * Test goal creation
     */
    public function testCreateGoal() {
        echo "Testing goal creation...\n";

        $goalData = array(
            'goalTitle' => 'Test Goal',
            'goalType' => 'KPI',
            'startDate' => date('Y-m-d'),
            'endDate' => date('Y-m-d', strtotime('+3 months')),
            'ownerUserID' => 1,
            'propriety' => 'Medium',
            'weight' => 0.25,
            'status' => 'Draft'
        );

        try {
            $result = Goal::createGoal($goalData, $this->DBConn);
            if ($result && isset($result['goalUUID'])) {
                echo "✓ Goal created successfully: " . $result['goalUUID'] . "\n";
                return $result['goalUUID'];
            } else {
                echo "✗ Goal creation failed\n";
                return false;
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Test goal retrieval
     */
    public function testGetGoal($goalUUID) {
        echo "Testing goal retrieval...\n";

        try {
            $goal = Goal::getGoal($goalUUID, $this->DBConn);
            if ($goal) {
                echo "✓ Goal retrieved successfully\n";
                return true;
            } else {
                echo "✗ Goal retrieval failed\n";
                return false;
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Test goal update
     */
    public function testUpdateGoal($goalUUID) {
        echo "Testing goal update...\n";

        $updateData = array(
            'goalTitle' => 'Updated Test Goal',
            'completionPercentage' => 50
        );

        try {
            $result = Goal::updateGoal($goalUUID, $updateData, $this->DBConn);
            if ($result) {
                echo "✓ Goal updated successfully\n";
                return true;
            } else {
                echo "✗ Goal update failed\n";
                return false;
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Test weight validation
     */
    public function testValidateWeightSum() {
        echo "Testing weight validation...\n";

        $goals = array(
            (object)array('weight' => 0.25),
            (object)array('weight' => 0.30),
            (object)array('weight' => 0.45)
        );

        try {
            $result = Goal::validateWeightSum($goals, $this->DBConn);
            if ($result) {
                echo "✓ Weight validation passed\n";
                return true;
            } else {
                echo "✗ Weight validation failed\n";
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
        echo "=== Goal Class Unit Tests ===\n\n";

        $goalUUID = $this->testCreateGoal();
        echo "\n";

        if ($goalUUID) {
            $this->testGetGoal($goalUUID);
            echo "\n";

            $this->testUpdateGoal($goalUUID);
            echo "\n";
        }

        $this->testValidateWeightSum();
        echo "\n";

        echo "=== Tests Complete ===\n";
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    $base = '../../../';
    set_include_path($base);
    include 'php/includes.php';

    $test = new GoalTest($DBConn);
    $test->runAllTests();
}

