/**
 * Handle offer request submissions
 * Creates a lead and a draft proposal
 * @return void
 */
public function client_submit_offer_request()
{
    // Check if request is POST
    if (!$this->input->post()) {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        return;
    }
    
    // Get post data
    $name = $this->input->post('name');
    $email = $this->input->post('email');
    $phone = $this->input->post('phone');
    $company = $this->input->post('company') ?: '';
    $message = $this->input->post('message') ?: '';
    $address = $this->input->post('address') ?: '';
    $city = $this->input->post('city') ?: '';
    $zip = $this->input->post('zip') ?: '';
    $product_id = $this->input->post('product_id');
    $product_name = $this->input->post('product_name');
    $product_price = $this->input->post('product_price');
    $selected_material = $this->input->post('selected_material') ?: '';
    $selected_glass = $this->input->post('selected_glass') ?: '';
    $selected_height = $this->input->post('selected_height') ?: '';
    $quantity = $this->input->post('quantity') ?: 1;
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($phone) || empty($product_id)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    // Load required models
    $this->load->model('leads_model');
    $this->load->model('proposals_model');
    $this->load->model('staff_model');
    
    // Transaction start - ensure all operations complete or none
    $this->db->trans_start();
    
    // 1. Create lead
    $lead_data = [
        'name' => $name,
        'email' => $email,
        'phonenumber' => $phone,
        'company' => $company,
        'address' => $address,
        'city' => $city,
        'zip' => $zip,
        'description' => "Ponuda zatražena preko web stranice za proizvod: {$product_name}\n\nDetalji proizvoda:\n" . 
                         "- Proizvod: {$product_name}\n" .
                         "- Količina: {$quantity}\n" .
                         ($selected_material ? "- Materijal: {$selected_material}\n" : '') .
                         ($selected_glass ? "- Staklo: {$selected_glass}\n" : '') .
                         ($selected_height ? "- Visina: {$selected_height}\n" : '') .
                         "\nDodatna poruka kupca:\n{$message}",
        'status' => 1, // Open - adjust based on your lead status IDs
        'source' => 2, // Website - adjust based on your source IDs
        'assigned' => 0, // Will be auto-assigned to the first admin
        'addedfrom' => 0, // System
        'is_public' => 1,
        'dateadded' => date('Y-m-d H:i:s')
    ];
    
    // Get the first admin for lead assignment
    $admins = $this->staff_model->get('', ['admin' => 1, 'active' => 1]);
    if (count($admins) > 0) {
        $lead_data['assigned'] = $admins[0]['staffid'];
    }
    
    // Insert lead
    $lead_id = $this->leads_model->add($lead_data);
    
    if (!$lead_id) {
        $this->db->trans_rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to create lead']);
        return;
    }
    
    // 2. Create draft proposal
    // Get the product details
    $this->load->model('products_model');
    $product = $this->products_model->get($product_id);
    
    // Create proposal data
    $total = floatval($product_price) * intval($quantity);
    
    $proposal_data = [
        'subject' => "Ponuda za {$product_name}",
        'rel_id' => $lead_id,
        'rel_type' => 'lead',
        'proposal_to' => $name,
        'address' => $address,
        'city' => $city,
        'zip' => $zip,
        'country' => 0, // Set default country if needed
        'phone' => $phone,
        'email' => $email,
        'status' => 6, // Draft status (6 = Draft in most installations)
        'assigned' => $lead_data['assigned'],
        'date' => date('Y-m-d'),
        'open_till' => date('Y-m-d', strtotime('+30 days')),
        'currency' => get_base_currency()->id,
        'datecreated' => date('Y-m-d H:i:s'),
        'addedfrom' => 0, // System
        'hash' => app_generate_hash(),
        'content' => 'Ponuda za naručene proizvode'
    ];
    
    // Add items to proposal
    $product_description = $product_name;
    if ($selected_material || $selected_glass || $selected_height) {
        $product_description .= " (";
        if ($selected_material) $product_description .= "Materijal: {$selected_material}, ";
        if ($selected_glass) $product_description .= "Staklo: {$selected_glass}, ";
        if ($selected_height) $product_description .= "Visina: {$selected_height}, ";
        $product_description = rtrim($product_description, ", ") . ")";
    }
    
    // Add item
    $proposal_data['newitems'][0] = [
        'description' => $product_description,
        'long_description' => $message,
        'qty' => $quantity,
        'unit' => '',
        'rate' => $product_price,
        'order' => 1
    ];
    
    // Create proposal
    $proposal_id = $this->proposals_model->add($proposal_data);
    
    if (!$proposal_id) {
        $this->db->trans_rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to create proposal']);
        return;
    }
    
    // Commit transaction
    $this->db->trans_commit();
    
    // Log activity
    log_activity('New offer request from website - Lead ID: ' . $lead_id . ', Proposal ID: ' . $proposal_id);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Offer request submitted successfully',
        'lead_id' => $lead_id,
        'proposal_id' => $proposal_id
    ]);
} 