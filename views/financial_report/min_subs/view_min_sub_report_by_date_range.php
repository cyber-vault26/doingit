<?php
// view_min_sub_report_by_date_range.php

$min_sub_id = (int) $_GET['id'];
$date1 = $_GET['date1'];
$date2 = $_GET['date2'];

$sub_details = selectMinSubsidiaryById($conn, $min_sub_id);
$name = $sub_details['name'] ?? 'Unknown';
?>
<div class="card m-2 card-outline card-info">
  <div class="card-header">
    <h4 class="card-title">Min Subsidiary: <?php echo htmlspecialchars($name); ?></h4>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover table-bordered data-table-basic data-table">
        <thead class="table-info">
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Details</th>
            <th>Narration</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Balance</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $balance = 0;
          $counter = 1;
          $total_debit = 0;
          $total_credit = 0;
          
          $transactions = selectAllSubTransactionByMinSubIdAndDateRange($conn, $min_sub_id, $date1, $date2);
          
          if($transactions && count($transactions) > 0){
              foreach($transactions as $transaction){
                  $date = date("d-M-Y", strtotime($transaction['date_']));
                  $debit = $credit = 0;
                  $details = '';
                  $narration = htmlspecialchars(trim($transaction['description'] ?? ''));
                  
                  if($transaction['dr_account'] == $min_sub_id){
                      $debit = floatval(str_replace(",", "", $transaction['amount']));
                      $corresponding = selectMinSubsidiaryById($conn, $transaction['cr_account']);
                      $details = "From " . htmlspecialchars($corresponding['name'] ?? 'Unknown');
                      $total_debit += $debit;
                      $balance += $debit;
                  } elseif($transaction['cr_account'] == $min_sub_id){
                      $credit = floatval(str_replace(",", "", $transaction['amount']));
                      $corresponding = selectMinSubsidiaryById($conn, $transaction['dr_account']);
                      $details = "To " . htmlspecialchars($corresponding['name'] ?? 'Unknown');
                      $total_credit += $credit;
                      $balance -= $credit;
                  }
                  
                  echo "<tr>
                    <td>" . $counter++ . "</td>
                    <td>$date</td>
                    <td>$details</td>
                    <td>$narration</td>
                    <td>" . ($debit > 0 ? number_format($debit, 2) : '0.00') . "</td>
                    <td>" . ($credit > 0 ? number_format($credit, 2) : '0.00') . "</td>
                    <td>" . number_format($balance, 2) . "</td>
                  </tr>";
              }
              
              echo "<tr class='table-danger font-weight-bold'>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td><strong>Closing Balance</strong></td>
                  <td><strong>" . number_format($total_debit, 2) . "</strong></td>
                  <td><strong>" . number_format($total_credit, 2) . "</strong></td>
                  <td><strong>" . number_format($balance, 2) . "</strong></td>
              </tr>";
          } else {
              //echo "<tr><td colspan='7' class='text-center'>No records found.</td></tr>";
          }
          ?>
        </tbody>
        <tfoot class="table-info">
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Details</th>
            <th>Narration</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Balance</th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>