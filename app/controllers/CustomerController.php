<?php
// FILE: /app/controllers/CustomerController.php

/**
 * SplashMarket - Customer Controller
 *
 * Manages customer accounts
 * PHP 7.0+ compatible
 */

class CustomerController extends Controller
{
    private $customerModel;
    private $orderModel;

    public function __construct()
    {
        parent::__construct();
        $this->customerModel = new Customer();
        $this->orderModel = new Order();
    }

    /**
     * List all customers
     */
    public function index()
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $page = $this->get('page', 1);
        $search = $this->get('search');

        if ($search) {
            $customers = [
                'data' => $this->customerModel->search($tenantId, $search),
                'total' => count($this->customerModel->search($tenantId, $search)),
                'page' => 1,
                'perPage' => 50,
                'totalPages' => 1
            ];
        } else {
            $customers = $this->customerModel->getByTenant($tenantId, $page, 20);
        }

        $this->render('customers/index', [
            'customers' => $customers['data'],
            'pagination' => $customers,
            'search' => $search
        ]);
    }

    /**
     * View customer details
     */
    public function view($customerId)
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $customer = $this->customerModel->getWithStats($customerId);

        if (!$customer || $customer['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Customer not found');
            $this->redirect('/customers');
        }

        // Get customer orders
        $orders = $this->orderModel->getByCustomer($customerId, 10);

        // Get customer addresses
        $addressModel = new Address();
        $addresses = $addressModel->getByCustomer($customerId);

        $this->render('customers/view', [
            'customer' => $customer,
            'orders' => $orders,
            'addresses' => $addresses
        ]);
    }

    /**
     * Show create customer form
     */
    public function create()
    {
        $this->requireAuth();
        $this->render('customers/create');
    }

    /**
     * Store new customer
     */
    public function store()
    {
        $this->requireAuth();
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        // Validate input
        $validator = new Validator($_POST);
        $validator->required('first_name')
                 ->required('last_name')
                 ->required('email')->email('email');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/customers/create');
        }

        // Check if email exists
        if ($this->customerModel->emailExists($this->post('email'), $tenantId)) {
            Session::setFlash('error', 'Email already exists');
            $this->redirect('/customers/create');
        }

        try {
            $customerId = $this->customerModel->create([
                'tenant_id' => $tenantId,
                'first_name' => $this->post('first_name'),
                'last_name' => $this->post('last_name'),
                'email' => $this->post('email'),
                'phone' => $this->post('phone')
            ]);

            Session::setFlash('success', 'Customer created successfully');
            $this->redirect('/customers/' . $customerId);

        } catch (Exception $e) {
            error_log('Customer creation error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to create customer');
            $this->redirect('/customers/create');
        }
    }

    /**
     * Show edit customer form
     */
    public function edit($customerId)
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $customer = $this->customerModel->findById($customerId);

        if (!$customer || $customer['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Customer not found');
            $this->redirect('/customers');
        }

        $this->render('customers/edit', [
            'customer' => $customer
        ]);
    }

    /**
     * Update customer
     */
    public function update($customerId)
    {
        $this->requireAuth();
        $this->requireCsrf();

        $tenantId = $this->getTenantId();

        $customer = $this->customerModel->findById($customerId);

        if (!$customer || $customer['tenant_id'] != $tenantId) {
            Session::setFlash('error', 'Customer not found');
            $this->redirect('/customers');
        }

        // Validate input
        $validator = new Validator($_POST);
        $validator->required('first_name')
                 ->required('last_name')
                 ->required('email')->email('email');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/customers/' . $customerId . '/edit');
        }

        // Check if email exists (excluding current customer)
        if ($this->customerModel->emailExists($this->post('email'), $tenantId, $customerId)) {
            Session::setFlash('error', 'Email already exists');
            $this->redirect('/customers/' . $customerId . '/edit');
        }

        try {
            $this->customerModel->updateCustomer($customerId, [
                'first_name' => $this->post('first_name'),
                'last_name' => $this->post('last_name'),
                'email' => $this->post('email'),
                'phone' => $this->post('phone')
            ]);

            Session::setFlash('success', 'Customer updated successfully');
            $this->redirect('/customers/' . $customerId);

        } catch (Exception $e) {
            error_log('Customer update error: ' . $e->getMessage());
            Session::setFlash('error', 'Failed to update customer');
            $this->redirect('/customers/' . $customerId . '/edit');
        }
    }
}
