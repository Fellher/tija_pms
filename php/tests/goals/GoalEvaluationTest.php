<?php
/**
 * Goal Evaluation Class Unit Tests
 *
 * @package    TIJA_PMS
 * @subpackage Tests
 * @version    1.0.0
 */

require_once '../../../php/includes.php';
require_once '../../../php/classes/goalevaluation.php';
require_once '../../../php/classes/goal.php';

class GoalEvaluationTest {

    private $DBConn;

    public function __construct($DBConn) {
        $this->DBConn = $DBConn;
    }

    /**
     * Test evaluation submission
     */
    public function testSubmitEvaluation() {
        echo "Testing evaluation submission...\n";

        // First create a test goal
        $goalData = array(
            'goalTitle' => 'Test Evaluation Goal',
            'goalType' => 'KPI',
            'startDate' => date('Y-m-d'),
            'endDate' => date('Y-m-d', strtotime('+3 months')),
            'ownerUserID' => 1,
            'status' => 'Active'
        );

        $goal = Goal::createGoal($goalData, $this->DBConn);
        if (!$goal || !isset($goal['goalUUID'])) {
            echo "✗ Failed to create test goal\n";
            return false;
        }

        $goalUUID = $goal['goalUUID'];

        try {
            $result = GoalEvaluation::submitEvaluation(
                $goalUUID,
                1, // evaluatorID
                85.5, // score
                'Test evaluation comment',
                $this->DBConn
            );

            if ($result) {
                echo "✓ Evaluation submitted successfully\n";
                return $goalUUID;
            } else {
                echo "✗ Evaluation submission failed\n";
                return false;
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Test weighted score calculation
     */
    public function testCalculateWeightedScore($goalUUID) {
        echo "Testing weighted score calculation...\n";

        try {
            $score = GoalEvaluation::calculateWeightedScore($goalUUID, $this->DBConn);
            if ($score !== false && $score !== null) {
                echo "✓ Weighted score calculated: " . number_format($score, 2) . "\n";
                return true;
            } else {
                echo "✗ Weighted score calculation failed\n";
                return false;
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Test 360 feedback retrieval
     */
    public function testGet360Feedback($goalUUID) {
        echo "Testing 360 feedback retrieval...\n";

        try {
            $feedback = GoalEvaluation::get360Feedback($goalUUID, $this->DBConn);
            if ($feedback !== false) {
                echo "✓ 360 feedback retrieved successfully\n";
                return true;
            } else {
                echo "✗ 360 feedback retrieval failed\n";
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
        echo "=== Goal Evaluation Class Unit Tests ===\n\n";

        $goalUUID = $this->testSubmitEvaluation();
        echo "\n";

        if ($goalUUID) {
            $this->testCalculateWeightedScore($goalUUID);
            echo "\n";

            $this->testGet360Feedback($goalUUID);
            echo "\n";
        }

        echo "=== Tests Complete ===\n";
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    $base = '../../../';
    set_include_path($base);
    include 'php/includes.php';

    $test = new GoalEvaluationTest($DBConn);
    $test->runAllTests();
}

