<?php

namespace MondayCloneClient\Features;

use MondayCloneClient\Core\HttpService;

class AdminRolesSettings
{
    const ROLES_FILE_DIR_PATH = WP_PLUGIN_DIR . '/monday-client-data/';
    const ROLES_FILE_PATH = WP_PLUGIN_DIR . '/monday-client-data/roles.json';

    private HttpService $host_http_service;

    public function __construct(HttpService $host_http_service)
    {
        $this->host_http_service = $host_http_service;

        add_action('admin_menu', [$this, 'add_roles_page'], 11);
        add_action('admin_enqueue_scripts', [$this, 'add_admin_settings_styles']);
        add_action('admin_post_monday_add_new_role', [$this, 'add_new_role'], 10, 3);
        add_action('admin_post_save_roles', [$this, 'save_roles'], 10, 3);
    }

    public function add_admin_settings_styles()
    {
        wp_enqueue_style('wpcs-admin-styles', MONDAY_PLUGIN_DIR_URI . '/assets/style.css', null, PluginBootstrap::PLUGIN_VERSION);
        wp_enqueue_script('wpcs-admin-scripts', MONDAY_PLUGIN_DIR_URI . '/assets/scripts.js', null, PluginBootstrap::PLUGIN_VERSION);
    }

    public function add_roles_page()
    {
        if (get_option(PluginBootstrap::EXTERNAL_ID, '') !== '') {
            return;
        }

        add_submenu_page(
            'wpcs-admin-tenant',
            'Roles',
            'Roles',
            'manage_options',
            'wpcs-plugins-roles',
            [$this, 'render_roles_page'],
            10
        );
    }

    public function render_roles_page()
    {
        $wp_plugins = get_plugins();
        if (isset($wp_plugins[PluginBootstrap::PLUGIN_NAME])) {
            unset($wp_plugins[PluginBootstrap::PLUGIN_NAME]);
        }

        $this->ensure_roles_file_exists();

        $roles = json_decode(file_get_contents(self::ROLES_FILE_PATH), true);

        ?>
        <h1>Roles</h1>

        <form action="<?= admin_url('admin-post.php') ?>" method="post">
            <?php wp_nonce_field('monday_add_new_role', 'add_new_role_nonce'); ?>
            <input type="hidden" name="action" value="monday_add_new_role">
            <input name="role" type='text' placeholder='New role name'/>
            <button type="submit" class='button-secondary'>Add new role</button>
        </form>

        <form action="<?= admin_url('admin-post.php') ?>" method="post" id="manage_plugins_roles">
            <?php wp_nonce_field('save_roles', 'save_roles_nonce'); ?>
            <input type="hidden" name="action" value="save_roles">

            <div class="wpcs roles-container">
                <?php foreach ($roles as $role_name => $role_data): ?>
                    <div id='wpcs-role-<?= $role_name ?>' class='wpcs single-role-container'>
                        <input type='hidden' name='delete_roles[<?= $role_name ?>]' value="0"/>
                        <h2><?= $role_data['title'] ?></h2>
                        <ul>
                            <?php foreach ($wp_plugins as $plugin_name => $plugin) : ?>
                                <li>
                                    <input
                                        <?= in_array($plugin_name, $role_data['plugins']) ? 'checked' : '' ?>
                                            type='checkbox'
                                            name='roles[<?= $role_name ?>][<?= $plugin_name ?>]'/>
                                    <?= $plugin['Name'] ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class='button-secondary wpcs-mark-role-delete'
                                data-target-role='<?= $role_name ?>'>Mark
                            Delete Role
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type='submit' class='button-primary'>Save</button>
        </form>

        <?php
    }

    private function ensure_roles_file_exists(): void
    {
        if (!file_exists(self::ROLES_FILE_PATH)) {
            mkdir(self::ROLES_FILE_DIR_PATH, 0755, true);
        }

        if (!file_exists(self::ROLES_FILE_PATH)) {
            file_put_contents(self::ROLES_FILE_PATH, "{}");
        }
    }

    public function add_new_role()
    {
        if (!wp_verify_nonce($_POST['add_new_role_nonce'], 'monday_add_new_role')) {
            wp_redirect(wp_get_referer());
            return;
        }

        $this->ensure_roles_file_exists();
        $data = json_decode(file_get_contents(self::ROLES_FILE_PATH), true);

        $new_role_name = sanitize_text_field($_POST['role']);
        $role_slug = sanitize_title_with_dashes($new_role_name);
        $data[$role_slug] = [
            'title' => $new_role_name,
            'plugins' => []
        ];

        $encoded_data = json_encode($data);
        file_put_contents(self::ROLES_FILE_PATH, $encoded_data);

        $this->update_host_roles();

        wp_redirect(wp_get_referer());
    }

    private function update_host_roles()
    {
        try {
            $this->host_http_service->get('/user-role-plan/update');
        } catch (\Exception $exception) {

        }
    }

    public function save_roles()
    {
        if (!wp_verify_nonce($_POST['save_roles_nonce'], 'save_roles') || !isset($_POST['roles'])) {
            wp_redirect(wp_get_referer());
            return;
        }

        $this->ensure_roles_file_exists();

        $data = json_decode(file_get_contents(self::ROLES_FILE_PATH), true);

        $roles_plugins = $_POST['roles'];


        // Remove deleted roles
        $deleted_roles = array_keys(array_filter($_POST['delete_roles'], fn($item) => $item === "1"));
        foreach ($deleted_roles as $role) {
            unset($data[$role]);
            unset($roles_plugins[$role]);
        }

        // Set new rules
        foreach ($roles_plugins as $role_slug => $plugins) {
            $activated_plugins_files = array_keys($plugins);
            $filtered_activated_plugins_files = array_filter($activated_plugins_files, function ($plugin_file) {
                return file_exists(WP_PLUGIN_DIR . '/' . $plugin_file);
            });
            $data[$role_slug] = [
                'title' => $data[$role_slug]['title'],
                'plugins' => $filtered_activated_plugins_files
            ];
        }

        $encoded_data = json_encode($data);
        file_put_contents(self::ROLES_FILE_PATH, $encoded_data);

        $this->update_host_roles();

        wp_redirect(wp_get_referer());
    }
}