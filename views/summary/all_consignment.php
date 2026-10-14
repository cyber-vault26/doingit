<div class="card card-info card-outline">
    <div class="card-header">
        <h4 class="card-title">Projects List</h4>
    </div>
    <div class="card-body">
        <table class="table table-sm table-bordered data-table">
    <thead class="table-info">
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Date</th>
            <th>Client</th>
            <th>Locaton</th>
            <th>Price</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $consignements = listAllProjects($conn);
        if ($consignements && is_array($consignements)) {
            $counter = 1;
            foreach ($consignements as $consignement) {
                echo "<tr>";
                echo "<td>$counter</td>";
                echo "<td>{$consignement['name']}</td>";
                echo "<td>{$consignement['date_']}</td>";
                echo "<td>{$consignement['customer_name']}</td>";
                echo "<td>{$consignement['location']}</td>";
                echo "<td>{$consignement['price']}</td>";
                echo "<td>{$consignement['status']}</td>";
               echo "<td>
                        <a href='./?page=open_project&id={$consignement['id']}' class='btn btn-sm btn-outline-primary'>
                            <i class='fa-solid fa-eye'></i>
                            open
                        </a>
                      </td>";
                echo "</tr>";

                $counter++;
            }
        } else {
            echo "<tr><td colspan='8' class='text-center'>No data available</td></tr>";
        }
        ?>
    </tbody>
</table>

    </div>
</div>



 