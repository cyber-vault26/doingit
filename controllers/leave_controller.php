<?php
    session_start();
    if(!$_SESSION){
        echo "<script> window.history.back() </script>";
    }
    require_once "../configs.php";
    require_once "../functions/leave_functions.php";
    require_once __DIR__ . "/../functions/notification_functions.php";

    $conn = openConn();
    
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        if(isset($_POST['action']) && $_POST['action'] === 'create_request'){
            // Get form data
            $user_id = (int) $_POST['user_id'];
            $leave_type_id = (int) $_POST['leave_type_id'];
            $start_date = $conn->real_escape_string($_POST['start_date']);
            $end_date = $conn->real_escape_string($_POST['end_date']);
            $total_days = (float) $_POST['total_days'];
            $reason = $_POST['reason'];
            $priority_level = $conn->real_escape_string($_POST['priority_level']);
            $backup_person_id = !empty($_POST['backup_person_id']) ? (int) $_POST['backup_person_id'] : null;
            $emergency_contact_phone = !empty($_POST['emergency_contact_phone']) ? $conn->real_escape_string($_POST['emergency_contact_phone']) : null;
            $work_handover_notes = !empty($_POST['work_handover_notes']) ? $conn->real_escape_string($_POST['work_handover_notes']) : null;
            $half_day = isset($_POST['half_day']) ? 1 : 0;
            $start_time = !empty($_POST['start_time']) ? $conn->real_escape_string($_POST['start_time']) : null;
            $end_time = !empty($_POST['end_time']) ? $conn->real_escape_string($_POST['end_time']) : null;
            
            // Handle file upload
            $attachment_path = null;
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/leave_documents/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['attachment']['name']);
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $file_path)) {
                    $attachment_path = $file_name;
                }
            }
            
            // Validate leave balance using new logic (max entitlement approach)
            $leaveBalance = getLeaveBalanceNew($conn, $user_id, $leave_type_id);
            if (!$leaveBalance || $leaveBalance['available_balance'] < $total_days) {
                echo "<script>alert('Insufficient leave balance. You have " . $leaveBalance['available_balance'] . " days available, but requested " . $total_days . " days.'); window.history.back();</script>";
                return;
            }
            
            // Validate date ranges
            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            if ($start > $end) {
                echo "<script>alert('Start date cannot be after end date.'); window.history.back();</script>";
                return;
            }
            
            // Check for overlapping requests
            $overlapCheck = validateLeaveRequest($conn, $user_id, $leave_type_id, $start_date, $end_date, $total_days);
            // if (!$overlapCheck['is_valid']) {
            //     echo "<script>alert('Leave request validation failed: " . addslashes($overlapCheck['error_message']) . "'); window.history.back();</script>";
            //     return;
            // }
            
            // Create leave request data array
            $leaveRequestData = [
                'user_id' => $user_id,
                'leave_type_id' => $leave_type_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'total_days' => $total_days,
                'reason' => $reason,
                'priority_level' => $priority_level,
                'backup_person_id' => $backup_person_id,
                'emergency_contact_phone' => $emergency_contact_phone,
                'work_handover_notes' => $work_handover_notes,
                'attachment_path' => $attachment_path,
                'half_day' => $half_day,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'status' => 'pending',
                'created_by' => $_SESSION['userid']
            ];
            
            // Call function to create leave request
            $createRequest = createLeaveRequest($conn, $leaveRequestData);
            
            if(!$createRequest){
                echo "<script>alert('Error creating leave request: " . addslashes($createRequest) . "'); window.history.back();</script>";
                return;
            } else {
                // Don't deduct balance on request submission - only deduct on approval
                // Balance will be calculated dynamically based on approved leave
                
                // Send notification to approvers (if needed)
                // $sendNotification = sendLeaveRequestNotification($conn, $createRequest, $user_id);
                
                echo "<script>alert('Leave request submitted successfully! Your request ID is LR-" . str_pad($createRequest, 6, '0', STR_PAD_LEFT) . ".'); window.location.href='../?page=my_leaves';</script>";
            }
        }
        
        if(isset($_POST['action']) && $_POST['action'] === 'create'){
            $policy_name = $conn->real_escape_string($_POST['policy_name']);
            $leave_type_id = (int) $_POST['leave_type_id'];
            $description = $conn->real_escape_string($_POST['description']);
            $priority = (int) $_POST['priority'];
            $effective_date = $conn->real_escape_string($_POST['effective_date']);
            $expiry_date = !empty($_POST['expiry_date']) ? $conn->real_escape_string($_POST['expiry_date']) : null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Scope determination
            $scope = $conn->real_escape_string($_POST['scope']);
            $user_id = null;
            if ($scope === 'user' && !empty($_POST['user_id'])) {
                $user_id = (int) $_POST['user_id'];
            }
            
            // Accrual settings
            $accrual_rate = (float) $_POST['accrual_rate'];
            $accrual_period = $conn->real_escape_string($_POST['accrual_period']);
            $max_carry_over_days = (float) $_POST['max_carry_over_days'];
            $probation_period_days = (int) $_POST['probation_period_days'];
            $tenure_based_accrual = isset($_POST['tenure_based_accrual']) ? 1 : 0;
            
            // Build accrual tiers JSON if tenure-based
            $accrual_tiers = null;
            if ($tenure_based_accrual && isset($_POST['tier_years']) && isset($_POST['tier_rates'])) {
                $tiers = [];
                for ($i = 0; $i < count($_POST['tier_years']); $i++) {
                    if (!empty($_POST['tier_years'][$i]) && !empty($_POST['tier_rates'][$i])) {
                        $years = (int) $_POST['tier_years'][$i];
                        $rate = (float) $_POST['tier_rates'][$i];
                        $tiers[$years] = $rate;
                    }
                }
                if (!empty($tiers)) {
                    $accrual_tiers = json_encode($tiers);
                }
            }
            
            // Approval settings
            $approval_levels = (int) $_POST['approval_levels'];
            $minimum_staffing_required = (int) $_POST['minimum_staffing_required'];
            $auto_approve_threshold_days = (int) $_POST['auto_approve_threshold_days'];
            $auto_approve_conditions = !empty($_POST['auto_approve_conditions']) ? $conn->real_escape_string($_POST['auto_approve_conditions']) : null;
            
            // Additional settings
            $blackout_periods = !empty($_POST['blackout_periods']) ? $conn->real_escape_string($_POST['blackout_periods']) : null;
            $documentation_requirements = !empty($_POST['documentation_requirements']) ? $conn->real_escape_string($_POST['documentation_requirements']) : null;
            
            // Validate JSON fields
            if ($auto_approve_conditions && !json_decode($auto_approve_conditions)) {
                echo "<script>alert('Invalid JSON format in auto-approve conditions'); window.history.back();</script>";
                exit;
            }
            
            if ($blackout_periods && !json_decode($blackout_periods)) {
                echo "<script>alert('Invalid JSON format in blackout periods'); window.history.back();</script>";
                exit;
            }
            
            if ($documentation_requirements && !json_decode($documentation_requirements)) {
                echo "<script>alert('Invalid JSON format in documentation requirements'); window.history.back();</script>";
                exit;
            }
            
            // Create policy data array
            $policyData = [
                'leave_type_id' => $leave_type_id,
                'user_id' => $user_id,
                'policy_name' => $policy_name,
                'description' => $description,
                'accrual_rate' => $accrual_rate,
                'accrual_period' => $accrual_period,
                'max_carry_over_days' => $max_carry_over_days,
                'probation_period_days' => $probation_period_days,
                'tenure_based_accrual' => $tenure_based_accrual,
                'accrual_tiers' => $accrual_tiers,
                'blackout_periods' => $blackout_periods,
                'minimum_staffing_required' => $minimum_staffing_required,
                'approval_levels' => $approval_levels,
                'auto_approve_conditions' => $auto_approve_conditions,
                'documentation_requirements' => $documentation_requirements,
                'is_active' => $is_active,
                'priority' => $priority,
                'effective_date' => $effective_date,
                'expiry_date' => $expiry_date,
                'created_by' => $_SESSION['userid']
            ];
            
            // Call function to create policy (you'll need to create this function)
            $createPolicy = createLeavePolicy($conn, $policyData);
            
            if(!$createPolicy){
                echo "<script>alert('Error creating policy: " . addslashes($createPolicy) . "'); window.history.back();</script>";
                return;
            } else {
                echo "<script>alert('Policy created successfully!'); window.location.href='./?page=leave_policies_list';</script>";
            }
        }
        
        if(isset($_POST['action']) && $_POST['action'] === 'update'){
            // Update policy logic here
            $policy_id = (int) $_POST['policy_id'];
            
            // Similar validation and update logic as create
            // ... (implement as needed)
            
            echo "<script>alert('Policy updated successfully!'); window.location.href='./?page=leave_policies_list';</script>";
        }
        
        if(isset($_POST['action']) && $_POST['action'] === 'delete'){
            // Delete policy logic here
            $policy_id = (int) $_POST['policy_id'];
            
            // Soft delete or hard delete logic
            $deletePolicy = deleteLeavePolicy($conn, $policy_id);
            
            if(!$deletePolicy){
                echo "<script>alert('Error deleting policy: " . addslashes($deletePolicy) . "'); window.history.back();</script>";
                return;
            } else {
                echo "<script>alert('Policy deleted successfully!'); window.location.href='./?page=leave_policies_list';</script>";
            }
        }
    }

    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        if(isset($_GET['action']) && $_GET['action'] === 'delete'){
            $policy_id = (int) $_GET['id'];
            
            $deletePolicy = deleteLeavePolicy($conn, $policy_id);
            
            if(!$deletePolicy){
                echo "<script>alert('Error deleting policy: " . addslashes($deletePolicy) . "'); window.history.back();</script>";
                return;
            } else {
                echo "<script>alert('Policy deleted successfully!'); window.location.href='./?page=leave_policies_list';</script>";
            }
        }
        
        if(isset($_GET['action']) && $_GET['action'] === 'toggle_status'){
            $policy_id = (int) $_GET['id'];
            
            $toggleStatus = togglePolicyStatus($conn, $policy_id);
            
            if(!$toggleStatus){
                echo "<script>alert('Error toggling policy status: " . addslashes($toggleStatus) . "'); window.history.back();</script>";
                return;
            } else {
                echo "<script>alert('Policy status updated successfully!'); window.location.href='./?page=leave_policies_list';</script>";
            }
        }
        
        if(isset($_GET['action']) && $_GET['action'] === 'get_policy'){
            $policy_id = (int) $_GET['id'];
            
            $policy = getLeavePolicyById($conn, $policy_id);
            
            if($policy){
                header('Content-Type: application/json');
                echo json_encode($policy);
            } else {
                header('HTTP/1.1 404 Not Found');
                echo json_encode(['error' => 'Policy not found']);
            }
            exit;
        }
        if(isset($_GET['action']) && $_GET['action'] === 'approve_leave'){
            $leave_id = (int) $_GET['leave_id'];
            $status = (string) $_GET['status'];
            
            $approveLeave = approveLeaveRequest($conn, $leave_id, $_SESSION['userid'], $status);
            print_r($approveLeave);
         //   return;
            if(!$approveLeave){
                setNotification("error", $approveLeave);
                echo "<script window.history.back();</script>";
                return;
            } else {
                setNotification("success", "Leave approved successfully!");
                echo "<script>window.location.href='../?page=leave_list';</script>";
            }
        }
        if(isset($_GET['action']) && $_GET['action'] === 'reject_leave'){
            $leave_id = (int) $_GET['leave_id'];
            $status = $_GET['status'];
            
            $rejectLeave = rejectLeaveRequest($conn, $leave_id, $_SESSION['userid'],$status, ' ');
            
            if(!$rejectLeave){
                setNotification("error", $rejectLeave);
                echo "<script> window.history.back();</script>";
                return;
            } else {
                setNotification("success", "Leave rejected successfully!");
                echo "<script>window.location.href='../?page=leave_list';</script>";
            }
        }
    }
    
 
?>