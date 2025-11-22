<?php
// FILE: /app/controllers/StoreController.php

/**
 * SplashMarket - Store Settings Controller
 *
 * Manages tenant/store settings
 * PHP 7.0+ compatible
 */

class StoreController extends Controller
{
    private $tenantModel;

    public function __construct()
    {
        parent::__construct();
        $this->tenantModel = new Tenant();
    }

    /**
     * Show store settings
     */
    public function settings()
    {
        $this->requireRole(['tenant_admin']);
        $tenantId = $this->getTenantId();

        $tenant = $this->tenantModel->findById($tenantId);

        $this->render('store/settings', [
            'tenant' => $tenant
        ]);
    }

    /**
     * Update store settings
     */
    public function updateSettings()
    {
        $this->requireRole(['tenant_admin']);
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        // Validate input
        $validator = new Validator($_POST);
        $validator->required('store_name')
                 ->required('contact_email')->email('contact_email');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/store/settings');
        }

        try {
            $updateData = [
                'store_name' => $this->post('store_name'),
                'store_description' => $this->post('store_description'),
                'contact_email' => $this->post('contact_email'),
                'contact_phone' => $this->post('contact_phone'),
                'address' => $this->post('address'),
                'city' => $this->post('city'),
                'state' => $this->post('state'),
                'country' => $this->post('country'),
                'postal_code' => $this->post('postal_code'),
                'currency' => $this->post('currency', 'USD'),
                'theme_color' => $this->post('theme_color'),
                'meta_title' => $this->post('meta_title'),
                'meta_description' => $this->post('meta_description'),
                'footer_text' => $this->post('footer_text'),
                'subdomain' => $this->post('subdomain')
            ];

            // Handle logo upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $logoPath = uploadFile($_FILES['logo'], 'stores');
                $updateData['logo'] = $logoPath;
            }

            // Handle banner upload
            if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
                $bannerPath = uploadFile($_FILES['banner_image'], 'stores');
                $updateData['banner_image'] = $bannerPath;
            }

            $this->tenantModel->updateSettings($tenantId, $updateData);

            Session::setFlash('success', 'Store settings updated successfully');
        } catch (Exception $e) {
            error_log('Store settings update error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to update store settings');
        }

        $this->redirect('/store/settings');
    }

    /**
     * Show API key
     */
    public function apiKey()
    {
        $this->requireRole(['tenant_admin']);
        $tenantId = $this->getTenantId();

        $tenant = $this->tenantModel->findById($tenantId);

        $this->render('store/api-key', [
            'tenant' => $tenant
        ]);
    }

    /**
     * Regenerate API key
     */
    public function regenerateApiKey()
    {
        $this->requireRole(['tenant_admin']);
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        try {
            $newKey = $this->tenantModel->regenerateApiKey($tenantId);
            Session::setFlash('success', 'API key regenerated successfully');
        } catch (Exception $e) {
            error_log('API key regeneration error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to regenerate API key');
        }

        $this->redirect('/store/api-key');
    }
}
