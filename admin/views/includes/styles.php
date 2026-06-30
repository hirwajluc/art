<?php
// views/includes/styles.php
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<style>
:root {
    --primary: #1E90FF;
    --secondary: #FFD700;
    --accent: #00BFFF;
    --success: #32CD32;
    --warning: #FFA500;
    --danger: #FF6347;
    --dark: #2C3E50;
    --light: #F8F9FA;
    --gray: #6C757D;
    --white: #FFFFFF;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: var(--light);
    line-height: 1.6;
}

.admin-container {
    display: flex;
    min-height: 100vh;
}

/* Sidebar Styles */
.sidebar {
    width: 280px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
    padding: 20px 0;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    z-index: 1000;
}

.logo {
    text-align: center;
    padding: 0 20px 30px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    margin-bottom: 30px;
}

.logo h1 {
    font-size: 24px;
    font-weight: bold;
    color: var(--secondary);
    margin-bottom: 5px;
}

.logo p {
    font-size: 12px;
    opacity: 0.8;
}

.nav-menu {
    list-style: none;
}

.nav-item {
    margin-bottom: 5px;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 15px 25px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.nav-link:hover, .nav-link.active {
    background-color: rgba(255,255,255,0.1);
    border-left-color: var(--secondary);
    transform: translateX(5px);
}

.nav-link i {
    margin-right: 12px;
    width: 20px;
    text-align: center;
}

/* Main Content */
.main-content {
    flex: 1;
    margin-left: 280px;
    padding: 30px;
    min-width: 0; /* prevent flex overflow */
}

/* Inner wrapper – keeps readable line lengths on ultra-wide monitors */
.main-content > *:not(.top-bar) {
    max-width: 1600px;
}

@media (max-width: 900px) {
    .sidebar {
        width: 220px;
    }
    .main-content {
        margin-left: 220px;
        padding: 20px;
    }
}

@media (max-width: 640px) {
    .sidebar {
        display: none;
    }
    .main-content {
        margin-left: 0;
        padding: 15px;
    }
}

.top-bar {
    background: white;
    padding: 20px 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    font-size: 28px;
    color: var(--dark);
    font-weight: 600;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-welcome {
    display: flex;
    flex-direction: column;
    text-align: right;
}

.user-welcome span {
    font-size: 14px;
    color: var(--dark);
}

.user-welcome small {
    font-size: 11px;
    color: var(--gray);
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    flex-shrink: 0;
}

/* Dashboard Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
}

.stat-number {
    font-size: 48px;
    font-weight: bold;
    color: var(--primary);
    margin-bottom: 10px;
}

.stat-label {
    font-size: 16px;
    color: var(--gray);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.stat-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 30px;
    opacity: 0.3;
    color: var(--primary);
}

/* Charts */
.charts-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.chart-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.chart-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 20px;
    text-align: center;
}

/* Tables */
.table-container {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.table-header {
    background: var(--primary);
    color: white;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.table-title {
    font-size: 20px;
    font-weight: 600;
}

.search-box {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.search-input {
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    outline: none;
    min-width: 250px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    font-size: 14px;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-success {
    background: var(--success);
    color: white;
}

.btn-warning {
    background: var(--warning);
    color: white;
}

.btn-danger {
    background: var(--danger);
    color: white;
}

.btn-secondary {
    background: var(--gray);
    color: white;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 15px 20px;
    text-align: left;
    border-bottom: 1px solid #eee;
    vertical-align: top;
}

.data-table th {
    background-color: var(--light);
    font-weight: 600;
    color: var(--dark);
    position: sticky;
    top: 0;
}

.data-table tr:hover {
    background-color: #f8f9fa;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-approved {
    background: #d4edda;
    color: #155724;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

/* Forms */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: var(--dark);
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    outline: none;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(30, 144, 255, 0.2);
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

/* Alerts */
.alert {
    padding: 15px 20px;
    border-radius: 5px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .sidebar.active {
        transform: translateX(0);
    }

    .main-content {
        margin-left: 0;
        padding: 20px;
    }

    .charts-container {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .table-header {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box {
        justify-content: stretch;
    }

    .search-input {
        min-width: auto;
        flex: 1;
    }
}
</style>