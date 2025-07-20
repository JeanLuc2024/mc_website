<?php
/**
 * Email Templates Management Page
 * 
 * This page allows admin to manage email templates for client communication.
 */

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Include required files
require_once '../php/config.php';
require_once '../php/email_communication.php';

// Set page title for header
$page_title = "Email Templates";

// Get database connection
$conn = connectDB();

// Handle form submission for creating/updating templates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_template'])) {
        // Create new template
        $template_name = sanitizeInput($_POST['template_name']);
        $template_category = sanitizeInput($_POST['template_category']);
        $subject_template = sanitizeInput($_POST['subject_template']);
        $message_template = $_POST['message_template']; // Don't sanitize message content
        
        try {
            $sql = "INSERT INTO email_templates (template_name, template_category, subject_template, message_template, created_by_admin_id) 
                    VALUES (:name, :category, :subject, :message, :admin_id)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $template_name);
            $stmt->bindParam(':category', $template_category);
            $stmt->bindParam(':subject', $subject_template);
            $stmt->bindParam(':message', $message_template);
            $stmt->bindParam(':admin_id', $_SESSION['admin_id']);
            
            if ($stmt->execute()) {
                $success_message = "Email template created successfully!";
            } else {
                $error_message = "Failed to create email template.";
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_template'])) {
        // Update existing template
        $template_id = (int)$_POST['template_id'];
        $template_name = sanitizeInput($_POST['template_name']);
        $template_category = sanitizeInput($_POST['template_category']);
        $subject_template = sanitizeInput($_POST['subject_template']);
        $message_template = $_POST['message_template'];
        
        try {
            $sql = "UPDATE email_templates SET template_name = :name, template_category = :category, 
                    subject_template = :subject, message_template = :message WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $template_name);
            $stmt->bindParam(':category', $template_category);
            $stmt->bindParam(':subject', $subject_template);
            $stmt->bindParam(':message', $message_template);
            $stmt->bindParam(':id', $template_id);
            
            if ($stmt->execute()) {
                $success_message = "Email template updated successfully!";
            } else {
                $error_message = "Failed to update email template.";
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['toggle_status'])) {
        // Toggle template active status
        $template_id = (int)$_POST['template_id'];
        $new_status = $_POST['is_active'] === '1' ? 0 : 1;
        
        try {
            $sql = "UPDATE email_templates SET is_active = :status WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':status', $new_status);
            $stmt->bindParam(':id', $template_id);
            
            if ($stmt->execute()) {
                $success_message = "Template status updated successfully!";
            } else {
                $error_message = "Failed to update template status.";
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Get all email templates
try {
    $sql = "SELECT et.*, au.full_name as created_by_name 
            FROM email_templates et 
            LEFT JOIN admin_users au ON et.created_by_admin_id = au.id 
            ORDER BY et.template_category, et.template_name";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group templates by category
    $templates_by_category = [];
    foreach ($templates as $template) {
        $templates_by_category[$template['template_category']][] = $template;
    }
} catch (Exception $e) {
    $error_message = "Failed to load templates: " . $e->getMessage();
    $templates_by_category = [];
}

// Include header
include_once 'includes/header.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-envelope-open-text"></i> Email Templates</h1>
        <div class="header-actions">
            <button class="btn-primary" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Create Template
            </button>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="notification success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success_message; ?></span>
            <button class="close-notification">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="notification error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error_message; ?></span>
            <button class="close-notification">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Templates by Category -->
    <div class="templates-container">
        <?php foreach ($templates_by_category as $category => $category_templates): ?>
            <div class="category-section">
                <div class="category-header">
                    <h2><i class="fas fa-folder"></i> <?php echo ucwords($category); ?> Templates</h2>
                    <span class="template-count"><?php echo count($category_templates); ?> templates</span>
                </div>
                
                <div class="templates-grid">
                    <?php foreach ($category_templates as $template): ?>
                        <div class="template-card <?php echo $template['is_active'] ? 'active' : 'inactive'; ?>">
                            <div class="template-header">
                                <h3><?php echo htmlspecialchars($template['template_name']); ?></h3>
                                <div class="template-actions">
                                    <button class="action-btn edit-btn" onclick="editTemplate(<?php echo $template['id']; ?>)" title="Edit Template">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                        <input type="hidden" name="is_active" value="<?php echo $template['is_active']; ?>">
                                        <button type="submit" name="toggle_status" class="action-btn toggle-btn" 
                                                title="<?php echo $template['is_active'] ? 'Deactivate' : 'Activate'; ?> Template">
                                            <i class="fas fa-<?php echo $template['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="template-content">
                                <div class="template-subject">
                                    <strong>Subject:</strong> <?php echo htmlspecialchars($template['subject_template']); ?>
                                </div>
                                <div class="template-preview">
                                    <?php echo nl2br(htmlspecialchars(substr($template['message_template'], 0, 150))); ?>
                                    <?php if (strlen($template['message_template']) > 150): ?>
                                        <span class="read-more">... <a href="#" onclick="viewTemplate(<?php echo $template['id']; ?>)">Read more</a></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="template-footer">
                                <span class="status-badge <?php echo $template['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $template['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                                <span class="template-meta">
                                    Updated: <?php echo date('M j, Y', strtotime($template['updated_at'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Help Section -->
    <div class="help-section">
        <h3><i class="fas fa-question-circle"></i> Available Variables</h3>
        <p>You can use these variables in your email templates. They will be automatically replaced with actual booking data:</p>
        <div class="variables-grid">
            <div class="variable-item">
                <code>{client_name}</code>
                <span>Client's full name</span>
            </div>
            <div class="variable-item">
                <code>{booking_ref}</code>
                <span>Booking reference number</span>
            </div>
            <div class="variable-item">
                <code>{event_type}</code>
                <span>Type of event</span>
            </div>
            <div class="variable-item">
                <code>{event_date}</code>
                <span>Event date (formatted)</span>
            </div>
            <div class="variable-item">
                <code>{event_time}</code>
                <span>Event time (formatted)</span>
            </div>
            <div class="variable-item">
                <code>{event_location}</code>
                <span>Event location</span>
            </div>
            <div class="variable-item">
                <code>{guests}</code>
                <span>Number of guests</span>
            </div>
            <div class="variable-item">
                <code>{package}</code>
                <span>Selected package</span>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Template Modal -->
<div id="templateModal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3 id="modal-title">Create Email Template</h3>
            <span class="close-modal">&times;</span>
        </div>
        <form method="POST" id="template-form">
            <input type="hidden" name="template_id" id="template_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Template Name</label>
                        <input type="text" name="template_name" id="template_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="template_category" id="template_category" class="form-control" required>
                            <option value="confirmation">Confirmation</option>
                            <option value="follow_up">Follow Up</option>
                            <option value="cancellation">Cancellation</option>
                            <option value="pricing">Pricing</option>
                            <option value="availability">Availability</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Subject Template</label>
                    <input type="text" name="subject_template" id="subject_template" class="form-control" required 
                           placeholder="e.g., Your Booking is Confirmed - {booking_ref}">
                </div>
                
                <div class="form-group">
                    <label>Message Template</label>
                    <textarea name="message_template" id="message_template" rows="12" class="form-control" required 
                              placeholder="Write your email template here. Use variables like {client_name}, {booking_ref}, etc."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeTemplateModal()">Cancel</button>
                <button type="submit" name="create_template" id="submit-btn" class="btn-primary">Create Template</button>
            </div>
        </form>
    </div>
</div>

<!-- View Template Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="view-title">Template Details</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div id="view-content"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<style>
.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.templates-container {
    margin-bottom: 30px;
}

.category-section {
    margin-bottom: 40px;
}

.category-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.category-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 18px;
}

.template-count {
    font-size: 14px;
    color: #6c757d;
    background: #f8f9fa;
    padding: 4px 12px;
    border-radius: 12px;
}

.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.template-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    border-left: 4px solid #3498db;
}

.template-card.inactive {
    opacity: 0.6;
    border-left-color: #95a5a6;
}

.template-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.template-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.template-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 16px;
}

.template-actions {
    display: flex;
    gap: 5px;
}

.template-content {
    padding: 20px;
}

.template-subject {
    margin-bottom: 15px;
    font-size: 14px;
    color: #495057;
}

.template-subject strong {
    color: #2c3e50;
}

.template-preview {
    font-size: 13px;
    color: #6c757d;
    line-height: 1.5;
}

.read-more a {
    color: #3498db;
    text-decoration: none;
}

.template-footer {
    background: #f8f9fa;
    padding: 10px 20px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.template-meta {
    font-size: 12px;
    color: #6c757d;
}

.help-section {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    margin-top: 30px;
}

.help-section h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 16px;
}

.variables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.variable-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.variable-item code {
    background: #f8f9fa;
    color: #e83e8c;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
}

.variable-item span {
    font-size: 12px;
    color: #6c757d;
}

.modal-content.large {
    max-width: 800px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

@media (max-width: 768px) {
    .templates-grid {
        grid-template-columns: 1fr;
    }

    .variables-grid {
        grid-template-columns: 1fr;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .content-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
}
</style>

<script>
// Modal functionality
const templateModal = document.getElementById('templateModal');
const viewModal = document.getElementById('viewModal');
const closeButtons = document.querySelectorAll('.close-modal');

function openCreateModal() {
    document.getElementById('modal-title').textContent = 'Create Email Template';
    document.getElementById('submit-btn').textContent = 'Create Template';
    document.getElementById('submit-btn').name = 'create_template';
    document.getElementById('template-form').reset();
    document.getElementById('template_id').value = '';
    templateModal.style.display = 'block';
}

function editTemplate(templateId) {
    // Get template data (you would fetch this via AJAX in a real implementation)
    // For now, we'll open the modal and let the user edit
    document.getElementById('modal-title').textContent = 'Edit Email Template';
    document.getElementById('submit-btn').textContent = 'Update Template';
    document.getElementById('submit-btn').name = 'update_template';
    document.getElementById('template_id').value = templateId;

    // In a real implementation, you would fetch the template data via AJAX
    // and populate the form fields

    templateModal.style.display = 'block';
}

function viewTemplate(templateId) {
    // In a real implementation, you would fetch the full template content via AJAX
    document.getElementById('view-title').textContent = 'Template Details';
    document.getElementById('view-content').innerHTML = '<p>Loading template details...</p>';
    viewModal.style.display = 'block';
}

function closeTemplateModal() {
    templateModal.style.display = 'none';
}

function closeViewModal() {
    viewModal.style.display = 'none';
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target === templateModal) {
        closeTemplateModal();
    }
    if (event.target === viewModal) {
        closeViewModal();
    }
});

// Close modal buttons
closeButtons.forEach(button => {
    button.addEventListener('click', function() {
        const modal = this.closest('.modal');
        modal.style.display = 'none';
    });
});

// Close notifications
document.querySelectorAll('.close-notification').forEach(button => {
    button.addEventListener('click', function() {
        this.parentElement.style.display = 'none';
    });
});

// Auto-hide notifications after 5 seconds
setTimeout(function() {
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        notification.style.display = 'none';
    });
}, 5000);
</script>

<?php include_once 'includes/footer.php'; ?>
