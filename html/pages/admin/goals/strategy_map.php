<?php
/**
 * Strategy Map Visualization
 * Interactive visualization of goal cascading hierarchy
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

// Security check
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

// Check admin permissions
if (!isset($isAdmin) || !$isAdmin) {
    if (!isset($isValidAdmin) || !$isValidAdmin) {
        Alert::error("Access denied. Administrator privileges required.", true);
        return;
    }
}

require_once 'php/classes/goal.php';
require_once 'php/classes/goalhierarchy.php';

// Get root goals (goals with no parent)
$rootGoals = $DBConn->retrieve_db_table_rows(
    'tija_goals',
    array('goalUUID', 'goalTitle', 'goalType', 'status', 'completionPercentage'),
    array('parentGoalUUID' => 'NULL', 'status' => 'Active', 'sysEndTime' => 'NULL', 'Lapsed' => 'N'),
    false
);

// Build hierarchy data for visualization
$hierarchyData = array();
if ($rootGoals) {
    foreach ($rootGoals as $root) {
        $children = $DBConn->retrieve_db_table_rows(
            'tija_goals',
            array('goalUUID', 'goalTitle', 'goalType', 'status', 'completionPercentage'),
            array('parentGoalUUID' => $root->goalUUID, 'sysEndTime' => 'NULL', 'Lapsed' => 'N'),
            false
        );

        $hierarchyData[] = array(
            'id' => $root->goalUUID,
            'name' => $root->goalTitle,
            'type' => $root->goalType,
            'completion' => $root->completionPercentage ?? 0,
            'children' => $children ? array_map(function($c) {
                return array(
                    'id' => $c->goalUUID,
                    'name' => $c->goalTitle,
                    'type' => $c->goalType,
                    'completion' => $c->completionPercentage ?? 0
                );
            }, $children) : array()
        );
    }
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Strategy Map</h4>
                    <p class="text-muted mb-0">Visual representation of goal cascading from global to local</p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" onclick="resetView()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset View
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="exportMap()">
                        <i class="bi bi-download me-2"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Strategy Map Container -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="strategyMapContainer" style="min-height: 600px; background: #f8f9fa; border-radius: 4px; position: relative;">
                        <svg id="strategyMapSVG" width="100%" height="600"></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6>Legend</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <span class="badge bg-primary me-2">Strategic</span> Strategic Goals
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-info me-2">OKR</span> Objectives & Key Results
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-success me-2">KPI</span> Key Performance Indicators
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="progress me-2" style="width: 100px; height: 20px;">
                                    <div class="progress-bar bg-success" style="width: 100%"></div>
                                </div>
                                <small>Completion %</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
// Strategy Map Data
const hierarchyData = <?php echo json_encode($hierarchyData); ?>;

// D3.js Strategy Map Visualization
function renderStrategyMap() {
    const svg = d3.select("#strategyMapSVG");
    svg.selectAll("*").remove();

    const width = document.getElementById('strategyMapContainer').offsetWidth;
    const height = 600;
    svg.attr("width", width).attr("height", height);

    // Create tree layout
    const tree = d3.tree()
        .size([height - 100, width - 200]);

    // Convert data to hierarchy
    const root = d3.hierarchy({
        name: "Global Strategy",
        children: hierarchyData.map(goal => ({
            name: goal.name,
            type: goal.type,
            completion: goal.completion,
            children: goal.children
        }))
    });

    tree(root);

    // Color scale based on completion
    const colorScale = d3.scaleLinear()
        .domain([0, 100])
        .range(["#dc3545", "#ffc107", "#28a745"]);

    // Draw links
    svg.selectAll(".link")
        .data(root.links())
        .enter()
        .append("path")
        .attr("class", "link")
        .attr("d", d3.linkHorizontal()
            .x(d => d.y + 100)
            .y(d => d.x + 50))
        .style("fill", "none")
        .style("stroke", "#ccc")
        .style("stroke-width", 2);

    // Draw nodes
    const node = svg.selectAll(".node")
        .data(root.descendants())
        .enter()
        .append("g")
        .attr("class", "node")
        .attr("transform", d => `translate(${d.y + 100},${d.x + 50})`);

    // Add circles
    node.append("circle")
        .attr("r", d => d.depth === 0 ? 20 : d.depth === 1 ? 15 : 10)
        .style("fill", d => {
            if (d.depth === 0) return "#6c757d";
            const completion = d.data.completion || 0;
            return colorScale(completion);
        })
        .style("stroke", "#fff")
        .style("stroke-width", 2)
        .on("click", function(event, d) {
            // Show goal details on click
            if (d.data.id) {
                window.location.href = '<?= "{$base}html/?s=admin&ss=goals&p=goal_detail&goalUUID=" ?>' + d.data.id;
            }
        })
        .style("cursor", "pointer");

    // Add labels
    node.append("text")
        .attr("dy", d => d.depth === 0 ? -25 : -15)
        .attr("text-anchor", "middle")
        .style("font-size", d => d.depth === 0 ? "14px" : "12px")
        .style("font-weight", d => d.depth === 0 ? "bold" : "normal")
        .text(d => {
            const name = d.data.name || '';
            return name.length > 30 ? name.substring(0, 30) + '...' : name;
        });

    // Add type badges
    node.filter(d => d.data.type)
        .append("text")
        .attr("dy", 20)
        .attr("text-anchor", "middle")
        .style("font-size", "10px")
        .text(d => d.data.type);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    renderStrategyMap();

    // Re-render on window resize
    window.addEventListener('resize', function() {
        renderStrategyMap();
    });
});

function resetView() {
    renderStrategyMap();
}

function exportMap() {
    // Export as PNG/SVG
    alert('Export functionality - would export map as image');
}
</script>

<style>
.node circle {
    transition: r 0.2s;
}

.node circle:hover {
    r: 25;
}

.link {
    transition: stroke-width 0.2s;
}

.link:hover {
    stroke-width: 4;
}
</style>

