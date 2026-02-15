<?php

return [
    // Driver
    'driver_login_required' => 'يجب تسجيل الدخول أولاً.',
    'driver_no_vehicles' => 'لا توجد مركبات مرتبطة بجوالك.',
    'driver_vehicle_not_linked' => 'المركبة غير مرتبطة بجوالك.',
    'driver_invalid_services' => 'بعض الخدمات المختارة غير صالحة.',
    'driver_request_sent' => 'تم إرسال طلب الخدمة. ستتلقى الشركة إشعاراً وستوافق على الطلب.',
    'driver_fuel_success' => 'تم تسجيل تعبئة الوقود بنجاح.',
    'driver_otp_sent' => 'تم إرسال رمز التحقق إلى جوالك.',
    'driver_session_expired' => 'انتهت الجلسة. أدخل جوالك مرة أخرى.',
    'driver_otp_expired' => 'انتهت صلاحية الرمز.',
    'driver_otp_invalid' => 'رمز التحقق غير صحيح.',
    'driver_login_success' => 'تم تسجيل الدخول.',
    'driver_phone_not_registered' => 'رقم الجوال غير مسجّل كسائق لمركبة. تواصل مع شركتك لإضافة جوالك.',

    // Vehicles (Company)
    'vehicle_added' => 'تم إضافة المركبة بنجاح',
    'vehicle_updated' => 'تم تحديث المركبة بنجاح',

    // Orders (Company)
    'order_created' => 'تم إنشاء الطلب بنجاح.',
    'order_in_progress_cancel' => 'الطلب قيد التنفيذ ولا يمكن إلغاؤه مباشرة.',
    'order_cancel_requested' => 'تم إرسال طلب الإلغاء للمدير.',
    'invalid_vehicle' => 'المركبة غير صالحة.',
    'invalid_branch' => 'الفرع غير صالح.',
    'invalid_services' => 'واحدة أو أكثر من الخدمات غير مفعّلة.',

    // Orders (Admin)
    'order_assigned' => 'تم إسناد الطلب للفني بنجاح.',
    'order_status_updated' => 'تم تحديث حالة الطلب بنجاح',
    'order_completed_no_change' => 'لا يمكن تغيير طلب مكتمل.',
    'order_cancelled_no_change' => 'لا يمكن تغيير طلب ملغي.',
    'order_override_note_required' => 'هذا انتقال غير قياسي (تجاوز). الرجاء كتابة سبب التغيير.',
    'order_transition_not_allowed' => 'انتقال غير مسموح: :from → :to',
    'order_hold_success' => 'تم تعليق طلب العميل',

    // Orders (Technician)
    'task_already_completed' => 'هذه المهمة مكتملة بالفعل.',
    'task_completed_success' => 'تم تأكيد إنجاز المهمة بنجاح',
    'order_accepted' => 'تم قبول الطلب',
    'order_rejected' => 'تم رفض الطلب',

    // Invoice
    'no_invoice_for_order' => 'لا توجد فاتورة لهذا الطلب.',
    'invoice_pdf_error' => 'حدث خطأ أثناء إنشاء PDF.',
    'invoice_created' => 'تم إنشاء الفاتورة.',

    // Payment
    'payment_recorded' => 'تم تسجيل بيانات الدفع.',
    'payment_note_admin' => 'تم تسجيل الدفع من لوحة الأدمن.',
    'payment_already_paid' => 'هذه الدفعة مدفوعة بالفعل.',
    'tap_not_implemented' => 'ربط Tap لم يُنفّذ بعد في هذا الكنترولر.',
    'receipt_uploaded' => 'تم رفع الإيصال بنجاح وسيتم مراجعته من الإدارة.',

    // Branches
    'branch_added' => 'تم إضافة الفرع بنجاح',
    'branch_updated' => 'تم تحديث الفرع بنجاح',

    // Services
    'service_created' => 'تم إنشاء الخدمة بنجاح',
    'service_updated' => 'تم تحديث الخدمة بنجاح',
    'service_deleted' => 'تم حذف الخدمة نهائيًا.',
    'service_toggled' => 'تم تفعيل الخدمة.',
    'service_disabled' => 'تم تعطيل الخدمة.',

    // Auth (suspended account)
    'account_suspended' => 'الحساب موقوف. تواصل مع الإدارة.',

    // Users (Admin)
    'technician_created' => 'تم إنشاء حساب الفني بنجاح.',
    'technician_updated' => 'تم تحديث بيانات الفني.',
    'technician_status_updated' => 'تم تحديث حالة الفني.',
    'technician_deleted' => 'تم حذف الفني بنجاح.',
    'cannot_delete_admin' => 'لا يمكن حذف مدير النظام.',

    // Notifications
    'notification_marked_read' => 'تم تعليم الإشعار كمقروء',
    'all_notifications_marked_read' => 'تم تعليم جميع الإشعارات كمقروء',

    // Auth (Unified)
    'phone_not_company' => 'رقم الجوال غير مسجّل كشركة. إنشاء حساب من الرابط أدناه.',
    'phone_not_driver' => 'رقم الجوال غير مسجّل كسائق لمركبة. تواصل مع شركتك لإضافة جوالك.',
    'phone_not_found' => 'رقم الجوال غير مسجّل كشركة أو كسائق. إنشاء حساب شركة من الرابط أدناه.',
    'otp_sent' => 'تم إرسال رمز التحقق إلى جوالك.',
    'otp_send_error' => 'حدث خطأ أثناء إرسال رمز التحقق. حاول مرة أخرى.',
    'otp_no_valid_code' => 'لا يوجد رمز صالح. أعد المحاولة.',
    'otp_invalid_try_again' => 'رمز غير صحيح. حاول مرة أخرى.',
    'session_expired_retry' => 'انتهت الجلسة. أدخل جوالك مرة أخرى.',
    'session_expired_relogin' => 'انتهت الجلسة. أعد تسجيل الدخول.',
    'otp_expired_resend' => 'انتهت صلاحية الرمز. أعد إرسال الرمز.',
    'otp_invalid' => 'رمز التحقق غير صحيح.',
    'no_company_for_phone' => 'لا توجد شركة بهذا الرقم.',
    'company_login_success' => 'تم تسجيل الدخول بنجاح.',

    // Attachments
    'attachment_uploaded' => 'تم رفع المرفق.',
    'attachment_deleted' => 'تم حذف المرفق.',
    'photos_before_uploaded' => 'تم رفع صور (قبل) بنجاح',
    'photos_after_uploaded' => 'تم رفع صور (بعد) بنجاح',

    // Bank accounts
    'bank_account_added' => 'تم إضافة الحساب البنكي بنجاح.',
    'bank_account_updated' => 'تم تحديث الحساب البنكي.',
    'bank_account_deleted' => 'تم حذف الحساب البنكي.',
    'bank_account_status_updated' => 'تم تحديث حالة الحساب.',
    'bank_account_default_set' => 'تم تعيين الحساب كافتراضي.',
    'cannot_delete_default_bank' => 'لا يمكن حذف الحساب الافتراضي. عيّن حسابًا آخر كافتراضي أولاً.',

    // Customers
    'customer_added' => 'تم إضافة العميل بنجاح.',
    'customer_updated' => 'تم تحديث بيانات العميل.',
    'customer_deleted' => 'تم حذف العميل.',

    // Inventory
    'inventory_item_added' => 'تم إضافة عنصر للمخزون.',
    'inventory_item_updated' => 'تم تحديث عنصر المخزون.',
    'inventory_item_deleted' => 'تم حذف عنصر المخزون.',
];
