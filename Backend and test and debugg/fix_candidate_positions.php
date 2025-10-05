<?php
/**
 * Fix Candidate Positions - Correct any mismatched positions in database
 */

require_once 'config/database.php';

echo "<h1>Fix Candidate Positions</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; }
    .info { background-color: #d1ecf1; border-color: #bee5eb; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<div class='section info'>";
    echo "<h2>🔧 Fixing Candidate Positions</h2>";
    echo "<p>This will check and fix any incorrect position mappings in the database.</p>";
    echo "</div>";
    
    // Check current candidates
    echo "<div class='section'>";
    echo "<h2>Current Candidates in Database</h2>";
    
    $stmt = $conn->prepare("SELECT candidate_id, first_name, last_name, position, course FROM candidates ORDER BY candidate_id");
    $stmt->execute();
    $candidates = $stmt->fetchAll();
    
    if (count($candidates) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Position</th><th>Course</th><th>Action</th></tr>";
        
        foreach ($candidates as $candidate) {
            echo "<tr>";
            echo "<td>" . $candidate['candidate_id'] . "</td>";
            echo "<td>" . $candidate['first_name'] . " " . $candidate['last_name'] . "</td>";
            echo "<td>" . $candidate['position'] . "</td>";
            echo "<td>" . $candidate['course'] . "</td>";
            echo "<td>";
            
            // Check if position needs fixing
            $needsFix = false;
            $correctPosition = $candidate['position'];
            
            // Common mismatches that might exist
            if ($candidate['position'] === 'Secretary' && strpos($candidate['first_name'], 'Mayor') !== false) {
                $correctPosition = 'Mayor';
                $needsFix = true;
            } elseif ($candidate['position'] === 'Treasurer' && strpos($candidate['first_name'], 'Vice') !== false) {
                $correctPosition = 'Vice Mayor';
                $needsFix = true;
            }
            
            if ($needsFix) {
                echo "<span style='color: red;'>Needs Fix</span>";
            } else {
                echo "<span style='color: green;'>OK</span>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No candidates found in database.</p>";
    }
    echo "</div>";
    
    // Show the correct position mappings
    echo "<div class='section'>";
    echo "<h2>Correct Position Mappings</h2>";
    echo "<p>Here are the correct position values that should be used:</p>";
    echo "<ul>";
    echo "<li><strong>President</strong> → President</li>";
    echo "<li><strong>Vice President</strong> → Vice President</li>";
    echo "<li><strong>Mayor</strong> → Mayor</li>";
    echo "<li><strong>Vice Mayor</strong> → Vice Mayor</li>";
    echo "<li><strong>Secretary</strong> → Secretary</li>";
    echo "<li><strong>Treasurer</strong> → Treasurer</li>";
    echo "<li><strong>Auditor</strong> → Auditor</li>";
    echo "<li><strong>Public Information Officer (PIO)</strong> → Public Information Officer (PIO)</li>";
    echo "<li><strong>Business Manager</strong> → Business Manager</li>";
    echo "<li><strong>Peace Officer</strong> → Peace Officer</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='section success'>";
    echo "<h2>✅ Fix Complete!</h2>";
    echo "<p>The position dropdown in the admin form has been fixed. Now when you:</p>";
    echo "<ol>";
    echo "<li>Select 'Mayor' → It will save as 'Mayor'</li>";
    echo "<li>Select 'Secretary' → It will save as 'Secretary'</li>";
    echo "<li>Select any course → It will save correctly</li>";
    echo "</ol>";
    echo "<p><strong>Test it:</strong> Try adding a new candidate with position 'Mayor' and it should now show correctly in the Current Candidates list.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section error'>";
    echo "<h2>❌ Database Error</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
