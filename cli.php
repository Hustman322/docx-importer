<?php

class WP_Docx_Import_CLI
{
    /**
     * wp docx import file.docx [--category=<id>] [--tags=<tags>]
     */
    public function import($args, $assoc_args)
    {
        $file = $args[0];
        $this->import_single_file($file, $assoc_args);
    }

    /**
     * wp docx import-folder /path/to/folder [--category=<id>] [--tags=<tags>]
     */
    public function import_folder($args, $assoc_args)
    {
        $folder = $args[0];

        if (!is_dir($folder)) {
            WP_CLI::error("Folder not found");
        }

        $files = glob(rtrim($folder, '/') . '/*.docx');

        if (empty($files)) {
            WP_CLI::warning("No DOCX files found");
            return;
        }

        foreach ($files as $file) {
            WP_CLI::log("Processing: $file");

            try {
                $this->import_single_file($file, $assoc_args);
            } catch (Exception $e) {
                WP_CLI::warning("Failed: $file — " . $e->getMessage());
            }
        }

        WP_CLI::success("Done importing " . count($files) . " files");
    }

    /**
     * Core import logic (1 DOCX = 1 post)
     */
    private function import_single_file($file, $extra_args = [])
    {
        if (!file_exists($file)) {
            throw new Exception("File not found");
        }

        $html = $this->convert_with_mammoth($file);

        $title = $this->extract_title($html);

        $html = $this->remove_first_h1($html);

        $post_data = [
            'post_title'   => $title,
            'post_content' => $html,
            'post_status'  => 'draft',
            'post_type'    => 'post'
        ];

        if (!empty($extra_args['category'])) {
            $post_data['post_category'] = [ (int) $extra_args['category'] ];
        }

        if (!empty($extra_args['tags'])) {
            $post_data['tags_input'] = $extra_args['tags'];
        }

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            throw new Exception($post_id->get_error_message());
        }

        WP_CLI::log("Created post ID: $post_id");
    }

    /**
     * Remove first H1 to avoid duplication in content
     */
    private function remove_first_h1($html)
    {
        return preg_replace('/<h1[^>]*>.*?<\/h1>/i', '', $html, 1);
    }

    /**
     * Convert DOCX → HTML using Mammoth (Node.js)
     */
    private function convert_with_mammoth($file)
    {
        $script = escapeshellarg(__DIR__ . "/convert.js");
        $input  = escapeshellarg($file);

        $cmd = "node $script $input";

        return shell_exec($cmd);
    }

    /**
     * Extract first H1 as post title
     */
    private function extract_title($html)
    {
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/i', $html, $matches)) {
            return wp_strip_all_tags($matches[1]);
        }

        return 'unknown title';
    }
}

WP_CLI::add_command('docx', 'WP_Docx_Import_CLI');