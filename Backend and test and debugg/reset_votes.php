<?php
/**
 * Reset Votes - Clear all votes for testing purposes
 * WARNING: This will delete all votes from the database!
 */

require_once 'config/database.php';

echo "<h1>Reset Votes (Testing Only)</h1>";

if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Count votes before deletion
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM votes");
        $stmt->execute();
        $voteCount = $stmt->fetch()['count'];
        
        // Delete all votes
        $stmt = $conn->prepare("DELETE FROM votes");
        $result = $stmt->execute();
        
        if ($result) {
            echo "<p style='color: green; font-weight: bold;'>✅ Successfully deleted $voteCount votes from the database.</p>";
            echo "<p>You can now vote again for any position.</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to delete votes.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: orange; font-weight: bold;'>⚠️ WARNING: This will delete ALL votes from the database!</p>";
    echo "<p>This is for testing purposes only. In a real election, you would never do this.</p>";
    
    echo "<form method='POST'>";
    echo "<p>Are you sure you want to delete all votes?</p>";
    echo "<input type='hidden' name='confirm' value='yes'>";
    echo "<button type='submit' style='background: red; color: white; padding: 10px 20px; border: none; cursor: pointer;'>Yes, Delete All Votes</button>";
    echo "</form>";
    
    echo "<p><a href='check_my_votes.php'>← Back to View Votes</a></p>";
}
?>
