<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Campaigns_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
               $this->set_charset_utf8mb4();
                 $this->load->model(['leads_model', 'clients_model']);
    }


    /**
     * Set character set for the connection and results to utf8mb4
     */
    private function set_charset_utf8mb4() {
        $this->db->query("SET NAMES utf8mb4");
        $this->db->query("SET character_set_connection=utf8mb4");
        $this->db->query("SET character_set_results=utf8mb4");
        $this->db->query("SET character_set_client=utf8mb4");
    }
    public function save($post_data)
    {
        unset($post_data['image']);
        $post_data['scheduled_send_time']   = isset($post_data['scheduled_send_time']) ? to_sql_date($post_data['scheduled_send_time'], true) : null;
        $post_data['send_now']              = (isset($post_data['send_now']) ? 1 : 0);
        $post_data['select_all']            = (isset($post_data['select_all']) ? 1 : 0);
        $post_data['header_params']         = json_encode($post_data['header_params'] ?? []);
        $post_data['body_params']           = json_encode($post_data['body_params'] ?? []);
        $post_data['footer_params']         = json_encode($post_data['footer_params'] ?? []);

        $rel_ids  = (isset($post_data['lead_ids']) && !empty($post_data['lead_ids'])) ? $post_data['lead_ids'] : ((isset($post_data['contact_ids']) && !empty($post_data['contact_ids'])) ? $post_data['contact_ids'] : '');
        $rel_type = (isset($post_data['lead_ids']) && !empty($post_data['lead_ids'])) ? 'leads' : ((isset($post_data['contact_ids']) && !empty($post_data['contact_ids'])) ? 'contacts' : '');

        unset($post_data['lead_ids'], $post_data['contact_ids']);

        if (1 == $post_data['select_all']) {
            if ('leads' == $post_data['rel_type']) {
                $leads    = $this->leads_model->get();
                $rel_ids  = array_column($leads, 'id');
                $rel_type = 'leads';
            } elseif ('contacts' == $post_data['rel_type']) {
                $contacts = $this->clients_model->get_contacts();
                $rel_ids  = array_column($contacts, 'id');
                $rel_type = 'contacts';
            }
        }

        $insert   = $update   = false;
        $template = get_whatsapp_template($post_data['template_id']);
        if (!empty($post_data['id'])) {
            $update = $this->db->update(db_prefix().'whatsapp_campaigns', $post_data, ['id' => $post_data['id']]);
            if ($update) {
                $this->db->delete(db_prefix().'whatsapp_campaign_data', ['campaign_id' => $post_data['id']]);
                foreach ($rel_ids as $rel_id) {
                    $this->db->insert(db_prefix().'whatsapp_campaign_data', [
                        'campaign_id'       => $post_data['id'],
                        'rel_id'            => $rel_id,
                        'rel_type'          => $rel_type,
                        'header_message'    => $template['header_data_text'],
                        'body_message'      => $template['body_data'],
                        'footer_message'    => $template['footer_data'],
                        'status'            => 1,
                    ]);
                }
            }
        } else {
            $insert = $this->db->insert(db_prefix().'whatsapp_campaigns', $post_data);
            if ($insert) {
                $insert_id = $this->db->insert_id();
                foreach ($rel_ids as $rel_id) {
                    $this->db->insert(db_prefix().'whatsapp_campaign_data', [
                        'campaign_id'       => $insert_id,
                        'rel_id'            => $rel_id,
                        'rel_type'          => $rel_type,
                        'header_message'    => $template['header_data_text'],
                        'body_message'      => $template['body_data'],
                        'footer_message'    => $template['footer_data'],
                        'status'            => 1,
                    ]);
                }
            }
        }

        $campaign_id   = !empty($post_data['id']) ? $post_data['id'] : $insert_id;
        whatsapp_handle_campaign_upload($campaign_id, 'campaign');
        if ($post_data['send_now']) {
            $scheduledData = $this->db
                ->select(db_prefix().'whatsapp_campaigns.*, '.db_prefix().'whatsapp_templates.*, '.db_prefix().'whatsapp_campaign_data.*')
                ->join(db_prefix().'whatsapp_campaigns', db_prefix().'whatsapp_campaigns.id = '.db_prefix().'whatsapp_campaign_data.campaign_id', 'left')
                ->join(db_prefix().'whatsapp_templates', db_prefix().'whatsapp_campaigns.template_id = '.db_prefix().'whatsapp_templates.id', 'left')
                ->where(db_prefix().'whatsapp_campaign_data.status', 1)
                ->where(db_prefix().'whatsapp_campaigns.is_bot', 0)
                ->where(db_prefix().'whatsapp_campaign_data.campaign_id', $campaign_id)
                ->get(db_prefix().'whatsapp_campaign_data')->result_array();

            if (!empty($scheduledData)) {
                $this->load->model('whatsapp_interaction_model');
                $this->whatsapp_interaction_model->send_campaign($scheduledData);
            }
        }

        return [
            'type'      => $insert || $update ? 'success' : 'danger',
            'message'   => $insert ? _l('added_successfully', _l('campaign')) : ($update ? _l('updated_successfully', _l('campaign')) : _l('something_went_wrong')),
            'campaign_id'=> $campaign_id,
        ];
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            return $this->db->select(
                db_prefix().'whatsapp_campaigns.*,'.
                    db_prefix().'whatsapp_templates.*,'.
                    db_prefix().'whatsapp_templates.template_id as tmp_id,'.
                    db_prefix().'whatsapp_templates.header_params_count,'.
                    db_prefix().'whatsapp_templates.body_params_count,'.
                    db_prefix().'whatsapp_templates.footer_params_count,'.
                    'CONCAT("[", GROUP_CONCAT('.db_prefix().'whatsapp_campaign_data.rel_id SEPARATOR ","), "]") as rel_ids,'
            )
                ->join(db_prefix().'whatsapp_templates', db_prefix().'whatsapp_templates.id = '.db_prefix().'whatsapp_campaigns.template_id')
                ->join(db_prefix().'whatsapp_campaign_data', db_prefix().'whatsapp_campaign_data.campaign_id = '.db_prefix().'whatsapp_campaigns.id', 'LEFT')
                ->get_where(db_prefix().'whatsapp_campaigns', [db_prefix().'whatsapp_campaigns.id' => $id])->row_array();
        }

        return $this->db->get(db_prefix().'whatsapp_campaigns')->result_array();
    }

    public function delete($id)
    {
        $campaign = $this->get($id);
        $delete = $this->db->delete(db_prefix().'whatsapp_campaigns', ['id' => $id]);

        if ($delete) {
            $this->db->delete(db_prefix().'whatsapp_campaign_data', ['campaign_id' => $id]);

            $path = WHATSAPP_MODULE_UPLOAD_FOLDER . '/campaign/' . $campaign['filename'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        return [
            'message' => $delete ? _l('deleted', _l('campaign')) : _l('something_went_wrong'),
        ];
    }

    public function pause_resume_campaign($id)
    {
        $campaign = $this->get($id);
        $update   = $this->db->update(db_prefix().'whatsapp_campaigns', ['pause_campaign' => (1 == $campaign['pause_campaign'] ? 0 : 1)], ['id' => $id]);

        return ['message' => $update && 1 == $campaign['pause_campaign'] ? _l('campaign_resumed') : _l('campaign_paused')];
    }

public function delete_campaign_files($id)
{
    $campaign = $this->get($id);
    $type = ($campaign['is_bot'] == 1) ? 'template' : 'campaign';

    // Update database to set filename to NULL
    $update = $this->db->update(db_prefix() . 'whatsapp_campaigns', ['filename' => NULL], ['id' => $id]);
    
    // Build file path
    $path = WHATSAPP_MODULE_UPLOAD_FOLDER . '/' . $type . '/' . $campaign['filename'];

    // Check if file exists before attempting to delete
    if ($update && !empty($campaign['filename']) && file_exists($path)) {
        if (unlink($path)) {
            $message = _l('image_deleted_successfully');
        } else {
            $message = _l('failed_to_delete_image');
        }
    } else {
        // File does not exist or update failed
        $message = ($update) ? _l('file_does_not_exist') : _l('something_went_wrong');
    }

    // Return response
    return [
        'message' => $message,
        'url'     => ($campaign['is_bot'] == 1) ? admin_url('whatsapp/bots/bot/template/' . $id) : admin_url('whatsapp/campaigns/campaign/' . $id),
    ];
}

}
