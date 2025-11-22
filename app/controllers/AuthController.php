<?php
// FILE: /app/controllers/AuthController.php

/**
 * SplashMarket - Authentication Controller
 *
 * Handles user login, logout, and registration
 * PHP 7.0+ compatible
 */

class AuthController extends Controller
{
    /**
     * Show login page
     */
    public function showLogin()
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/login', [], null);
    }

    /**
     * Handle login submission
     */
    public function login()
    {
        $this->requireCsrf();

        $email = $this->post('email');
        $password = $this->post('password');

        // Validate input
        $validator = new Validator($_POST);
        $validator->required('email')->email('email')
                 ->required('password');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/login');
        }

        // Attempt login
        if (Auth::attempt($email, $password)) {
            Session::setFlash('success', 'Welcome back!');
            $this->redirect('/dashboard');
        } else {
            Session::setFlash('error', 'Invalid email or password');
            $this->redirect('/login');
        }
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        Auth::logout();
        Session::setFlash('success', 'You have been logged out');
        $this->redirect('/login');
    }

    /**
     * Show registration page (for platform admin to create tenants)
     */
    public function showRegister()
    {
        $this->render('auth/register', [], null);
    }

    /**
     * Handle registration submission
     */
    public function register()
    {
        $this->requireCsrf();

        // Validate input
        $validator = new Validator($_POST);
        $validator->required('name')
                 ->required('email')->email('email')
                 ->required('password')->min('password', 6)
                 ->required('store_name');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/register');
        }

        // Check if email exists
        $userModel = new User();
        if ($userModel->emailExists($this->post('email'))) {
            Session::setFlash('error', 'Email already exists');
            $this->redirect('/register');
        }

        try {
            // Create tenant
            $tenantModel = new Tenant();
            $tenantId = $tenantModel->create([
                'store_name' => $this->post('store_name'),
                'contact_email' => $this->post('email'),
                'subdomain' => strtolower(preg_replace('/[^a-z0-9]/', '', $this->post('store_name')))
            ]);

            // Create tenant admin user
            $userId = $userModel->create([
                'tenant_id' => $tenantId,
                'name' => $this->post('name'),
                'email' => $this->post('email'),
                'password' => $this->post('password'),
                'role' => 'tenant_admin'
            ]);

            // Create default subscription (free plan)
            $planModel = new Plan();
            $freePlan = $planModel->findBySlug('free');

            if ($freePlan) {
                $subscriptionModel = new Subscription();
                $subscriptionModel->create([
                    'tenant_id' => $tenantId,
                    'plan_id' => $freePlan['id'],
                    'start_date' => date('Y-m-d H:i:s'),
                    'status' => 'active'
                ]);
            }

            Session::setFlash('success', 'Account created! Please login.');
            $this->redirect('/login');

        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            Session::setFlash('error', 'An error occurred during registration');
            $this->redirect('/register');
        }
    }
}
