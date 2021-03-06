<?php

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Response;

class DownloadFileResponse extends Response
{
    /**
     * @param mixed $filename
     * @param mixed $fileContent
     */
    public function __construct($filename, $fileContent)
    {
        parent::__construct($fileContent);

        $this->headers->set('Content-type', 'text/html');
        $this->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
    }
}
