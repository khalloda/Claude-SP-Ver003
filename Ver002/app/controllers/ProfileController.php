<?php

/**
 * File: app/controllers/ProfileController.php
 * Purpose: Dedicated user profile management controller
 * Depends on: Controller, User model, Auth
 * Notes: Handles user profile display, editing, preferences, and security settings
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Display user profile
     */
    public function index(): void
    {
        $this->requireAuth();

        $user = User::find($this->getCurrentUser()['id']);

        if (!$user) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/dashboard');
        }

        // Get user statistics
        $stats = [
            'total_logins' => $user->getTotalLogins(),
            'last_login' => $user->getLastLogin(),
            'quotes_created' => $user->getQuotesCreated(),
            'orders_processed' => $user->getOrdersProcessed(),
            'invoices_created' => $user->getInvoicesCreated(),
            'account_created' => $user->created_at,
            'last_updated' => $user->updated_at
        ];

        // Get recent activity
        $recentActivity = $user->getRecentActivity(10);

        $this->view('profile/index', [
            'user' => $user,
            'stats' => $stats,
            'recent_activity' => $recentActivity,
            'page_title' => t('nav.profile')
        ]);
    }

    /**
     * Show profile edit form
     */
    public function edit(): void
    {
        $this->requireAuth();

        $user = User::find($this->getCurrentUser()['id']);

        if (!$user) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/dashboard');
        }

        $this->view('profile/edit', [
            'user' => $user,
            'page_title' => t('common.edit') . ' ' . t('nav.profile')
        ]);
    }

    /**
     * Update user profile
     */
    public function update(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        $user = User::find($this->getCurrentUser()['id']);

        if (!$user) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/dashboard');
        }

        if (!$this->validate([
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'phone' => 'max:20'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Check email uniqueness (excluding current user)
        $existingUser = User::findByEmail($this->input['email']);
        if ($existingUser && $existingUser->id !== $user->id) {
            $this->setFlash('error', t('users.email_exists'));
            $this->flashInput();
            $this->back();
        }

        $userData = $this->sanitizeInput([
            'name' => $this->input['name'],
            'email' => $this->input['email'],
            'phone' => $this->input['phone'] ?? '',
            'bio' => $this->input['bio'] ?? ''
        ]);

        $user->fill($userData);
        
        if ($user->save()) {
            $this->clearOldInput();
            $this->setFlash('success', t('messages.success.updated'));
            $this->redirect('/profile');
        } else {
            $this->flashInput();
            $this->setFlash('error', t('messages.error.general'));
            $this->back();
        }
    }

    /**
     * Show change password form
     */
    public function changePassword(): void
    {
        $this->requireAuth();

        $this->view('profile/change-password', [
            'page_title' => t('users.change_password')
        ]);
    }

    /**
     * Update user password
     */
    public function updatePassword(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        $user = User::find($this->getCurrentUser()['id']);

        if (!$user) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/dashboard');
        }

        if (!$this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required'
        ])) {
            $this->flashInput();
            $this->back();
        }

        // Verify current password
        if (!password_verify($this->input['current_password'], $user->password)) {
            $this->setFlash('error', t('users.current_password_incorrect'));
            $this->back();
        }

        // Check password confirmation
        if ($this->input['new_password'] !== $this->input['confirm_password']) {
            $this->setFlash('error', t('users.password_confirmation_mismatch'));
            $this->back();
        }

        // Check if new password is different from current
        if (password_verify($this->input['new_password'], $user->password)) {
            $this->setFlash('error', t('users.new_password_same'));
            $this->back();
        }

        $user->password = password_hash($this->input['new_password'], PASSWORD_DEFAULT);
        $user->must_change_password = 0;
        $user->password_changed_at = date('Y-m-d H:i:s');
        
        if ($user->save()) {
            $this->setFlash('success', t('users.password_updated'));
            $this->redirect('/profile');
        } else {
            $this->setFlash('error', t('messages.error.general'));
            $this->back();
        }
    }

    /**
     * Show user preferences form
     */
    public function preferences(): void
    {
        $this->requireAuth();

        $user = User::find($this->getCurrentUser()['id']);

        if (!$user) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/dashboard');
        }

        // Get available languages and currencies
        $languages = [
            'en' => 'English',
            'ar' => 'العربية'
        ];

        $currencies = [
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            'AED' => 'UAE Dirham',
            'SAR' => 'Saudi Riyal'
        ];

        $timezones = [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time',
            'Europe/London' => 'London',
            'Asia/Dubai' => 'Dubai',
            'Asia/Riyadh' => 'Riyadh'
        ];

        $this->view('profile/preferences', [
            'user' => $user,
            'languages' => $languages,
            'currencies' => $currencies,
            'timezones' => $timezones,
            'page_title' => t('users.preferences')
        ]);
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->setFlash('error', t('messages.error.invalid_csrf'));
            $this->back();
        }

        $user = User::find($this->getCurrentUser()['id']);

        if (!$user) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/dashboard');
        }

        // Build preferences array
        $preferences = [
            'language' => $this->input['language'] ?? 'en',
            'currency' => $this->input['currency'] ?? 'USD',
            'timezone' => $this->input['timezone'] ?? 'UTC',
            'date_format' => $this->input['date_format'] ?? 'Y-m-d',
            'time_format' => $this->input['time_format'] ?? '24',
            'items_per_page' => (int)($this->input['items_per_page'] ?? 20),
            'theme' => $this->input['theme'] ?? 'light',
            'sidebar_collapsed' => (bool)($this->input['sidebar_collapsed'] ?? false),
            'notifications' => [
                'email_alerts' => (bool)($this->input['email_alerts'] ?? false),
                'low_stock_alerts' => (bool)($this->input['low_stock_alerts'] ?? false),
                'order_notifications' => (bool)($this->input['order_notifications'] ?? false),
                'system_updates' => (bool)($this->input['system_updates'] ?? false)
            ],
            'dashboard' => [
                'show_stats' => (bool)($this->input['show_stats'] ?? true),
                'show_charts' => (bool)($this->input['show_charts'] ?? true),
                'show_recent_activity' => (bool)($this->input['show_recent_activity'] ?? true),
                'show_quick_actions' => (bool)($this->input['show_quick_actions'] ?? true)
            ]
        ];

        $user->preferences = json_encode($preferences);
        
        if ($user->save()) {
            // Update session with new preferences
            $_SESSION['user_preferences'] = $preferences;
            
            $this->setFlash('success', t('users.preferences_updated'));
            $this->redirect('/profile/preferences');
        } else {
            $this->setFlash('error', t('messages.error.general'));
            $this->back();
        }
    }

    /**
     * Get user activity log
     */
    public function activity(): void
    {
        $this->requireAuth();

        $user = User::find($this->getCurrentUser()['id']);

        if (!$user) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/dashboard');
        }

        $page = (int)($this->input['page'] ?? 1);
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $activities = $user->getActivityLog($limit, $offset);
        $totalActivities = $user->getActivityCount();
        $totalPages = ceil($totalActivities / $limit);

        $this->view('profile/activity', [
            'user' => $user,
            'activities' => $activities,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_activities' => $totalActivities,
            'page_title' => t('users.activity_log')
        ]);
    }

    /**
     * Export user data (GDPR compliance)
     */
    public function exportData(): void
    {
        $this->requireAuth();

        $user = User::find($this->getCurrentUser()['id']);

        if (!$user) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        try {
            $userData = [
                'personal_information' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'department' => $user->department,
                    'bio' => $user->bio,
                    'role' => $user->role,
                    'status' => $user->status,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'last_login' => $user->last_login,
                    'preferences' => json_decode($user->preferences, true)
                ],
                'statistics' => [
                    'total_logins' => $user->getTotalLogins(),
                    'quotes_created' => $user->getQuotesCreated(),
                    'orders_processed' => $user->getOrdersProcessed(),
                    'invoices_created' => $user->getInvoicesCreated()
                ],
                'activity_log' => $user->getActivityLog(100),
                'export_date' => date('Y-m-d H:i:s'),
                'export_format_version' => '1.0'
            ];

            $filename = 'user_data_' . $user->username . '_' . date('Y-m-d') . '.json';

            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');

            echo json_encode($userData, JSON_PRETTY_PRINT);
            exit;

        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.export_failed')
            ], 500);
        }
    }

    /**
     * Delete user account (self-deletion)
     */
    public function deleteAccount(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $user = User::find($this->getCurrentUser()['id']);

        if (!$user) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.not_found')
            ], 404);
        }

        // Verify password for account deletion
        if (!isset($this->input['password']) || !password_verify($this->input['password'], $user->password)) {
            $this->json([
                'success' => false,
                'message' => t('users.password_required_for_deletion')
            ], 400);
        }

        // Check if user is the only admin
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->where('status', 'active')->count();
            if ($adminCount <= 1) {
                $this->json([
                    'success' => false,
                    'message' => t('users.cannot_delete_only_admin')
                ], 400);
            }
        }

        // Check if user has created important records
        if ($user->hasCreatedRecords()) {
            // Instead of preventing deletion, anonymize the data
            $user->name = 'Deleted User';
            $user->username = 'deleted_' . $user->id . '_' . time();
            $user->email = 'deleted_' . $user->id . '@deleted.local';
            $user->phone = '';
            $user->bio = '';
            $user->status = 'deleted';
            $user->deleted_at = date('Y-m-d H:i:s');
            
            if ($user->save()) {
                // Logout the user
                session_destroy();
                
                $this->json([
                    'success' => true,
                    'message' => t('users.account_anonymized'),
                    'redirect' => '/'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => t('messages.error.general')
                ], 500);
            }
        } else {
            // Permanently delete if no records exist
            if ($user->delete()) {
                session_destroy();
                
                $this->json([
                    'success' => true,
                    'message' => t('users.account_deleted'),
                    'redirect' => '/'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => t('messages.error.general')
                ], 500);
            }
        }
    }

    /**
     * Generate and download user activity report
     */
    public function downloadReport(): void
    {
        $this->requireAuth();

        $user = User::find($this->getCurrentUser()['id']);

        if (!$user) {
            $this->setFlash('error', t('messages.error.not_found'));
            $this->redirect('/dashboard');
        }

        $reportType = $this->input['type'] ?? 'activity';
        $format = $this->input['format'] ?? 'pdf';

        try {
            switch ($reportType) {
                case 'activity':
                    $data = $user->getActivityLog(1000);
                    $filename = 'user_activity_' . $user->username . '_' . date('Y-m-d');
                    break;
                    
                case 'statistics':
                    $data = [
                        'user' => $user->toArray(),
                        'stats' => [
                            'total_logins' => $user->getTotalLogins(),
                            'quotes_created' => $user->getQuotesCreated(),
                            'orders_processed' => $user->getOrdersProcessed(),
                            'invoices_created' => $user->getInvoicesCreated()
                        ]
                    ];
                    $filename = 'user_statistics_' . $user->username . '_' . date('Y-m-d');
                    break;
                    
                default:
                    $this->setFlash('error', t('messages.error.invalid_report_type'));
                    $this->back();
            }

            if ($format === 'pdf') {
                $this->generatePDFReport($data, $filename, $reportType);
            } else {
                $this->generateCSVReport($data, $filename, $reportType);
            }

        } catch (\Exception $e) {
            $this->setFlash('error', t('messages.error.report_generation_failed'));
            $this->back();
        }
    }

    /**
     * Generate PDF report
     */
    private function generatePDFReport($data, $filename, $type): void
    {
        // This would use a PDF library like TCPDF or mPDF
        // For now, we'll create a simple HTML-to-PDF conversion
        
        ob_start();
        include APP_PATH . '/views/profile/reports/' . $type . '_pdf.php';
        $html = ob_get_clean();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
        
        // Simple PDF generation (in production, use proper PDF library)
        echo $html;
        exit;
    }

    /**
     * Generate CSV report
     */
    private function generateCSVReport($data, $filename, $type): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        if ($type === 'activity') {
            fputcsv($output, ['Date', 'Action', 'Details', 'IP Address']);
            foreach ($data as $activity) {
                fputcsv($output, [
                    $activity['created_at'],
                    $activity['action'],
                    $activity['details'],
                    $activity['ip_address'] ?? ''
                ]);
            }
        } elseif ($type === 'statistics') {
            fputcsv($output, ['Metric', 'Value']);
            foreach ($data['stats'] as $key => $value) {
                fputcsv($output, [ucfirst(str_replace('_', ' ', $key)), $value]);
            }
        }
        
        fclose($output);
        exit;
    }
}