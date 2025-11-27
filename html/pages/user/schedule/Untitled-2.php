<div class="assignee-container position-relative" 
                     data-assignee-id="' . $assigneeId . '" 
                     data-assignee-name="' . $assigneeName . '">
                    <div class="assignee-avatar" data-bs-toggle="tooltip" title="' . $assigneeName . '">
                        ' . ($assigneeAvatar ? 
                            '<img src="' . htmlspecialchars($assigneeAvatar, ENT_QUOTES, 'UTF-8') . '" alt="' . $assigneeName . '" class="rounded-circle" style="width: 32px; height: 32px;">' :
                            '<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">' . $assigneeInitials . '</div>'
                        ) . '
                    </div>
                    <button class="btn btn-sm btn-danger assignee-delete-btn position-absolute top-0 end-0 translate-middle removeAssigneeFromTask" 
                            style="width: 16px; height: 16px; padding: 0; border-radius: 50%; font-size: 10px; display: none; z-index: 10;"
                            data-assignment-id="' . $assignee['assignmentId'] . '"
                            data-assignee-id="' . $assignee['userId'] . '"
                           
                            data-project-id="' . $assignee['projectID'] . '"
                            data-bs-toggle="tooltip" 
                            title="Remove ' . $assigneeName . ' from task"
                           >
                        <i class="uil uil-times"></i>
                    </button>
                </div>';

                <style>
.assignee-container {
    transition: all 0.2s ease-in-out;
}

.assignee-container:hover .assignee-delete-btn {
    display: block !important;
}

.assignee-container:hover .assignee-avatar {
    opacity: 0.8;
    transform: scale(0.95);
}

.assignee-delete-btn {
    transition: all 0.2s ease-in-out;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.assignee-delete-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.assignee-delete-btn:active {
    transform: scale(0.95);
}
</style>