<?php
$sub_id = (int) $conn->real_escape_string($_GET['sub_id']);
$date1 = $_GET['date1'];
$date2 = $_GET['date2'];
$sub_details = selectSubsidiaryById($conn, $sub_id);
$name = $sub_details['name'] ?? 'Unknown';
?>
<div class="card m-2 card-outline card-info">
  <div class="card-header">
    <h4 class="card-title">Min Ledger Report by Date Range - Subsidiary: <?php echo htmlspecialchars($name); ?></h4>
    <p class="mb-0">Period: <?php echo date("d-M-Y", strtotime($date1)) . " to " . date("d-M-Y", strtotime($date2)); ?></p>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover table-bordered data-table-basic data-table">
        <thead class="table-info">
          <tr>
            <th>#</th>
            <th>Min Subsidiary Account</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Balance</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $grand_balance = 0;
          $grand_credit = 0;
          $grand_debit = 0;
          $counter = 1;
          $minor_subs = selectMinSubsidiariesByParentId($conn, $sub_id);
          if($minor_subs && count($minor_subs) > 0){
              foreach($minor_subs as $minor_sub){
                  $minBalance = 0;
                  $minCredit = 0;
                  $minDebit = 0;
                  $subTransactions = selectAllSubTransactionByMinSubIdAndDateRange($conn, (int)$minor_sub['id'], $date1, $date2);
                  if($subTransactions && is_array($subTransactions)){
                      foreach($subTransactions as $transaction){
                          if($transaction['cr_account'] == $minor_sub['id']){
                              $minBalance -= $transaction['amount'];
                              $minCredit += $transaction['amount'];
                          } elseif($transaction['dr_account'] == $minor_sub['id']){
                              $minBalance += $transaction['amount'];
                              $minDebit += $transaction['amount'];
                          }
                      }
                  }
                  $grand_balance += $minBalance;
                  $grand_credit += $minCredit;
                  $grand_debit += $minDebit;
                  echo "<tr>
                      <td>" . $counter++ . "</td>
                      <td>" . htmlspecialchars($minor_sub['name']) . "</td>
                      <td>" . number_format($minDebit, 2) . "</td>
                      <td>" . number_format($minCredit, 2) . "</td>
                      <td>" . number_format($minBalance, 2) . "</td>
                  </tr>";
              }
              echo "<tr class='table-danger font-weight-bold'>
                  <td></td>
                  <td><strong>Grand Total</strong></td>
                  <td><strong>" . number_format($grand_debit, 2) . "</strong></td>
                  <td><strong>" . number_format($grand_credit, 2) . "</strong></td>
                  <td><strong>" . number_format($grand_balance, 2) . "</strong></td>
              </tr>";
          }
          ?>
        </tbody>
        <tfoot class="table-info">
          <tr>
            <th>#</th>
            <th>Min Subsidiary Account</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Balance</th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>