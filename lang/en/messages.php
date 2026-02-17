<?php

return [
    // Driver
    'driver_login_required' => 'You must log in first.',
    'driver_no_vehicles' => 'No vehicles linked to your phone.',
    'driver_vehicle_not_linked' => 'Vehicle is not linked to your phone.',
    'driver_invalid_services' => 'Some selected services are invalid.',
    'driver_request_sent' => 'Service request sent. The company will receive a notification and approve the request.',
    'driver_fuel_success' => 'Fuel refill registered successfully.',
    'driver_otp_sent' => 'Verification code sent to your phone.',
    'driver_session_expired' => 'Session expired. Enter your phone again.',
    'driver_otp_expired' => 'Code has expired.',
    'driver_otp_invalid' => 'Invalid verification code.',
    'driver_login_success' => 'Logged in successfully.',
    'driver_phone_not_registered' => 'Phone number is not registered as a driver. Contact your company to add your phone.',

    // Vehicles (Company)
    'vehicle_added' => 'Vehicle added successfully.',
    'vehicle_updated' => 'Vehicle updated successfully.',

    // Orders (Company)
    'order_created' => 'Order created successfully.',
    'order_in_progress_cancel' => 'Order is in progress and cannot be cancelled directly.',
    'order_cancel_requested' => 'Cancellation request sent to the manager.',
    'invalid_vehicle' => 'Invalid vehicle.',
    'invalid_branch' => 'Invalid branch.',
    'invalid_services' => 'One or more services are not enabled.',

    // Orders (Admin)
    'order_assigned' => 'Order assigned to technician successfully.',
    'order_status_updated' => 'Order status updated successfully.',
    'order_completed_no_change' => 'Cannot change a completed order.',
    'order_cancelled_no_change' => 'Cannot change a cancelled order.',
    'order_override_note_required' => 'This is a non-standard transition (override). Please provide a reason.',
    'order_transition_not_allowed' => 'Transition not allowed: :from → :to',
    'order_hold_success' => 'Customer order has been put on hold.',

    // Orders (Technician)
    'task_already_completed' => 'This task is already completed.',
    'task_completed_success' => 'Task completion confirmed successfully.',
    'order_accepted' => 'Order accepted.',
    'order_rejected' => 'Order rejected.',

    // Invoice
    'no_invoice_for_order' => 'No invoice for this order.',
    'invoice_pdf_error' => 'An error occurred while creating the PDF.',
    'invoice_created' => 'Invoice created successfully.',

    // Payment
    'payment_recorded' => 'Payment recorded successfully.',
    'payment_note_admin' => 'Payment recorded from admin panel.',
    'payment_note_tap' => 'Payment completed via Tap.',
    'payment_already_paid' => 'This payment is already paid.',
    'tap_not_implemented' => 'Tap integration is not yet implemented in this controller.',
    'receipt_uploaded' => 'Receipt uploaded successfully. It will be reviewed by admin.',
    'payment_not_found' => 'Payment not found.',
    'payment_success' => 'Payment completed successfully.',
    'payment_pending_or_failed' => 'Payment is pending or was not completed. Check status below.',

    // Branches
    'branch_added' => 'Branch added successfully.',
    'branch_updated' => 'Branch updated successfully.',

    // Services
    'service_created' => 'Service created successfully.',
    'service_updated' => 'Service updated successfully.',
    'service_deleted' => 'Service permanently deleted.',
    'service_toggled' => 'Service enabled.',
    'service_disabled' => 'Service disabled.',

    // Auth (suspended account)
    'account_suspended' => 'Account suspended. Contact administration.',

    // Users (Admin)
    'technician_created' => 'Technician account created successfully.',
    'technician_updated' => 'Technician updated successfully.',
    'technician_status_updated' => 'Technician status updated.',
    'technician_deleted' => 'Technician deleted successfully.',
    'cannot_delete_admin' => 'Cannot delete the system administrator.',

    // Notifications
    'notification_marked_read' => 'Notification marked as read.',
    'all_notifications_marked_read' => 'All notifications marked as read.',

    // Auth (Unified)
    'phone_not_company' => 'Phone number is not registered as a company. Create an account from the link below.',
    'phone_not_driver' => 'Phone number is not registered as a driver. Contact your company to add your phone.',
    'phone_not_found' => 'Phone number is not registered as company or driver. Create a company account from the link below.',
    'otp_sent' => 'Verification code sent to your phone.',
    'otp_send_error' => 'An error occurred while sending the verification code. Please try again.',
    'otp_no_valid_code' => 'No valid code. Please try again.',
    'otp_invalid_try_again' => 'Invalid code. Please try again.',
    'session_expired_retry' => 'Session expired. Enter your phone again.',
    'session_expired_relogin' => 'Session expired. Please log in again.',
    'otp_expired_resend' => 'Code has expired. Resend the code.',
    'otp_invalid' => 'Invalid verification code.',
    'no_company_for_phone' => 'No company with this number.',
    'company_login_success' => 'Logged in successfully.',

    // Attachments
    'attachment_uploaded' => 'Attachment uploaded successfully.',
    'attachment_deleted' => 'Attachment deleted successfully.',
    'photos_before_uploaded' => 'Before photos uploaded successfully.',
    'photos_after_uploaded' => 'After photos uploaded successfully.',

    // Bank accounts
    'bank_account_added' => 'Bank account added successfully.',
    'bank_account_updated' => 'Bank account updated successfully.',
    'bank_account_deleted' => 'Bank account deleted successfully.',
    'bank_account_status_updated' => 'Bank account status updated.',
    'bank_account_default_set' => 'Account set as default.',
    'cannot_delete_default_bank' => 'Cannot delete the default account. Set another account as default first.',

    // Customers
    'customer_added' => 'Customer added successfully.',
    'customer_updated' => 'Customer updated successfully.',
    'customer_deleted' => 'Customer deleted successfully.',

    // Inventory
    'inventory_item_added' => 'Inventory item added successfully.',
    'inventory_item_updated' => 'Inventory item updated successfully.',
    'inventory_item_deleted' => 'Inventory item deleted successfully.',
];
