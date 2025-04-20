<?php
/**
 * Page class for rendering HTML templates
 */
class Page {
    private $template;

    /**
     * Constructor - stores template path
     * 
     * @param string $template Path to template file
     */
    public function __construct($template) {
        $this->template = $template;
    }

    /**
     * Render page by replacing placeholders in template with data
     * 
     * @param array $data Associative array of data to inject into template
     * @return string Rendered HTML
     */
    public function Render($data) {
        // Read template file
        $content = file_get_contents($this->template);
        
        // Replace placeholders with data
        if (!empty($data) && is_array($data)) {
            foreach ($data as $key => $value) {
                $content = str_replace('{{' . $key . '}}', htmlspecialchars($value), $content);
            }
        }
        
        return $content;
    }
}