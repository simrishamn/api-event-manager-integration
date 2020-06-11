<?php

namespace EventManagerIntegration\Helper;

class IntermediateImagesCleaner
{
    use \EventManagerIntegration\LoggerSupport;

    public function cleanMunicipioGeneratedIntermediateImages($filePath)
    {
        if (empty($filePath)) {
            $this->error("Got empty filePath when trying to clean intermediate images");
            return;
        }

        $this->log("Cleaning intermediate files for path: <{$filePath}>");
        $baseFileName = wp_basename($filePath);
        $baseDir = dirname($filePath);
        $extension = wp_check_filetype($baseFileName)["ext"];
        $baseFileNamePrefix = str_replace(".{$extension}", "", $baseFileName);
        if ($extension === false) {
            $this->error("Tried to clean old intermediate images but got no extension for <{$filePath}>");
            return;
        }

        $attachments = glob("{$baseDir}/*");
        if ($attachments === false) {
            $this->error("Failed to list files when cleaning old intermediate images. File path: <{$filePath}>");
            return;
        }

        $count = 0;
        foreach($attachments as $attachment) {
            $attachmentFileName = wp_basename($attachment);
            if (preg_match("/^$baseFileNamePrefix-\d+x\d+\.{$extension}$/", $attachmentFileName)) {
                wp_delete_file($attachment);
                $count++;
            }
        }
        $this->log("Cleared $count intermediate files from path: <{$filePath}>");
    }
}
