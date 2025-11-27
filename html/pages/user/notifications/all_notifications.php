<?php
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}
$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);
$allEmployees = Employee::employees([], false, $DBConn);
$orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;

$checklistStatuses = Proposal::proposal_checklist_status(['proposalChecklistStatusType'=>'checkListItem'], false, $DBConn);

$myTaskNotifications = Notifications::user_notifications(array('employeeID'=>$employeeID,  'notificationStatus'=>'unread'), false, $DBConn);?>

<div class="col-12">
  <div class="card card-body  card-customized">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="card-title mb-0">My Notifications</h4>
      <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=all_notifications&uid={$employeeID}"?>" class="btn btn-sm btn-primary">View All Notifications</a>
    </div>
    <?php

      if($myTaskNotifications && count($myTaskNotifications) > 0) {
        foreach($myTaskNotifications as $notification) {
          // var_dump  ($notification);
          $originator = isset($notification->originatorName) ? $notification->originatorName : 'System';
          $dateAdded = isset($notification->DateAdded) ? date('d M Y H:i', strtotime($notification->DateAdded)) : '';
          $notes = isset($notification->notificationNotes) ? $notification->notificationNotes : '';
          $statusBadge = ($notification->notificationStatus == 'unread') ? '<span class="badge bg-danger">Unread</span>' : '<span class="badge bg-secondary">Read</span>';?>

          <div class="alert alert-light border mb-3" role="alert">
            <div class="d-flex justify-content-between align-items-center  mb-2">
              <h5 class="mb-0 text-capitalize font-20 t400" ><?=  str_replace('_', ' ', $notification->notificationType) ?></h5>
              <span class="  d-flex align-items-center">
              <p class="mb-1 badge bg-success "> <?= htmlspecialchars($dateAdded)?></p>
              <span class=" btn btn-sm btn-outline-secondary me-2 fs-18 me-2"> <?= htmlspecialchars($originator) ?></span>
                <!-- <a href="index.php?page=user/notifications/view_notification&nid=<?= $notification->notificationID ?>&uid=<?= $employeeID ?>" class="btn btn-sm btn-outline-primary me-2">View Task</a> -->
                <button class="btn btn-sm btn-outline-secondary me-2 markAsRead"
                data-notification-id="<?= $notification->notificationID ?>"
                data-employee-id="<?= $employeeID ?>"
                data-action="mark_as_read"

                >Mark as Read</button>
                <?= $statusBadge; ?>
              </span>
            </div>

            <?php
            if(!empty($notes)) {
              echo '<div class="mb-0 lh-1">' . $notes . '</div>';
            } else {
              echo '<div class="mb-0 lh-1 ">'. $notification->notificationText.'</div>';
            }?>
          </div>
          <?php
        }
      } else {
        Alert::info("You have no task notifications at the moment.", false, array('fst-italic', 'text-center', 'font-18'));
      }
      ?>

  </div>
</div>

<?php
// var_dump($myTaskNotifications);
?>
<script>
  // Ensure that the dom is loaded
  document.addEventListener('DOMContentLoaded', function() {
    // Aselect all mark as read buttons and add event listeners
    const markAsReadButtons = document.querySelectorAll('.markAsRead');
    markAsReadButtons.forEach(button => {
      button.addEventListener('click', function() {
        //get all data attributes
        const data = this.dataset;
        console.log(data);
        const notificationID = this.getAttribute('data-notification-id');
        const employeeID = this.getAttribute('data-employee-id');
        const action = this.getAttribute('data-action');

        // Confirm the action with the user by creating a custom modal form with action php/scripts/notifications/mark_as_read.php and method POST
        // create the modal
        const modal = document.createElement('div');
        modal.classList.add('modal', 'fade');
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('role', 'dialog');
        modal.innerHTML = `
          <div class="modal-dialog" role="document">
            <form class="modal-content" action="../php/scripts/notifications/mark_as_read.php" method="POST">
              <div class="modal-header">
                <h5 class="modal-title">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <input type="hidden" name="notificationID" id="modalNotificationID" value="${notificationID}">
              <input type="hidden" name="employeeID" id="modalEmployeeID" value="${employeeID}">
              <input type="hidden" name="action" id="modalAction" value="${action}">
              <div class="modal-body">
                <p>Are you sure you want to mark this notification as read?</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="confirmMarkAsRead">Yes, Mark as Read</button>
              </div>
            </form>
          </div>
        `;
        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        // Add event listener to the confirm button
        const confirmButton = modal.querySelector('#confirmMarkAsRead');
        confirmButton.addEventListener('click', function(event) {
          event.preventDefault();
          //get the neearested form
          const form = modal.querySelector('form');
          // Submit the form
          // form.submit();
          // Optionally, you can use AJAX to submit the form without reloading the page

          const modalNotificationID = document.getElementById('modalNotificationID').value;
          const modalEmployeeID = document.getElementById('modalEmployeeID').value;
          const modalAction = document.getElementById('modalAction').value;
          // Send AJAX request to mark the notification as read
          fetch('../php/scripts/notifications/mark_as_read.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              notificationID: modalNotificationID,
              employeeID: modalEmployeeID,
              action: modalAction
            })

          })
          .then(response => response.json())
          .then(data => {
            console.log(data);
            if (data.success) {
              // Optionally, you can remove the notification from the DOM or update its status
              if (typeof showToast === 'function') {
                  showToast('Notification marked as read.', 'success');
              } else {
                  alert('Notification marked as read.');
              }
              setTimeout(() => location.reload(), 1000); // Reload the page to reflect changes
            } else {
              if (typeof showToast === 'function') {
                  showToast('Error: ' + data.message, 'error');
              } else {
                  alert('Error: ' + data.message);
              }
            }
          })
          .catch(error => {
            console.error('Error:', error);
            if (typeof showToast === 'function') {
                showToast('An error occurred while processing your request.', 'error');
            } else {
                alert('An error occurred while processing your request.');
            }
          });
          // Hide and remove the modal
          bsModal.hide();
          modal.remove();
        });



        // if (confirm('Are you sure you want to mark this notification as read?')) {
        //   // Send AJAX request to mark the notification as read
        //   fetch('../php/scripts/notifications/mark_as_read.php', {
        //     method: 'POST',
        //     headers: {
        //       'Content-Type': 'application/json'
        //     },
        //     body: JSON.stringify({
        //       notificationID: notificationID,
        //       employeeID: employeeID,
        //       action: action
        //     })
        //   })
        //   .then(response => response.json())
        //   .then(data => {
        //     if (data.success) {
        //       // Optionally, you can remove the notification from the DOM or update its status
        //       alert('Notification marked as read.');
        //       location.reload(); // Reload the page to reflect changes
        //     } else {
        //       alert('Error: ' + data.message);
        //     }
        //   })
        //   .catch(error => {
        //     console.error('Error:', error);
        //     alert('An error occurred while processing your request.');
        //   });
        // }
      });
    });

  });
</script>