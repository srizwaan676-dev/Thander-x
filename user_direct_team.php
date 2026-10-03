<?php
/**
 * Active Report - Shows all activated referral IDs with details
 * @version 2.0.0
 */

session_start();
include("../db.php");

// Get count of active users
$count_sql = "SELECT COUNT(*) as total FROM contact WHERE LOWER(TRIM(status)) = 'active'";
$count_result = mysqli_query($conn, $count_sql);
$total_active = mysqli_fetch_assoc($count_result)['total'] ?? 0;

// Get all active users - using your actual column names
$sql = "
    SELECT id, referral_id, name, mobile, email, city, status
    FROM contact
    WHERE LOWER(TRIM(status)) = 'active'
    ORDER BY id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Report | Admin Panel</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 30px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header-left h1 {
            font-size: 28px;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header-left h1 i {
            color: #16a34a;
        }
        
        .header-left p {
            color: #6b7280;
            margin-top: 4px;
            font-size: 14px;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(22, 163, 74, 0.25);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(22, 163, 74, 0.35);
        }
        
        .btn-secondary {
            background: #ffffff;
            color: #374151;
            border: 2px solid #e5e7eb;
        }
        
        .btn-secondary:hover {
            border-color: #16a34a;
            background: #f9fafb;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            padding: 18px 22px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .stat-card .stat-icon.green {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .stat-card .stat-icon.blue {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .stat-card .stat-icon.purple {
            background: #ede9fe;
            color: #7c3aed;
        }
        
        .stat-card .stat-info .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }
        
        .stat-card .stat-info .stat-label {
            font-size: 13px;
            color: #6b7280;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            overflow-x: auto;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .card-header h2 {
            font-size: 20px;
            color: #111827;
        }
        
        .card-header .badge {
            background: #dcfce7;
            color: #166534;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .search-box input {
            padding: 10px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: all 0.3s ease;
            min-width: 250px;
        }
        
        .search-box input:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.08);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        thead {
            background: #f8fafc;
            border-radius: 10px;
        }
        
        th {
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
        }
        
        td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f3f5;
            color: #111827;
        }
        
        tr:hover {
            background: #fafbfc;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-badge.active {
            background: #dcfce7;
            color: #166534;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 15px;
        }
        
        .empty-state h3 {
            font-size: 20px;
            color: #374151;
            margin-bottom: 8px;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
            }
            
            .header-actions .btn {
                flex: 1;
                justify-content: center;
            }
            
            .stats {
                grid-template-columns: 1fr 1fr;
            }
            
            .search-box input {
                min-width: 150px;
            }
            
            .card {
                padding: 15px;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 10px 12px;
            }
        }
        
        @media (max-width: 480px) {
            .stats {
                grid-template-columns: 1fr;
            }
            
            .search-box {
                flex-direction: column;
                width: 100%;
            }
            
            .search-box input {
                width: 100%;
            }
        }
        
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            }
            
            .header-left h1 {
                color: #f3f4f6;
            }
            
            .header-left p {
                color: #9ca3af;
            }
            
            .stat-card {
                background: #1f2937;
            }
            
            .stat-card .stat-info .stat-number {
                color: #f3f4f6;
            }
            
            .stat-card .stat-info .stat-label {
                color: #9ca3af;
            }
            
            .card {
                background: #1f2937;
            }
            
            .card-header h2 {
                color: #f3f4f6;
            }
            
            thead {
                background: #374151;
            }
            
            th {
                color: #e5e7eb;
                border-bottom-color: #4b5563;
            }
            
            td {
                color: #f3f4f6;
                border-bottom-color: #374151;
            }
            
            tr:hover {
                background: #374151;
            }
            
            .search-box input {
                background: #374151;
                border-color: #4b5563;
                color: #f3f4f6;
            }
            
            .search-box input:focus {
                border-color: #16a34a;
            }
            
            .btn-secondary {
                background: #374151;
                color: #e5e7eb;
                border-color: #4b5563;
            }
            
            .btn-secondary:hover {
                background: #4b5563;
            }
            
            .empty-state h3 {
                color: #e5e7eb;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>
                    <i class="fas fa-file-alt"></i> Active Report
                </h1>
                <p><i class="fas fa-users"></i> All activated referral IDs with full details</p>
            </div>
            <div class="header-actions">
                <a href="activate.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Activate New
                </a>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $total_active; ?></div>
                    <div class="stat-label">Total Active</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-calendar-today"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo date('d M Y'); ?></div>
                    <div class="stat-label">Today's Date</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo date('h:i A'); ?></div>
                    <div class="stat-label">Current Time</div>
                </div>
            </div>
        </div>
        
        <!-- Table -->
        <div class="card">
            <div class="card-header">
                <h2>
                    <i class="fas fa-table"></i> Active Referral IDs
                    <span class="badge">
                        <i class="fas fa-users"></i> <?php echo $total_active; ?> Records
                    </span>
                </h2>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="🔍 Search by Name, ID, Email..." onkeyup="searchTable()">
                </div>
            </div>
            
            <?php if (mysqli_num_rows($result) > 0): ?>
                <table id="activeTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Referral ID</th>
                            <th>Name</th>
                            <th>Mobile</th>
                            <!-- <th>Email</th> -->
                            <th>City</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td>
                                <strong style="color: #16a34a;">
                                    <i class="fas fa-hashtag"></i> 
                                    <?php echo htmlspecialchars($row['referral_id']); ?>
                                </strong>
                            </td>
                            <td>
                                <i class="fas fa-user"></i> 
                                <?php echo htmlspecialchars($row['name']); ?>
                            </td>
                            <td>
                                <i class="fas fa-phone"></i> 
                                <?php echo htmlspecialchars($row['mobile']); ?>
                            </td>
                            <!-- <td>
                                <i class="fas fa-envelope"></i> 
                                <?php echo htmlspecialchars($row['email']); ?>
                            </td> -->
                            <td>
                                <i class="fas fa-city"></i> 
                                <?php echo htmlspecialchars($row['city'] ?? 'N/A'); ?>
                            </td>
                            <td>
                                <span class="status-badge active">
                                    <i class="fas fa-check-circle"></i> Active
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <h3>No Active Referral IDs</h3>
                    <p>There are no activated referral IDs yet. Click "Activate New" to activate one.</p>
                    <br>
                    <a href="activate.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Activate New
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('activeTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell) {
                        const text = cell.textContent || cell.innerText;
                        if (text.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                row.style.display = found ? '' : 'none';
            }
        }
        
        // Auto-refresh every 30 seconds to show new activations
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>