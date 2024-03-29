<?php

declare(strict_types=1);

namespace Solido\TestUtils\Symfony;

use Solido\TestUtils\Functional\Request as BaseRequest;
use Solido\TestUtils\Functional\Response;
use Solido\TestUtils\HttpTestCaseInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

use function Safe\fclose;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\tempnam;
use function sys_get_temp_dir;

class Request extends BaseRequest
{
    private Response $response;

    public function __construct(private readonly HttpTestCaseInterface $testCase)
    {
        parent::__construct();
    }

    public function __destruct()
    {
        if (isset($this->response)) {
            return;
        }

        $this->expectResponse()->shouldHaveSuccessStatus()->check();
    }

    public function expectResponse(): Response
    {
        return $this->response ?? ($this->response = new Response(fn () => $this->perform()));
    }

    protected function perform(): HttpResponse
    {
        $files = [];
        foreach ($this->files as $name => $file) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'upload_' . $name);
            $handle = fopen($tmpFile, 'wb+');
            fwrite($handle, $file->getStream()->getContents());
            fclose($handle);

            $files[] = new UploadedFile($tmpFile, $file->getClientFilename() ?? 'file', $file->getClientMediaType(), $file->getError(), true);
        }

        return $this->testCase::request($this->path, $this->method, $this->content, $this->headers, $files);
    }
}
