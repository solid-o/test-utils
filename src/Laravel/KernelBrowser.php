<?php

declare(strict_types=1);

namespace Solido\TestUtils\Laravel;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class KernelBrowser extends HttpKernelBrowser
{
    private bool $hasPerformedRequest = false;
    private bool $reboot = true;
    private bool $catchExceptions = true;

    public function getKernel(): HttpKernelInterface
    {
        return $this->kernel;
    }

    /**
     * Sets whether to catch exceptions when the kernel is handling a request.
     */
    public function catchExceptions(bool $catchExceptions): void
    {
        $this->catchExceptions = $catchExceptions;
        parent::catchExceptions($catchExceptions);
    }

    /**
     * {@inheritdoc}
     */
    protected function doRequest($request): Response
    {
        // avoid shutting down the Kernel if no request has been performed yet
        // WebTestCase::createClient() boots the Kernel but do not handle a request
        if ($this->hasPerformedRequest && $this->reboot) {
            $this->kernel->terminate();
        } else {
            $this->hasPerformedRequest = true;
        }

        $request = Request::createFromBase($request);
        $response = $this->kernel->handle($request, HttpKernelInterface::MAIN_REQUEST, $this->catchExceptions);

        $this->kernel->get(Kernel::class)->terminate($request, $response);
        $this->kernel->terminate();

        return $response;
    }
}
