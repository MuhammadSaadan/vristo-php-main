<?php 
require '../config.php';


?>

<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">

<?php
$log_question = array();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['user_id']; 

    $sql = "SELECT * FROM ph9_question WHERE user_id = $user_id ORDER BY id ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $log_question[] = $row;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $logquestion = $_POST['delete_id'];
    
    $stmt = $conn->prepare("DELETE FROM ph9_questions WHERE id = ?");
    $stmt->bind_param("i", $logquestion);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['deletion_success'] = true;
        unset($_SESSION['update_success']);
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
      
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

if (isset($_SESSION['deletion_success']) && $_SESSION['deletion_success'] === true) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>
    <script>
        const toast = window.Swal.mixin({
            toast: true,
            position: 'top',
            showConfirmButton: false,
            timer: 3000,
            showCloseButton: true,
            customClass: {
                popup: 'background-color: #5cb85c; color: white; border-radius: 5px;'
            }
        });
        toast.fire({
            title: 'Result Updated',
            icon: 'success'
        });
    </script>";
    unset($_SESSION['deletion_success']); // Clear the success message
}


if (isset($_GET['updateSuccess']) && $_GET['updateSuccess'] == 'true') {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>
    <script>
        const coloredToast = () => {
            const toast = window.Swal.mixin({
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 3000,
                showCloseButton: true,
                customClass: {
                    popup: 'background-color: #5cb85c; color: white; border-radius: 5px;'
                }
            });
            toast.fire({
                title: 'Result Updated',
                icon: 'success'
            });
        };
        coloredToast();
    </script>";
    unset($_SESSION['updateSuccess']); // Clear the success message

}

include '../header-main.php'; 

$conn->close();
?>


<!-- arrowed -->
<ol class="flex text-primary font-semibold dark:text-white-dark">
    <li class="bg-[#ebedf2] rounded-tl-md rounded-bl-md dark:bg-[#1b2e4b]"><a href="/dashboardUser/dashboard.php" class="p-1.5 ltr:pl-3 rtl:pr-3 ltr:pr-2 rtl:pl-2 relative  h-full flex items-center before:absolute ltr:before:-right-[15px] rtl:before:-left-[15px] rtl:before:rotate-180 before:inset-y-0 before:m-auto before:w-0 before:h-0 before:border-[16px] before:border-l-[15px] before:border-r-0 before:border-t-transparent before:border-b-transparent before:border-l-[#ebedf2] before:z-[1] dark:before:border-l-[#1b2e4b] hover:text-primary/70 dark:hover:text-white-dark/70">Dashboard</a></li>
    <li class="bg-[#ebedf2] dark:bg-[#1b2e4b]"><a href="/selfTest/index.php"class="p-1.5 px-3 ltr:pl-6 rtl:pr-6 relative  h-full flex items-center before:absolute ltr:before:-right-[15px] rtl:before:-left-[15px] rtl:before:rotate-180 before:inset-y-0 before:m-auto before:w-0 before:h-0 before:border-[16px] before:border-l-[15px] before:border-r-0 before:border-t-transparent before:border-b-transparent before:border-l-[#ebedf2] before:z-[1] dark:before:border-l-[#1b2e4b] hover:text-primary/70 dark:hover:text-white-dark/70">Mental Health Self Test</a></li>
    <li class="bg-[#ebedf2] dark:bg-[#1b2e4b]"><a class="bg-primary text-white-light p-1.5 ltr:pl-6 rtl:pr-6 ltr:pr-2 rtl:pl-2 relative  h-full flex items-center before:absolute ltr:before:-right-[15px] rtl:before:-left-[15px] rtl:before:rotate-180 before:inset-y-0 before:m-auto before:w-0 before:h-0 before:border-[16px] before:border-l-[15px] before:border-r-0 before:border-t-transparent before:border-b-transparent before:border-l-primary before:z-[1]">View Results</a></li>
</ol>
<br>

<div class="panel">

<div class="table-responsive">
    <table id="resultsTable" class="table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Score</th>
                <th>Severity</th>
                <th>Comments</th>
                <th>Date and Time</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
    <?php 
    $currentNumber = 1; // Initialize the counter for row numbering

    foreach ($log_question as $question) :
    ?>
    <tr id="row-<?= $question['id'] ?>">
        <td><?= $currentNumber ?></td> 
        <td><?= $question['total'] ?></td>
        <td><?= $question['severity'] ?></td>
        <td><?= $question['comment'] ?></td>
        <td><?= $question['logged_at'] ?></td>
        <td class="p-3 border-b border-[#ebedf2] dark:border-[#191e3a] text-center">
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn btn-danger btn-sm" onclick="showAlert(<?= $symptom['log_id'] ?>)">Delete</button>
            </div>
        </td>
    </tr>
    <?php 
    $currentNumber++; // Increment the counter for the next row
    endforeach; 
    ?>
</tbody>
    </table>
</div>
</div>

<?php include '../footer-main.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        new simpleDatatables.DataTable('#resultsTable');
     
}
    );

    async function showAlert(logId) {
        const swalWithBootstrapButtons = window.Swal.mixin({
            confirmButtonClass: 'btn btn-secondary',
            cancelButtonClass: 'btn btn-dark ltr:mr-3 rtl:ml-3',
            buttonsStyling: false,
        });
        swalWithBootstrapButtons
        .fire({
            title: '<div style="text-align: center;">Are you sure?</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            padding: '2em',
        })
        .then((result) => {
            if (result.value) {
                deleteLog(logId);
            } 
         
        });
    }

    async function deleteLog(logId) {
    const formData = new FormData();
    formData.append('delete_id', logId);

    try {
        const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            body: formData
        });

        if (response.ok) {

            // Reload the page after a short delay (e.g., 1.5 seconds)
            setTimeout(() => {
                window.location.reload();
            }, 1500); // Adjust the delay time as needed
        } else {
            // If deletion failed, display an error message
            swalWithBootstrapButtons.fire('Error', 'Failed to delete', 'error');
        }
    } catch (error) {
        // Handle any unexpected errors
        swalWithBootstrapButtons.fire('Error', 'An unexpected error occurred.', 'error');
    }
}


</script>

