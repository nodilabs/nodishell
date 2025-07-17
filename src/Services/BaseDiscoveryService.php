<?php

namespace NodiLabs\NodiShell\Services;

class BaseDiscoveryService
{
    public function getNamespaceFromFile($filePath)
    {
        if (! file_exists($filePath)) {
            throw new Exception('File not found');
        }

        $content = file_get_contents($filePath);

        // Regular expression to match the namespace declaration
        if (preg_match('/namespace\s+([^\s;]+);/m', $content, $matches)) {
            return $matches[1];
        } else {
            throw new Exception('No namespace found in the file');
        }
    }
}
