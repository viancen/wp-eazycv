<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://eazycv.nl
 * @since      1.0.0
 *
 * @package    wp_eazycv
 * @subpackage wp_eazycv/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    wp_eazycv
 * @subpackage wp_eazycv/public
 * @author     Inforvision BV <info@inforvision.nl>
 */
class Wp_EazyCV_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $wp_eazycv The ID of this plugin.
     */
    private $wp_eazycv;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * The api connection of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    public $api = null;
    public $job = null;
    public $eazycvPage = null;
    public $eazyCvLicence = null;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $wp_eazycv The name of the plugin.
     * @param string $version The version of this plugin.
     *
     * @since    1.0.0
     *
     */
    public function __construct($wp_eazycv, $version)
    {

        $this->wp_eazycv = $wp_eazycv;
        $this->version = $version;


        $this->setup_api();

    }


    /**
     * EazyCV api connection
     *
     * @return bool
     * @throws Eazycv_Error
     */
    private function setup_api()
    {
        //check connection
        $optionServiceUrl = get_option('wp_eazycv_service_url');
        $optionKey = get_option('wp_eazycv_apikey');
        $optionInstance = get_option('wp_eazycv_instance');
        if (!empty($optionKey) && !empty($optionInstance)) {
            $this->api = new EazycvClient($optionKey, $optionInstance, $optionServiceUrl);

            try {
                $this->eazyCvLicence = $this->api->get('licence');
            } catch (Exception $wEx) {
                return false;
            }
        }
    }

    /*
     * ggreat magic
     */
    private function performApply($postData)
    {


        if (!Wp_EazyCV_DEBUG) {

            if (!isset($postData['grepact'])) {
                return 'Error';
            }

            $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . get_option('wp_eazycv_google_api_secret') . '&response=' . $postData['grepact'] . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
            $resp = json_decode($response);

            if (!$resp->success) {
                return 'Error-Captcha';
            }
        }

        $attachments = [
            'attachment1',
            'attachment2',
            'attachment3',
        ];
        foreach ($attachments as $attachmentName) {
            if (isset($postData['files'][$attachmentName]) && !empty($postData['files'][$attachmentName]['tmp_name'])) {
                $file = $postData['files'][$attachmentName];

                $source = file_get_contents($postData['files'][$attachmentName]['tmp_name']);

                $imageFileType = strtolower(pathinfo($postData['files'][$attachmentName]['name'], PATHINFO_EXTENSION));

                if (in_array($imageFileType, [
                    'txt',
                    'pdf',
                    'doc',
                    'png',
                    'gif',
                    'jpg',
                    'docx'
                ])
                ) {
                    $mimeType = [
                        'txt' => 'application/text',
                        'pdf' => 'application/pdf',
                        'doc' => 'application/msword',
                        'png' => 'image/png',
                        'jpg' => 'image/jpg',
                        'gif' => 'image/gif',
                        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ];
                    //resume addon
                    $postData['attachments'][] = [
                        'filename' => $postData['files'][$attachmentName]['name'],
                        'mime_type' => $mimeType[$imageFileType],
                        'content' => base64_encode($source)
                    ];
                }

                unset($postData['files'][$attachmentName]);
            }
        }

        if (isset($postData['files']['cv_document']) && !empty($postData['files']['cv_document']['tmp_name'])) {

            $file = $postData['files']['cv_document'];

            $source = file_get_contents($postData['files']['cv_document']['tmp_name']);

            $imageFileType = strtolower(pathinfo($postData['files']['cv_document']['name'], PATHINFO_EXTENSION));

            if (in_array($imageFileType, [
                'txt',
                'pdf',
                'doc',
                'docx'
            ])
            ) {
                $mimeType = [
                    'txt' => 'application/text',
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ];
                //resume addon
                $postData['resume'] = [
                    'filename' => $postData['files']['cv_document']['name'],
                    'mime_type' => $mimeType[$imageFileType],
                    'content' => base64_encode($source)
                ];

            }

            unset($postData['files']['cv_document']);
            $postData['useTextkernel'] = false;
        }

        if (isset($postData['files']['cv_document_tk']) && !empty($postData['files']['cv_document_tk']['tmp_name'])) {

            $file = $postData['files']['cv_document_tk'];

            $source = file_get_contents($postData['files']['cv_document_tk']['tmp_name']);

            $imageFileType = strtolower(pathinfo($postData['files']['cv_document_tk']['name'], PATHINFO_EXTENSION));

            if (in_array($imageFileType, [
                'txt',
                'pdf',
                'doc',
                'docx'
            ])
            ) {
                $mimeType = [
                    'txt' => 'application/text',
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ];

                //resume addon
                $postData['resume'] = [
                    'filename' => $postData['files']['cv_document_tk']['name'],
                    'mime_type' => $mimeType[$imageFileType],
                    'content' => base64_encode($source)
                ];

            }

            unset($postData['files']['cv_document_tk']);
            $postData['useTextkernel'] = true;
        }

        if (isset($postData['files']['avatar'])) {

            $file = $postData['files']['avatar'];

            $source = file_get_contents($postData['files']['avatar']['tmp_name']);

            $imageFileType = strtolower(pathinfo($postData['files']['avatar']['name'], PATHINFO_EXTENSION));

            if (in_array($imageFileType, [
                'png',
                'jpg',
                'jpeg'
            ])
            ) {
                $mimeType = [
                    'png' => 'image/png',
                    'jpg' => 'image/jpg',
                    'jpeg' => 'image/jpeg',
                ];
                //resume addon
                $postData['avatar'] = [
                    'filename' => $postData['files']['cv_document_tk']['name'],
                    'mime_type' => $mimeType[$imageFileType],
                    'content' => base64_encode($source)
                ];
            }

            unset($postData['files']['avatar']);
        }

        $userFields = [
            'first_name',
            'last_name',
            'prefix',
            'birth_date',
            'gender',
            'avatar',
            'country'
        ];

        foreach ($userFields as $fieldName) {
            if (isset($postData[$fieldName])) {
                $postData['user'][$fieldName] = $postData[$fieldName];
                unset($postData[$fieldName]);
            }
        }

        if (!isset($postData['user']['gender'])) {
            $postData['user']['gender'] = 'm';
        }

        //$postData['subscription_form_id'] = $this->apply_form;
        unset($postData['files']);
        unset($postData['grepact']);


        try {
            $res = $this->api->post('candidates/signup', $postData);
        } catch (Exception $exception) {
            if (Wp_EazyCV_DEBUG) {
                dump($exception->getMessage());
            }
            return 'Error';
        }

        return 'Oke';
    }

    /**
     * Insert meta
     * @param $metaText
     */
    public function change_page_meta()
    {
        if (!empty($this->job['texts']['meta']['content'])) {
            $metaText = $this->job['texts']['meta']['content'];
        } else {
            $metaText = $this->job['original_functiontitle'];
        };
        ?>
        <!--EazyCV Meta Description Jobs -->
        <meta name="description" content="<?php print $metaText; ?>">
        <!-- /EazyCV Meta Description Jobs -->
        <?php
    }


    public function has_query_var($var)
    {
        global $wp_query;

        return isset($wp_query->query_vars[$var]);
    }

    /**
     * check_custom_pages()
     *
     */
    public function check_custom_pages()
    {


        //sets meta data and pre thingies before rendering the shortcodes on our pages
        if ($this->has_query_var('EazyCVProcess')) {
            $postProcessing = get_query_var('EazyCVProcess', '');

            if (!empty($postProcessing)) {
                if (!empty($_POST)) {
                    $refUrl = $_POST['eazy-url'];
                    unset($_POST['eazy-url']);

                    $newApplication = $_POST;
                    if (!empty($_FILES)) {
                        $newApplication['files'] = $_FILES;
                    }

                    $success = $this->performApply($newApplication);
                    if ($success == 'Error') {
                        echo json_encode(['status' => 'error']);
                    } elseif ($success == 'Error-Captcha') {
                        echo json_encode(['status' => 'captcha']);
                    } else {
                        echo json_encode(['status' => 'success']);
                        //return '<div class="eazy-success">' . $form['success_message'] . '</div><div id="eazycv-success-apply"></div>';
                    }
                    die();
                }
            }
        }

        if ($this->has_query_var('JobID')) {
            $pagename = get_query_var('JobID', '');
            if (!empty($pagename)) {

                $this->eazycvPage = 'job';

                $jobId = explode('-', $pagename);
                $jobId = array_pop($jobId);

                if (intval($jobId) > 1) {
                    $this->job = $this->api->get('jobs/published/' . $jobId);
                    if (empty($this->job['id'])) {
                        wp_redirect('/');
                        exit;
                    }

                    add_filter('pre_get_document_title', array($this, 'change_page_title'), 50, 1);
                    add_action('wp_head', array($this, 'change_page_meta'));

                }
            }
        }

        if ($this->has_query_var('ProjectID')) {
            $pagename = get_query_var('ProjectID', '');
            if (!empty($pagename)) {

                $this->eazycvPage = 'project';

                $jobId = explode('-', $pagename);
                $jobId = array_pop($jobId);

                if (intval($jobId) > 1) {
                    $this->job = $this->api->get('jobs/published/' . $jobId);

                    if (empty($this->job['id'])) {
                        wp_redirect('/');
                        exit;
                    }
                    add_filter('pre_get_document_title', array($this, 'change_page_title'), 50, 1);
                    add_action('wp_head', array($this, 'change_page_meta'));

                }
            }
        }

        if ($this->has_query_var('EazyApplyTo')) {
            //sets meta data and pre thingies before rendering the shortcodes on our pages
            $pagename = get_query_var('EazyApplyTo', '');
            if (!empty($pagename)) {

                $this->eazycvPage = 'apply';
                $jobId = explode('-', $pagename);
                $jobId = array_pop($jobId);

                if (intval($jobId) > 1) {
                    $this->job = $this->api->get('jobs/published/' . $jobId);
                    if (empty($this->job['id'])) {
                        wp_redirect('/');
                        exit;
                    }
                    add_filter('pre_get_document_title', array($this, 'change_page_title'), 50, 1);
                    add_action('wp_head', array($this, 'change_page_meta'));
                }
            }
        }

    }

    /**
     * @return mixed
     */
    public function change_page_title($baseOption = null)
    {

        if (!empty($baseOption)) {
            $getTitle = get_option($baseOption);
        } else {
            if ($this->eazycvPage == 'job') {
                $getTitle = get_option('wp_eazycv_jobpage_title');
            } elseif ($this->eazycvPage == 'project') {
                $getTitle = get_option('wp_eazycv_projectpage_title');
            } elseif ($this->eazycvPage == 'apply') {
                $getTitle = get_option('wp_eazycv_apply_page_title');
            }
        }

        if (empty($this->job['original_functiontitle']) && !empty($this->job['functiontitle'])) {
            $this->job['original_functiontitle'] = $this->job['functiontitle'];
        }

        if (!empty($getTitle)) {
            foreach ($this->job as $key => $val) {
                if (is_string($val)) {
                    $getTitle = str_ireplace('*|' . $key . '|*', $val, $getTitle);
                } elseif (is_array($val)) {
                    foreach ($val as $kkey => $vval) {
                        if (is_string($vval)) {
                            $getTitle = str_ireplace('*|' . $key . '.' . $kkey . '|*', $vval, $getTitle);
                        }
                    }
                }
            }

        } else {
            $getTitle = $this->job['original_functiontitle'];
        }

        return $getTitle;
    }


    public function setup_ajax()
    {

        //both admin and public
        add_action('wp_ajax_eazycv_check_email_address', 'eazycv_check_email_address');
        add_action('wp_ajax_nopriv_eazycv_check_email_address', 'eazycv_check_email_address');

        function eazycv_check_email_address()
        {
            global $wpdb; // this is how you get access to the database

            $stripEmail = filter_var($_POST['email_address'], FILTER_SANITIZE_EMAIL);

            if (filter_var($stripEmail, FILTER_VALIDATE_EMAIL) && (!empty(get_option('wp_eazycv_apikey')) && !empty(get_option('wp_eazycv_instance')))) {

                $api = new EazycvClient(get_option('wp_eazycv_apikey'), get_option('wp_eazycv_instance'), get_option('wp_eazycv_service_url'));
                $result = $api->post('users/check-existing-email-address', [
                    'email' => $stripEmail
                ]);
                if (isset($result['exists']) && intval($result['exists']) == 1) {
                    $jobId = '';
                    if (isset($_POST['job_id'])) {
                        $jobId = intval($_POST['job_id']);
                    }
                    echo json_encode([
                        'error' => true,
                        'message' => __('Het lijkt erop dat je eerder gesolliciteerd hebt met dit e-mailadres. 
					                    <a class="eazycv-error-applied eazycv-link" target="_blank" href="https://' . get_option('wp_eazycv_instance') . '.eazycv.cloud?email=' . urlencode($stripEmail) . '&apply-to=' . $jobId . '">Klik hier om verder te gaan</a>.')
                    ]);
                } else {
                    echo json_encode(['error' => false]);
                }
            } else {
                echo json_encode([
                    'error' => true,
                    'message' => __('Dit is geen geldig e-mailadres. ')
                ]);
            }
            wp_die();
        }
    }

    /**
     * make url work
     */
    public function setup_rewrites()
    {

        add_filter('query_vars', 'add_eazycv_vars', 0, 1);
        function add_eazycv_vars($vars)
        {
            $vars[] = 'EazyCVCheckEmail';
            $vars[] = 'JobID';
            $vars[] = 'ProjectID';
            $vars[] = 'EazyCVSearch';
            $vars[] = 'EazyApplyTo';
            $vars[] = 'EazyCVProcess';

            return $vars;
        }

        add_action('init', 'add_projectid_rule');
        function add_projectid_rule($jobPage)
        {
            $jobPage = get_option('wp_eazycv_projectpage');

            if ($jobPage) {
                add_rewrite_rule(
                    '^' . get_option('wp_eazycv_projectpage') . '/([^/]*)/?',
                    'index.php?pagename=' . get_option('wp_eazycv_projectpage') . '&ProjectID=$matches[1]',
                    'top'
                );
            }
        }

        add_action('init', 'add_jobid_rule');
        function add_jobid_rule($jobPage)
        {
            $jobPage = get_option('wp_eazycv_jobpage');

            if ($jobPage) {
                add_rewrite_rule(
                    '^' . get_option('wp_eazycv_jobpage') . '/([^/]*)/?',
                    'index.php?pagename=' . get_option('wp_eazycv_jobpage') . '&JobID=$matches[1]',
                    'top'
                );
            }
        }


        add_action('init', 'add_apply_rule');
        function add_apply_rule($jobPage)
        {
            $jobPage = get_option('wp_eazycv_apply_page');

            if ($jobPage) {
                add_rewrite_rule(
                    '^' . get_option('wp_eazycv_apply_page') . '/([^/]*)/?',
                    'index.php?pagename=' . get_option('wp_eazycv_apply_page') . '&EazyApplyTo=$matches[1]',
                    'top'
                );
            }
        }

        add_action('init', 'add_jobsearch_rule');
        function add_jobsearch_rule($jobPage)
        {
            $jobPage = get_option('wp_eazycv_jobsearch_page');

            if ($jobPage) {
                add_rewrite_rule(
                    '^' . get_option('wp_eazycv_jobsearch_page') . '/([^/]*)/?',
                    'index.php?pagename=' . get_option('wp_eazycv_jobsearch_page') . '&EazyCVSearch=true',
                    'top'
                );
            }
        }

        add_action('init', 'add_process_apply_rule');
        function add_process_apply_rule()
        {
            add_rewrite_rule(
                'eazycv-process-subscription',
                'index.php?EazyCVProcess=subscription',
                'top'
            );
        }
    }


    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wp_EazyCV_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wp_EazyCV_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style('eazy-font-awesome', plugin_dir_url(__FILE__) . 'css/fa/css/font-awesome.min.css');

        wp_enqueue_style($this->wp_eazycv . '-lighbox', plugin_dir_url(__FILE__) . 'css/wp-eazycv-lightbox.css', array(), $this->version, 'all');
        wp_enqueue_style($this->wp_eazycv, plugin_dir_url(__FILE__) . 'css/wp-eazycv-public.css', array(), $this->version, 'all');

        $customCss = get_option('wp_eazycv_styling');
        if (!empty($customCss)) {
            wp_add_inline_style($this->wp_eazycv, $customCss);
        }

    }

    /**
     * Shortcode Function
     *
     * @param Attributes $atts l|t URL TEXT.
     *
     * @return string
     * @since  1.0.0
     */
    function shortcode_eazycv_job($atts)
    {

        if (!empty($this->api)) {
            try {
                $emolJobView = new Wp_EazyCV_Job($this->job, $this->api, $atts);

                return $emolJobView->render();
            } catch (Exception $x) {

            }
        }

        return 1;
    }

    /**
     * Shortcode Function
     *
     * @param Attributes $atts l|t URL TEXT.
     *
     * @return string
     * @since  1.0.0
     */
    function shortcode_eazycv_job_search($atts)
    {

        if (!empty($this->api)) {
            try {
                $emolJobView = new Wp_EazyCV_Job_Search($this->api, $atts);

                return $emolJobView->render();
            } catch (Exception $x) {

            }
        }

        return 1;
    }

    /**
     * Shortcode Function
     *
     * @param Attributes $atts l|t URL TEXT.
     *
     * @return string
     * @since  1.0.0
     */
    function shortcode_eazycv_apply($atts)
    {

        if (!empty($this->api)) {
            try {
                $emolJobView = new Wp_EazyCV_Apply($this->api, $atts, $this->job);

                return $emolJobView->render();
            } catch (Exception $x) {

            }
        }
        return 1;
    }

    /**
     * add shortcodes for eazy
     */
    public function setup_shortcodes()
    {
        add_shortcode('eazycv_job', array($this, 'shortcode_eazycv_job'));
        add_shortcode('eazycv_apply', array($this, 'shortcode_eazycv_apply'));
        add_shortcode('eazycv_job_search', array($this, 'shortcode_eazycv_job_search'));

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {


        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wp_EazyCV_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wp_EazyCV_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        /**
         * frontend ajax requests.
         */

        wp_enqueue_script($this->wp_eazycv . '-js-lightbox', plugin_dir_url(__FILE__) . 'js/vendor/wp-eazycv-lightbox.js', array('jquery'), $this->version);
        wp_enqueue_script($this->wp_eazycv, plugin_dir_url(__FILE__) . 'js/wp-eazycv-public.js', array('jquery'), $this->version);

        wp_localize_script($this->wp_eazycv, 'eazycv_ajax_object',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
            )
        );

        $customScript = get_option('wp_eazycv_scripting');
        if (!empty($customScript)) {
            wp_add_inline_script($this->wp_eazycv, $customScript);
        }


    }


}
