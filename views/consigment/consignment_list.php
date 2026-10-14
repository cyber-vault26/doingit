<div class=" card card-info card-outline">
 <div class=" card-header">
        <h4>Consignment List</h4>
 </div>

 <div class=" card-footer">
            <div class="btn-group mb-3" id="tab-buttons">
                <button class="btn btn-outline-primary active" data-target="#all">All</button>
                <button class="btn btn-outline-warning" data-target="#pending">Pending</button>
                <button class="btn btn-outline-success" data-target="#Approved">Approved</button>
                <button class="btn btn-outline-dark" data-target="#closed">Closed</button>
                <button class="btn btn-outline-danger" data-target="#trashed">Trashed</button>
               
            </div>

            <div class=" card-body">
                 <div class="tab-content">
                        <div class="tab-pane active" id="all">
                            <!-- apply fliter -->
                             <div class=" container">
                                    <h3>Apply filter</h3>
                                    <div class=" row">
                                        <div class=" col-4">
                                            <label for="">Start Date</label>
                                            <input type="date" name="" class=" form-control all-date1" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-01") ?>" id="">
                                        </div>
                                        <div class=" col-4">
                                            <label for="">End Date</label>
                                            <input type="date" name="" class=" form-control all-date2" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>" id="">
                                        </div>
                                        <div class=" col-4 mt-4">
                                            <button class=" btn btn-info btn-sm" onclick="fetchConsingmentList('all')">Filter</button>
                                        </div>
                                    </div>
                                    <div class=" all-data"></div>
                             </div>
                        </div>
                        <div class=" tab-pane" id="pending">
                            <div class=" container">
                                    <h3>Apply filter</h3>
                                    <div class=" row">
                                        <div class=" col-4">
                                            <label for="">Start Date</label>
                                            <input type="date" name="" class=" form-control pending-date1" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-01") ?>" id="">
                                        </div>
                                        <div class=" col-4">
                                            <label for="">End Date</label>
                                            <input type="date" name="" class=" form-control pending-date2" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>" id="">
                                        </div>
                                        <div class=" col-4 mt-4">
                                            <button class=" btn btn-info btn-sm" onclick="fetchConsingmentList('pending')">Filter</button>
                                        </div>
                                    </div>
                                    <div class=" pending-data"></div>
                             </div>
                        </div>
                        <div class=" tab-pane" id="Approved">
                            <div class=" container">
                                    <h3>Apply filter</h3>
                                    <div class=" row">
                                        <div class=" col-4">
                                            <label for="">Start Date</label>
                                            <input type="date" name="" class=" form-control Approved-date1" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-01") ?>" id="">
                                        </div>
                                        <div class=" col-4">
                                            <label for="">End Date</label>
                                            <input type="date" name="" class=" form-control Approved-date2" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>" id="">
                                        </div>
                                        <div class=" col-4 mt-4">
                                            <button class=" btn btn-info btn-sm " onclick="fetchConsingmentList('Approved')">Filter</button>
                                        </div>
                                    </div>
                                    <div class=" Approved-data"></div>
                             </div>
                        </div>
                        <div class=" tab-pane" id="closed">
                            <div class=" container">
                                    <h3>Apply filter</h3>
                                    <div class=" row">
                                        <div class=" col-4">
                                            <label for="">Start Date</label>
                                            <input type="date" name="" class=" form-control closed-date1" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-01") ?>" id="">
                                        </div>
                                        <div class=" col-4">
                                            <label for="">End Date</label>
                                            <input type="date" name="" class=" form-control closed-date2" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>" id="">
                                        </div>
                                        <div class=" col-4">
                                            <button class=" btn btn-info btn-sm mt-4" onclick="fetchConsingmentList('closed')">Filter</button>
                                        </div>
                                    </div>
                                    <div class=" closed-data"></div>
                             </div>
                        </div>
                        <div class=" tab-pane" id="trashed">
                            <div class=" container">
                                    <h3>Apply filter</h3>
                                    <div class=" row">
                                        <div class=" col-4">
                                            <label for="">Start Date</label>
                                            <input type="date" name="" class=" form-control trashed-date1" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-01") ?>" id="">
                                        </div>
                                        <div class=" col-4">
                                            <label for="">End Date</label>
                                            <input type="date" name="" class=" form-control trashed-date2" max="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>" id="">
                                        </div>
                                        <div class=" col-4 mt-4">
                                            <button class=" btn btn-info btn-sm " onclick="fetchConsingmentList('trashed')">Filter</button>
                                        </div>
                                    </div>
                                    <div class=" trashed-data"></div>
                             </div>
                        </div>
                 </div>
            </div>
 </div>
</div>

     <div id="spinner-overlay">
           <div class="spinner"></div>
                <p style="margin-top: 15px; margin-left:15px; font-weight: bold;">Processing...</p>
            
          
            </div>
<script>

$(document).ready(function() {
  $('#tab-buttons button').click(function() {
    // Remove active class from all buttons
    $('#tab-buttons button').removeClass('active');

    // Add active class to clicked button
    $(this).addClass('active');

    // Hide all tab content
    $('.tab-pane').removeClass('active').hide();

    // Show target tab
    var target = $(this).data('target');
    $(target).addClass('active').show();

    if(target === '#all'){
        fetchConsingmentList('all');
    }
    if(target === '#pending'){
        fetchConsingmentList('pending');
    }
    if(target === '#Approved'){
        fetchConsingmentList('Approved');
    }
    if(target === '#closed'){
        fetchConsingmentList('closed');
    }
    if(target === '#trashed'){
        fetchConsingmentList('trashed');
    }
     
  });

  
  
  fetchConsingmentList('all');
});
</script>