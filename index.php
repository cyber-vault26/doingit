<?php
session_start();
if (!$_SESSION) {
    echo "<script>window.location.href='./login.php'</script>";
}
//print_r($_SESSION);
$usertype  = $_SESSION['usertype'] ?? '';
$userlevel = (int)($_SESSION['userlevel'] ?? 0);
$cost_center = ($_SESSION['usertype'] === 'shop-keeper') ? (int)$_SESSION['cost_center'] : null;
function hideItem($usertype, $userlevel, $type, $level = null) {
    if($level === null) return $usertype === $type ? ' d-none' : '';
    return ($usertype === $type && $userlevel == $level) ? ' d-none' : '';
}
 
?>
<!DOCTYPE html>
<html lang="en">
<head>
     
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TellicERP</title>
    <link rel="icon" type="image/png" href="dist/img/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
    <link rel="stylesheet" href="./plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="./plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="./dist/datatable2/datatables.css">
    <link rel="stylesheet" href="./dist/css/custom.css">
    <link rel="stylesheet" href="./dist/css/primary-color.css">
    <link rel="stylesheet" href="./dist/css/preview-profoma.css">
    <link rel="stylesheet" href="./dist/css/preview-stock-batch.css">
    <link rel="stylesheet" href="./dist/css/stock-reports.css">
    <link rel="stylesheet" href="./dist/css/stock-report-movement.css">
    <link rel="stylesheet" href="./dist/css/stock-report-ledger.css">
    <link rel="stylesheet" href="./dist/css/stock-report-selling-ledger.css">
    <link rel="stylesheet" href="./dist/css/stock-report-item-selling.css">
    <link rel="stylesheet" href="./dist/css/sales-voucher-invoice.css">
    <link rel="stylesheet" href="./dist/css/purchase-voucher-invoice.css">
    <link rel="stylesheet" href="./dist/css/normal-voucher.css">
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="./plugins/select2/js/select2.full.min.js"></script>
    <script src="./dist/js/graphs_function.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<iframe id="printFrame" style="display:none;"></iframe>
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center"></div>
    <?php
    require_once "./configs.php";
    require_once "./functions/subsidiary_functions.php";
    require_once "./functions/route_functions.php";
    require_once "./functions/vehicle_functions.php";
    require_once "./functions/cosignment_functions.php";
    require_once "./functions/transaction_functions.php";
    require_once "./functions/ledger_functions.php";
    require_once "./functions/utilities_functions.php";
   // require_once "./functions/project_functions.php";
    require_once "./functions/payroll_functions.php";
    require_once "./functions/master_functions.php";
    require_once "./functions/min_sub_functions.php";
    require_once "./functions/procurement_functions.php";
    require_once "./functions/user_functions.php";
    require_once "./functions/leave_function.php";
    require_once "./functions/cost_center_functions.php";
    require_once "./functions/stock_functions.php";
    require_once "./functions/retail_functions.php";
    require_once "./functions/opening_balance_functions.php";
    require_once "./functions/summary_function.php";
    require_once "./functions/horel_order_functions.php";
    require_once "./functions/voucher_functions.php"; 
    //require_once "./functions/retirement_functions.php";
    require_once "./functions/submain_functions.php";
    require_once "./functions/attendance_functions.php";
    require_once "./functions/profoma_functions.php";
    require_once "./functions/notification_functions.php";
    require_once "./functions/purchase_order_functions.php";
    require_once "./functions/purchase_request_functions.php";
    require_once "./functions/department_functions.php";
    require_once "./functions/stock_date_request_functions.php";
    include './toast_notification.php';
    $conn = openConn();
    if(empty($_SESSION['department_data'])){
        $_SESSION['department_data'] =0;
    }
    if(empty($_SESSION['employee_data'])){
        $_SESSION['employee_data'] = 0;
    }
   // print_r($_SESSION['department_data']);
    ?>
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">

    <!-- LEFT SIDE -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="./" class="nav-link">Home</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="./?page=logout" class="nav-link">Logout</a>
        </li>
    </ul>

    <!-- RIGHT SIDE -->
    <ul class="navbar-nav ml-auto">

        <!-- STOCK DATE REQUESTS DROPDOWN -->
        <li class="nav-item dropdown">
            <a class="nav-link" href="#" data-toggle="dropdown" title="Stock Date Requests"
               style="cursor:pointer; padding:8px 10px; display:flex; align-items:center;">
                <span style="width:32px;height:32px;border-radius:50%;background:#17a2b8;display:inline-flex;align-items:center;justify-content:center;position:relative;">
                    <i class="fas fa-calendar-check" style="font-size:13px;color:#fff;"></i>
                    <span id="stock-date-request-badge"
                          style="display:none;position:absolute;top:-3px;right:-3px;min-width:16px;height:16px;background:#dc3545;border-radius:50%;font-size:9px;color:#fff;align-items:center;justify-content:center;border:2px solid #fff;font-weight:700;padding:0 3px;">
                        0
                    </span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right p-0" style="width:340px;border-radius:0;border:1px solid #dee2e6;">
                
                <!-- HEADER -->
                <div style="background:#17a2b8;padding:10px 16px;display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-size:13px;font-weight:600;color:#fff;">
                            <i class="fas fa-calendar-check mr-2"></i> Stock Date Requests
                        </div>
                        <div style="font-size:11px;color:rgba(255,255,255,.75);margin-top:2px;">Pending approvals</div>
                    </div>
                    <span id="stock-date-dropdown-count"
                          style="background:#dc3545;color:#fff;font-size:12px;font-weight:700;padding:2px 8px;border-radius:10px;display:none;">
                        0
                    </span>
                </div>

                <!-- LOADING STATE -->
                <div id="stock-date-loading" style="padding:20px;text-align:center;color:#6c757d;">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Loading...
                </div>

                <!-- REQUESTS LIST (filled by AJAX) -->
                <div id="stock-date-list" style="max-height:280px;overflow-y:auto;display:none;"></div>

                <!-- FOOTER -->
                <div style="padding:10px 16px;background:#f8f9fa;border-top:1px solid #dee2e6;text-align:center;">
                    <a href="./?page=stock_date_requests" style="font-size:12px;color:#17a2b8;font-weight:600;">
                        <i class="fas fa-list mr-1"></i> See All Requests
                    </a>
                </div>

            </div>
        </li>

        <!-- NOTIFICATIONS -->
        <li class="nav-item dropdown">
            <a class="nav-link" href="#" data-toggle="dropdown" title="Notifications"
               style="cursor:pointer; padding:8px 10px; display:flex; align-items:center;">
                <span style="width:32px;height:32px;border-radius:50%;background:#ffc107;display:inline-flex;align-items:center;justify-content:center;position:relative;">
                    <i class="fas fa-bell" style="font-size:13px;color:#fff;"></i>
                    <span style="position:absolute;top:-3px;right:-3px;width:16px;height:16px;background:#dc3545;border-radius:50%;font-size:9px;color:#fff;display:flex;align-items:center;justify-content:center;border:2px solid #fff;font-weight:700;">3</span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right p-0" style="width:310px;border-radius:0;border:1px solid #dee2e6;">
                <div style="background:#007bff;padding:10px 16px;font-size:13px;font-weight:600;color:#fff;">
                    <i class="fas fa-bell mr-2"></i> Notifications
                    <span class="badge badge-warning float-right mt-1">3</span>
                </div>
                <a href="#" class="dropdown-item d-flex align-items-start py-2" style="border-bottom:1px solid #f1f3f5;gap:10px;">
                    <i class="fas fa-calendar-times text-warning mt-1" style="font-size:14px;min-width:16px;"></i>
                    <div>
                        <div style="font-size:13px;color:#343a40;">Year-end closing due in 7 days</div>
                        <small class="text-muted">2 hours ago</small>
                    </div>
                </a>
                <a href="#" class="dropdown-item d-flex align-items-start py-2" style="border-bottom:1px solid #f1f3f5;gap:10px;">
                    <i class="fas fa-file-alt text-info mt-1" style="font-size:14px;min-width:16px;"></i>
                    <div>
                        <div style="font-size:13px;color:#343a40;">Q3 income statement is ready</div>
                        <small class="text-muted">Yesterday</small>
                    </div>
                </a>
                <a href="#" class="dropdown-item d-flex align-items-start py-2" style="gap:10px;">
                    <i class="fas fa-check-circle text-success mt-1" style="font-size:14px;min-width:16px;"></i>
                    <div>
                        <div style="font-size:13px;color:#343a40;">FY 2024 closed successfully</div>
                        <small class="text-muted">31 Dec 2024</small>
                    </div>
                </a>
                <div style="padding:10px 16px;background:#f8f9fa;border-top:1px solid #dee2e6;text-align:center;">
                    <a href="./?page=notifications" style="font-size:12px;color:#007bff;">View all notifications</a>
                </div>
            </div>
        </li>

        <!-- USER MENU -->
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown"
               style="cursor:pointer; gap:7px; padding:8px 10px;">
                <span style="width:32px;height:32px;border-radius:50%;background:#28a745;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-user" style="font-size:13px;color:#fff;"></i>
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right p-0" style="width:230px;border-radius:0;border:1px solid #dee2e6;">
                <li style="background:#007bff;padding:14px 16px;">
                    <span style="width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;margin-bottom:8px;">
                        <i class="fas fa-user" style="font-size:20px;color:#fff;"></i>
                    </span>
                    <div style="font-size:14px;font-weight:600;color:#fff;"><?= $_SESSION['username'] ?></div>
                    <div style="font-size:11px;color:rgba(255,255,255,.75);text-transform:capitalize;"><?= $_SESSION['usertype'] ?></div>
                </li>
                <li>
                    <a href="./?page=personal_info" class="dropdown-item" style="font-size:13px;padding:10px 16px;border-bottom:1px solid #f1f3f5;">
                        <i class="fas fa-id-card mr-2 text-secondary" style="width:16px;"></i> My Profile
                    </a>
                </li>
                <li>
                    <a href="./?page=logout" class="dropdown-item" style="font-size:13px;padding:10px 16px;color:#dc3545;">
                        <i class="fas fa-sign-out-alt mr-2" style="width:16px;color:#dc3545;"></i> Logout
                    </a>
                </li>
            </ul>
        </li>

    </ul>
</nav>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index3.html" class="brand-link">
            <span class="brand-text font-weight-light">TellicERP</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image"></div>
                <div class="info">
                    <a href="./?page=user_profile" class="d-block"><?php echo $_SESSION['username']; ?></a>
                </div>
            </div>
            <?php include("./sidebar.php"); ?>
        </div>
    </aside>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item active">TellicERP V1</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <?php
            $page = $_GET['page'] ?? $_POST['page'] ?? 'default';
            switch ($page) {
                case"add_review_questions":
                    include "./views/360_evaluation/add_review_questions.php";
                    break;
                case"review_questions_list":
                    include "./views/360_evaluation/review_questions_list.php";
                    break;
                case"review_periods":
                    include "./views/360_evaluation/review_periods.php";
                    break;
                case"review_assignments":
                    include "./views/360_evaluation/review_assignments.php";
                    break;
                // end of 360 
                case'default':
                    include "./views/dashboard/admin.php";
                      break;
                case 'logout':
                    session_destroy();
                    echo "<script>window.location.href='./'</script>";
                    break;
                     // department
                    case"add_department":
                        include "./views/department/add_department.php";
                        break;
                    case"department_list":
                        include "./views/department/department_list.php";
                        break;
                    case"add_department_settings":
                        include "./views/department/add_department_settings.php";
                        break;
                    case"department_settings":
                        include "./views/department/add_department_settings.php";
                        break;
                    case"manage_ranks":
                    include "./views/leave/manage_ranks.php";
                    break;
                // reconciliation
                case"add_monthly_balance":
                    include "./views/reconciliation/add_monthly_balance.php";
                    break;
                case"reconcile":
                    include "./views/reconciliation/reconcile.php";
                    break;
                case"edit_monthly_closing_balance":
                    include "./views/reconciliation/edit_monthly_closing_balance.php";
                    break;
                case"preview_transactions":
                    include "./views/reconciliation/preview_transactions.php";
                    break;
   case"leave_type_list":
                    include "./views/leave/leave_type_list.php";
                    break;
                case "leave_policy":
                    include "./views/leave/leave_policy.php";
                    break;
                case"leave_policies_list":
                    include "./views/leave/leave_policies_list.php";
                    break;
                case"add_new_policy":
                    include "./views/leave/add_new_policy.php";
                    break;
                case"request_leave":
                    include "./views/leave/request_leave.php";
                    break;
                    case"leave_list":
                    include "./views/leave/leave_list.php";
                    break;
                case"initialize_balances":
                    include "./views/leave/initialize_balances.php";
                    break;
                case"pending_leave_request":
                    include "./views/leave/pending_leave_request.php";
                    break;
                case"waiting_for_director_approval":
                    include "./views/leave/director_pending_list.php";
                    break;

                case"my_leaves":
                    include "./views/leave/my_leave.php";
                    break;
                case 'attendance_dashboard':
                    include "./views/attendance/attendance_dashboard.php";
                    break;

               case"time_management_report":
                    include "./views/attendance/time_management_report.php";
                    break;

                case 'coa':
                    include "./views/core/chart_of_account.php";
                    break;

                case 'ledger':
                    include "./views/ledger/ledger_list.php";
                    break;
                case 'edit_ledger':
                    include "./views/ledger/edit_ledger.php";
                    break;

                case 'Add_masters':
                    include "./views/submaster/master_list.php";
                    break;
                case 'submaster':
                    include "./views/submaster/submaster_list.php";
                    break;

                case 'create_subsidiary':
                    include "./views/subsidiaries/create_subsidiary.php";
                    break;
                case 'add_other_subsidiary':
                    include "./views/subsidiaries/add_other_subsidiary.php";
                    break;
                case 'register_asset':
                    include "./views/subsidiaries/register_asset.php";
                    break;
                case 'register_staff':
                    include "./views/subsidiaries/register_staff_sub.php";
                    break;
                case 'register_customer':
                    include "./views/subsidiaries/register_customer.php";
                    break;
                case 'register_supplier':
                    include "./views/subsidiaries/register_supplier.php";
                    break;
                case 'register_stock':
                    include "./views/subsidiaries/register_stock.php";
                    break;
                case 'subsidiary_list':
                    include "./views/subsidiaries/subsidiary_list.php";
                    break;
                case 'edit_subsidiary':
                    $id = (int) $_GET['id'];
                    include "./views/subsidiaries/edit_subsidiary.php";
                    break;
                case 'delete_subsidiary':
                    $deleted_by = (int) $_SESSION['userid'];
                    $id = (int) $_GET['id'];
                    $conn->begin_transaction();
                    $softDeleteSub = softDeleteSubsidiary($conn, $id, $deleted_by);
                    if (!is_numeric($softDeleteSub)) {
                        $conn->rollback();
                        echo $softDeleteSub;
                        exit;
                    }
                    $deleteTransactions = softDeleteTransactionBySubId($conn, $id, $deleted_by);
                    if ($deleteTransactions !== true) {
                        $conn->rollback();
                        echo $deleteTransactions;
                        exit;
                    }
                    $conn->commit();
                    setNotification('success', 'Deleted');
                    echo "<script>window.location.href='./?page=subsidiary_list';</script>";
                    break;
                case 'restore_subsidiary':
                    $restored_by = (int) $_SESSION['userid'];
                    $id = (int) $_GET['id'];
                    $conn->begin_transaction();
                    $restoreDeletedSub = restoreDeletedSubsidiary($conn, $id, $restored_by);
                    if (!is_numeric($restoreDeletedSub)) {
                        $conn->rollback();
                        echo $restoreDeletedSub;
                        exit;
                    }
                    $restoreTransactions = restoreDeletedTransactionBySubId($conn, $id, $restored_by);
                    if ($restoreTransactions !== true) {
                        $conn->rollback();
                        echo $restoreTransactions;
                        exit;
                    }
                    $conn->commit();
                    setNotification('success', 'Restored');
                    echo "<script>window.location.href='./?page=subsidiary_list';</script>";
                    break;

                case 'vouchers':
                    include "./views/voucher/voucher.php";
                    break;
                case 'journal_voucher':
                    include "./views/voucher/journal_voucher.php";
                    break;
                case 'sales_voucher':
                    include "./views/voucher/sales_voucher.php";
                    break;
                case 'edit_sales_voucher':
                    include "./views/voucher/edit_sales_voucher.php";
                    break;
                case 'purchase_voucher':
                    include "./views/voucher/purchase_voucher.php";
                    break;
                case 'edit_purchase_voucher':
                    include "./views/voucher/edit_purchase_voucher.php";
                    break;
                case 'payment_voucher':
                    include "./views/voucher/payment_voucher.php";
                    break;
                case 'receipt_voucher':
                    include "./views/voucher/receipt_voucher.php";
                    break;
                case 'bulky_vouchers':
                    include "./views/voucher/bulky_vouchers.php";
                    break;
                case 'all_bulky_vouchers':
                    include "./views/voucher/all_bulky_vouchers.php";
                    break;
                case 'preview_bulky_voucher':
                    include "./views/voucher/preview_bulky_voucher.php";
                    break;
                case 'edit_bulky_voucher':
                    include "./views/voucher/edit_bulky_voucher.php";
                    break;
                case 'preview_voucher':
                    $id = (int) $_GET['id'];
                    include "./views/voucher/preview_voucher.php";
                    break;
                case 'preview_invoice':
                    include "./views/voucher/preview_invoice.php";
                    break;
                case 'preview_purchase_invoice':
                    include "./views/voucher/preview_purchase_invoice.php";
                    break;
                
                case 'sales_voucher_report':
                    include "./views/voucher/sales_voucher_report.php";
                    break;
                case 'sales_purchase_report':
                    include "./views/voucher/sales_purchase_report.php";
                    break;
                case 'all_voucher':
                    include "./views/voucher/all_voucher.php";
                    break;

                case 'trial_balance':
                    include "./views/financial_report/trial_balance_form.php";
                    break;
                case 'view_trial_balance':
                    include "./views/financial_report/view_trial_balance.php";
                    break;
                case 'view_trial_balance_by_ledger':
                    include "./views/financial_report/view_trial_balance_by_ledger.php";
                    break;
                case 'balance_sheet_form':
                    include "./views/financial_report/balance_sheet_form.php";
                    break;
                case 'view_balance_sheet_by_ledger':
                    include "./views/financial_report/view_balance_sheet_by_ledger.php";
                    break;
                case 'view_balance_sheet_by_subsidiaries':
                    include "./views/financial_report/view_balance_sheet_by_subsidiaries.php";
                    break;
                case 'income_statement_form':
                    include "./views/financial_report/income_statement_form.php";
                    break;
                case 'view_income_statement_by_subsidiaries':
                    include "./views/financial_report/view_income_statement_by_subsidiaries.php";
                    break;
                case 'view_income_statement_by_ledger':
                    include "./views/financial_report/view_income_statement_by_ledger.php";
                    break;
                case 'ledger_report_form':
                    include "./views/financial_report/ledger_report_form.php";
                    break;
                case 'view_ledger_report':
                    include "./views/financial_report/view_ledger_report.php";
                    break;
                case 'view_ledger_report_by_date_range':
                    include "./views/financial_report/view_ledger_report_by_date_range.php";
                    break;
                case 'subsidiary_report':
                    include "./views/financial_report/subsidiary_report_form.php";
                    break;
                case 'view_subsidiary_report':
                    include "./views/financial_report/view_subsdiary_report.php";
                    break;
                case 'view_subsidiary_report_by_date_range':
                    include "./views/financial_report/view_subsidiary_report_by_date_range.php";
                    break;

                case 'add_opening_balance':
                    include "./views/opening_balance/add_opening_balance.php";
                    break;
                case 'opening_balance_list':
                    include "./views/opening_balance/opening_balance_list.php";
                    break;
                case 'basic_salary_list':
                    include "./views/payroll/salary_list.php";
                    break;
                case 'preview_salary_receipt':
                    include "./views/payroll/preview_salary_receipt.php";
                    break;
                case 'payroll_voucher_form':
                    include "./views/payroll/pauroll_voucher_form.php";
                    break;
                case 'payroll_voucher':
                    include "./views/payroll/payroll_voucher.php";
                    break;
                case 'payrol_report_list':
                    include "./views/payroll/payroll_report_list.php";
                    break;
                case 'preview_payroll_report':
                    include "./views/payroll/preview_payroll.php";
                    break;
                case 'generate_payroll_entry':
                    include "./views/payroll/generate_entry.php";
                    break;

                case 'create_profoma':
                    include "./views/profoma/create_profoma.php";
                    break;
                case 'pending_profoma_list':
                    include "./views/profoma/pending_profoma_list.php";
                    break;
                case 'approved_profoma_list':
                    include "./views/profoma/approved_profoma_list.php";
                    break;
                case 'preview_profoma':
                    include "./views/profoma/preview_profoma.php";
                    break;

                case 'create_purchase_order':
                    include "./views/purchase_order/create_purchase_order.php";
                    break;
                case 'pending_purchase_order_list':
                    include "./views/purchase_order/pending_purchase_order_list.php";
                    break;
                case 'approved_purchase_order_list':
                    include "./views/purchase_order/approved_purchase_order_list.php";
                    break;
                case 'preview_purchase_order':
                    include "./views/purchase_order/preview_purchase_order.php";
                    break;
                case 'edit_purchase_order':
                    include "./views/purchase_order/edit_purchase_order.php";
                    break;
                case 'request_purchase':
                    include "./views/purchase_request/create_purchase_request.php";
                    break;
                case 'purchase_request_list':
                    include "./views/purchase_request/purchase_request_list.php";
                    break;
                case 'preview_purchase_request':
                    include "./views/purchase_request/preview_purchase_request.php";
                    break;

                

                case 'master_last_balance':
                    include "./views/summary/master_last_balance.php";
                    break;
                case 'ledger_report_summary':
                    include "./views/summary/ledger_last_balance.php";
                    break;
                case 'sub_report_summary':
                    include "./views/summary/subsidiary_last_balance.php";
                    break;
                case 'project_cost_report':
                    include "./views/summary/project_cost_report.php";
                    break;
                case 'consignment_income_report':
                    include "./views/summary/consignment_income_report.php";
                    break;
                case 'consignment_profit_report':
                    include "./views/summary/consignment_profit_report.php";
                    break;

                case 'warnings_letter':
                    include "./views/letters_and_contracts/warnings_letter.php";
                    break;
                case 'view_warnings':
                    include "./views/letters_and_contracts/view_warnings.php";
                    break;
                case 'contract_letter':
                    include "./views/letters_and_contracts/contract_letter.php";
                    break;
                case 'view_contracts':
                    include "./views/letters_and_contracts/view_contracts.php";
                    break;
                case 'job_offer_letter':
                    include "./views/letters_and_contracts/job_offer_letter.php";
                    break;

                case 'register_user':
                    include "./views/user/register_users.php";
                    break;
                case 'user_list':
                    include "./views/user/user_list.php";
                    break;
                 case"edit_user":
                    include "./views/user/edit_user.php";
                    break;
                case 'reset_password':
                    include "./views/user/reset_password.php";
                    break;
                case 'user_profile':
                    include "./views/user/user_profile.php";
                    break;

                case 'currency_list':
                    include "./views/utilities/currency_list.php";
                    break;
                case 'unit_list':
                    include "./views/utilities/unit_list.php";  
                    break;
                case"stock_manufacturing_orders":  
                    include("./views/stock/stock_manufacturing_orders.php");
                    break;
                case"stock_manufacturing":  
                    include("./views/stock/stock_manufacturing.php");
                    break;
                case"upload_items_form":  
                    include("./views/stock/upload_items_form.php");
                    break;
                case"stock_store_list":  
                    include("./views/stock/stock_store_list.php");
                    break;
                case"stock_unit_conversions":  
                    include("./views/stock/stock_unit_conversions.php");
                    break;
                case"stock_uploads_form":  
                    include("./views/stock/stock_uploads_form.php");
                    break;
                case"processes_stock_upload_data":  
                    include("./views/stock/processes_stock_upload_data.php");
                    break;
                case"record_stock_movement":  
                    include("./views/stock/record_stock_movement.php");
                    break;
                 
                case"preview_stock":
                    include("./views/stock/preview_stock.php");
                    break;
                      
                case"edit_gdn":
                    include("./views/stock/edit_gdn.php");
                    break;
                    
                 case"edit_grn":
                    include("./views/stock/edit_grn.php");
                    break;
                 
                  case"edit_tranfered_stocks":
                    include("./views/stock/edit_tranfered_stocks.php");
                    break;
                    
                    
                 case"stock_date_requests":
                    include("./views/utilities/stock_date_requests.php");
                    break;
                 
                   
                 
                 
                 
                case"stock_movement_list":
                    include("./views/stock/stock_movement_list.php");
                    break;
                case"stock_item_report":
                    include("./views/stock/stock_item_report.php");
                    break;
                case"stock_ledger_report":
                    include("./views/stock/stok_ledger_report.php");
                    break;
                case"stock_selling_report":
                    include("./views/stock/stock_selling_report.php");
                    break;
                case"stock_item_selling_report":
                    include("./views/stock/stock_item_selling_report.php");
                    break;
                case"stock_tb_report_form":
                    include("./views/stock/stock_tb_report_form.php");
                    break;
                case"min_sub_list":
                    include("./views/min_subs/min_sub_list.php");  
                    break;
                case"advance_cash_request":
                    include("./views/advance_cash/request_adv_cash.php");
                    break;
                case"advance_cash_request_list":
                    include("./views/advance_cash/advance_cash_request_list.php");
                    break;
                case 'approved_advance_cash_req_list':
                    include "./views/advance_cash/approved_advance_cash_req_list.php";
                    break;
                case 'preview_advance_cash_request':
                    include "./views/advance_cash/preview_advance_cash_request.php";
                    break;
                    
                 case 'edit_advance_cash_request':
                    include "./views/advance_cash/edit_advance_cash_request.php";
                    break;    
                case"my_advance_cash_request":
                    include("./views/advance_cash/my_advance_cash_request.php");
                    break;    
                case 'create_retirement':
                    include "./views/retirement/create_retirement.php";
                    break;
                case 'retirement_list':
                    include "./views/retirement/retirement_list.php";
                    break;
                case 'my_retirement_list':
                    include "./views/retirement/my_retirement_list.php";
                    break;
                case 'preview_retirement':
                    include "./views/retirement/preview_retirement.php";
                    break;
                case 'edit_retirement':
                    include "./views/retirement/edit_retirement.php";
                    break;
              
                 
            }
            ?>
        </section>
    </div>
    <footer class="main-footer">
        <strong>&copy; <?= date("Y") ?> <a href="tellicerp.co.tz">Tellicerp</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"></div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="plugins/jquery/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<script>$.widget.bridge('uibutton', $.ui.button)</script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/chart.js/Chart.min.js"></script>
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="dist/js/adminlte.js"></script>
<script src="./dist/js/functions.js"></script>
<script src="./dist/js/graphs_function.js"></script>
<script src="./dist/datatable2/datatables.js"></script>
<script src="./dist/js/html-table-search.js"></script>
<script src="./plugins/select2/js/select2.full.min.js"></script>
<script>
initializeSelect();
formatNumber();
reinitializeTableSearch();
$(document).ready(function() {
    loadVoucherBadges();
});
$(document).ready(function () {
    new DataTable('.data-table', {
        responsive: true,
        ordering: false,
        pageLength: 100,
        layout: {
            topStart: {
                buttons: ['copy', 'excel', 'pdf', 'print']
            }
        }
    });
});

// Fix Select2 inside Bootstrap modals
document.addEventListener('DOMContentLoaded', function () {

    function initSelect2InContainer(container, modalId) {
        $(container).find('.select2-form').each(function () {
            if ($(this).hasClass('select2-hidden-accessible')) {
                return;
            }
            $(this).select2({
                placeholder: "Select an option",
                dropdownParent: $(modalId)
            });
        });

        $(container).find('.select2bs4-form').each(function () {
            if ($(this).hasClass('select2-hidden-accessible')) {
                return;
            }
            $(this).select2({
                theme: 'bootstrap4',
                dropdownParent: $(modalId)
            });
        });
    }

    ['#addMinSubModal', '#editMinSubModal', '#addSubmainModal', '#editSubmainModal', '#addWithdrawalModal', '#addLedgerModal', '#editLedgerModal', '#editWithdrawalModal'].forEach(function (modalId) {
        const modalEl = document.querySelector(modalId);
        if (!modalEl) return;

        modalEl.addEventListener('shown.bs.modal', function () {
            initSelect2InContainer(modalEl, modalId);
        });

        var contentContainers = modalEl.querySelectorAll('.modal-body');
        contentContainers.forEach(function (container) {
            var observer = new MutationObserver(function (mutations) {
                var hasNewSelect = false;
                mutations.forEach(function (m) {
                    m.addedNodes.forEach(function (node) {
                        if (node.nodeType === 1 && (node.matches('select') || node.querySelector('select'))) {
                            hasNewSelect = true;
                        }
                    });
                });
                if (hasNewSelect) {
                    initSelect2InContainer(container, modalId);
                }
            });

            observer.observe(container, { childList: true, subtree: true });
        });
    });

});

</script>



<script>
// ── runs once after page fully loads ──────────────────────────────
window.addEventListener('load', function(){
    setTimeout(fetchStockDateBadge, 500);
});

// ── fetch just the COUNT for the badge ───────────────────────────
function fetchStockDateBadge(){
    $.get('./requests/notification_requests.php', { stock_date_request: 1 }, function(data){
        var count = data.stock_date_requests || 0;
        var badge = document.getElementById('stock-date-request-badge');
        var countEl = document.getElementById('stock-date-dropdown-count');
        if(count > 0){
            var label = count > 99 ? '99+' : count;
            badge.textContent  = label;
            badge.style.display = 'flex';
            countEl.textContent = label;
            countEl.style.display = 'inline-block';
        } else {
            badge.style.display   = 'none';
            countEl.style.display = 'none';
        }
    }, 'json');
}

// ── fetch FULL LIST only when dropdown is opened ──────────────────
var stockDropdownLoaded = false;
document.addEventListener('click', function(e){
    var toggle = e.target.closest('a[data-toggle="dropdown"]');
    if(!toggle) return;
    var menu = toggle.nextElementSibling;
    if(!menu || !menu.querySelector('#stock-date-list')) return;
    if(stockDropdownLoaded) return;

    $.get('./requests/notification_requests.php', { stock_date_request_list: 1 })
        .done(function(data){
            var list    = menu.querySelector('#stock-date-list');
            var loading = menu.querySelector('#stock-date-loading');
            if(!list || !loading) return;
            loading.style.display = 'none';
            list.style.display    = 'block';

            if(!data.requests || data.requests.length === 0){
                list.innerHTML = `
                <div style="padding:32px 16px;text-align:center;">
                    <i class="fas fa-check-circle" style="font-size:28px;color:#a3e4d7;"></i>
                    <div style="font-size:12px;color:#adb5bd;margin-top:8px;">All caught up — no pending requests</div>
                </div>`;
            } else {
                var html = '';
                data.requests.forEach(function(r){
                    var initial = (r.user_name || '?').trim().charAt(0).toUpperCase();
                    html += `
                    <div style="display:flex;gap:10px;padding:10px 14px;border-bottom:1px solid #f1f3f5;position:relative;transition:background .15s;"
                         onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                        <div style="position:absolute;left:0;top:0;bottom:0;width:3px;background:#17a2b8;"></div>
                        <div style="width:30px;height:30px;border-radius:50%;background:#e7f6f8;color:#17a2b8;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            ${initial}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;justify-content:space-between;align-items:baseline;gap:6px;">
                                <span style="font-size:13px;font-weight:600;color:#343a40;">${r.user_name || ''}</span>
                                <span style="font-size:9px;text-transform:uppercase;font-weight:700;color:#17a2b8;background:#e7f6f8;padding:2px 6px;border-radius:3px;letter-spacing:.5px;flex-shrink:0;">${r.transaction_type}</span>
                            </div>
                            <div style="font-size:11px;color:#adb5bd;margin-top:2px;">
                                <i class="far fa-calendar-alt mr-1"></i>${r.requested_date}
                            </div>
                            <div style="font-size:12px;color:#6c757d;margin-top:4px;line-height:1.4;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                                ${r.reason}
                            </div>
                        </div>
                    </div>`;
                });
                list.innerHTML = html;
            }
            stockDropdownLoaded = true;
        })
        .fail(function(xhr, status, err){
            console.error('[stock-date] AJAX FAILED', status, err, xhr.responseText);
        });
}, true);

// ── approve a single request inline ──────────────────────────────
function approveStockRequest(id, btn){
    btn.disabled    = true;
    btn.innerHTML   = '<i class="fas fa-spinner fa-spin"></i>';
    $.post('./requests/notification_requests.php', { approve_stock_request: 1, id: id }, function(data){
        if(data.success){
            // remove the row
            $(btn).closest('div[style*="border-bottom"]').fadeOut(300, function(){ 
                $(this).remove();
                // update badge count
                stockDropdownLoaded = false; // allow reload next open
                fetchStockDateBadge();
            });
        } else {
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-check mr-1"></i> Approve';
            alert(data.message || 'Error approving request.');
        }
    }, 'json');
}
</script>

</body>
</html>