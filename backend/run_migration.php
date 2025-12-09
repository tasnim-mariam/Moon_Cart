<?php
/**
 * Database Migration Runner
 * Run this file in your browser to automatically update the database
 * URL: http://localhost/Moon_Cart/backend/run_migration.php
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration - MoonCart</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        .code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Database Migration</h1>
        <p class="subtitle">MoonCart - Add 'completed' Status</p>

        <?php
        $conn = null;
        $error = null;
        $success = false;

        try {
            $conn = getConnection();
            
            // Check current status column definition
            $stmt = $conn->query("SHOW COLUMNS FROM orders WHERE Field = 'status'");
            $column = $stmt->fetch();
            
            if ($column) {
                $currentEnum = $column['Type'];
                
                // Check if 'completed' already exists
                if (strpos($currentEnum, 'completed') !== false) {
                    echo '<div class="status-box success">';
                    echo '<strong>✅ Migration Already Applied</strong><br>';
                    echo 'The "completed" status is already in your database.';
                    echo '</div>';
                    $success = true;
                } else {
                    // Run the migration
                    if (isset($_GET['run']) && $_GET['run'] === 'yes') {
                        $sql = "ALTER TABLE orders 
                                MODIFY COLUMN status ENUM('pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'completed', 'cancelled') DEFAULT 'pending'";
                        
                        $conn->exec($sql);
                        
                        echo '<div class="status-box success">';
                        echo '<strong>✅ Migration Successful!</strong><br>';
                        echo 'The "completed" status has been added to your database.';
                        echo '</div>';
                        echo '<div class="status-box info">';
                        echo '<strong>Next Steps:</strong><br>';
                        echo '1. Go back to your customer dashboard<br>';
                        echo '2. Click the "Received" button on a confirmed order<br>';
                        echo '3. It should work now!';
                        echo '</div>';
                        $success = true;
                    } else {
                        echo '<div class="status-box info">';
                        echo '<strong>📋 Migration Ready</strong><br>';
                        echo 'Current status column: <code>' . htmlspecialchars($currentEnum) . '</code><br><br>';
                        echo 'This will add "completed" to the status options.';
                        echo '</div>';
                        
                        echo '<div class="status-box">';
                        echo '<strong>SQL Command to Run:</strong>';
                        echo '<div class="code">ALTER TABLE orders 
MODIFY COLUMN status ENUM(\'pending\', \'confirmed\', \'preparing\', \'out_for_delivery\', \'delivered\', \'completed\', \'cancelled\') DEFAULT \'pending\';</div>';
                        echo '</div>';
                        
                        echo '<a href="?run=yes" class="btn">🚀 Run Migration Now</a>';
                    }
                }
            } else {
                throw new Exception("Could not find 'status' column in orders table");
            }
            
        } catch (PDOException $e) {
            $error = $e->getMessage();
            echo '<div class="status-box error">';
            echo '<strong>❌ Migration Failed</strong><br>';
            echo 'Error: ' . htmlspecialchars($error);
            echo '</div>';
            
            echo '<div class="status-box info">';
            echo '<strong>Manual Fix:</strong><br>';
            echo '1. Open phpMyAdmin<br>';
            echo '2. Select database: <code>mooncart_db</code><br>';
            echo '3. Go to SQL tab<br>';
            echo '4. Run the SQL command shown above';
            echo '</div>';
        } catch (Exception $e) {
            $error = $e->getMessage();
            echo '<div class="status-box error">';
            echo '<strong>❌ Error</strong><br>';
            echo htmlspecialchars($error);
            echo '</div>';
        }
        ?>

        <?php if ($success): ?>
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <a href="../customer-dashboard.html" style="color: #667eea; text-decoration: none;">
                    ← Back to Customer Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

