<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <li class="nav-item"><a href="./" class="nav-link"><i class="nav-icon fas fa-th"></i><p>Home</p></a></li>


        <!-- <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">-->
        <!--    <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Manage Attendance<i class="right fas fa-angle-left"></i></p></a>-->
        <!--    <ul class="nav nav-treeview">-->
        <!--        <li class="nav-item"><a href="./?page=attendance_dashboard" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Attendance Summary</p></a></li>-->
        <!--        <li class="nav-item "><a href="./?page=time_management_report" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Time Management Report</p></a></li>-->
        <!--        <li class="nav-item d-none"><a href="./?page=ledger" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Ledger</p></a></li>-->
        <!--        <li class="nav-item d-none"><a href="./?page=create_subsidiary" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Create Subsidiaries</p></a></li>-->
        <!--        <li class="nav-item d-none"><a href="./?page=subsidiary_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Subsidiaries List</p></a></li>-->
        <!--        <li class="nav-item d-none"><a href="./?page=min_sub_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Min Sub List</p></a></li>-->
        <!--        <li class="nav-item d-none"><a href="./?page=coa" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Chart of Account</p></a></li>-->
        <!--    </ul>-->
        <!--</li>-->

        <li class="nav-header<?= hideItem($usertype, $userlevel, 'Manager') ?>">Accounting Menu</li>

        <li class="nav-item<?= hideItem($usertype, $userlevel, 'Manager') ?>">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Account Creations<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>"><a href="./?page=Add_masters" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Create Masters</p></a></li>
                <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>"><a href="./?page=submaster" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Submaster</p></a></li>
                <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>"><a href="./?page=ledger" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Ledger</p></a></li>
                <li class="nav-item"><a href="./?page=create_subsidiary" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Create Subsidiaries</p></a></li>
                <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>"><a href="./?page=subsidiary_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Subsidiaries List</p></a></li>
                <li class="nav-item"><a href="./?page=min_sub_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Min Sub List</p></a></li>
                <li class="nav-item"><a href="./?page=coa" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Chart of Account</p></a></li>
            </ul>
        </li>

        <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
            <a href="#" class="nav-link"><i class="nav-icon fa fa-balance-scale"></i><p>Opening Balances<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=add_opening_balance" class="nav-link"><i class="fa fa-plus nav-icon"></i><p>Add Opening Balance</p></a></li>
                <li class="nav-item"><a href="./?page=opening_balance_list" class="nav-link"><i class="fa fa-list nav-icon"></i><p>Opening Balance List</p></a></li>
            </ul>
        </li>

        <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Vouchers<i class="right fas fa-angle-left"></i><span id="badge-tv-total" class="badge badge-info right"></span></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=vouchers" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Create Voucher</p></a></li>
                <li class="nav-item"><a href="./?page=all_voucher&status=draft" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Review Vouchers</p><span id="badge-tv-draft" class="badge badge-warning right"></span></a></li>
                <li class="nav-item<?= hideItem($usertype, $userlevel, 'accountant', 2) ?>"><a href="./?page=all_voucher&status=pending" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pending Vouchers</p><span id="badge-tv-pending" class="badge badge-info right"></span></a></li>
                <li class="nav-item"><a href="./?page=all_voucher&status=active" class="nav-link"><i class="far fa-circle nav-icon"></i><p>All Vouchers</p></a></li>
                <li class="nav-item"><a href="./?page=all_voucher&status=rejected" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Rejected Vouchers</p><span id="badge-tv-rejected" class="badge badge-danger right"></span></a></li>
            </ul>
        </li> 
        <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Bulky Vouchers<i class="right fas fa-angle-left"></i><span id="badge-bv-total" class="badge badge-info right"></span></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=bulky_vouchers" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Create Bulky Voucher</p></a></li>
                <li class="nav-item"><a href="./?page=all_bulky_vouchers&status=draft" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Review Bulky Voucher</p><span id="badge-bv-draft" class="badge badge-warning right"></span></a></li>
                <li class="nav-item<?= hideItem($usertype, $userlevel, 'accountant', 2) ?>"><a href="./?page=all_bulky_vouchers&status=pending" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pending Bulky Voucher</p><span id="badge-bv-pending" class="badge badge-info right"></span></a></li>
                <li class="nav-item"><a href="./?page=all_bulky_vouchers&status=active" class="nav-link"><i class="far fa-circle nav-icon"></i><p>All Bulky Voucher</p></a></li>
                <li class="nav-item"><a href="./?page=all_bulky_vouchers&status=rejected" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Rejected Bulky Voucher</p><span id="badge-bv-rejected" class="badge badge-danger right"></span></a></li>
            </ul>
        </li>

        <li class="nav-header<?= hideItem($usertype, $userlevel, 'staff') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">Accounting Report</li>

        <li class="nav-item  <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
            <a href="#" class="nav-link"><i class="nav-icon fa fa-balance-scale"></i><p>Voucher Report<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=sales_voucher_report" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Sales Voucher Report</p></a></li>
                <li class="nav-item"><a href="./?page=sales_purchase_report" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Sales Purchase Report</p></a></li>
            </ul>
        </li>

        <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Financial Reports<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=subsidiary_report" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Account Statement</p></a></li>
                <li class="nav-item"><a href="./?page=ledger_report_form" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Ledger Report</p></a></li>
                <li class="nav-item"><a href="./?page=balance_sheet_form" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Balance Sheets</p></a></li>
                <li class="nav-item"><a href="./?page=income_statement_form" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Income Statements</p></a></li>
                <li class="nav-item"><a href="./?page=trial_balance" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Trial Balances</p></a></li>
            </ul>
        </li>

        <li class="nav-item  <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Reconciliation<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=add_monthly_balance" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Add Bank Balance</p></a></li>
                <li class="nav-item"><a href="./?page=reconcile" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Reconcile</p></a></li>
            </ul>
        </li>
        <li class="nav-header  <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">Procurement Menu</li>

        <!--<li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">-->
        <!--    <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Adv Cash Management<i class="right fas fa-angle-left"></i></p></a>  -->
        <!--    <ul class="nav nav-treeview">-->
        <!--        <li class="nav-item"><a href="./?page=advance_cash_request" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Create Adv Cash Request</p></a></li>-->
        <!--        <li class="nav-item"><a href="./?page=advance_cash_request_list&status=pending" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pending ADV Request</p></a></li>-->
        <!--        <li class="nav-item"><a href="./?page=advance_cash_request_list&status=approved" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Approved ADV Cash </p></a></li>-->
        <!--        <li class="nav-item"><a href="./?page=advance_cash_request_list&status=paid"class="nav-link"><i class="far fa-circle nav-icon"></i><p>Paid ADV Cash </p></a></li>-->
        <!--        <li class="nav-item"><a href="./?page=advance_cash_request_list&status=rejected" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Rejected ADV Cash</p></a></li>-->
        <!--        <li class="nav-item"><a href="./?page=my_advance_cash_request" class="nav-link"><i class="far fa-circle nav-icon"></i><p>My ADV Cash Request</p></a></li>-->
        <!--        <li class="nav-item"><a href="./?page=advance_cash_req_report" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Advance Cash Report</p></a></li>-->
        <!--    </ul>-->
        <!--</li>-->

        <!--<li class="nav-item  <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">-->
        <!--    <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Retirement Management<i class="right fas fa-angle-left"></i></p></a>-->
        <!--    <ul class="nav nav-treeview">-->
                <!--<li class="nav-item"><a href="./?page=create_retirement" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Create Retirement</p></a></li>-->
        <!--        <li class="nav-item"><a href="./?page=retirement_list&status=pending" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pending Retirement List</p></a></li>-->
        <!--        <li class="nav-item"><a href="./?page=retirement_list&status=approved" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Approved Retirement List</p></a></li>-->
        <!--        <li class="nav-item"><a href="./?page=retirement_list&status=rejected" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Rejected Retirement List</p></a></li>-->
        <!--        <li class="nav-item"><a href="./?page=my_retirement_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>My Retirement List</p></a></li>-->
        <!--    </ul>-->
        <!--</li>-->

        <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Profoma<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=create_profoma" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Create Profoma</p></a></li>
                <li class="nav-item"><a href="./?page=pending_profoma_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pending Profoma List</p></a></li>
                <li class="nav-item"><a href="./?page=approved_profoma_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Approved Profoma List</p></a></li>
            </ul>
        </li>

        <!--<li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">-->
        <!--    <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Purchase Orders<i class="right fas fa-angle-left"></i></p></a>-->
        <!--    <ul class="nav nav-treeview">-->
        <!--        <li class="nav-item"><a href="./?page=create_purchase_order" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Create PO</p></a></li>-->
        <!--        <li class="nav-item"><a href="./?page=pending_purchase_order_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pending PO List</p></a></li>-->
        <!--        <li class="nav-item"><a href="./?page=approved_purchase_order_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Approved PO List</p></a></li>-->
        <!--    </ul>-->
        <!--</li>-->
        
        <!--<li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">-->
        <!--    <a href="#" class="nav-link"><i class="nav-icon fas fa-clipboard-list"></i><p>Purchase Request<i class="right fas fa-angle-left"></i></p></a>-->
        <!--    <ul class="nav nav-treeview">-->
        <!--        <li class="nav-item"><a href="./?page=request_purchase" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Create Purchase Request</p></a></li>-->
        <!--        <li class="nav-item"><a href="./?page=purchase_request_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Purchase Request List</p></a></li>-->
        <!--    </ul>-->
        <!--</li>-->
        
        <!--<li class="nav-header <?= hideItem($usertype, $userlevel, 'staff') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?>">Manufacturing</li>-->
        
        <!-- <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?>">-->
        <!--  <a href="#" class="nav-link" data-menu="stock_store"> -->
        <!--    <i class="nav-icon fas fa-tachometer-alt"></i><p>Stock Manufacturing<i class="right fas fa-angle-left"></i></p>  -->
        <!--  </a>-->
        <!--  <ul class="nav nav-treeview <?= hideItem($usertype, $userlevel, 'staff') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?>">-->
        <!--    <li class="nav-item"><a href="./?page=stock_manufacturing_orders" class="nav-link" data-page="stock_manufacturing" data-menu="stock_manufacturing"><i class="far fa-circle nav-icon"></i><p>Order List</p></a></li>-->
        <!--    <li class="nav-item"><a href="./?page=stock_manufacturing" class="nav-link" data-page="stock_manufacturing" data-menu="stock_manufacturing"><i class="far fa-circle nav-icon"></i><p>Stock Manufacturing</p></a></li>-->
           
        <!--  </ul>-->
        <!--</li> -->
        
        <!--<li class="nav-item">-->
        <!--  <a href="#" class="nav-link" data-menu="stock_store"> -->
        <!--    <i class="nav-icon fas fa-tachometer-alt"></i><p>Item Names<i class="right fas fa-angle-left"></i></p>  -->
        <!--  </a>-->
        <!--  <ul class="nav nav-treeview">-->
        <!--    <li class="nav-item"><a href="./?page=upload_items_form" class="nav-link" data-page="stock_manufacturing" data-menu="stock_manufacturing"><i class="far fa-circle nav-icon"></i><p>Order List</p></a></li>-->
            <!--<li class="nav-item"><a href="./?page=stock_manufacturing" class="nav-link" data-page="stock_manufacturing" data-menu="stock_manufacturing"><i class="far fa-circle nav-icon"></i><p>Stock Manufacturing</p></a></li>-->
           
        <!--  </ul>-->
        <!--</li> -->
        
         
        
        
    
        
        
         
        
        <li class="nav-header">Stock Items</li>
        
         <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
          <a href="#" class="nav-link" data-menu="stock_store"> 
            <i class="nav-icon fas fa-tachometer-alt"></i><p>Stock Store<i class="right fas fa-angle-left"></i></p>  
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item"><a href="./?page=stock_store_list" class="nav-link" data-page="stock_store_list" data-menu="stock_store"><i class="far fa-circle nav-icon"></i><p>Store List</p></a></li>
          </ul>
        </li> 
        
        <li class="nav-item">
          <a href="#" class="nav-link" data-menu="stock_unit_conversions"> 
            <i class="nav-icon fas fa-tachometer-alt"></i><p>Stock Unit Conversions<i class="right fas fa-angle-left"></i></p>  
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item"><a href="./?page=stock_unit_conversions" class="nav-link" data-page="stock_unit_conversions" data-menu="stock_unit_conversions"><i class="far fa-circle nav-icon"></i><p> Unit Conversions</p></a></li>
          </ul>
        </li>  
        
        
        <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
          <a href="#" class="nav-link" data-menu="stock_uploads"> 
            <i class="nav-icon fas fa-tachometer-alt"></i><p>Stock Upload<i class="right fas fa-angle-left"></i></p>  
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item"><a href="./?page=stock_uploads_form" class="nav-link" data-page="stock_uploads_form" data-menu="stock_uploads"><i class="far fa-circle nav-icon"></i><p>Upload Stock</p></a></li>
          </ul>
        </li> 
        
        <li class="nav-item">
          <a href="#" class="nav-link" data-menu="stock_management"> 
            <i class="nav-icon fas fa-tachometer-alt"></i><p>Stock Management<i class="right fas fa-angle-left"></i><span id="badge-stock-total" class="badge badge-info right"></span></p>  
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item"><a href="./?page=record_stock_movement" class="nav-link" data-page="record_stock_movement" data-menu="stock_management"><i class="far fa-circle nav-icon"></i><p>Record Stock Movement</p></a></li>
            <li class="nav-item"><a href="./?page=stock_movement_list&status=pending" class="nav-link" data-page="stock_movement_list" data-menu="stock_management"><i class="far fa-circle nav-icon"></i><p>Pending Stock</p><span id="badge-stock-pending" class="badge badge-warning right"></span></a></li>
            <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>"><a href="./?page=stock_movement_list&status=reviewed" class="nav-link" data-page="stock_movement_list" data-menu="stock_management"><i class="far fa-circle nav-icon"></i><p>Review Stock</p><span id="badge-stock-reviewed" class="badge badge-info right"></span></a></li>
            <li class="nav-item"><a href="./?page=stock_movement_list&status=approved" class="nav-link" data-page="stock_movement_list" data-menu="stock_management"><i class="far fa-circle nav-icon"></i><p>GRN & GDN List</p></a></li>
            <li class="nav-item"><a href="./?page=stock_movement_list&status=rejected" class="nav-link" data-page="stock_movement_list" data-menu="stock_management"><i class="far fa-circle nav-icon"></i><p>Rejected Stock</p><span id="badge-stock-rejected" class="badge badge-danger right"></span></a></li>
          </ul>
        </li>
        
        <li class="nav-item">
          <a href="#" class="nav-link" data-menu="stock_report">
            <i class="nav-icon fas fa-tachometer-alt"></i><p>Stock Report<i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item"><a href="./?page=stock_item_report" class="nav-link" data-page="stock_item_report_form" data-menu="stock_report"><i class="far fa-circle nav-icon"></i><p>Stock Item Report</p></a></li>
            <li class="nav-item"><a href="./?page=stock_ledger_report" class="nav-link" data-page="stock_ledger_report_form" data-menu="stock_report"><i class="far fa-circle nav-icon"></i><p>Stock Ledger Report</p></a></li>
             <li class="nav-item"><a href="./?page=stock_item_selling_report" class="nav-link" data-page="stock_item_selling_report" data-menu="stock_report"><i class="far fa-circle nav-icon"></i><p>Stock Item Sell Report</p></a></li>
            <li class="nav-item"><a href="./?page=stock_selling_report" class="nav-link" data-page="stock_selling_report" data-menu="stock_report"><i class="far fa-circle nav-icon"></i><p>Stock Sell Ledger Report</p></a></li>
          </ul>
        </li>
    

        <li class="nav-header ">HR Menu</li>
            <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Department<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=add_department" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Add Department</p></a></li>
                <li class="nav-item"><a href="./?page=department_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Department List</p></a></li>
                <li class="nav-item"><a href="./?page=manage_ranks" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Manage Ranks</p></a></li>
                
                
            </ul>
        </li>
        <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Payroll Management<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=add_basic_salary" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Add Basic Salary</p></a></li>
                <li class="nav-item"><a href="./?page=basic_salary_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Basic Salary List</p></a></li>
                <li class="nav-item"><a href="./?page=payroll_voucher_form" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Payroll Voucher</p></a></li>
                <li class="nav-item"><a href="./?page=payrol_report_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Payroll Report</p></a></li>
            </ul>
        </li>

        <li class="nav-item ">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Leave Management<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=request_leave" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Request Leave</p></a></li>
                <li class="nav-item"><a href="./?page=leave_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Leave List</p></a></li>
                <li class="nav-item"><a href="./?page=my_leaves" class="nav-link"><i class="far fa-circle nav-icon"></i><p>My Leaves</p></a></li>
                <li class="nav-item"><a href="./?page=pending_leave_request" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pending Leave Request</p></a></li>
                <li class="nav-item"><a href="./?page=waiting_for_director_approval" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Waiting for Director Approval</p></a></li>
                 
              <li class="nav-item"><a href="./?page=leave_type_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Leave types</p></a></li>
                
                <li class="nav-item"><a href="./?page=leave_policies_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Leave Policies</p></a></li>
                
                <li class="nav-item"><a href="./?page=leave_reports" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Leave Reports</p></a></li>
                <li class="nav-item"><a href="./?page=leave_summary_reports" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Leave Summary Reports</p></a></li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-clipboard"></i><p>360 Performance<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item d-none"><a href="./?page=create_scale_questions" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Create Scale Questions</p></a></li>
                <li class="nav-item d-none"><a href="./?page=scale_questions_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Scale Questions List</p></a></li>
                <li class="nav-item"><a href="./?page=add_review_questions" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Add Review Questions</p></a></li>
                <li class="nav-item"><a href="./?page=review_questions_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Review Questions List</p></a></li>
                <li class="nav-item"><a href="./?page=review_periods" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Review Periods</p></a></li>
                <li class="nav-item"><a href="./?page=review_assignments" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Review Assignments</p></a></li>
                <li class="nav-item"><a href="./?page=360_perfomance" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Performance Evaluation</p></a></li>
                <li class="nav-item"><a href="./?page=360_perfomance_reports" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Reports</p></a></li>
                <li class="nav-item"><a href="./?page=360_perfomance_summary_reports" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Summary Reports</p></a></li>
            </ul>
        </li>

        

        <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Staff Management<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=register_user" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Register New Staff</p></a></li>
                <li class="nav-item"><a href="./?page=user_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Staff List</p></a></li>
                <li class="nav-item"><a href="./?page=staff_center" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Staff Center</p></a></li>
            </ul>
        </li>

         

        

        <li class="nav-header">Utility Items</li>

        <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Currency Settings<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=currency_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Register Currency</p></a></li>
              
            </ul>
        </li>
        
        <li class="nav-item">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Unit Settings<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=unit_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Register Units</p></a></li>
              
            </ul>
        </li>

        <li class="nav-item <?= hideItem($usertype, $userlevel, 'staff') ?><?= hideItem($usertype, $userlevel, 'Manager') ?> <?= hideItem($usertype, $userlevel, 'shop-keeper') ?> <?= hideItem($usertype, $userlevel, 'mamanufacturing') ?>">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>System Settings<i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
                <li class="nav-item"><a href="./?page=register_user" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Register System User</p></a></li>
                <li class="nav-item"><a href="./?page=user_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>User List</p></a></li>
                <li class="nav-item"><a href="./?page=reset_password" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Reset Password</p></a></li>
                 
            </ul>
        </li>

    </ul>
</nav>

 