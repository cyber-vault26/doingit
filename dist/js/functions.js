function formatNumber(){
  const $inputs = $('.format-number');
  function doFormat(){
    $inputs.each(function(){
      let $el = $(this);
      let val = $el.val().replace(/,/g, '');
      if(val !== '' && !isNaN(val)){
        let parts = val.split('.');
        parts[0] = Number(parts[0]).toLocaleString();
        $el.val(parts.join('.'));
      }
    });
  }
  $inputs.on('input change', function(){
    doFormat();
  });
  const observer = new MutationObserver(doFormat);
  $inputs.each(function(){
    observer.observe(this, { attributes: true, attributeFilter: ['value'] });
  });
}

function addTableRow(tableId) {
    let table = $("#" + tableId);
    let tbody = table.find("tbody");
    if (tbody.length === 0) {
        table.append("<tbody></tbody>");
        tbody = table.find("tbody");
    }
    let newRow = tbody.find("tr.data-row:first").clone();
    newRow.find("span.select2").remove();
    newRow.find("select").removeClass('select2-hidden-accessible').show().val("");
    newRow.find("input").val("");
    tbody.append(newRow);
    initializeSelect();
    formatNumber();
}
function removeTableRow(tableId) {
    let table = $("#" + tableId);
    let rowCount = table.find("tbody tr").length;
    if(rowCount > 1){
        table.find("tbody tr:last").remove();
    }
}


function reinitializeTableSearch(){
  $('table.table-search').tableSearch({
            searchText:'Search here',
            searchPlaceHolder:'Input Value'
        });
} 


function calculateEqv1() {
    var currValue = document.getElementById("curr_value").value.replace(/,/g, '');
    var currency = parseFloat(currValue) || 0;
    var amounts = document.getElementsByName("amount[]");
    var eqvs = document.getElementsByName("eqv[]");
    for(var i = 0; i < amounts.length; i++){
        var amountValue = amounts[i].value.replace(/,/g, '');
        var result = currency * (parseFloat(amountValue) || 0);
        eqvs[i].value = result.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}

  

 function calculateEqv(){
    console.log("am running");
    var currency = parseFloat($("input[name='curr']").val().replace(/,/g, '')) || 0;  
    console.log(currency);
    var amounts = $(".debt_amount"); 
    var eqvs = $(".equiv_dr"); 
    amounts.each(function(index){
        var amountValue = $(this).val().replace(/,/g, '');  
        var result = currency * (parseFloat(amountValue) || 0);
        $(eqvs[index]).val(result.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    });
}
 
 
function exportTableToExcel(table, sheetName, fileName) {
    if (!table) {
      alert('No table found to export!');
      return;
    }
    var wb = XLSX.utils.book_new();
    var ws = XLSX.utils.table_to_sheet(table);
    var today = new Date();
    var dateStr = today.getFullYear() + '-' + 
                  String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                  String(today.getDate()).padStart(2, '0');
    var fullFileName = fileName + '_' + dateStr + '.xlsx';
    XLSX.utils.book_append_sheet(wb, ws, sheetName);
    XLSX.writeFile(wb, fullFileName);
}

 
function selectCurrency(str) {
    if(!str) return;
    $.get('./processes/api/currency.php?id=' + str, function(response) {
        $('[id^="currency"]').html(response);
        formatNumber();
        calculateEqv1();  
    });
}
 
function initializeSelect(){
  

         $(".select2-form").select2({
                       placeholder: "Select an option",
                             });
            $('.select2bs4-form').select2({
      theme: 'bootstrap4'
    })
         


}


 
function calculatePayee(amount) {
  //  console.log(amount);
    var payee = 0;
 if (amount <= 270000) {
      payee = 0;
    } else if (amount > 270000 && amount <= 520000) {
      payee = 0.08 * (amount - 270000);
    } else if (amount > 520000 && amount <= 760000) {
      payee = 20000 + 0.20 * (amount - 520000);
    } else if (amount > 760000 && amount <= 1000000) {
      payee = 68000 + 0.25 * (amount - 760000);
    } else if (amount > 1000000) {
      payee = 128000 + 0.30 * (amount - 1000000);
    }
    return payee;
  }


function showSpinner() {
  $('#spinner-overlay').css('display', 'flex');
}

function hideSpinner() {
  $('#spinner-overlay').hide();
}


function downloadPDF(filename) {
    var logo = document.querySelector('.v-logo img, .invoice-header-logo img, .pv-logo img');
    var prev = logo ? logo.getAttribute('style') : '';
    if(logo) logo.style.cssText = 'width:100px; height:80px; object-fit:contain;';
    var element = document.querySelector('.card-body');
    var opt = {
        margin: 10,
        filename: filename,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(element).save().then(function() {
        if(logo) logo.style.cssText = prev || '';
    });
}
 
function loadVoucherBadges() {
    $.get('./requests/notification_requests.php', { voucher_notification_badge: 1 }, function(data) {
        function setBadge(id, count) {
            if(count > 0) $('#' + id).text(count > 99 ? '99+' : count);
        }
        var tvDraft    = data.transactions.draft    + data.sales.draft    + data.purchases.draft;
        var tvPending  = data.transactions.pending  + data.sales.pending  + data.purchases.pending;
        var tvRejected = data.transactions.rejected + data.sales.rejected + data.purchases.rejected;
        setBadge('badge-tv-draft',    tvDraft);
        setBadge('badge-tv-pending',  tvPending);
        setBadge('badge-tv-rejected', tvRejected);
        var tvTotal = tvDraft + tvPending + tvRejected;
        if(tvTotal > 0) $('#badge-tv-total').text(tvTotal > 99 ? '99+' : tvTotal);
        setBadge('badge-bv-draft',    data.bulky.draft);
        setBadge('badge-bv-pending',  data.bulky.pending);
        setBadge('badge-bv-rejected', data.bulky.rejected);
        var bvTotal = data.bulky.draft + data.bulky.pending + data.bulky.rejected;
        if(bvTotal > 0) $('#badge-bv-total').text(bvTotal > 99 ? '99+' : bvTotal);
        setBadge('badge-stock-pending',  data.stock.pending);
        setBadge('badge-stock-reviewed', data.stock.reviewed);
        setBadge('badge-stock-rejected', data.stock.rejected);
        var stockTotal = data.stock.pending + data.stock.reviewed + data.stock.rejected;
        if(stockTotal > 0) $('#badge-stock-total').text(stockTotal > 99 ? '99+' : stockTotal);
    }, 'json').fail(function(xhr) {
        console.log('Badge request failed:', xhr.status, xhr.responseText);
    });
}
 function toggleAll(checkbox){
                $('.row-select').prop('checked', checkbox.checked).each(function(){ toggleRow(this); });
            }
            function toggleRow(checkbox){
                var row = $(checkbox).closest('tr');
                $(checkbox).is(':checked') ? row.addClass('table-danger') : row.removeClass('table-danger');
                $('.bulk-actions').toggle($('.row-select:checked').length > 0);
            }
            function bulkAction(action){
                var selected_ids = $('.row-select:checked').map(function(){ return this.value; }).get();
                if(!selected_ids.length || !confirm('Are you sure you want to ' + action + ' selected items?')) return;
                var formData = selected_ids.map(function(id){ return 'selected_ids[]=' + encodeURIComponent(id); }).join('&') + '&' + action + '=1';
                $.ajax({
                    url: './controllers/bulk_action_controller.php',
                    type: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    data: formData,
                    success: function(response){
                        if(response.trim() == 'SUCCESS'){
                            $('.row-select:checked').prop('checked', false);
                            $('.select-all').prop('checked', false);
                            fetchVoucherByType('sales');
                        } else {
                            alert('Error: ' + response);
                        }
                    }
                });
            }
            function bulkProjectBudgetAction(action){
                var selected_ids = $('.row-select:checked').map(function(){ return this.value; }).get();
                if(!selected_ids.length || !confirm('Are you sure you want to ' + action + ' selected items?')) return;
                var formData = selected_ids.map(function(id){ return 'selected_ids[]=' + encodeURIComponent(id); }).join('&') + '&' + action + '=1';
                console.log(formData);
                $.ajax({
                    url: './controllers/bulk_action_controller.php',
                    type: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    data: formData,
                    success: function(response){
                        if(response.trim() == 'SUCCESS'){
                            $('.row-select:checked').prop('checked', false);
                            $('.select-all').prop('checked', false);
                            // fetchVoucherByType('sales');
                            window.location.reload();
                        } else {
                            alert('Error: ' + response);
                        }
                    }
                });
            }
 
function stockSetBaseQty(qtyEl, unitSel, baseQtyEl) {
    qtyEl     = $(qtyEl);
    unitSel   = $(unitSel);
    baseQtyEl = $(baseQtyEl);

    var qty = parseFloat(qtyEl.val().replace(/,/g,'').replace(/[^0-9.]/g,'')) || 0;
    var factor   = parseFloat(unitSel.find('option:selected').data('factor')) || 1;
    var baseQty  = qty * factor;
    var balance  = parseFloat(unitSel.attr('data-balance'));
    var unitName = unitSel.find('option:selected').text().split('(')[0].trim(); // remove "(X left)" part

    if (isNaN(balance)) {
        qtyEl.val('');
        baseQtyEl.val('');
        Swal.fire({ icon: 'warning', title: 'Select a Store First!', text: 'Please select the store before entering quantity.', confirmButtonColor: '#f39c12' });
        return;
    }

    if (qty > 0 && baseQty > balance) {
    var maxInSelectedUnit = (balance / factor).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    var maxInBaseUnit     = balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    var baseUnitName      = unitSel.find('option:first').text().split('(')[0].trim(); // always the base unit name

    qtyEl.css({ 'border-color': '#e74c3c', 'background-color': '#fff5f5' }).val('');
    baseQtyEl.val('');

    Swal.fire({
        icon: 'error',
        title: 'Insufficient Stock!',
        html:
            'You typed <b>' + qty.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ' + unitName + '</b><br><br>' +
            'Available: <b style="color:green;">' + maxInBaseUnit     + ' ' + baseUnitName + '</b><br>' +
            'You can issue only: <b style="color:orange;">' + maxInSelectedUnit + ' ' + unitName + '</b>',
        confirmButtonColor: '#d33'
    });
    return;
}

    qtyEl.css({ 'border-color': '', 'background-color': '' });
    var parts = baseQty.toFixed(4).split('.');
    parts[0]  = Number(parts[0]).toLocaleString();
    baseQtyEl.val(parts.join('.'));
}
  
 
  
  