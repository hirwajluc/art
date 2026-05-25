<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GREATER Art Competition - Registrations</title>
    <?php include 'views/includes/styles.php'; ?>
</head>
<body>
    <div class="admin-container">
        <?php include 'views/includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'views/includes/topbar.php'; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">All Registrations (<?php echo $totalCount; ?>)</h3>
                    <div class="search-box">
                        <form method="GET" style="display: flex; gap: 10px;">
                            <input type="hidden" name="page" value="registrations">
                            <input type="text" 
                                   class="search-input" 
                                   name="search" 
                                   placeholder="Search by name, email, or user code..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="?page=registrations" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User Code</th>
                            <!--<th>Full Name</th>
                            <th>Email</th>-->
                            <th>Phone</th>
                            <th>Category</th>
                            <th>Nationality</th>
                            <th>Birth Date</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($registrations)): ?>
                            <?php foreach ($registrations as $registration): ?>
                                <tr>
                                    <td>
                                        <span class="user-code"><?php echo htmlspecialchars($registration['userCode']); ?></span>
                                    </td>
                                    <!--<td>
                                        <strong><?php echo htmlspecialchars($registration['fullName']); ?></strong>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($registration['email']); ?>" class="email-link">
                                            <?php echo htmlspecialchars($registration['email']); ?>
                                        </a>
                                    </td>-->
                                    <td><?php echo htmlspecialchars($registration['phone']); ?></td>
                                    <td>
                                        <span class="category-badge category-<?php echo $registration['category']; ?>">
                                            <?php echo $registration['category'] === 'photography_paint' ? 'Photography/Painting' : 'Short Video'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="country-flag"><?php echo htmlspecialchars($registration['nationality']); ?></span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($registration['birthDate'])); ?></td>
                                    <td><?php echo date('M j, Y H:i', strtotime($registration['registrationDate'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?page=registration_detail&id=<?php echo $registration['id']; ?>" 
                                               class="btn btn-primary btn-sm" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="mailto:<?php echo htmlspecialchars($registration['email']); ?>" 
                                               class="btn btn-warning btn-sm" 
                                               title="Send Email">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="no-data">
                                    <div class="no-data-content">
                                        <i class="fas fa-search"></i>
                                        <p>No registrations found</p>
                                        <?php if (!empty($search)): ?>
                                            <small>Try adjusting your search terms</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=registrations&p=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                               class="btn btn-secondary">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=registrations&p=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=registrations&p=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                               class="btn btn-secondary">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <style>
        .user-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: var(--primary);
            background: #f0f8ff;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .email-link {
            color: var(--primary);
            text-decoration: none;
        }

        .email-link:hover {
            text-decoration: underline;
        }

        .category-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .category-photography_paint {
            background: #e3f2fd;
            color: #1565c0;
        }

        .category-short_video {
            background: #fff3e0;
            color: #e65100;
        }

        .country-flag {
            font-weight: 500;
            color: var(--dark);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
        }

        .no-data {
            text-align: center;
            padding: 40px 20px;
        }

        .no-data-content {
            color: var(--gray);
        }

        .no-data-content i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .no-data-content p {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .no-data-content small {
            font-size: 14px;
            opacity: 0.8;
        }

        .search-box form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-secondary {
            background: var(--gray);
            color: white;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 20px;
            border-top: 1px solid #eee;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: var(--gray);
            transition: all 0.3s ease;
        }

        .pagination .current {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            font-weight: bold;
        }

        .pagination a:hover {
            background: var(--light);
            border-color: var(--primary);
        }
    </style>
</body>
</html>