<?php
/**
*
*/
class VHostGenerator
{
    public $vhost_template;
    public $server_name;
    public $document_root_path;
    public $host_file;
    public $apache_vhost;

    function __construct($config)
    {
        $this->vhost_template       = $config['vhost_template'];
        $this->server_name          = $config['server_name'];
        $this->document_root_path   = $config['document_root_path'];
        $this->apache_vhost         = $config['apache_vhost'];
        $this->host_file            = $config['host_file'];
    }

    public function generate()
    {
        $template       = $this->readFile(dirname(__FILE__).'/'.$this->vhost_template);
        $vhost_conf     = "\n\n".$this->replaceString($template);

        // Append host config to httpd-xampp.conf
        if ($this->appendText($this->apache_vhost, $vhost_conf)) {
            // Append host name i.e laravel.dev
            // to /etc/host
            $content = "\n".'127.0.0.1 '."\t".$this->server_name;
            if ($this->appendText($this->host_file, $content)) {
                echo "Virtual Host created successfully\n";
                return true;
            }else{
                echo "Failed to create Virtual Host\n";
                return false;
            }
        }else{
            echo "Failed to create Virtual Host\n";
            return false;
        }
    }

    private function readFile($file_path)
    {
        try {
            $fhandle = fopen($file_path, 'r');
            $content = fread($fhandle, filesize($file_path));
            fclose($fhandle);
            return $content;
        } catch (Exception $exc) {
            echo $exc->getMessage().'\n';
            echo $exc->getTraceAsString();
            return false;
        }
    }

    private function replaceString($templateContent)
    {
        $templateServer         = str_replace('{{SERVER_NAME}}', $this->server_name, $templateContent);
        $document_root_path     = $this->convertSlash($this->document_root_path);
        $result                 = str_replace('{{DOCUMENT_ROOT}}', $document_root_path, $templateServer);
        return $result;
    }

    private function convertSlash($document_root_path)
    {
        $new_path = str_replace('\\', '/', $document_root_path);
        return $new_path;
    }

    private function appendText($file_path, $content)
    {
        try {
            $fhandle = fopen($file_path, 'a+');
            fwrite($fhandle, $content);
            fclose($fhandle);
            return true;
        } catch (Exception $exc) {
            echo $exc->getMessage().'\n';
            echo $exc->getTraceAsString();
            return false;
        }
    }

}